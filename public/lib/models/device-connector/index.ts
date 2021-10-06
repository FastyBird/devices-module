import { Item } from '@vuex-orm/core'
import * as exchangeEntitySchema
  from '@fastybird/modules-metadata/resources/schemas/devices-module/entity.device.connector.json'
import {
  ModuleOrigin,
  DeviceConnectorEntity as ExchangeEntity,
  DevicesModule as RoutingKeys, ConnectorType,
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

import Device from '@/lib/models/devices/Device'
import { DeviceInterface } from '@/lib/models/devices/types'
import DeviceConnector from '@/lib/models/device-connector/DeviceConnector'
import {
  DeviceConnectorEntityTypes,
  DeviceConnectorResponseInterface,
  DeviceConnectorUpdateInterface,
  DeviceConnectorInterface,
  DeviceConnectorCreateInterface,
} from '@/lib/models/device-connector/types'

import {
  ApiError,
  OrmError,
} from '@/lib/errors'
import {
  JsonApiModelPropertiesMapper,
  JsonApiJsonPropertiesMapper,
} from '@/lib/jsonapi'
import { DeviceConnectorJsonModelInterface, ModuleApiPrefix, SemaphoreTypes } from '@/lib/types'
import Connector from '@/lib/models/connectors/Connector'
import { ConnectorInterface } from '@/lib/models/connectors/types'

interface SemaphoreFetchingState {
  item: string[]
}

interface SemaphoreState {
  fetching: SemaphoreFetchingState
  creating: string[]
  updating: string[]
  deleting: string[]
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
  jsonPropertiesMapper: new JsonApiJsonPropertiesMapper(),
})

const apiOptions = {
  dataTransformer: (result: AxiosResponse<DeviceConnectorResponseInterface>): DeviceConnectorJsonModelInterface | DeviceConnectorJsonModelInterface[] => jsonApiFormatter.deserialize(result.data) as DeviceConnectorJsonModelInterface | DeviceConnectorJsonModelInterface[],
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

const moduleActions: ActionTree<DeviceConnectorState, unknown> = {
  async get({ state, commit }, payload: { device: DeviceInterface }): Promise<boolean> {
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
    } catch (e: any) {
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

  async add({ commit }, payload: { id?: string | null, draft?: boolean, device: DeviceInterface, connector: ConnectorInterface, data: DeviceConnectorCreateInterface }): Promise<Item<DeviceConnector>> {
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
    } catch (e: any) {
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
      } catch (e: any) {
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

  async edit({ state, commit }, payload: { connector: DeviceConnectorInterface, data: DeviceConnectorUpdateInterface }): Promise<Item<DeviceConnector>> {
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
    } catch (e: any) {
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
      } catch (e: any) {
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

  async save({ state, commit }, payload: { connector: DeviceConnectorInterface }): Promise<Item<DeviceConnector>> {
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
    } catch (e: any) {
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

  async socketData({ state, commit }, payload: { origin: string, routingKey: string, data: string }): Promise<boolean> {
    if (payload.origin !== ModuleOrigin.MODULE_DEVICES) {
      return false
    }

    if (
      ![
        RoutingKeys.DEVICES_CONNECTOR_ENTITY_CREATED,
        RoutingKeys.DEVICES_CONNECTOR_ENTITY_UPDATED,
        RoutingKeys.DEVICES_CONNECTOR_ENTITY_DELETED,
      ].includes(payload.routingKey as RoutingKeys)
    ) {
      return false
    }

    const body: ExchangeEntity = JSON.parse(payload.data)

    const validate = jsonSchemaValidator.compile<ExchangeEntity>(exchangeEntitySchema)

    if (validate(body)) {
      if (
        !DeviceConnector.query().where('id', body.id).exists() &&
        payload.routingKey === RoutingKeys.DEVICES_CONNECTOR_ENTITY_DELETED
      ) {
        return true
      }

      if (payload.routingKey === RoutingKeys.DEVICES_CONNECTOR_ENTITY_DELETED) {
        commit('SET_SEMAPHORE', {
          type: SemaphoreTypes.DELETING,
          id: body.id,
        })

        try {
          await DeviceConnector.delete(body.id)
        } catch (e: any) {
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
        if (payload.routingKey === RoutingKeys.DEVICES_CONNECTOR_ENTITY_UPDATED && state.semaphore.updating.includes(body.id)) {
          return true
        }

        commit('SET_SEMAPHORE', {
          type: payload.routingKey === RoutingKeys.DEVICES_CONNECTOR_ENTITY_UPDATED ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
          id: body.id,
        })

        const entityData: { [index: string]: string | number | string[] | ConnectorType | boolean | null | undefined } = {
          type: DeviceConnectorEntityTypes.CONNECTOR,
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
            } else if (camelName === 'connector') {
              const connector = Connector.query().where('id', body[attrName]).first()

              if (connector !== null) {
                entityData.connectorId = connector.id
              }
            } else {
              entityData[camelName] = body[attrName]
            }
          })

        try {
          await DeviceConnector.insertOrUpdate({
            data: entityData,
          })
        } catch (e: any) {
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
            type: payload.routingKey === RoutingKeys.DEVICES_CONNECTOR_ENTITY_UPDATED ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
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
