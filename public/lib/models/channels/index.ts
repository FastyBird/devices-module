import { Item } from '@vuex-orm/core'
import { RpCallResponse } from '@fastybird/vue-wamp-v1'
import * as exchangeEntitySchema from '@fastybird/modules-metadata/resources/schemas/devices-module/entity.channel.json'
import {
  ModuleOrigin,
  ChannelControlAction,
  ChannelEntity as ExchangeEntity,
  DevicesModule as RoutingKeys,
} from '@fastybird/modules-metadata'

import {
  ActionTree,
  GetterTree,
  MutationTree,
} from 'vuex'
import Jsona from 'jsona'
import Ajv from 'ajv'
import { AxiosResponse } from 'axios'
import get from 'lodash/get'
import uniq from 'lodash/uniq'

import Device from '@/lib/models/devices/Device'
import { DeviceInterface } from '@/lib/models/devices/types'
import Channel from '@/lib/models/channels/Channel'
import {
  ChannelEntityTypes,
  ChannelInterface,
  ChannelResponseInterface,
  ChannelsResponseInterface,
  ChannelUpdateInterface,
} from '@/lib/models/channels/types'

import {
  ApiError,
  OrmError,
} from '@/lib/errors'
import {
  JsonApiModelPropertiesMapper,
  JsonApiJsonPropertiesMapper,
} from '@/lib/jsonapi'
import { ChannelJsonModelInterface, ModuleApiPrefix, SemaphoreTypes } from '@/lib/types'

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

interface ChannelState {
  semaphore: SemaphoreState
  firstLoad: string[]
}

interface FirstLoadAction {
  id: string
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
  dataTransformer: (result: AxiosResponse<ChannelResponseInterface> | AxiosResponse<ChannelsResponseInterface>): ChannelJsonModelInterface | ChannelJsonModelInterface[] => jsonApiFormatter.deserialize(result.data) as ChannelJsonModelInterface | ChannelJsonModelInterface[],
}

const jsonSchemaValidator = new Ajv()

const moduleState: ChannelState = {

  semaphore: {
    fetching: {
      items: [],
      item: [],
    },
    creating: [],
    updating: [],
    deleting: [],
  },

  firstLoad: [],

}

const moduleGetters: GetterTree<ChannelState, unknown> = {
  firstLoadFinished: state => (deviceId: string): boolean => {
    return state.firstLoad.includes(deviceId)
  },

  getting: state => (channelId: string): boolean => {
    return state.semaphore.fetching.item.includes(channelId)
  },

  fetching: state => (deviceId: string | null): boolean => {
    return deviceId !== null ? state.semaphore.fetching.items.includes(deviceId) : state.semaphore.fetching.items.length > 0
  },
}

const moduleActions: ActionTree<ChannelState, unknown> = {
  async get({ state, commit }, payload: { device: DeviceInterface, id: string }): Promise<boolean> {
    if (state.semaphore.fetching.item.includes(payload.id)) {
      return false
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.GETTING,
      id: payload.id,
    })

    try {
      await Channel.api().get(
        `${ModuleApiPrefix}/v1/devices/${payload.device.id}/channels/${payload.id}?include=properties,configuration`,
        apiOptions,
      )

      return true
    } catch (e: any) {
      throw new ApiError(
        'devices-module.channels.get.failed',
        e,
        'Fetching channel failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.GETTING,
        id: payload.id,
      })
    }
  },

  async fetch({ state, commit }, payload: { device: DeviceInterface }): Promise<boolean> {
    if (state.semaphore.fetching.items.includes(payload.device.id) || payload.device.draft) {
      return false
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.FETCHING,
      id: payload.device.id,
    })

    try {
      await Channel.api().get(
        `${ModuleApiPrefix}/v1/devices/${payload.device.id}/channels?include=properties,configuration`,
        apiOptions,
      )

      commit('SET_FIRST_LOAD', {
        id: payload.device.id,
      })

      return true
    } catch (e: any) {
      throw new ApiError(
        'devices-module.channels.fetch.failed',
        e,
        'Fetching channels failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.FETCHING,
        id: payload.device.id,
      })
    }
  },

  async edit({ state, commit }, payload: { channel: ChannelInterface, data: ChannelUpdateInterface }): Promise<Item<Channel>> {
    if (state.semaphore.updating.includes(payload.channel.id)) {
      throw new Error('devices-module.channels.update.inProgress')
    }

    if (!Channel.query().where('id', payload.channel.id).exists()) {
      throw new Error('devices-module.channels.update.failed')
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.UPDATING,
      id: payload.channel.id,
    })

    try {
      await Channel.update({
        where: payload.channel.id,
        data: payload.data,
      })
    } catch (e: any) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.channel.id,
      })

      throw new OrmError(
        'devices-module.channels.edit.failed',
        e,
        'Edit channel failed.',
      )
    }

    const updatedEntity = Channel.find(payload.channel.id)

    if (updatedEntity === null) {
      const device = Device.find(payload.channel.deviceId)

      if (device !== null) {
        // Updated entity could not be loaded from database
        await Channel.get(
          device,
          payload.channel.id,
        )
      }

      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.channel.id,
      })

      throw new Error('devices-module.channels.update.failed')
    }

    try {
      await Channel.api().patch(
        `${ModuleApiPrefix}/v1/devices/${updatedEntity.deviceId}/channels/${updatedEntity.id}?include=properties,configuration`,
        jsonApiFormatter.serialize({
          stuff: updatedEntity,
        }),
        apiOptions,
      )

      return Channel.find(payload.channel.id)
    } catch (e: any) {
      const device = Device.find(payload.channel.deviceId)

      if (device !== null) {
        // Updating entity on api failed, we need to refresh entity
        await Channel.get(
          device,
          payload.channel.id,
        )
      }

      throw new ApiError(
        'devices-module.channels.update.failed',
        e,
        'Edit channel failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.channel.id,
      })
    }
  },

  transmitCommand(_store, payload: { channel: ChannelInterface, command: ChannelControlAction }): Promise<boolean> {
    if (!Channel.query().where('id', payload.channel.id).exists()) {
      throw new Error('devices-module.channel.transmit.failed')
    }

    const device = Device.find(payload.channel.deviceId)

    if (device === null) {
      throw new Error('devices-module.channel.transmit.failed')
    }

    return new Promise((resolve, reject) => {
      Channel.wamp().call<{ data: string }>({
        routing_key: RoutingKeys.CHANNELS_CONTROLS,
        origin: Channel.$devicesModuleOrigin,
        data: {
          control: payload.command,
          device: device.id,
          channel: payload.channel.id,
        },
      })
        .then((response: RpCallResponse<{ data: string }>): void => {
          if (get(response.data, 'response') === 'accepted') {
            resolve(true)
          } else {
            reject(new Error('devices-module.channel.transmit.failed'))
          }
        })
        .catch((): void => {
          reject(new Error('devices-module.channel.transmit.failed'))
        })
    })
  },

  async socketData({ state, commit }, payload: { origin: string, routingKey: string, data: string }): Promise<boolean> {
    if (payload.origin !== ModuleOrigin.MODULE_DEVICES_ORIGIN) {
      return false
    }

    if (
      ![
        RoutingKeys.CHANNELS_ENTITY_CREATED,
        RoutingKeys.CHANNELS_ENTITY_UPDATED,
        RoutingKeys.CHANNELS_ENTITY_DELETED,
      ].includes(payload.routingKey as RoutingKeys)
    ) {
      return false
    }

    const body: ExchangeEntity = JSON.parse(payload.data)

    const validate = jsonSchemaValidator.compile<ExchangeEntity>(exchangeEntitySchema)

    if (validate(body)) {
      if (
        !Channel.query().where('id', body.id).exists() &&
        payload.routingKey === RoutingKeys.CHANNELS_ENTITY_DELETED
      ) {
        return true
      }

      if (payload.routingKey === RoutingKeys.CHANNELS_ENTITY_DELETED) {
        commit('SET_SEMAPHORE', {
          type: SemaphoreTypes.DELETING,
          id: body.id,
        })

        try {
          await Channel.delete(body.id)
        } catch (e: any) {
          throw new OrmError(
            'devices-module.channels.delete.failed',
            e,
            'Delete channel failed.',
          )
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: SemaphoreTypes.DELETING,
            id: body.id,
          })
        }
      } else {
        if (payload.routingKey === RoutingKeys.CHANNELS_ENTITY_UPDATED && state.semaphore.updating.includes(body.id)) {
          return true
        }

        commit('SET_SEMAPHORE', {
          type: payload.routingKey === RoutingKeys.CHANNELS_ENTITY_UPDATED ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
          id: body.id,
        })

        const entityData: { [index: string]: string | string[] | null | undefined } = {
          type: ChannelEntityTypes.CHANNEL,
        }

        const camelRegex = new RegExp('_([a-z0-9])', 'g')

        Object.keys(body)
          .forEach((attrName) => {
            const camelName = attrName.replace(camelRegex, g => g[1].toUpperCase())

            if (camelName === 'device') {
              const device = Device.query().where('id', body[attrName]).first()

              if (device !== null) {
                entityData.deviceId = device.id
              }
            } else {
              entityData[camelName] = body[attrName]
            }
          })

        try {
          await Channel.insertOrUpdate({
            data: entityData,
          })
        } catch (e: any) {
          const failedEntity = Channel.query().with('device').where('id', body.id).first()

          if (failedEntity !== null && failedEntity.device !== null) {
            // Updating entity on api failed, we need to refresh entity
            await Channel.get(
              failedEntity.device,
              body.id,
            )
          }

          throw new OrmError(
            'devices-module.channels.update.failed',
            e,
            'Edit channel failed.',
          )
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: payload.routingKey === RoutingKeys.CHANNELS_ENTITY_UPDATED ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
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

const moduleMutations: MutationTree<ChannelState> = {
  ['SET_FIRST_LOAD'](state: ChannelState, action: FirstLoadAction): void {
    state.firstLoad.push(action.id)

    // Make all keys uniq
    state.firstLoad = uniq(state.firstLoad)
  },

  ['SET_SEMAPHORE'](state: ChannelState, action: SemaphoreAction): void {
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

  ['CLEAR_SEMAPHORE'](state: ChannelState, action: SemaphoreAction): void {
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
            // Find created item in deleting semaphore...
            if (item === action.id) {
              // ...and remove it
              state.semaphore.deleting.splice(index, 1)
            }
          })
        break
    }
  },

  ['RESET_STATE'](state: ChannelState): void {
    Object.assign(state, moduleState)
  },
}

export default {
  state: (): ChannelState => (moduleState),
  getters: moduleGetters,
  actions: moduleActions,
  mutations: moduleMutations,
}
