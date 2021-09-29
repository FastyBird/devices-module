import {
  Fields,
  Model,
} from '@vuex-orm/core'

import Connector from '@/lib/models/connectors/Connector'
import { ConnectorInterface } from '@/lib/models/connectors/types'
import {
  ConnectorControlEntityTypes,
  ConnectorControlInterface,
} from '@/lib/models/connector-controls/types'

// ENTITY MODEL
// ============
export default class ConnectorControl extends Model implements ConnectorControlInterface {
  static get entity(): string {
    return 'connectors_connector_control'
  }

  static fields(): Fields {
    return {
      id: this.string(''),
      type: this.string(ConnectorControlEntityTypes.CONTROL),

      name: this.string(''),

      connector: this.belongsTo(Connector, 'id'),
      connectorBackward: this.hasOne(Connector, 'id', 'connectorId'),

      connectorId: this.string(''),
    }
  }

  id!: string
  type!: ConnectorControlEntityTypes

  name!: string

  connector!: ConnectorInterface | null
  connectorBackward!: ConnectorInterface | null

  connectorId!: string

  get connectorInstance(): ConnectorInterface | null {
    return this.connector
  }

  static async get(connector: ConnectorInterface, id: string): Promise<boolean> {
    return await ConnectorControl.dispatch('get', {
      connector,
      id,
    })
  }

  static async fetch(connector: ConnectorInterface): Promise<boolean> {
    return await ConnectorControl.dispatch('fetch', {
      connector,
    })
  }

  static transmitCommand(control: ConnectorControl, value?: string | number | boolean | null): Promise<boolean> {
    return ConnectorControl.dispatch('transmitCommand', {
      control,
      value,
    })
  }

  static reset(): Promise<void> {
    return ConnectorControl.dispatch('reset')
  }
}
