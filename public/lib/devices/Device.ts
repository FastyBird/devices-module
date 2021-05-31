import {
  Fields,
  Item,
  Model,
} from '@vuex-orm/core'
import {
  DeviceModel,
  DeviceControlAction,
  FirmwareManufacturer,
  HardwareManufacturer,
  DeviceConnectionState,
} from '@fastybird/modules-metadata'

import capitalize from 'lodash/capitalize'

import {
  DeviceCreateInterface,
  DeviceEntityTypes,
  DeviceInterface,
  DeviceUpdateInterface,
} from '@/lib/devices/types'
import DeviceProperty from '@/lib/device-properties/DeviceProperty'
import { DevicePropertyInterface } from '@/lib/device-properties/types'
import DeviceConfiguration from '@/lib/device-configuration/DeviceConfiguration'
import { DeviceConfigurationInterface } from '@/lib/device-configuration/types'
import Channel from '@/lib/channels/Channel'
import { ChannelInterface } from '@/lib/channels/types'
import DeviceConnector from '@/lib/device-connector/DeviceConnector'

// ENTITY MODEL
// ============
export default class Device extends Model implements DeviceInterface {
  static get entity(): string {
    return 'device'
  }

  static fields(): Fields {
    return {
      id: this.string(''),
      type: this.string(DeviceEntityTypes.DEVICE),

      draft: this.boolean(false),

      parentId: this.string(null).nullable(),

      key: this.string(''),
      identifier: this.string(''),
      name: this.string(null).nullable(),
      comment: this.string(null).nullable(),
      state: this.string(DeviceConnectionState.UNKNOWN),
      enabled: this.boolean(false),

      hardwareModel: this.string(DeviceModel.CUSTOM),
      hardwareManufacturer: this.string(HardwareManufacturer.GENERIC),
      hardwareVersion: this.string(null).nullable(),
      macAddress: this.string(null).nullable(),

      firmwareManufacturer: this.string(FirmwareManufacturer.GENERIC),
      firmwareVersion: this.string(null).nullable(),

      control: this.attr([]),

      owner: this.string(null).nullable(),

      // Relations
      relationshipNames: this.attr([]),

      children: this.hasMany(Device, 'parentId'),
      channels: this.hasMany(Channel, 'deviceId'),
      properties: this.hasMany(DeviceProperty, 'deviceId'),
      configuration: this.hasMany(DeviceConfiguration, 'deviceId'),
      connector: this.hasOne(DeviceConnector, 'deviceId'),
    }
  }

  id!: string
  type!: DeviceEntityTypes

  draft!: boolean

  parentId!: string | null

  key!: string
  identifier!: string
  name!: string | null
  comment!: string | null
  state!: DeviceConnectionState
  enabled!: boolean

  hardwareModel!: DeviceModel
  hardwareManufacturer!: HardwareManufacturer
  hardwareVersion!: string | null
  macAddress!: string | null

  firmwareManufacturer!: FirmwareManufacturer
  firmwareVersion!: string | null

  control!: Array<string>

  owner!: string | null

  relationshipNames!: Array<string>

  children!: Array<DeviceInterface>
  channels!: Array<ChannelInterface>
  properties!: Array<DevicePropertyInterface>
  configuration!: Array<DeviceConfigurationInterface>
  connector!: DeviceConnector

  get isEnabled(): boolean {
    return this.enabled
  }

  get isReady(): boolean {
    return this.state === DeviceConnectionState.READY || this.state === DeviceConnectionState.RUNNING
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

    /*
    if (Object.prototype.hasOwnProperty.call(Device.store(), '$i18n')) {
      if (this.isCustomModel) {
        return capitalize(this.identifier)
      }

      if (!Device.store().$i18n.t(`devices.vendors.${this.hardwareManufacturer}.devices.${this.hardwareModel}.title`).toString().includes('devices.vendors.')) {
        return Device.store().$i18n.t(`devices.vendors.${this.hardwareManufacturer}.devices.${this.hardwareModel}.title`).toString()
      }
    }
    */

    return capitalize(this.identifier)
  }

  get hasComment(): boolean {
    return this.comment !== null && this.comment !== ''
  }

  get isCustomModel(): boolean {
    return this.hardwareModel === DeviceModel.CUSTOM
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

  static async add(data: DeviceCreateInterface, id?: string, draft = true): Promise<Item<Device>> {
    return await Device.dispatch('add', {
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

  static transmitCommand(device: DeviceInterface, command: DeviceControlAction): Promise<boolean> {
    return Device.dispatch('transmitCommand', {
      device,
      command,
    })
  }

  static reset(): void {
    Device.dispatch('reset')
  }
}
