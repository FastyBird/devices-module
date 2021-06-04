import { Item } from '@vuex-orm/core'
import * as exchangeEntitySchema
  from '@fastybird/modules-metadata/resources/schemas/devices-module/entity.device.connector.json'
import {
  ModuleOrigin,
  DeviceConnectorEntity as ExchangeEntity,
  DevicesModule as RoutingKeys,
} from '@fastybird/modules-metadata'

import {
  ActionTree,
  MutationTree,
} from 'vuex'
import Jsona from 'jsona'
import Ajv from 'ajv'
import { v4 as uuid } from 'uuid'
import { AxiosResponse } from 'axios'
import uniq from 'lodash/uniq'

import Device from '@/lib/devices/Device'
import { DeviceInterface } from '@/lib/devices/types'
import DeviceConnector from '@/lib/device-connector/DeviceConnector'
import {
  DeviceConnectorEntityTypes,
  DeviceConnectorResponseInterface,
  DeviceConnectorUpdateInterface,
  DeviceConnectorInterface,
  DeviceConnectorCreateInterface,
} from '@/lib/device-connector/types'

import {
  ApiError,
  OrmError,
} from '@/lib/errors'
import {
  JsonApiModelPropertiesMapper,
  JsonApiPropertiesMapper,
} from '@/lib/jsonapi'
import { DeviceConnectorJsonModelInterface, ModuleApiPrefix, SemaphoreTypes } from '@/lib/types'
import { ConnectorInterface } from '@/lib/connectors/types'

interface SemaphoreFetchingState {
  item: Array<string>
}

interface SemaphoreState {
  fetching: SemaphoreFetchingState
  creating: Array<string>
  updating: Array<string>
  deleting: Array<string>
}

interface DeviceConnectorState {
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
  dataTransformer: (result: AxiosResponse<DeviceConnectorResponseInterface>): DeviceConnectorJsonModelInterface | Array<DeviceConnectorJsonModelInterface> => <DeviceConnectorJsonModelInterface | Array<DeviceConnectorJsonModelInterface>>jsonApiFormatter.deserialize(result.data),
}

const jsonSchemaValidator = new Ajv()

const moduleState: DeviceConnectorState = {

  semaphore: {
    fetching: {
      item: [],
    },
    creating: [],
    updating: [],
    deleting: [],
  },

}

const moduleActions: ActionTree<DeviceConnectorState, any> = {
  async get({state, commit}, payload: { device: DeviceInterface }): Promise<boolean> {
    if (state.semaphore.fetching.item.includes(payload.device.id)) {
      return false
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.GETTING,
      id: payload.device.id,
    })

    try {
      await DeviceConnector.api().get(
        `${ModuleApiPrefix}/v1/devices/${payload.device.id}/connector`,
        apiOptions,
      )

      return true
    } catch (e) {
      throw new ApiError(
        'devices-module.device-connector.get.failed',
        e,
        'Fetching device connector failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.GETTING,
        id: payload.device.id,
      })
    }
  },

  async add({commit}, payload: { id?: string | null, draft?: boolean, device: DeviceInterface, connector: ConnectorInterface, data: DeviceConnectorCreateInterface }): Promise<Item<DeviceConnector>> {
    const id = typeof payload.id !== 'undefined' && payload.id !== null && payload.id !== '' ? payload.id : uuid().toString()
    const draft = typeof payload.draft !== 'undefined' ? payload.draft : false

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.CREATING,
      id,
    })

    try {
      await DeviceConnector.insert({
        data: Object.assign({}, payload.data, {
          id,
          draft,
          deviceId: payload.device.id,
          connectorId: payload.connector.id,
        }),
      })
    } catch (e) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.CREATING,
        id,
      })

      throw new OrmError(
        'devices-module.device-connector.create.failed',
        e,
        'Create device connector failed.',
      )
    }

    const createdEntity = DeviceConnector.find(id)

    if (createdEntity === null) {
      await DeviceConnector.delete(id)

      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.CREATING,
        id,
      })

      throw new Error('devices-module.device-connector.create.failed')
    }

    if (draft) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.CREATING,
        id,
      })

      return DeviceConnector.find(id)
    } else {
      try {
        await DeviceConnector.api().post(
          `${ModuleApiPrefix}/v1/devices/${payload.device.id}/connector`,
          jsonApiFormatter.serialize({
            stuff: createdEntity,
          }),
          apiOptions,
        )

        return DeviceConnector.find(id)
      } catch (e) {
        // Entity could not be created on api, we have to remove it from database
        await DeviceConnector.delete(id)

        throw new ApiError(
          'devices-module.device-connector.create.failed',
          e,
          'Create device connector failed.',
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
             }, payload: { connector: DeviceConnectorInterface, data: DeviceConnectorUpdateInterface }): Promise<Item<DeviceConnector>> {
    if (state.semaphore.updating.includes(payload.connector.id)) {
      throw new Error('devices-module.device-connector.update.inProgress')
    }

    if (!DeviceConnector.query().where('id', payload.connector.id).exists()) {
      throw new Error('devices-module.device-connector.update.failed')
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.UPDATING,
      id: payload.connector.id,
    })

    try {
      await DeviceConnector.update({
        where: payload.connector.id,
        data: payload.data,
      })
    } catch (e) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.connector.id,
      })

      throw new OrmError(
        'devices-module.device-connector.update.failed',
        e,
        'Edit device connector failed.',
      )
    }

    const updatedEntity = DeviceConnector.find(payload.connector.id)

    if (updatedEntity === null) {
      const device = Device.find(payload.connector.deviceId)

      if (device !== null) {
        // Updated entity could not be loaded from database
        await DeviceConnector.get(device)
      }

      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.connector.id,
      })

      throw new Error('devices-module.device-connector.update.failed')
    }

    if (updatedEntity.draft) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.connector.id,
      })

      return DeviceConnector.find(payload.connector.id)
    } else {
      try {
        await DeviceConnector.api().patch(
          `${ModuleApiPrefix}/v1/devices/${updatedEntity.deviceId}/connector`,
          jsonApiFormatter.serialize({
            stuff: updatedEntity,
          }),
          apiOptions,
        )

        return DeviceConnector.find(payload.connector.id)
      } catch (e) {
        const device = Device.find(payload.connector.deviceId)

        if (device !== null) {
          // Updating entity on api failed, we need to refresh entity
          await DeviceConnector.get(device)
        }

        throw new ApiError(
          'devices-module.device-connector.update.failed',
          e,
          'Edit device connector failed.',
        )
      } finally {
        commit('CLEAR_SEMAPHORE', {
          type: SemaphoreTypes.UPDATING,
          id: payload.connector.id,
        })
      }
    }
  },

  async save({state, commit}, payload: { connector: DeviceConnectorInterface }): Promise<Item<DeviceConnector>> {
    if (state.semaphore.updating.includes(payload.connector.id)) {
      throw new Error('devices-module.device-connector.save.inProgress')
    }

    if (!DeviceConnector.query().where('id', payload.connector.id).where('draft', true).exists()) {
      throw new Error('devices-module.device-connector.save.failed')
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.UPDATING,
      id: payload.connector.id,
    })

    const entityToSave = DeviceConnector.find(payload.connector.id)

    if (entityToSave === null) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.connector.id,
      })

      throw new Error('devices-module.device-connector.save.failed')
    }

    try {
      await DeviceConnector.api().post(
        `${ModuleApiPrefix}/v1/devices/${entityToSave.deviceId}/connector`,
        jsonApiFormatter.serialize({
          stuff: entityToSave,
        }),
        apiOptions,
      )

      return DeviceConnector.find(payload.connector.id)
    } catch (e) {
      throw new ApiError(
        'devices-module.device-connector.save.failed',
        e,
        'Save draft device connector failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.connector.id,
      })
    }
  },

  async socketData({state, commit}, payload: { origin: string, routingKey: string, data: string }): Promise<boolean> {
    if (payload.origin !== ModuleOrigin.MODULE_DEVICES_ORIGIN) {
      return false
    }

    if (
      ![
        RoutingKeys.DEVICES_CONNECTOR_CREATED_ENTITY,
        RoutingKeys.DEVICES_CONNECTOR_UPDATED_ENTITY,
        RoutingKeys.DEVICES_CONNECTOR_DELETED_ENTITY,
      ].includes(payload.routingKey as RoutingKeys)
    ) {
      return false
    }

    const body: ExchangeEntity = JSON.parse(payload.data)

    const validate = jsonSchemaValidator.compile<ExchangeEntity>(exchangeEntitySchema)

    if (validate(body)) {
      if (
        !DeviceConnector.query().where('id', body.id).exists() &&
        (payload.routingKey === RoutingKeys.DEVICES_PROPERTY_UPDATED_ENTITY || payload.routingKey === RoutingKeys.DEVICES_PROPERTY_DELETED_ENTITY)
      ) {
        throw new Error('devices-module.device-connector.update.failed')
      }

      if (payload.routingKey === RoutingKeys.DEVICES_PROPERTY_DELETED_ENTITY) {
        commit('SET_SEMAPHORE', {
          type: SemaphoreTypes.DELETING,
          id: body.id,
        })

        try {
          await DeviceConnector.delete(body.id)
        } catch (e) {
          throw new OrmError(
            'devices-module.device-connector.delete.failed',
            e,
            'Delete device connector failed.',
          )
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: SemaphoreTypes.DELETING,
            id: body.id,
          })
        }
      } else {
        if (payload.routingKey === RoutingKeys.DEVICES_PROPERTY_UPDATED_ENTITY && state.semaphore.updating.includes(body.id)) {
          return true
        }

        commit('SET_SEMAPHORE', {
          type: payload.routingKey === RoutingKeys.DEVICES_PROPERTY_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
          id: body.id,
        })

        const entityData: { [index: string]: any } = {
          type: DeviceConnectorEntityTypes.CONNECTOR,
        }

        Object.keys(body)
          .forEach((attrName) => {
            const kebabName = attrName.replace(/([a-z][A-Z0-9])/g, g => `${g[0]}_${g[1].toLowerCase()}`)

            if (kebabName === 'device') {
              const device = Device.query().where('identifier', body[attrName]).first()

              if (device !== null) {
                entityData.deviceId = device.id
              }
            } else {
              entityData[kebabName] = body[attrName]
            }
          })

        try {
          await DeviceConnector.insertOrUpdate({
            data: entityData,
          })
        } catch (e) {
          const failedEntity = DeviceConnector.query().with('device').where('id', body.id).first()

          if (failedEntity !== null && failedEntity.device !== null) {
            // Updating entity on api failed, we need to refresh entity
            await DeviceConnector.get(failedEntity.device)
          }

          throw new OrmError(
            'devices-module.device-connector.update.failed',
            e,
            'Edit device connector failed.',
          )
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: payload.routingKey === RoutingKeys.DEVICES_PROPERTY_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
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

const moduleMutations: MutationTree<DeviceConnectorState> = {
  ['SET_SEMAPHORE'](state: DeviceConnectorState, action: SemaphoreAction): void {
    switch (action.type) {
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

  ['CLEAR_SEMAPHORE'](state: DeviceConnectorState, action: SemaphoreAction): void {
    switch (action.type) {
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

  ['RESET_STATE'](state: DeviceConnectorState): void {
    Object.assign(state, moduleState)
  },
}

export default {
  state: (): DeviceConnectorState => (moduleState),
  actions: moduleActions,
  mutations: moduleMutations,
}
