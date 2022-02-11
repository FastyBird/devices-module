import {
  Fields,
  Item,
  Model,
} from '@vuex-orm/core'

import {
  ConnectorInterface,
  FbBusConnectorUpdateInterface,
  FbMqttConnectorUpdateInterface,
  ModbusConnectorUpdateInterface,
  ShellyConnectorUpdateInterface,
  SonoffConnectorUpdateInterface,
  TuyaConnectorUpdateInterface,
} from '@/lib/models/connectors/types'
import Device from '@/lib/models/devices/Device'
import { DeviceInterface } from '@/lib/models/devices/types'
import { CONNECTOR_ENTITY_REG_EXP } from '@/lib/helpers'

// ENTITY MODEL
// ============
export default class Connector extends Model implements ConnectorInterface {
  id!: string
  type!: string
  connector!: { source: string, type: string }

  name!: string
  enabled!: boolean

  // Relations
  relationshipNames!: string[]

  devices!: DeviceInterface[]

  static get entity(): string {
    return 'devices_module_connector'
  }

  get isEnabled(): boolean {
    return this.enabled
  }

  get icon(): string {
    return 'magic'
  }

  static fields(): Fields {
    return {
      id: this.string(''),
      type: this.string(''),
      connector: this.attr({ source: 'N/A', type: 'N/A' }),

      name: this.string(''),
      enabled: this.boolean(true),

      // Relations
      relationshipNames: this.attr([]),

      devices: this.hasMany(Device, 'connectorId'),
    }
  }

  static async get(id: string): Promise<boolean> {
    return await Connector.dispatch('get', {
      id,
    })
  }

  static async fetch(): Promise<boolean> {
    return await Connector.dispatch('fetch')
  }

  static async edit(connector: ConnectorInterface, data: FbMqttConnectorUpdateInterface | FbBusConnectorUpdateInterface | ShellyConnectorUpdateInterface | TuyaConnectorUpdateInterface | SonoffConnectorUpdateInterface | ModbusConnectorUpdateInterface): Promise<Item<Connector>> {
    return await Connector.dispatch('edit', {
      connector,
      data,
    })
  }

  static reset(): Promise<void> {
    return Connector.dispatch('reset')
  }

  static beforeCreate(items: ConnectorInterface[] | ConnectorInterface): ConnectorInterface[] | ConnectorInterface {
    if (Array.isArray(items)) {
      return items.map((item: ConnectorInterface) => {
        return Object.assign(item, clearConnectorAttributes(item))
      })
    } else {
      return Object.assign(items, clearConnectorAttributes(items))
    }
  }

  static beforeUpdate(items: ConnectorInterface[] | ConnectorInterface): ConnectorInterface[] | ConnectorInterface {
    if (Array.isArray(items)) {
      return items.map((item: ConnectorInterface) => {
        return Object.assign(item, clearConnectorAttributes(item))
      })
    } else {
      return Object.assign(items, clearConnectorAttributes(items))
    }
  }
}

const clearConnectorAttributes = (item: {[key: string]: any}): {[key: string]: any} => {
  const typeRegex = new RegExp(CONNECTOR_ENTITY_REG_EXP)

  const parsedTypes = typeRegex.exec(`${item.type}`)

  item.connector = { source: 'N/A', type: 'N/A' }

  if (
    parsedTypes !== null
    && 'groups' in parsedTypes
    && typeof parsedTypes.groups !== 'undefined'
    && 'source' in parsedTypes.groups
    && 'type' in parsedTypes.groups
  ) {
    item.connector = {
      source: parsedTypes.groups.source,
      type: parsedTypes.groups.type,
    }
  }

  return item
}
