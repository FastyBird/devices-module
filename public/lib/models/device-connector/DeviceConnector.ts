import {
  Fields,
  Item,
  Model,
} from '@vuex-orm/core'

import Device from '@/lib/models/devices/Device'
import {
  DeviceConnectorCreateInterface,
  DeviceConnectorEntityTypes,
  DeviceConnectorInterface,
  DeviceConnectorUpdateInterface,
} from '@/lib/models/device-connector/types'
import { DeviceInterface } from '@/lib/models/devices/types'
import Connector from '@/lib/models/connectors/Connector'
import { ConnectorInterface } from '@/lib/models/connectors/types'

// ENTITY MODEL
// ============

export default class DeviceConnector extends Model implements DeviceConnectorInterface {
  static get entity(): string {
    return 'devices_device_connector'
  }

  static fields(): Fields {
    return {
      id: this.string(''),
      type: this.string(DeviceConnectorEntityTypes.CONNECTOR),

      draft: this.boolean(false),

      // FB BUS Connector specific
      address: this.number(0),
      maxPacketLength: this.number(0),
      descriptionSupport: this.boolean(false),
      settingsSupport: this.boolean(false),
      configuredKeyLength: this.number(0),
      pubSubPubSupport: this.boolean(false),
      pubSubSubSupport: this.boolean(false),
      pubSubSubMaxSubscriptions: this.number(0),
      pubSubSubMaxConditions: this.number(0),
      pubSubSubMaxActions: this.number(0),

      // FB MQTT Connector specific
      username: this.string(''),
      password: this.string(''),

      // Relations
      relationshipNames: this.attr([]),

      device: this.belongsTo(Device, 'id'),
      deviceBackward: this.hasOne(Device, 'id', 'deviceId'),

      connector: this.belongsTo(Connector, 'id'),
      connectorBackward: this.hasOne(Connector, 'id', 'connectorId'),

      deviceId: this.string(''),
      connectorId: this.string(''),
    }
  }

  id!: string
  type!: DeviceConnectorEntityTypes

  draft!: boolean

  // FB BUS Connector specific
  address!: number
  maxPacketLength!: number
  descriptionSupport!: boolean
  settingsSupport!: boolean
  configuredKeyLength!: number
  pubSubPubSupport!: boolean
  pubSubSubSupport!: boolean
  pubSubSubMaxSubscriptions!: number
  pubSubSubMaxConditions!: number
  pubSubSubMaxActions!: number

  // FB MQTT Connector specific
  username!: string
  password!: string

  relationshipNames!: string[]

  device!: DeviceInterface | null
  deviceBackward!: DeviceInterface | null

  connector!: ConnectorInterface | null
  connectorBackward!: ConnectorInterface | null

  deviceId!: string
  connectorId!: string

  static async get(device: DeviceInterface): Promise<boolean> {
    return await DeviceConnector.dispatch('get', {
      device,
    })
  }

  static async add(device: DeviceInterface, connector: ConnectorInterface, data: DeviceConnectorCreateInterface, id?: string | null, draft = true): Promise<Item<DeviceConnector>> {
    return await DeviceConnector.dispatch('add', {
      id,
      draft,
      device,
      connector,
      data,
    })
  }

  static async edit(connector: DeviceConnectorInterface, data: DeviceConnectorUpdateInterface): Promise<Item<DeviceConnector>> {
    return await DeviceConnector.dispatch('edit', {
      connector,
      data,
    })
  }

  static async save(connector: DeviceConnectorInterface): Promise<Item<DeviceConnector>> {
    return await DeviceConnector.dispatch('save', {
      connector,
    })
  }

  static async remove(connector: DeviceConnectorInterface): Promise<boolean> {
    return await DeviceConnector.dispatch('remove', {
      connector,
    })
  }

  static reset(): Promise<void> {
    return DeviceConnector.dispatch('reset')
  }
}
