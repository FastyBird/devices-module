import { Item } from '@vuex-orm/core'
import { RpCallResponse } from '@fastybird/vue-wamp-v1'
import * as exchangeEntitySchema
  from '@fastybird/modules-metadata/resources/schemas/devices-module/entity.device.property.json'
import {
  ModuleOrigin,
  DevicePropertyEntity as ExchangeEntity,
  DevicesModuleRoutes as RoutingKeys,
  DataType, normalizeValue, PropertyType,
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
import DeviceProperty from '@/lib/models/device-properties/DeviceProperty'
import {
  DevicePropertyEntityTypes,
  DevicePropertyInterface,
  DevicePropertyResponseInterface,
  DevicePropertiesResponseInterface,
  DevicePropertyUpdateInterface,
} from '@/lib/models/device-properties/types'

import {
  ApiError,
  OrmError,
} from '@/lib/errors'
import {
  JsonApiModelPropertiesMapper,
  JsonApiJsonPropertiesMapper,
} from '@/lib/jsonapi'
import { DevicePropertyJsonModelInterface, ModuleApiPrefix, SemaphoreTypes } from '@/lib/types'

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

interface DevicePropertyState {
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
  dataTransformer: (result: AxiosResponse<DevicePropertyResponseInterface> | AxiosResponse<DevicePropertiesResponseInterface>): DevicePropertyJsonModelInterface | DevicePropertyJsonModelInterface[] => jsonApiFormatter.deserialize(result.data) as DevicePropertyJsonModelInterface | DevicePropertyJsonModelInterface[],
}

const jsonSchemaValidator = new Ajv()

const moduleState: DevicePropertyState = {

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

const moduleActions: ActionTree<DevicePropertyState, unknown> = {
  async get({ state, commit }, payload: { device: DeviceInterface, id: string }): Promise<boolean> {
    if (state.semaphore.fetching.item.includes(payload.id)) {
      return false
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.GETTING,
      id: payload.id,
    })

    try {
      await DeviceProperty.api().get(
        `${ModuleApiPrefix}/v1/devices/${payload.device.id}/properties/${payload.id}`,
        apiOptions,
      )

      return true
    } catch (e: any) {
      throw new ApiError(
        'devices-module.device-properties.fetch.failed',
        e,
        'Fetching device property failed.',
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
      await DeviceProperty.api().get(
        `${ModuleApiPrefix}/v1/devices/${payload.device.id}/properties`,
        apiOptions,
      )

      return true
    } catch (e: any) {
      throw new ApiError(
        'devices-module.device-properties.fetch.failed',
        e,
        'Fetching device properties failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.FETCHING,
        id: payload.device.id,
      })
    }
  },

  async edit({ state, commit }, payload: { property: DevicePropertyInterface, data: DevicePropertyUpdateInterface }): Promise<Item<DeviceProperty>> {
    if (state.semaphore.updating.includes(payload.property.id)) {
      throw new Error('devices-module.device-properties.update.inProgress')
    }

    if (!DeviceProperty.query().where('id', payload.property.id).exists()) {
      throw new Error('devices-module.device-properties.update.failed')
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.UPDATING,
      id: payload.property.id,
    })

    try {
      await DeviceProperty.update({
        where: payload.property.id,
        data: payload.data,
      })
    } catch (e: any) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.property.id,
      })

      throw new OrmError(
        'devices-module.device-properties.update.failed',
        e,
        'Edit device property failed.',
      )
    }

    const updatedEntity = DeviceProperty.find(payload.property.id)

    if (updatedEntity === null) {
      const propertyDevice = Device.find(payload.property.deviceId)

      if (propertyDevice !== null) {
        // Updated entity could not be loaded from database
        await DeviceProperty.get(
          propertyDevice,
          payload.property.id,
        )
      }

      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.property.id,
      })

      throw new Error('devices-module.device-properties.update.failed')
    }

    const device = Device.find(payload.property.deviceId)

    if (device === null) {
      throw new Error('devices-module.device-configuration.update.failed')
    }

    try {
      await DeviceProperty.api().patch(
        `${ModuleApiPrefix}/v1/devices/${device.id}/properties/${updatedEntity.id}`,
        jsonApiFormatter.serialize({
          stuff: updatedEntity,
        }),
        apiOptions,
      )

      return DeviceProperty.find(payload.property.id)
    } catch (e: any) {
      // Updating entity on api failed, we need to refresh entity
      await DeviceProperty.get(
        device,
        payload.property.id,
      )

      throw new ApiError(
        'devices-module.device-properties.update.failed',
        e,
        'Edit device property failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.property.id,
      })
    }
  },

  async transmitData(_store, payload: { property: DevicePropertyInterface, value: string }): Promise<boolean> {
    if (!DeviceProperty.query().where('id', payload.property.id).exists()) {
      throw new Error('devices-module.device-properties.transmit.failed')
    }

    const device = Device.find(payload.property.deviceId)

    if (device === null) {
      throw new Error('devices-module.device-properties.transmit.failed')
    }

    const backupValue = payload.property.actualValue

    const expectedValue = payload.property.dataType !== null ? normalizeValue(payload.property.dataType, payload.value, payload.property.format) : payload.value

    try {
      await DeviceProperty.update({
        where: payload.property.id,
        data: {
          value: expectedValue,
        },
      })
    } catch (e: any) {
      throw new OrmError(
        'devices-module.device-properties.transmit.failed',
        e,
        'Edit device property failed.',
      )
    }

    return new Promise((resolve, reject) => {
      DeviceProperty.wamp().call<{ data: string }>({
        routing_key: RoutingKeys.DEVICES_PROPERTIES_DATA,
        origin: DeviceProperty.$devicesModuleOrigin,
        data: {
          device: device.id,
          property: payload.property.id,
          expected_value: expectedValue,
        },
      })
        .then((response: RpCallResponse<{ data: string }>): void => {
          if (get(response.data, 'response') === 'accepted') {
            resolve(true)
          } else {
            DeviceProperty.update({
              where: payload.property.id,
              data: {
                value: backupValue,
              },
            })

            reject(new Error('devices-module.device-properties.transmit.failed'))
          }
        })
        .catch((): void => {
          DeviceProperty.update({
            where: payload.property.id,
            data: {
              value: backupValue,
            },
          })

          reject(new Error('devices-module.device-properties.transmit.failed'))
        })
    })
  },

  async socketData({ state, commit }, payload: { origin: string, routingKey: string, data: string }): Promise<boolean> {
    if (payload.origin !== ModuleOrigin.MODULE_DEVICES) {
      return false
    }

    if (
      ![
        RoutingKeys.DEVICES_PROPERTY_ENTITY_CREATED,
        RoutingKeys.DEVICES_PROPERTY_ENTITY_UPDATED,
        RoutingKeys.DEVICES_PROPERTY_ENTITY_DELETED,
      ].includes(payload.routingKey as RoutingKeys)
    ) {
      return false
    }

    const body: ExchangeEntity = JSON.parse(payload.data)

    const validate = jsonSchemaValidator.compile<ExchangeEntity>(exchangeEntitySchema)

    if (validate(body)) {
      if (
        !DeviceProperty.query().where('id', body.id).exists() &&
        payload.routingKey === RoutingKeys.DEVICES_PROPERTY_ENTITY_DELETED
      ) {
        return true
      }

      if (payload.routingKey === RoutingKeys.DEVICES_PROPERTY_ENTITY_DELETED) {
        commit('SET_SEMAPHORE', {
          type: SemaphoreTypes.DELETING,
          id: body.id,
        })

        try {
          await DeviceProperty.delete(body.id)
        } catch (e: any) {
          throw new OrmError(
            'devices-module.device-properties.delete.failed',
            e,
            'Delete device property failed.',
          )
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: SemaphoreTypes.DELETING,
            id: body.id,
          })
        }
      } else {
        if (payload.routingKey === RoutingKeys.DEVICES_PROPERTY_ENTITY_UPDATED && state.semaphore.updating.includes(body.id)) {
          return true
        }

        commit('SET_SEMAPHORE', {
          type: payload.routingKey === RoutingKeys.DEVICES_PROPERTY_ENTITY_UPDATED ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
          id: body.id,
        })

        const entityData: { [index: string]: string | boolean | number | string[] | ((string | null)[])[] | (number | null)[] | DataType | null | undefined } = {}

        const camelRegex = new RegExp('_([a-z0-9])', 'g')

        Object.keys(body)
          .forEach((attrName) => {
            const camelName = attrName.replace(camelRegex, g => g[1].toUpperCase())

            if (camelName === 'type') {
              switch (body[attrName]) {
                case PropertyType.DYNAMIC:
                  entityData[camelName] = DevicePropertyEntityTypes.PROPERTY_DYNAMIC
                  break

                case PropertyType.STATIC:
                  entityData[camelName] = DevicePropertyEntityTypes.PROPERTY_STATIC
                  break
              }
            } else if (camelName === 'device') {
              const device = Device.query().where('id', body[attrName]).first()

              if (device !== null) {
                entityData.deviceId = device.id
              }
            } else {
              entityData[camelName] = body[attrName]
            }
          })

        try {
          await DeviceProperty.insertOrUpdate({
            data: entityData,
          })
        } catch (e: any) {
          const failedEntity = DeviceProperty.query().with('device').where('id', body.id).first()

          if (failedEntity !== null && failedEntity.device !== null) {
            // Updating entity on api failed, we need to refresh entity
            await DeviceProperty.get(
              failedEntity.device,
              body.id,
            )
          }

          throw new OrmError(
            'devices-module.device-properties.update.failed',
            e,
            'Edit device property failed.',
          )
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: payload.routingKey === RoutingKeys.DEVICES_PROPERTY_ENTITY_UPDATED ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
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

const moduleMutations: MutationTree<DevicePropertyState> = {
  ['SET_SEMAPHORE'](state: DevicePropertyState, action: SemaphoreAction): void {
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

  ['CLEAR_SEMAPHORE'](state: DevicePropertyState, action: SemaphoreAction): void {
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

  ['RESET_STATE'](state: DevicePropertyState): void {
    Object.assign(state, moduleState)
  },
}

export default {
  state: (): DevicePropertyState => (moduleState),
  actions: moduleActions,
  mutations: moduleMutations,
}
