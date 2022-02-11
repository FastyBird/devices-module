import {
  Fields,
  Item,
  Model,
} from '@vuex-orm/core'
import {
  DeviceModel,
  FirmwareManufacturer,
  HardwareManufacturer,
  ConnectionState,
  DevicePropertyName,
} from '@fastybird/metadata'

import capitalize from 'lodash/capitalize'

import {
  DeviceCreateInterface,
  DeviceInterface,
  DeviceUpdateInterface,
} from '@/lib/models/devices/types'
import DeviceProperty from '@/lib/models/device-properties/DeviceProperty'
import { DevicePropertyInterface } from '@/lib/models/device-properties/types'
import Channel from '@/lib/models/channels/Channel'
import { ChannelInterface } from '@/lib/models/channels/types'
import Connector from '@/lib/models/connectors/Connector'
import { ConnectorInterface } from '@/lib/models/connectors/types'
import { DEVICE_ENTITY_REG_EXP } from '@/lib/helpers'
import { DeviceControlInterface } from '@/lib/models/device-controls/types'
import DeviceControl from '@/lib/models/device-controls/DeviceControl'

// ENTITY MODEL
// ============
export default class Device extends Model implements DeviceInterface {
  id!: string
  type!: string
  device!: { source: string, type: string }

  draft!: boolean

  identifier!: string
  name!: string | null
  comment!: string | null
  enabled!: boolean

  hardwareModel!: string
  hardwareManufacturer!: string
  hardwareVersion!: string | null
  macAddress!: string | null

  firmwareManufacturer!: string
  firmwareVersion!: string | null

  // Relations
  relationshipNames!: string[]

  parent!: DeviceInterface | null
  parentBackward!: DeviceInterface | null
  parentId!: string | null

  children!: DeviceInterface[]

  channels!: ChannelInterface[]
  controls!: DeviceControlInterface[]
  properties!: DevicePropertyInterface[]

  connector!: ConnectorInterface
  connectorBackward!: ConnectorInterface
  connectorId!: string

  owner!: string | null

  static get entity(): string {
    return 'devices_module_device'
  }

  get isEnabled(): boolean {
    return this.enabled
  }

  get isReady(): boolean {
    const property = DeviceProperty
      .query()
      .where('identifier', DevicePropertyName.STATE)
      .where('deviceId', this.id)
      .first()

    return property !== null && (
      property.value === ConnectionState.READY
      || property.value === ConnectionState.RUNNING
      || property.value === ConnectionState.CONNECTED
    )
  }

  get icon(): string {
    if (this.hardwareManufacturer === HardwareManufacturer.ITEAD) {
      switch (this.hardwareModel) {
        case DeviceModel.SONOFF_SC:
          return 'thermometer-half'

        case DeviceModel.SONOFF_POW:
        case DeviceModel.SONOFF_POW_R2:
          return 'calculator'
      }
    }

    return 'plug'
  }

  get title(): string {
    if (this.name !== null) {
      return this.name
    }

    const storeInstance = Device.store()

    if (
      Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')
    ) {
      if (this.isCustomModel) {
        return capitalize(this.identifier)
      }

      // @ts-ignore
      if (!storeInstance.$i18n.t(`devicesModule.vendors.${this.hardwareManufacturer}.devices.${this.hardwareModel}.title`).toString().includes('devicesModule.vendors.')) {
        // @ts-ignore
        return storeInstance.$i18n.t(`devicesModule.vendors.${this.hardwareManufacturer}.devices.${this.hardwareModel}.title`).toString()
      }
    }

    return capitalize(this.identifier)
  }

  get hasComment(): boolean {
    return this.comment !== null && this.comment !== ''
  }

  get isCustomModel(): boolean {
    return this.hardwareModel === DeviceModel.CUSTOM
  }

  static fields(): Fields {
    return {
      id: this.string(''),
      type: this.string(''),
      device: this.attr({ source: 'N/A', type: 'N/A' }),

      draft: this.boolean(false),

      identifier: this.string(''),
      name: this.string(null).nullable(),
      comment: this.string(null).nullable(),
      enabled: this.boolean(false),

      hardwareModel: this.string(DeviceModel.CUSTOM),
      hardwareManufacturer: this.string(HardwareManufacturer.GENERIC),
      hardwareVersion: this.string(null).nullable(),
      hardwareMacAddress: this.string(null).nullable(),

      firmwareManufacturer: this.string(FirmwareManufacturer.GENERIC),
      firmwareVersion: this.string(null).nullable(),

      // Relations
      relationshipNames: this.attr([]),

      parentId: this.string(null).nullable(),
      parent: this.belongsTo(Device, 'id'),
      parentBackward: this.hasOne(Device, 'id', 'parentId'),

      children: this.hasMany(Device, 'parentId'),

      channels: this.hasMany(Channel, 'deviceId'),
      controls: this.hasMany(DeviceControl, 'deviceId'),
      properties: this.hasMany(DeviceProperty, 'deviceId'),

      connector: this.belongsTo(Connector, 'id'),
      connectorBackward: this.hasOne(Connector, 'id', 'connectorId'),

      connectorId: this.string(''),

      owner: this.string(null).nullable(),
    }
  }

  static async get(id: string, includeChannels: boolean): Promise<boolean> {
    return await Device.dispatch('get', {
      id,
      includeChannels,
    })
  }

  static async fetch(includeChannels: boolean): Promise<boolean> {
    return await Device.dispatch('fetch', {
      includeChannels,
    })
  }

  static async add(connector: ConnectorInterface, data: DeviceCreateInterface, id?: string | null, draft = true): Promise<Item<Device>> {
    return await Device.dispatch('add', {
      connector,
      id,
      draft,
      data,
    })
  }

  static async edit(device: DeviceInterface, data: DeviceUpdateInterface): Promise<Item<Device>> {
    return await Device.dispatch('edit', {
      device,
      data,
    })
  }

  static async save(device: DeviceInterface): Promise<Item<Device>> {
    return await Device.dispatch('save', {
      device,
    })
  }

  static async remove(device: DeviceInterface): Promise<boolean> {
    return await Device.dispatch('remove', {
      device,
    })
  }

  static reset(): Promise<void> {
    return Device.dispatch('reset')
  }

  static beforeCreate(items: DeviceInterface[] | DeviceInterface): DeviceInterface[] | DeviceInterface {
    if (Array.isArray(items)) {
      return items.map((item: DeviceInterface) => {
        return Object.assign(item, clearDeviceAttributes(item))
      })
    } else {
      return Object.assign(items, clearDeviceAttributes(items))
    }
  }

  static beforeUpdate(items: DeviceInterface[] | DeviceInterface): DeviceInterface[] | DeviceInterface {
    if (Array.isArray(items)) {
      return items.map((item: DeviceInterface) => {
        return Object.assign(item, clearDeviceAttributes(item))
      })
    } else {
      return Object.assign(items, clearDeviceAttributes(items))
    }
  }
}

const clearDeviceAttributes = (item: {[key: string]: any}): {[key: string]: any} => {
  const typeRegex = new RegExp(DEVICE_ENTITY_REG_EXP)

  const parsedTypes = typeRegex.exec(`${item.type}`)

  item.device = { source: 'N/A', type: 'N/A' }

  if (
    parsedTypes !== null
    && 'groups' in parsedTypes
    && typeof parsedTypes.groups !== 'undefined'
    && 'source' in parsedTypes.groups
    && 'type' in parsedTypes.groups
  ) {
    item.device = {
      source: parsedTypes.groups.source,
      type: parsedTypes.groups.type,
    }
  }

  return item
}
