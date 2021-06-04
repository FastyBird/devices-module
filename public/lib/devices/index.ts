import { Item } from '@vuex-orm/core'
import { RpCallResponse } from '@fastybird/vue-wamp-v1'
import * as exchangeEntitySchema from '@fastybird/modules-metadata/resources/schemas/devices-module/entity.device.json'
import {
  ModuleOrigin,
  DeviceControlAction,
  DeviceEntity as ExchangeEntity,
  DevicesModule as RoutingKeys,
} from '@fastybird/modules-metadata'

import {
  ActionTree,
  GetterTree,
  MutationTree,
} from 'vuex'
import Jsona from 'jsona'
import Ajv from 'ajv'
import { v4 as uuid } from 'uuid'
import { AxiosResponse } from 'axios'
import get from 'lodash/get'
import uniq from 'lodash/uniq'

import Device from '@/lib/devices/Device'
import {
  DeviceCreateInterface,
  DeviceInterface,
  DeviceResponseInterface,
  DevicesResponseInterface,
  DeviceUpdateInterface,
} from '@/lib/devices/types'
import Channel from '@/lib/channels/Channel'

import {
  ApiError,
  OrmError,
} from '@/lib/errors'
import {
  JsonApiModelPropertiesMapper,
  JsonApiPropertiesMapper,
} from '@/lib/jsonapi'
import { DeviceJsonModelInterface, ModuleApiPrefix, SemaphoreTypes } from '@/lib/types'

interface SemaphoreFetchingState {
  items: boolean
  item: Array<string>
}

interface SemaphoreState {
  fetching: SemaphoreFetchingState
  creating: Array<string>
  updating: Array<string>
  deleting: Array<string>
}

interface DeviceState {
  semaphore: SemaphoreState
  firstLoad: boolean
}

interface SemaphoreAction {
  type: SemaphoreTypes
  id?: string
}

const jsonApiFormatter = new Jsona({
  modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
  jsonPropertiesMapper: new JsonApiPropertiesMapper(),
})

const apiOptions = {
  dataTransformer: (result: AxiosResponse<DeviceResponseInterface> | AxiosResponse<DevicesResponseInterface>): DeviceJsonModelInterface | Array<DeviceJsonModelInterface> => <DeviceJsonModelInterface | Array<DeviceJsonModelInterface>>jsonApiFormatter.deserialize(result.data),
}

const jsonSchemaValidator = new Ajv()

const moduleState: DeviceState = {

  semaphore: {
    fetching: {
      items: false,
      item: [],
    },
    creating: [],
    updating: [],
    deleting: [],
  },

  firstLoad: false,

}

const moduleGetters: GetterTree<DeviceState, any> = {
  firstLoadFinished: state => (): boolean => {
    return !!state.firstLoad
  },

  getting: state => (id: string): boolean => {
    return state.semaphore.fetching.item.includes(id)
  },

  fetching: state => (): boolean => {
    return !!state.semaphore.fetching.items
  },
}

const moduleActions: ActionTree<DeviceState, any> = {
  async get({state, commit}, payload: { id: string, includeChannels: boolean }): Promise<boolean> {
    if (state.semaphore.fetching.item.includes(payload.id)) {
      return false
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.GETTING,
      id: payload.id,
    })

    try {
      await Device.api().get(
        `${ModuleApiPrefix}/v1/devices/${payload.id}?include=properties,configuration,connector`,
        apiOptions,
      )

      if (payload.includeChannels) {
        const device = Device.find(payload.id)

        if (device !== null) {
          await Channel.fetch(
            device,
          )
        }
      }

      return true
    } catch (e) {
      throw new ApiError(
        'devices-module.devices.fetch.failed',
        e,
        'Fetching devices failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.GETTING,
        id: payload.id,
      })
    }
  },

  async fetch({state, commit}, payload: { includeChannels: boolean }): Promise<boolean> {
    if (state.semaphore.fetching.items) {
      return false
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.FETCHING,
    })

    try {
      await Device.api().get(
        `${ModuleApiPrefix}/v1/devices?include=properties,configuration,connector`,
        apiOptions,
      )

      if (payload.includeChannels) {
        const devices = await Device.all()

        const promises: Array<Promise<boolean>> = []

        devices.forEach((device: DeviceInterface) => {
          promises.push(
            Channel.fetch(
              device,
            ),
          )
        })

        await Promise.all(promises)
      }

      commit('SET_FIRST_LOAD', true)

      return true
    } catch (e) {
      throw new ApiError(
        'devices-module.devices.fetch.failed',
        e,
        'Fetching devices failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.FETCHING,
      })
    }
  },

  async add({commit}, payload: { id?: string | null, draft?: boolean, data: DeviceCreateInterface }): Promise<Item<Device>> {
    const id = typeof payload.id !== 'undefined' && payload.id !== null && payload.id !== '' ? payload.id : uuid().toString()
    const draft = typeof payload.draft !== 'undefined' ? payload.draft : false

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.CREATING,
      id,
    })

    try {
      await Device.insert({
        data: Object.assign({}, payload.data, {id, draft}),
      })
    } catch (e) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.CREATING,
        id,
      })

      throw new OrmError(
        'devices-module.devices.create.failed',
        e,
        'Create new device failed.',
      )
    }

    const createdEntity = Device.find(id)

    if (createdEntity === null) {
      await Device.delete(id)

      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.CREATING,
        id,
      })

      throw new Error('devices-module.devices.create.failed')
    }

    if (draft) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.CREATING,
        id,
      })

      return Device.find(id)
    } else {
      try {
        await Device.api().post(
          `${ModuleApiPrefix}/v1/devices?include=properties,configuration,connector`,
          jsonApiFormatter.serialize({
            stuff: createdEntity,
          }),
          apiOptions,
        )

        return Device.find(id)
      } catch (e) {
        // Entity could not be created on api, we have to remove it from database
        await Device.delete(id)

        throw new ApiError(
          'devices-module.devices.create.failed',
          e,
          'Create new device failed.',
        )
      } finally {
        commit('CLEAR_SEMAPHORE', {
          type: SemaphoreTypes.CREATING,
          id,
        })
      }
    }
  },

  async edit({
               state,
               commit,
             }, payload: { device: DeviceInterface, data: DeviceUpdateInterface }): Promise<Item<Device>> {
    if (state.semaphore.updating.includes(payload.device.id)) {
      throw new Error('devices-module.devices.update.inProgress')
    }

    if (!Device.query().where('id', payload.device.id).exists()) {
      throw new Error('devices-module.devices.update.failed')
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.UPDATING,
      id: payload.device.id,
    })

    try {
      await Device.update({
        where: payload.device.id,
        data: payload.data,
      })
    } catch (e) {
      throw new OrmError(
        'devices-module.devices.update.failed',
        e,
        'Edit device failed.',
      )
    }

    const updatedEntity = Device.find(payload.device.id)

    if (updatedEntity === null) {
      // Updated entity could not be loaded from database
      await Device.get(
        payload.device.id,
        false,
      )

      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.device.id,
      })

      throw new Error('devices-module.devices.update.failed')
    }

    if (updatedEntity.draft) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.device.id,
      })

      return Device.find(payload.device.id)
    } else {
      try {
        await Device.api().patch(
          `${ModuleApiPrefix}/v1/devices/${updatedEntity.id}?include=properties,configuration,connector`,
          jsonApiFormatter.serialize({
            stuff: updatedEntity,
          }),
          apiOptions,
        )

        return Device.find(payload.device.id)
      } catch (e) {
        // Updating entity on api failed, we need to refresh entity
        await Device.get(
          payload.device.id,
          false,
        )

        throw new ApiError(
          'devices-module.devices.update.failed',
          e,
          'Edit device failed.',
        )
      } finally {
        commit('CLEAR_SEMAPHORE', {
          type: SemaphoreTypes.UPDATING,
          id: payload.device.id,
        })
      }
    }
  },

  async save({state, commit}, payload: { device: DeviceInterface }): Promise<Item<Device>> {
    if (state.semaphore.updating.includes(payload.device.id)) {
      throw new Error('devices-module.devices.save.inProgress')
    }

    if (!Device.query().where('id', payload.device.id).where('draft', true).exists()) {
      throw new Error('devices-module.devices.save.failed')
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.UPDATING,
      id: payload.device.id,
    })

    const entityToSave = Device.find(payload.device.id)

    if (entityToSave === null) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.device.id,
      })

      throw new Error('devices-module.devices.save.failed')
    }

    try {
      await Device.api().post(
        `${ModuleApiPrefix}/v1/devices?include=properties,configuration,connector`,
        jsonApiFormatter.serialize({
          stuff: entityToSave,
        }),
        apiOptions,
      )

      return Device.find(payload.device.id)
    } catch (e) {
      throw new ApiError(
        'devices-module.devices.save.failed',
        e,
        'Save draft device failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.device.id,
      })
    }
  },

  async remove({state, commit}, payload: { device: DeviceInterface }): Promise<boolean> {
    if (state.semaphore.deleting.includes(payload.device.id)) {
      throw new Error('devices-module.devices.delete.inProgress')
    }

    if (!Device.query().where('id', payload.device.id).exists()) {
      return true
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.DELETING,
      id: payload.device.id,
    })

    try {
      await Device.delete(payload.device.id)
    } catch (e) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.DELETING,
        id: payload.device.id,
      })

      throw new OrmError(
        'devices-module.devices.delete.failed',
        e,
        'Delete device failed.',
      )
    }

    if (payload.device.draft) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.DELETING,
        id: payload.device.id,
      })

      return true
    } else {
      try {
        await Device.api().delete(
          `${ModuleApiPrefix}/v1/devices/${payload.device.id}`,
          {
            save: false,
          },
        )

        return true
      } catch (e) {
        // Deleting entity on api failed, we need to refresh entity
        await Device.get(
          payload.device.id,
          false,
        )

        throw new OrmError(
          'devices-module.devices.delete.failed',
          e,
          'Delete device failed.',
        )
      } finally {
        commit('CLEAR_SEMAPHORE', {
          type: SemaphoreTypes.DELETING,
          id: payload.device.id,
        })
      }
    }
  },

  transmitCommand(_store, payload: { device: DeviceInterface, command: DeviceControlAction }): Promise<boolean> {
    if (!Device.query().where('id', payload.device.id).exists()) {
      throw new Error('devices-module.device.transmit.failed')
    }

    return new Promise((resolve, reject) => {
      Device.wamp().call({
        routing_key: RoutingKeys.DEVICES_CONTROLS,
        origin: Device.$devicesModuleOrigin,
        data: {
          control: payload.command,
          device: payload.device.key,
        },
      })
        .then((response: RpCallResponse): void => {
          if (get(response.data, 'response') === 'accepted') {
            resolve(true)
          } else {
            reject(new Error('devices-module.device.transmit.failed'))
          }
        })
        .catch((): void => {
          reject(new Error('devices-module.device.transmit.failed'))
        })
    })
  },

  async socketData({state, commit}, payload: { origin: string, routingKey: string, data: string }): Promise<boolean> {
    if (payload.origin !== ModuleOrigin.MODULE_DEVICES_ORIGIN) {
      return false
    }

    if (
      ![
        RoutingKeys.DEVICES_CREATED_ENTITY,
        RoutingKeys.DEVICES_UPDATED_ENTITY,
        RoutingKeys.DEVICES_DELETED_ENTITY,
      ].includes(payload.routingKey as RoutingKeys)
    ) {
      return false
    }

    const body: ExchangeEntity = JSON.parse(payload.data)

    const validate = jsonSchemaValidator.compile<ExchangeEntity>(exchangeEntitySchema)

    if (validate(body)) {
      if (
        !Device.query().where('id', body.id).exists() &&
        (payload.routingKey === RoutingKeys.DEVICES_UPDATED_ENTITY || payload.routingKey === RoutingKeys.DEVICES_DELETED_ENTITY)
      ) {
        throw new Error('devices-module.devices.update.failed')
      }

      if (payload.routingKey === RoutingKeys.DEVICES_DELETED_ENTITY) {
        commit('SET_SEMAPHORE', {
          type: SemaphoreTypes.DELETING,
          id: body.id,
        })

        try {
          await Device.delete(body.id)
        } catch (e) {
          throw new OrmError(
            'devices-module.devices.delete.failed',
            e,
            'Delete device failed.',
          )
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: SemaphoreTypes.DELETING,
            id: body.id,
          })
        }
      } else {
        if (payload.routingKey === RoutingKeys.DEVICES_UPDATED_ENTITY && state.semaphore.updating.includes(body.id)) {
          return true
        }

        commit('SET_SEMAPHORE', {
          type: payload.routingKey === RoutingKeys.DEVICES_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
          id: body.id,
        })

        const entityData: { [index: string]: any } = {}

        Object.keys(body)
          .forEach((attrName) => {
            const kebabName = attrName.replace(/([a-z][A-Z0-9])/g, g => `${g[0]}_${g[1].toLowerCase()}`)

            entityData[kebabName] = body[attrName]
          })

        try {
          await Device.insertOrUpdate({
            data: entityData,
          })
        } catch (e) {
          // Updating entity on api failed, we need to refresh entity
          await Device.get(
            body.id,
            false,
          )

          throw new OrmError(
            'devices-module.devices.update.failed',
            e,
            'Edit device failed.',
          )
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: payload.routingKey === RoutingKeys.DEVICES_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
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

    Channel.reset()
  },
}

const moduleMutations: MutationTree<DeviceState> = {
  ['SET_FIRST_LOAD'](state: DeviceState, action: boolean): void {
    state.firstLoad = action
  },

  ['SET_SEMAPHORE'](state: DeviceState, action: SemaphoreAction): void {
    switch (action.type) {
      case SemaphoreTypes.FETCHING:
        state.semaphore.fetching.items = true
        break

      case SemaphoreTypes.GETTING:
        state.semaphore.fetching.item.push(get(action, 'id', 'notValid'))

        // Make all keys uniq
        state.semaphore.fetching.item = uniq(state.semaphore.fetching.item)
        break

      case SemaphoreTypes.CREATING:
        state.semaphore.creating.push(get(action, 'id', 'notValid'))

        // Make all keys uniq
        state.semaphore.creating = uniq(state.semaphore.creating)
        break

      case SemaphoreTypes.UPDATING:
        state.semaphore.updating.push(get(action, 'id', 'notValid'))

        // Make all keys uniq
        state.semaphore.updating = uniq(state.semaphore.updating)
        break

      case SemaphoreTypes.DELETING:
        state.semaphore.deleting.push(get(action, 'id', 'notValid'))

        // Make all keys uniq
        state.semaphore.deleting = uniq(state.semaphore.deleting)
        break
    }
  },

  ['CLEAR_SEMAPHORE'](state: DeviceState, action: SemaphoreAction): void {
    switch (action.type) {
      case SemaphoreTypes.FETCHING:
        state.semaphore.fetching.items = false
        break

      case SemaphoreTypes.GETTING:
        // Process all semaphore items
        state.semaphore.fetching.item
          .forEach((item: string, index: number): void => {
            // Find created item in reading one item semaphore...
            if (item === get(action, 'id', 'notValid')) {
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
            if (item === get(action, 'id', 'notValid')) {
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
            if (item === get(action, 'id', 'notValid')) {
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
            if (item === get(action, 'id', 'notValid')) {
              // ...and remove it
              state.semaphore.deleting.splice(index, 1)
            }
          })
        break
    }
  },

  ['RESET_STATE'](state: DeviceState): void {
    Object.assign(state, moduleState)
  },
}

export default {
  state: (): DeviceState => (moduleState),
  getters: moduleGetters,
  actions: moduleActions,
  mutations: moduleMutations,
}
