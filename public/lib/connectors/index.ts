import { Item } from '@vuex-orm/core'
import { RpCallResponse } from '@fastybird/vue-wamp-v1'
import * as exchangeEntitySchema from '@fastybird/modules-metadata/resources/schemas/devices-module/entity.connector.json'
import { ModuleOrigin, ConnectorControlAction, ConnectorEntity as ExchangeEntity, DevicesModule as RoutingKeys } from '@fastybird/modules-metadata'

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

import Connector from '@/lib/connectors/Connector'
import {
  SemaphoreTypes,
  ConnectorInterface,
  ConnectorResponseInterface,
  ConnectorsResponseInterface,
  ConnectorUpdateInterface,
} from '@/lib/connectors/types'

import {
  ApiError,
  OrmError,
} from '@/lib/errors'
import {
  JsonApiModelPropertiesMapper,
  JsonApiPropertiesMapper,
} from '@/lib/jsonapi'
import {
  ModuleApiPrefix,
  ConnectorJsonModelInterface,
} from '@/lib/types'

interface SemaphoreFetchingState {
  items: boolean
  item: Array<string>
}

interface SemaphoreState {
  fetching: SemaphoreFetchingState
  updating: Array<string>
}

interface ConnectorState {
  semaphore: SemaphoreState
  firstLoad: boolean
}

interface SemaphoreAction {
  type: SemaphoreTypes
  id?: string;
}

const jsonApiFormatter = new Jsona({
  modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
  jsonPropertiesMapper: new JsonApiPropertiesMapper(),
})

const apiOptions = {
  dataTransformer: (result: AxiosResponse<ConnectorResponseInterface> | AxiosResponse<ConnectorsResponseInterface>): ConnectorJsonModelInterface | Array<ConnectorJsonModelInterface> => <ConnectorJsonModelInterface | Array<ConnectorJsonModelInterface>>jsonApiFormatter.deserialize(result.data),
}

const jsonSchemaValidator = new Ajv()

const moduleState: ConnectorState = {

  semaphore: {
    fetching: {
      items: false,
      item: [],
    },
    updating: [],
  },

  firstLoad: false,
}

const moduleGetters: GetterTree<ConnectorState, any> = {
  firstLoadFinished: state => (): boolean => {
    return !!state.firstLoad
  },

  getting: state => (id: string): boolean => {
    return state.semaphore.fetching.item.includes(id)
  },

  fetching: state => (): boolean => {
    return state.semaphore.fetching.items
  },
}

const moduleActions: ActionTree<ConnectorState, any> = {
  async get({ state, commit }, payload: { id: string }): Promise<boolean> {
    if (state.semaphore.fetching.item.includes(payload.id)) {
      return false
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.GETTING,
      id: payload.id,
    })

    try {
      await Connector.api().get(
        `${ModuleApiPrefix}/v1/connectors/${payload.id}`,
        apiOptions,
      )

      return true
    } catch (e) {
      throw new ApiError(
        'devices-module.connectors.fetch.failed',
        e,
        'Fetching connectors failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.GETTING,
        id: payload.id,
      })
    }
  },

  async fetch({ state, commit }): Promise<boolean> {
    if (state.semaphore.fetching.items) {
      return false
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.FETCHING,
    })

    try {
      await Connector.api().get(
        `${ModuleApiPrefix}/v1/connectors`,
        apiOptions,
      )

      commit('SET_FIRST_LOAD', true)

      return true
    } catch (e) {
      throw new ApiError(
        'devices-module.connectors.fetch.failed',
        e,
        'Fetching connectors failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.FETCHING,
      })
    }
  },

  async edit({
    state,
    commit,
  }, payload: { connector: ConnectorInterface, data: ConnectorUpdateInterface }): Promise<Item<Connector>> {
    if (state.semaphore.updating.includes(payload.connector.id)) {
      throw new Error('devices-module.connectors.update.inProgress')
    }

    if (!Connector.query().where('id', payload.connector.id).exists()) {
      throw new Error('devices-module.connectors.update.failed')
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.UPDATING,
      id: payload.connector.id,
    })

    try {
      await Connector.update({
        where: payload.connector.id,
        data: payload.data,
      })
    } catch (e) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.connector.id,
      })

      throw new OrmError(
        'devices-module.connectors.update.failed',
        e,
        'Edit connector failed.',
      )
    }

    const updatedEntity = Connector.find(payload.connector.id)

    if (updatedEntity === null) {
      // Updated entity could not be loaded from database
      await Connector.get(
        payload.connector.id,
      )

      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.connector.id,
      })

      throw new Error('devices-module.connectors.update.failed')
    }

    try {
      await Connector.api().patch(
        `${ModuleApiPrefix}/v1/connectors/${updatedEntity.id}`,
        jsonApiFormatter.serialize({
          stuff: updatedEntity,
        }),
        apiOptions,
      )

      return Connector.find(payload.connector.id)
    } catch (e) {
      // Updating entity on api failed, we need to refresh entity
      await Connector.get(
        payload.connector.id,
      )

      throw new ApiError(
        'devices-module.connectors.update.failed',
        e,
        'Edit connector failed.',
      )
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.connector.id,
      })
    }
  },

  transmitCommand(_store, payload: { connector: ConnectorInterface, command: ConnectorControlAction }): Promise<boolean> {
    if (!Connector.query().where('id', payload.connector.id).exists()) {
      throw new Error('devices-module.connector.transmit.failed')
    }

    return new Promise((resolve, reject) => {
      Connector.wamp().call({
        routing_key: RoutingKeys.CONNECTOR_CONTROLS,
        origin: Connector.$devicesModuleOrigin,
        data: {
          control: payload.command,
          connector: payload.connector.id,
        },
      })
        .then((response: RpCallResponse): void => {
          if (get(response.data, 'response') === 'accepted') {
            resolve(true)
          } else {
            reject(new Error('devices-module.connector.transmit.failed'))
          }
        })
        .catch((): void => {
          reject(new Error('devices-module.connector.transmit.failed'))
        })
    })
  },

  async socketData({ state, commit }, payload: { origin: string, routingKey: string, data: string }): Promise<boolean> {
    if (payload.origin !== ModuleOrigin.MODULE_DEVICES_ORIGIN) {
      return false
    }

    if (
      ![
        RoutingKeys.CONNECTOR_CREATED_ENTITY,
        RoutingKeys.CONNECTOR_UPDATED_ENTITY,
        RoutingKeys.CONNECTOR_DELETED_ENTITY,
      ].includes(payload.routingKey as RoutingKeys)
    ) {
      return false
    }

    const body: ExchangeEntity = JSON.parse(payload.data)

    const validate = jsonSchemaValidator.compile<ExchangeEntity>(exchangeEntitySchema)

    if (validate(body)) {
      if (
        !Connector.query().where('id', body.id).exists() &&
        (payload.routingKey === RoutingKeys.CONNECTOR_UPDATED_ENTITY || payload.routingKey === RoutingKeys.CONNECTOR_DELETED_ENTITY)
      ) {
        throw new Error('devices-module.connectors.update.failed')
      }

      if (payload.routingKey === RoutingKeys.CONNECTOR_DELETED_ENTITY) {
        commit('SET_SEMAPHORE', {
          type: SemaphoreTypes.DELETING,
          id: body.id,
        })

        try {
          await Connector.delete(body.id)
        } catch (e) {
          throw new OrmError(
            'devices-module.connectors.delete.failed',
            e,
            'Delete connector failed.',
          )
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: SemaphoreTypes.DELETING,
            id: body.id,
          })
        }
      } else {
        if (payload.routingKey === RoutingKeys.CONNECTOR_UPDATED_ENTITY && state.semaphore.updating.includes(body.id)) {
          return true
        }

        commit('SET_SEMAPHORE', {
          type: payload.routingKey === RoutingKeys.CONNECTOR_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
          id: body.id,
        })

        const entityData: { [index: string]: any } = {}

        Object.keys(body)
          .forEach((attrName) => {
            const kebabName = attrName.replace(/([a-z][A-Z0-9])/g, g => `${g[0]}_${g[1].toLowerCase()}`)

            entityData[kebabName] = body[attrName]
          })

        try {
          await Connector.insertOrUpdate({
            data: entityData,
          })
        } catch (e) {
          // Updating entity on api failed, we need to refresh entity
          await Connector.get(
            body.id,
          )

          throw new OrmError(
            'devices-module.connectors.update.failed',
            e,
            'Edit connector failed.',
          )
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: payload.routingKey === RoutingKeys.CONNECTOR_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
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

const moduleMutations: MutationTree<ConnectorState> = {
  ['SET_FIRST_LOAD'](state: ConnectorState, action: boolean): void {
    state.firstLoad = action
  },

  ['SET_SEMAPHORE'](state: ConnectorState, action: SemaphoreAction): void {
    switch (action.type) {
      case SemaphoreTypes.FETCHING:
        state.semaphore.fetching.items = true
        break

      case SemaphoreTypes.GETTING:
        state.semaphore.fetching.item.push(get(action, 'id', 'notValid'))

        // Make all keys uniq
        state.semaphore.fetching.item = uniq(state.semaphore.fetching.item)
        break

      case SemaphoreTypes.UPDATING:
        state.semaphore.updating.push(get(action, 'id', 'notValid'))

        // Make all keys uniq
        state.semaphore.updating = uniq(state.semaphore.updating)
        break
    }
  },

  ['CLEAR_SEMAPHORE'](state: ConnectorState, action: SemaphoreAction): void {
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
    }
  },

  ['RESET_STATE'](state: ConnectorState): void {
    Object.assign(state, moduleState)
  },
}

export default {
  state: (): ConnectorState => (moduleState),
  getters: moduleGetters,
  actions: moduleActions,
  mutations: moduleMutations,
}
