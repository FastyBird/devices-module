import { Item } from '@vuex-orm/core'
import { RpCallResponse } from '@fastybird/vue-wamp-v1'
import * as exchangeEntitySchema
  from '@fastybird/metadata/resources/schemas/modules/devices-module/entity.channel.configuration.json'
import {
  ModuleOrigin,
  ChannelConfigurationEntity as ExchangeEntity,
  DevicesModuleRoutes as RoutingKeys,
  ActionRoutes,
  DataType,
} from '@fastybird/metadata'

import {
  ActionTree,
  MutationTree,
} from 'vuex'
import Jsona from 'jsona'
import Ajv from 'ajv'
import { AxiosResponse } from 'axios'
import get from 'lodash/get'
import uniq from 'lodash/uniq'

import Device from '@/lib/models/devices/Device'
import Channel from '@/lib/models/channels/Channel'
import { ChannelInterface } from '@/lib/models/channels/types'
import ChannelConfiguration from '@/lib/models/channel-configuration/ChannelConfiguration'
import {
  ChannelConfigurationEntityTypes,
  ChannelConfigurationInterface,
  ChannelConfigurationResponseInterface,
  ChannelConfigurationsResponseInterface,
  ChannelConfigurationUpdateInterface,
} from '@/lib/models/channel-configuration/types'

import {
  ApiError,
  OrmError,
} from '@/lib/errors'
import {
  JsonApiModelPropertiesMapper,
  JsonApiJsonPropertiesMapper,
} from '@/lib/jsonapi'
import { ChannelConfigurationJsonModelInterface, ModuleApiPrefix, SemaphoreTypes } from '@/lib/types'

interface SemaphoreFetchingState {
  items: string[]
  item: string[]
}

interface SemaphoreState {
  fetching: SemaphoreFetchingState
  creating: string[]
  updating: string[]
  deleting: string[]
}

interface ChannelConfigurationState {
  semaphore: SemaphoreState
}

interface SemaphoreAction {
  type: SemaphoreTypes
  id: string
}

const jsonApiFormatter = new Jsona({
  modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
  jsonPropertiesMapper: new JsonApiJsonPropertiesMapper(),
})

const apiOptions = {
  dataTransformer: (result: AxiosResponse<ChannelConfigurationResponseInterface> | AxiosResponse<ChannelConfigurationsResponseInterface>): ChannelConfigurationJsonModelInterface | ChannelConfigurationJsonModelInterface[] => jsonApiFormatter.deserialize(result.data) as ChannelConfigurationJsonModelInterface | ChannelConfigurationJsonModelInterface[],
}

const jsonSchemaValidator = new Ajv()

const moduleState: ChannelConfigurationState = {

  semaphore: {
    fetching: {
      items: [],
      item: [],
    },
    creating: [],
    updating: [],
    deleting: [],
  },

}

const moduleActions: ActionTree<ChannelConfigurationState, unknown> = {
  async get({ state, commit }, payload: { channel: ChannelInterface, id: string }): Promise<boolean> {
    if (state.semaphore.fetching.item.includes(payload.id)) {
      return false
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.GETTING,
      id: payload.id,
    })

    try {
      await ChannelConfiguration.api().get(
        `${ModuleApiPrefix}/v1/devices/${payload.channel.deviceId}/channels/${payload.channel.id}/configuration/${payload.id}`,
        apiOptions,
      )

      return true
    } catch (e: any) {
      throw new ApiError(
        'devices-module.channel-configuration.fetch.failed',
        e,
        'Fetching channel configuration failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.GETTING,
        id: payload.id,
      })
    }
  },

  async fetch({ state, commit }, payload: { channel: ChannelInterface }): Promise<boolean> {
    if (state.semaphore.fetching.items.includes(payload.channel.id)) {
      return false
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.FETCHING,
      id: payload.channel.id,
    })

    try {
      await ChannelConfiguration.api().get(
        `${ModuleApiPrefix}/v1/devices/${payload.channel.deviceId}/channels/${payload.channel.id}/configuration`,
        apiOptions,
      )

      return true
    } catch (e: any) {
      throw new ApiError(
        'devices-module.channel-configuration.fetch.failed',
        e,
        'Fetching channel configuration failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.FETCHING,
        id: payload.channel.id,
      })
    }
  },

  async edit({
               state,
               commit,
             }, payload: { configuration: ChannelConfigurationInterface, data: ChannelConfigurationUpdateInterface }): Promise<Item<ChannelConfiguration>> {
    if (state.semaphore.updating.includes(payload.configuration.id)) {
      throw new Error('devices-module.channel-configuration.update.inProgress')
    }

    if (!ChannelConfiguration.query().where('id', payload.configuration.id).exists()) {
      throw new Error('devices-module.channel-configuration.update.failed')
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.UPDATING,
      id: payload.configuration.id,
    })

    try {
      await ChannelConfiguration.update({
        where: payload.configuration.id,
        data: payload.data,
      })
    } catch (e: any) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.configuration.id,
      })

      throw new OrmError(
        'devices-module.channel-configuration.update.failed',
        e,
        'Edit channel configuration failed.',
      )
    }

    const updatedEntity = ChannelConfiguration.find(payload.configuration.id)

    if (updatedEntity === null) {
      const configurationChannel = Channel.find(payload.configuration.channelId)

      if (configurationChannel !== null) {
        // Updated entity could not be loaded from database
        await ChannelConfiguration.get(
          configurationChannel,
          payload.configuration.id,
        )
      }

      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.configuration.id,
      })

      throw new Error('devices-module.channel-configuration.update.failed')
    }

    const channel = Channel.find(payload.configuration.channelId)

    if (channel === null) {
      throw new Error('devices-module.channel-configuration.update.failed')
    }

    try {
      await ChannelConfiguration.api().patch(
        `${ModuleApiPrefix}/v1/devices/${channel.deviceId}/channels/${updatedEntity.channelId}/configuration/${updatedEntity.id}`,
        jsonApiFormatter.serialize({
          stuff: updatedEntity,
        }),
        apiOptions,
      )

      return ChannelConfiguration.find(payload.configuration.id)
    } catch (e: any) {
      // Updating entity on api failed, we need to refresh entity
      await ChannelConfiguration.get(
        channel,
        payload.configuration.id,
      )

      throw new ApiError(
        'devices-module.channel-configuration.update.failed',
        e,
        'Edit channel configuration failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.configuration.id,
      })
    }
  },

  async transmitData(_store, payload: { configuration: ChannelConfigurationInterface, value: string }): Promise<boolean> {
    if (!ChannelConfiguration.query().where('id', payload.configuration.id).exists()) {
      throw new Error('devices-module.channel-configuration.transmit.failed')
    }

    const channel = Channel.find(payload.configuration.channelId)

    if (channel === null) {
      throw new Error('devices-module.channel-configuration.transmit.failed')
    }

    const device = Device.find(channel.deviceId)

    if (device === null) {
      throw new Error('devices-module.channel-configuration.transmit.failed')
    }

    const backupValue = payload.configuration.value

    try {
      await ChannelConfiguration.update({
        where: payload.configuration.id,
        data: {
          value: payload.value,
        },
      })
    } catch (e: any) {
      throw new OrmError(
        'devices-module.channel-configuration.transmit.failed',
        e,
        'Edit channel configuration failed.',
      )
    }

    return new Promise((resolve, reject) => {
      ChannelConfiguration.wamp().call<{ data: string }>({
        routing_key: ActionRoutes.CHANNEL_CONFIGURATION,
        origin: ChannelConfiguration.$devicesModuleOrigin,
        data: {
          device: device.id,
          channel: channel.id,
          configuration: payload.configuration.id,
          expected_value: payload.value,
        },
      })
        .then((response: RpCallResponse<{ data: string }>): void => {
          if (get(response.data, 'response') === 'accepted') {
            resolve(true)
          } else {
            ChannelConfiguration.update({
              where: payload.configuration.id,
              data: {
                value: backupValue,
              },
            })

            reject(new Error('devices-module.channel-configuration.transmit.failed'))
          }
        })
        .catch((): void => {
          ChannelConfiguration.update({
            where: payload.configuration.id,
            data: {
              value: backupValue,
            },
          })

          reject(new Error('devices-module.channel-configuration.transmit.failed'))
        })
    })
  },

  async socketData({ state, commit }, payload: { origin: string, routingKey: string, data: string }): Promise<boolean> {
    if (payload.origin !== ModuleOrigin.MODULE_DEVICES) {
      return false
    }

    if (
      ![
        RoutingKeys.CHANNELS_CONFIGURATION_ENTITY_REPORTED,
        RoutingKeys.CHANNELS_CONFIGURATION_ENTITY_CREATED,
        RoutingKeys.CHANNELS_CONFIGURATION_ENTITY_UPDATED,
        RoutingKeys.CHANNELS_CONFIGURATION_ENTITY_DELETED,
      ].includes(payload.routingKey as RoutingKeys)
    ) {
      return false
    }

    const body: ExchangeEntity = JSON.parse(payload.data)

    const validate = jsonSchemaValidator.compile<ExchangeEntity>(exchangeEntitySchema)

    if (validate(body)) {
      if (
        !ChannelConfiguration.query().where('id', body.id).exists() &&
        payload.routingKey === RoutingKeys.CHANNELS_CONFIGURATION_ENTITY_DELETED
      ) {
        return true
      }

      if (payload.routingKey === RoutingKeys.CHANNELS_CONFIGURATION_ENTITY_DELETED) {
        commit('SET_SEMAPHORE', {
          type: SemaphoreTypes.DELETING,
          id: body.id,
        })

        try {
          await ChannelConfiguration.delete(body.id)
        } catch (e: any) {
          throw new OrmError(
            'devices-module.channel-configuration.delete.failed',
            e,
            'Delete channel configuration failed.',
          )
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: SemaphoreTypes.DELETING,
            id: body.id,
          })
        }
      } else {
        if (payload.routingKey === RoutingKeys.CHANNELS_CONFIGURATION_ENTITY_UPDATED && state.semaphore.updating.includes(body.id)) {
          return true
        }

        commit('SET_SEMAPHORE', {
          type: payload.routingKey === RoutingKeys.CHANNELS_CONFIGURATION_ENTITY_REPORTED ? SemaphoreTypes.GETTING : (payload.routingKey === RoutingKeys.CHANNELS_CONFIGURATION_ENTITY_UPDATED ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING),
          id: body.id,
        })

        const entityData: { [index: string]: string | number | (string | number | boolean)[] | DataType | null | undefined } = {
          type: ChannelConfigurationEntityTypes.CONFIGURATION,
        }

        const camelRegex = new RegExp('_([a-z0-9])', 'g')

        Object.keys(body)
          .forEach((attrName) => {
            const camelName = attrName.replace(camelRegex, g => g[1].toUpperCase())

            if (camelName === 'channel') {
              const channel = Channel.query().where('id', body[attrName]).first()

              if (channel !== null) {
                entityData.channelId = channel.id
              }
            } else {
              entityData[camelName] = body[attrName]
            }
          })

        try {
          await ChannelConfiguration.insertOrUpdate({
            data: entityData,
          })
        } catch (e: any) {
          const failedEntity = ChannelConfiguration.query().with('channel').where('id', body.id).first()

          if (failedEntity !== null && failedEntity.channel !== null) {
            // Updated entity could not be loaded from database
            await ChannelConfiguration.get(
              failedEntity.channel,
              body.id,
            )
          }

          throw new OrmError(
            'devices-module.channel-configuration.update.failed',
            e,
            'Edit channel configuration failed.',
          )
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: payload.routingKey === RoutingKeys.CHANNELS_CONFIGURATION_ENTITY_UPDATED ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
            id: body.id,
          })
        }
      }

      return true
    } else {
      return false
    }
  },

  reset({ commit }): void {
    commit('RESET_STATE')
  },
}

const moduleMutations: MutationTree<ChannelConfigurationState> = {
  ['SET_SEMAPHORE'](state: ChannelConfigurationState, action: SemaphoreAction): void {
    switch (action.type) {
      case SemaphoreTypes.FETCHING:
        state.semaphore.fetching.items.push(action.id)

        // Make all keys uniq
        state.semaphore.fetching.items = uniq(state.semaphore.fetching.items)
        break

      case SemaphoreTypes.GETTING:
        state.semaphore.fetching.item.push(action.id)

        // Make all keys uniq
        state.semaphore.fetching.item = uniq(state.semaphore.fetching.item)
        break

      case SemaphoreTypes.CREATING:
        state.semaphore.creating.push(action.id)

        // Make all keys uniq
        state.semaphore.creating = uniq(state.semaphore.creating)
        break

      case SemaphoreTypes.UPDATING:
        state.semaphore.updating.push(action.id)

        // Make all keys uniq
        state.semaphore.updating = uniq(state.semaphore.updating)
        break

      case SemaphoreTypes.DELETING:
        state.semaphore.deleting.push(action.id)

        // Make all keys uniq
        state.semaphore.deleting = uniq(state.semaphore.deleting)
        break
    }
  },

  ['CLEAR_SEMAPHORE'](state: ChannelConfigurationState, action: SemaphoreAction): void {
    switch (action.type) {
      case SemaphoreTypes.FETCHING:
        // Process all semaphore items
        state.semaphore.fetching.items
          .forEach((item: string, index: number): void => {
            // Find created item in reading one item semaphore...
            if (item === action.id) {
              // ...and remove it
              state.semaphore.fetching.items.splice(index, 1)
            }
          })
        break

      case SemaphoreTypes.GETTING:
        // Process all semaphore items
        state.semaphore.fetching.item
          .forEach((item: string, index: number): void => {
            // Find created item in reading one item semaphore...
            if (item === action.id) {
              // ...and remove it
              state.semaphore.fetching.item.splice(index, 1)
            }
          })
        break

      case SemaphoreTypes.CREATING:
        // Process all semaphore items
        state.semaphore.creating
          .forEach((item: string, index: number): void => {
            // Find created item in creating semaphore...
            if (item === action.id) {
              // ...and remove it
              state.semaphore.creating.splice(index, 1)
            }
          })
        break

      case SemaphoreTypes.UPDATING:
        // Process all semaphore items
        state.semaphore.updating
          .forEach((item: string, index: number): void => {
            // Find created item in updating semaphore...
            if (item === action.id) {
              // ...and remove it
              state.semaphore.updating.splice(index, 1)
            }
          })
        break

      case SemaphoreTypes.DELETING:
        // Process all semaphore items
        state.semaphore.deleting
          .forEach((item: string, index: number): void => {
            // Find removed item in removing semaphore...
            if (item === action.id) {
              // ...and remove it
              state.semaphore.deleting.splice(index, 1)
            }
          })
        break
    }
  },

  ['RESET_STATE'](state: ChannelConfigurationState): void {
    Object.assign(state, moduleState)
  },
}

export default {
  state: (): ChannelConfigurationState => (moduleState),
  actions: moduleActions,
  mutations: moduleMutations,
}
