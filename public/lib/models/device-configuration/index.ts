import { Item } from '@vuex-orm/core'
import { RpCallResponse } from '@fastybird/vue-wamp-v1'
import * as exchangeEntitySchema
  from '@fastybird/modules-metadata/resources/schemas/devices-module/entity.device.configuration.json'
import {
  ModuleOrigin,
  DeviceConfigurationEntity as ExchangeEntity,
  DevicesModule as RoutingKeys, DataType,
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
import DeviceConfiguration from '@/lib/models/device-configuration/DeviceConfiguration'
import {
  DeviceConfigurationEntityTypes,
  DeviceConfigurationInterface,
  DeviceConfigurationResponseInterface,
  DeviceConfigurationsResponseInterface,
  DeviceConfigurationUpdateInterface,
} from '@/lib/models/device-configuration/types'

import {
  ApiError,
  OrmError,
} from '@/lib/errors'
import {
  JsonApiModelPropertiesMapper,
  JsonApiJsonPropertiesMapper,
} from '@/lib/jsonapi'
import { DeviceConfigurationJsonModelInterface, ModuleApiPrefix, SemaphoreTypes } from '@/lib/types'

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

interface DeviceConfigurationState {
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
  dataTransformer: (result: AxiosResponse<DeviceConfigurationResponseInterface> | AxiosResponse<DeviceConfigurationsResponseInterface>): DeviceConfigurationJsonModelInterface | DeviceConfigurationJsonModelInterface[] => jsonApiFormatter.deserialize(result.data) as DeviceConfigurationJsonModelInterface | DeviceConfigurationJsonModelInterface[],
}

const jsonSchemaValidator = new Ajv()

const moduleState: DeviceConfigurationState = {

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

const moduleActions: ActionTree<DeviceConfigurationState, unknown> = {
  async get({ state, commit }, payload: { device: DeviceInterface, id: string }): Promise<boolean> {
    if (state.semaphore.fetching.item.includes(payload.id)) {
      return false
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.GETTING,
      id: payload.id,
    })

    try {
      await DeviceConfiguration.api().get(
        `${ModuleApiPrefix}/v1/devices/${payload.device.id}/configuration/${payload.id}`,
        apiOptions,
      )

      return true
    } catch (e) {
      throw new ApiError(
        'devices-module.device-configuration.fetch.failed',
        e,
        'Fetching device configuration failed.',
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
      await DeviceConfiguration.api().get(
        `${ModuleApiPrefix}/v1/devices/${payload.device.id}/configuration`,
        apiOptions,
      )

      return true
    } catch (e) {
      throw new ApiError(
        'devices-module.device-configuration.fetch.failed',
        e,
        'Fetching device configuration failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.FETCHING,
        id: payload.device.id,
      })
    }
  },

  async edit({ state, commit }, payload: { configuration: DeviceConfigurationInterface, data: DeviceConfigurationUpdateInterface }): Promise<Item<DeviceConfiguration>> {
    if (state.semaphore.updating.includes(payload.configuration.id)) {
      throw new Error('devices-module.device-configuration.update.inProgress')
    }

    if (!DeviceConfiguration.query().where('id', payload.configuration.id).exists()) {
      throw new Error('devices-module.device-configuration.update.failed')
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.UPDATING,
      id: payload.configuration.id,
    })

    try {
      await DeviceConfiguration.update({
        where: payload.configuration.id,
        data: payload.data,
      })
    } catch (e) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.configuration.id,
      })

      throw new OrmError(
        'devices-module.device-configuration.update.failed',
        e,
        'Edit device configuration failed.',
      )
    }

    const updatedEntity = DeviceConfiguration.find(payload.configuration.id)

    if (updatedEntity === null) {
      const configurationDevice = Device.find(payload.configuration.deviceId)

      if (configurationDevice !== null) {
        // Updated entity could not be loaded from database
        await DeviceConfiguration.get(
          configurationDevice,
          payload.configuration.id,
        )
      }

      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.configuration.id,
      })

      throw new Error('devices-module.device-configuration.update.failed')
    }

    const device = Device.find(payload.configuration.deviceId)

    if (device === null) {
      throw new Error('devices-module.device-configuration.update.failed')
    }

    try {
      await DeviceConfiguration.api().patch(
        `${ModuleApiPrefix}/v1/devices/${device.id}/configuration/${updatedEntity.id}`,
        jsonApiFormatter.serialize({
          stuff: updatedEntity,
        }),
        apiOptions,
      )

      return DeviceConfiguration.find(payload.configuration.id)
    } catch (e) {
      // Updating entity on api failed, we need to refresh entity
      await DeviceConfiguration.get(
        device,
        payload.configuration.id,
      )

      throw new ApiError(
        'devices-module.device-configuration.update.failed',
        e,
        'Edit device configuration failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.configuration.id,
      })
    }
  },

  async transmitData(_store, payload: { configuration: DeviceConfigurationInterface, value: string }): Promise<boolean> {
    if (!DeviceConfiguration.query().where('id', payload.configuration.id).exists()) {
      throw new Error('devices-module.device-configuration.transmit.failed')
    }

    const device = Device.find(payload.configuration.deviceId)

    if (device === null) {
      throw new Error('devices-module.device-configuration.transmit.failed')
    }

    const backupValue = payload.configuration.value

    try {
      await DeviceConfiguration.update({
        where: payload.configuration.id,
        data: {
          value: payload.value,
        },
      })
    } catch (e) {
      throw new OrmError(
        'devices-module.device-configuration.transmit.failed',
        e,
        'Edit device configuration failed.',
      )
    }

    return new Promise((resolve, reject) => {
      DeviceConfiguration.wamp().call<{ data: string }>({
        routing_key: RoutingKeys.DEVICES_CONFIGURATION_DATA,
        origin: DeviceConfiguration.$devicesModuleOrigin,
        data: {
          device: device.key,
          configuration: payload.configuration.key,
          expected: payload.value,
        },
      })
        .then((response: RpCallResponse<{ data: string }>): void => {
          if (get(response.data, 'response') === 'accepted') {
            resolve(true)
          } else {
            DeviceConfiguration.update({
              where: payload.configuration.id,
              data: {
                value: backupValue,
              },
            })

            reject(new Error('devices-module.device-configuration.transmit.failed'))
          }
        })
        .catch((): void => {
          DeviceConfiguration.update({
            where: payload.configuration.id,
            data: {
              value: backupValue,
            },
          })

          reject(new Error('devices-module.device-configuration.transmit.failed'))
        })
    })
  },

  async socketData({ state, commit }, payload: { origin: string, routingKey: string, data: string }): Promise<boolean> {
    if (payload.origin !== ModuleOrigin.MODULE_DEVICES_ORIGIN) {
      return false
    }

    if (
      ![
        RoutingKeys.DEVICES_CONFIGURATION_ENTITY_CREATED,
        RoutingKeys.DEVICES_CONFIGURATION_ENTITY_UPDATED,
        RoutingKeys.DEVICES_CONFIGURATION_ENTITY_DELETED,
      ].includes(payload.routingKey as RoutingKeys)
    ) {
      return false
    }

    const body: ExchangeEntity = JSON.parse(payload.data)

    const validate = jsonSchemaValidator.compile<ExchangeEntity>(exchangeEntitySchema)

    if (validate(body)) {
      if (
        !DeviceConfiguration.query().where('id', body.id).exists() &&
        (payload.routingKey === RoutingKeys.DEVICES_CONFIGURATION_ENTITY_UPDATED || payload.routingKey === RoutingKeys.DEVICES_CONFIGURATION_ENTITY_DELETED)
      ) {
        throw new Error('devices-module.device-configuration.update.failed')
      }

      if (payload.routingKey === RoutingKeys.DEVICES_CONFIGURATION_ENTITY_DELETED) {
        commit('SET_SEMAPHORE', {
          type: SemaphoreTypes.DELETING,
          id: body.id,
        })

        try {
          await DeviceConfiguration.delete(body.id)
        } catch (e) {
          throw new OrmError(
            'devices-module.device-configuration.delete.failed',
            e,
            'Delete device configuration failed.',
          )
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: SemaphoreTypes.DELETING,
            id: body.id,
          })
        }
      } else {
        if (payload.routingKey === RoutingKeys.DEVICES_CONFIGURATION_ENTITY_UPDATED && state.semaphore.updating.includes(body.id)) {
          return true
        }

        commit('SET_SEMAPHORE', {
          type: payload.routingKey === RoutingKeys.DEVICES_CONFIGURATION_ENTITY_UPDATED ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
          id: body.id,
        })

        const entityData: { [index: string]: string | number | (string | number | boolean)[] | DataType | null | undefined } = {
          type: DeviceConfigurationEntityTypes.CONFIGURATION,
        }

        Object.keys(body)
          .forEach((attrName) => {
            const kebabName = attrName.replace(/([a-z][A-Z0-9])/g, g => `${g[0]}_${g[1].toLowerCase()}`)

            if (kebabName === 'device') {
              const device = Device.query().where('device', body[attrName]).first()

              if (device !== null) {
                entityData.deviceId = device.id
              }
            } else {
              entityData[kebabName] = body[attrName]
            }
          })

        try {
          await DeviceConfiguration.insertOrUpdate({
            data: entityData,
          })
        } catch (e) {
          const failedEntity = DeviceConfiguration.query().with('device').where('id', body.id).first()

          if (failedEntity !== null && failedEntity.device !== null) {
            // Updating entity on api failed, we need to refresh entity
            await DeviceConfiguration.get(
              failedEntity.device,
              body.id,
            )
          }

          throw new OrmError(
            'devices-module.device-configuration.update.failed',
            e,
            'Edit device configuration failed.',
          )
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: payload.routingKey === RoutingKeys.DEVICES_CONFIGURATION_ENTITY_UPDATED ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
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

const moduleMutations: MutationTree<DeviceConfigurationState> = {
  ['SET_SEMAPHORE'](state: DeviceConfigurationState, action: SemaphoreAction): void {
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

  ['CLEAR_SEMAPHORE'](state: DeviceConfigurationState, action: SemaphoreAction): void {
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

  ['RESET_STATE'](state: DeviceConfigurationState): void {
    Object.assign(state, moduleState)
  },
}

export default {
  state: (): DeviceConfigurationState => (moduleState),
  actions: moduleActions,
  mutations: moduleMutations,
}
