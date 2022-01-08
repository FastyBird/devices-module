import { RpCallResponse } from '@fastybird/vue-wamp-v1'
import * as exchangeEntitySchema
  from '@fastybird/modules-metadata/resources/schemas/devices-module/entity.device.control.json'
import {
  ModuleOrigin,
  DeviceControlEntity as ExchangeEntity,
  DevicesModuleRoutes as RoutingKeys,
  GlobalRoutes as GlobalRoutingKeys,
  DataType,
  ControlAction,
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

import Device from '@/lib/models/devices/Device'
import { DeviceInterface } from '@/lib/models/devices/types'
import DeviceControl from '@/lib/models/device-controls/DeviceControl'
import {
  DeviceControlEntityTypes,
  DeviceControlInterface,
  DeviceControlResponseInterface,
  DeviceControlsResponseInterface,
} from '@/lib/models/device-controls/types'

import {
  ApiError,
  OrmError,
} from '@/lib/errors'
import {
  JsonApiModelPropertiesMapper,
  JsonApiJsonPropertiesMapper,
} from '@/lib/jsonapi'
import { DeviceControlJsonModelInterface, ModuleApiPrefix, SemaphoreTypes } from '@/lib/types'

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

interface DeviceControlState {
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
  dataTransformer: (result: AxiosResponse<DeviceControlResponseInterface> | AxiosResponse<DeviceControlsResponseInterface>): DeviceControlJsonModelInterface | DeviceControlJsonModelInterface[] => jsonApiFormatter.deserialize(result.data) as DeviceControlJsonModelInterface | DeviceControlJsonModelInterface[],
}

const jsonSchemaValidator = new Ajv()

const moduleState: DeviceControlState = {

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

const moduleActions: ActionTree<DeviceControlState, unknown> = {
  async get({ state, commit }, payload: { device: DeviceInterface, id: string }): Promise<boolean> {
    if (state.semaphore.fetching.item.includes(payload.id)) {
      return false
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.GETTING,
      id: payload.id,
    })

    try {
      await DeviceControl.api().get(
        `${ModuleApiPrefix}/v1/devices/${payload.device.id}/controls/${payload.id}`,
        apiOptions,
      )

      return true
    } catch (e: any) {
      throw new ApiError(
        'devices-module.device-controls.fetch.failed',
        e,
        'Fetching device control failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.GETTING,
        id: payload.id,
      })
    }
  },

  async fetch({ state, commit }, payload: { device: DeviceInterface }): Promise<boolean> {
    if (state.semaphore.fetching.items.includes(payload.device.id)) {
      return false
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.FETCHING,
      id: payload.device.id,
    })

    try {
      await DeviceControl.api().get(
        `${ModuleApiPrefix}/v1/devices/${payload.device.id}/controls`,
        apiOptions,
      )

      return true
    } catch (e: any) {
      throw new ApiError(
        'devices-module.device-controls.fetch.failed',
        e,
        'Fetching device controls failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.FETCHING,
        id: payload.device.id,
      })
    }
  },

  async transmitCommand(_store, payload: { control: DeviceControlInterface, value?: string | number | boolean | null }): Promise<boolean> {
    if (!DeviceControl.query().where('id', payload.control.id).exists()) {
      throw new Error('devices-module.device-controls.transmit.failed')
    }

    const device = Device.find(payload.control.deviceId)

    if (device === null) {
      throw new Error('devices-module.device-controls.transmit.failed')
    }

    return new Promise((resolve, reject) => {
      DeviceControl.wamp().call<{ data: string }>({
        routing_key: GlobalRoutingKeys.DEVICES_CONTROL_DATA,
        origin: DeviceControl.$devicesModuleOrigin,
        data: {
          action: ControlAction.SET,
          device: device.id,
          control: payload.control.id,
          expected_value: payload.value,
        },
      })
        .then((response: RpCallResponse<{ data: string }>): void => {
          if (get(response.data, 'response') === 'accepted') {
            resolve(true)
          } else {
            reject(new Error('devices-module.device-controls.transmit.failed'))
          }
        })
        .catch((): void => {
          reject(new Error('devices-module.device-controls.transmit.failed'))
        })
    })
  },

  async socketData({ state, commit }, payload: { origin: string, routingKey: string, data: string }): Promise<boolean> {
    if (payload.origin !== ModuleOrigin.MODULE_DEVICES) {
      return false
    }

    if (
      ![
        RoutingKeys.DEVICES_CONTROL_ENTITY_REPORTED,
        RoutingKeys.DEVICES_CONTROL_ENTITY_CREATED,
        RoutingKeys.DEVICES_CONTROL_ENTITY_UPDATED,
        RoutingKeys.DEVICES_CONTROL_ENTITY_DELETED,
      ].includes(payload.routingKey as RoutingKeys)
    ) {
      return false
    }

    const body: ExchangeEntity = JSON.parse(payload.data)

    const validate = jsonSchemaValidator.compile<ExchangeEntity>(exchangeEntitySchema)

    if (validate(body)) {
      if (
        !DeviceControl.query().where('id', body.id).exists() &&
        payload.routingKey === RoutingKeys.DEVICES_CONTROL_ENTITY_DELETED
      ) {
        return true
      }

      if (payload.routingKey === RoutingKeys.DEVICES_CONTROL_ENTITY_DELETED) {
        commit('SET_SEMAPHORE', {
          type: SemaphoreTypes.DELETING,
          id: body.id,
        })

        try {
          await DeviceControl.delete(body.id)
        } catch (e: any) {
          throw new OrmError(
            'devices-module.device-controls.delete.failed',
            e,
            'Delete device control failed.',
          )
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: SemaphoreTypes.DELETING,
            id: body.id,
          })
        }
      } else {
        if (payload.routingKey === RoutingKeys.DEVICES_CONTROL_ENTITY_UPDATED && state.semaphore.updating.includes(body.id)) {
          return true
        }

        commit('SET_SEMAPHORE', {
          type: payload.routingKey === RoutingKeys.DEVICES_CONTROL_ENTITY_REPORTED ? SemaphoreTypes.GETTING : (payload.routingKey === RoutingKeys.DEVICES_CONTROL_ENTITY_UPDATED ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING),
          id: body.id,
        })

        const entityData: { [index: string]: string | boolean | number | string[] | number[] | DataType | null | undefined } = {
          type: DeviceControlEntityTypes.CONTROL,
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
          await DeviceControl.insertOrUpdate({
            data: entityData,
          })
        } catch (e: any) {
          const failedEntity = DeviceControl.query().with('device').where('id', body.id).first()

          if (failedEntity !== null && failedEntity.device !== null) {
            // Updating entity on api failed, we need to refresh entity
            await DeviceControl.get(
              failedEntity.device,
              body.id,
            )
          }

          throw new OrmError(
            'devices-module.device-controls.update.failed',
            e,
            'Edit device control failed.',
          )
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: payload.routingKey === RoutingKeys.DEVICES_CONTROL_ENTITY_UPDATED ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
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

const moduleMutations: MutationTree<DeviceControlState> = {
  ['SET_SEMAPHORE'](state: DeviceControlState, action: SemaphoreAction): void {
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

  ['CLEAR_SEMAPHORE'](state: DeviceControlState, action: SemaphoreAction): void {
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

  ['RESET_STATE'](state: DeviceControlState): void {
    Object.assign(state, moduleState)
  },
}

export default {
  state: (): DeviceControlState => (moduleState),
  actions: moduleActions,
  mutations: moduleMutations,
}
