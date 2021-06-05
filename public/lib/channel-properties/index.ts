import { Item } from '@vuex-orm/core'
import { RpCallResponse } from '@fastybird/vue-wamp-v1'
import * as exchangeEntitySchema
  from '@fastybird/modules-metadata/resources/schemas/devices-module/entity.channel.property.json'
import {
  ModuleOrigin,
  ChannelPropertyEntity as ExchangeEntity,
  DevicesModule as RoutingKeys,
} from '@fastybird/modules-metadata'

import {
  ActionTree,
  MutationTree,
} from 'vuex'
import Jsona from 'jsona'
import Ajv from 'ajv'
import { AxiosResponse } from 'axios'
import get from 'lodash/get'
import uniq from 'lodash/uniq'

import Device from '@/lib/devices/Device'
import Channel from '@/lib/channels/Channel'
import { ChannelInterface } from '@/lib/channels/types'
import ChannelProperty from '@/lib/channel-properties/ChannelProperty'
import {
  ChannelPropertyEntityTypes,
  ChannelPropertyInterface,
  ChannelPropertyResponseInterface,
  ChannelPropertiesResponseInterface,
  ChannelPropertyUpdateInterface,
} from '@/lib/channel-properties/types'

import {
  ApiError,
  OrmError,
} from '@/lib/errors'
import {
  JsonApiModelPropertiesMapper,
  JsonApiPropertiesMapper,
} from '@/lib/jsonapi'
import { ChannelPropertyJsonModelInterface, ModuleApiPrefix, SemaphoreTypes } from '@/lib/types'

interface SemaphoreFetchingState {
  items: Array<string>
  item: Array<string>
}

interface SemaphoreState {
  fetching: SemaphoreFetchingState
  creating: Array<string>
  updating: Array<string>
  deleting: Array<string>
}

interface ChannelPropertyState {
  semaphore: SemaphoreState
}

interface SemaphoreAction {
  type: SemaphoreTypes
  id: string
}

const jsonApiFormatter = new Jsona({
  modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
  jsonPropertiesMapper: new JsonApiPropertiesMapper(),
})

const apiOptions = {
  dataTransformer: (result: AxiosResponse<ChannelPropertyResponseInterface> | AxiosResponse<ChannelPropertiesResponseInterface>): ChannelPropertyJsonModelInterface | Array<ChannelPropertyJsonModelInterface> => <ChannelPropertyJsonModelInterface | Array<ChannelPropertyJsonModelInterface>>jsonApiFormatter.deserialize(result.data),
}

const jsonSchemaValidator = new Ajv()

const moduleState: ChannelPropertyState = {

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

const moduleActions: ActionTree<ChannelPropertyState, any> = {
  async get({state, commit}, payload: { channel: ChannelInterface, id: string }): Promise<boolean> {
    if (state.semaphore.fetching.item.includes(payload.id)) {
      return false
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.GETTING,
      id: payload.id,
    })

    try {
      await ChannelProperty.api().get(
        `${ModuleApiPrefix}/v1/devices/${payload.channel.deviceId}/channels/${payload.channel.id}/properties/${payload.id}`,
        apiOptions,
      )

      return true
    } catch (e) {
      throw new ApiError(
        'devices-module.channel-properties.fetch.failed',
        e,
        'Fetching channel property failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.GETTING,
        id: payload.id,
      })
    }
  },

  async fetch({state, commit}, payload: { channel: ChannelInterface }): Promise<boolean> {
    if (state.semaphore.fetching.items.includes(payload.channel.id)) {
      return false
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.FETCHING,
      id: payload.channel.id,
    })

    try {
      await ChannelProperty.api().get(
        `${ModuleApiPrefix}/v1/devices/${payload.channel.deviceId}/channels/${payload.channel.id}/properties`,
        apiOptions,
      )

      return true
    } catch (e) {
      throw new ApiError(
        'devices-module.channel-properties.fetch.failed',
        e,
        'Fetching channel properties failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.FETCHING,
        id: payload.channel.id,
      })
    }
  },

  async edit({ state, commit }, payload: { property: ChannelPropertyInterface, data: ChannelPropertyUpdateInterface }): Promise<Item<ChannelProperty>> {
    if (state.semaphore.updating.includes(payload.property.id)) {
      throw new Error('devices-module.channel-properties.update.inProgress')
    }

    if (!ChannelProperty.query().where('id', payload.property.id).exists()) {
      throw new Error('devices-module.channel-properties.update.failed')
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.UPDATING,
      id: payload.property.id,
    })

    try {
      await ChannelProperty.update({
        where: payload.property.id,
        data: payload.data,
      })
    } catch (e) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.property.id,
      })

      throw new OrmError(
        'devices-module.channel-properties.update.failed',
        e,
        'Edit channel property failed.',
      )
    }

    const updatedEntity = ChannelProperty.find(payload.property.id)

    if (updatedEntity === null) {
      const channel = Channel.find(payload.property.channelId)

      if (channel !== null) {
        // Updated entity could not be loaded from database
        await ChannelProperty.get(
          channel,
          payload.property.id,
        )
      }

      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.property.id,
      })

      throw new Error('devices-module.channel-properties.update.failed')
    }

    const channel = Channel.find(payload.property.channelId)

    if (channel === null) {
      throw new Error('devices-module.channel-properties.update.failed')
    }

    try {
      await ChannelProperty.api().patch(
        `${ModuleApiPrefix}/v1/devices/${channel.deviceId}/channels/${updatedEntity.channelId}/properties/${updatedEntity.id}`,
        jsonApiFormatter.serialize({
          stuff: updatedEntity,
        }),
        apiOptions,
      )

      return ChannelProperty.find(payload.property.id)
    } catch (e) {
      // Updating entity on api failed, we need to refresh entity
      await ChannelProperty.get(
        channel,
        payload.property.id,
      )

      throw new ApiError(
        'devices-module.channel-properties.update.failed',
        e,
        'Edit channel property failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.property.id,
      })
    }
  },

  async transmitData(_store, payload: { property: ChannelPropertyInterface, value: string }): Promise<boolean> {
    if (!ChannelProperty.query().where('id', payload.property.id).exists()) {
      throw new Error('devices-module.channel-properties.transmit.failed')
    }

    const channel = Channel.find(payload.property.channelId)

    if (channel === null) {
      throw new Error('devices-module.channel-properties.transmit.failed')
    }

    const device = Device.find(channel.deviceId)

    if (device === null) {
      throw new Error('devices-module.channel-properties.transmit.failed')
    }

    const backupValue = payload.property.value

    try {
      await ChannelProperty.update({
        where: payload.property.id,
        data: {
          value: payload.value,
        },
      })
    } catch (e) {
      throw new OrmError(
        'devices-module.channel-properties.transmit.failed',
        e,
        'Edit channel property failed.',
      )
    }

    return new Promise((resolve, reject) => {
      ChannelProperty.wamp().call({
        routing_key: RoutingKeys.CHANNELS_PROPERTIES_DATA,
        origin: ChannelProperty.$devicesModuleOrigin,
        data: {
          device: device.key,
          channel: channel.key,
          property: payload.property.key,
          expected: payload.value,
        },
      })
        .then((response: RpCallResponse): void => {
          if (get(response.data, 'response') === 'accepted') {
            resolve(true)
          } else {
            ChannelProperty.update({
              where: payload.property.id,
              data: {
                value: backupValue,
              },
            })

            reject(new Error('devices-module.channel-properties.transmit.failed'))
          }
        })
        .catch((): void => {
          ChannelProperty.update({
            where: payload.property.id,
            data: {
              value: backupValue,
            },
          })

          reject(new Error('devices-module.channel-properties.transmit.failed'))
        })
    })
  },

  async socketData({state, commit}, payload: { origin: string, routingKey: string, data: string }): Promise<boolean> {
    if (payload.origin !== ModuleOrigin.MODULE_DEVICES_ORIGIN) {
      return false
    }

    if (
      ![
        RoutingKeys.CHANNELS_PROPERTY_CREATED_ENTITY,
        RoutingKeys.CHANNELS_PROPERTY_UPDATED_ENTITY,
        RoutingKeys.CHANNELS_PROPERTY_DELETED_ENTITY,
      ].includes(payload.routingKey as RoutingKeys)
    ) {
      return false
    }

    const body: ExchangeEntity = JSON.parse(payload.data)

    const validate = jsonSchemaValidator.compile<ExchangeEntity>(exchangeEntitySchema)

    if (validate(body)) {
      if (
        !ChannelProperty.query().where('id', body.id).exists() &&
        (payload.routingKey === RoutingKeys.CHANNELS_PROPERTY_UPDATED_ENTITY || payload.routingKey === RoutingKeys.CHANNELS_PROPERTY_DELETED_ENTITY)
      ) {
        throw new Error('devices-module.channel-properties.update.failed')
      }

      if (payload.routingKey === RoutingKeys.CHANNELS_PROPERTY_DELETED_ENTITY) {
        commit('SET_SEMAPHORE', {
          type: SemaphoreTypes.DELETING,
          id: body.id,
        })

        try {
          await ChannelProperty.delete(body.id)
        } catch (e) {
          throw new OrmError(
            'devices-module.channel-properties.delete.failed',
            e,
            'Delete channel property failed.',
          )
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: SemaphoreTypes.DELETING,
            id: body.id,
          })
        }
      } else {
        if (payload.routingKey === RoutingKeys.CHANNELS_PROPERTY_UPDATED_ENTITY && state.semaphore.updating.includes(body.id)) {
          return true
        }

        commit('SET_SEMAPHORE', {
          type: payload.routingKey === RoutingKeys.CHANNELS_PROPERTY_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
          id: body.id,
        })

        const entityData: { [index: string]: any } = {
          type: ChannelPropertyEntityTypes.PROPERTY,
        }

        Object.keys(body)
          .forEach((attrName) => {
            const kebabName = attrName.replace(/([a-z][A-Z0-9])/g, g => `${g[0]}_${g[1].toLowerCase()}`)

            if (kebabName === 'channel') {
              const channel = Channel.query().where('channel', body[attrName]).first()

              if (channel !== null) {
                entityData.channelId = channel.id
              }
            } else {
              entityData[kebabName] = body[attrName]
            }
          })

        try {
          await ChannelProperty.insertOrUpdate({
            data: entityData,
          })
        } catch (e) {
          const failedEntity = ChannelProperty.query().with('channel').where('id', body.id).first()

          if (failedEntity !== null && failedEntity.channel !== null) {
            // Updating entity on api failed, we need to refresh entity
            await ChannelProperty.get(
              failedEntity.channel,
              body.id,
            )
          }

          throw new OrmError(
            'devices-module.channel-properties.update.failed',
            e,
            'Edit channel property failed.',
          )
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: payload.routingKey === RoutingKeys.CHANNELS_PROPERTY_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
            id: body.id,
          })
        }
      }

      return true
    } else {
      return false
    }
  },

  reset({commit}): void {
    commit('RESET_STATE')
  },
}

const moduleMutations: MutationTree<ChannelPropertyState> = {
  ['SET_SEMAPHORE'](state: ChannelPropertyState, action: SemaphoreAction): void {
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

  ['CLEAR_SEMAPHORE'](state: ChannelPropertyState, action: SemaphoreAction): void {
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

  ['RESET_STATE'](state: ChannelPropertyState): void {
    Object.assign(state, moduleState)
  },
}

export default {
  state: (): ChannelPropertyState => (moduleState),
  actions: moduleActions,
  mutations: moduleMutations,
}
