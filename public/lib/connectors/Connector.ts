import {
  Fields,
  Item,
  Model,
} from '@vuex-orm/core'
import { ConnectorControlAction } from '@fastybird/modules-metadata'

import {
  ConnectorEntityTypes,
  ConnectorInterface,
  ConnectorUpdateInterface,
} from './types'

import DeviceConnector from '@/lib/device-connector/DeviceConnector'
import { DeviceConnectorInterface } from '@/lib/device-connector/types'

// ENTITY MODEL
// ============
export default class Connector extends Model implements ConnectorInterface {
  static get entity(): string {
    return 'connector'
  }

  static fields(): Fields {
    return {
      id: this.string(''),
      type: this.string(''),

      name: this.string(''),
      enabled: this.boolean(true),

      // Relations
      relationshipNames: this.attr([]),

      devices: this.hasMany(DeviceConnector, 'connectorId'),

      // FB bus
      address: this.number(null).nullable(),
      serialInterface: this.string(null).nullable(),
      baudRate: this.number(null).nullable(),

      // FB MQTT v1
      server: this.string(null).nullable(),
      port: this.number(null).nullable(),
      securedPort: this.number(null).nullable(),
      username: this.string(null).nullable(),
      password: this.string(null).nullable(),
    }
  }

  id!: string
  type!: ConnectorEntityTypes

  name!: string
  enabled!: boolean

  control!: Array<string>

  relationshipNames!: Array<string>

  devices!: Array<DeviceConnectorInterface>

  address!: number
  serialInterface!: string
  baudRate!: number

  server!: string
  port!: number
  securedPort!: number
  username!: string
  password!: string

  get isEnabled(): boolean {
    return this.enabled
  }

  get icon(): string {
    return 'magic'
  }

  static async get(id: string): Promise<boolean> {
    return await Connector.dispatch('get', {
      id,
    })
  }

  static async fetch(): Promise<boolean> {
    return await Connector.dispatch('fetch')
  }

  static async edit(connector: ConnectorInterface, data: ConnectorUpdateInterface): Promise<Item<Connector>> {
    return await Connector.dispatch('edit', {
      connector,
      data,
    })
  }

  static transmitCommand(connector: ConnectorInterface, command: ConnectorControlAction): Promise<boolean> {
    return Connector.dispatch('transmitCommand', {
      connector,
      command,
    })
  }

  static reset(): void {
    Connector.dispatch('reset')
  }
}
