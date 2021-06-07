import {
  Fields,
  Item,
  Model,
} from '@vuex-orm/core'
import { ChannelControlAction } from '@fastybird/modules-metadata'

import capitalize from 'lodash/capitalize'

import Device from '@/lib/models/devices/Device'
import { DeviceInterface } from '@/lib/models/devices/types'
import {
  ChannelEntityTypes,
  ChannelInterface,
  ChannelUpdateInterface,
} from '@/lib/models/channels/types'
import ChannelProperty from '@/lib/models/channel-properties/ChannelProperty'
import { ChannelPropertyInterface } from '@/lib/models/channel-properties/types'
import ChannelConfiguration from '@/lib/models/channel-configuration/ChannelConfiguration'
import { ChannelConfigurationInterface } from '@/lib/models/channel-configuration/types'

// ENTITY MODEL
// ============

export default class Channel extends Model implements ChannelInterface {
  static get entity(): string {
    return 'devices_channel'
  }

  static fields(): Fields {
    return {
      id: this.string(''),
      type: this.string(ChannelEntityTypes.CHANNEL),

      key: this.string(''),
      identifier: this.string(''),
      name: this.string(null).nullable(),
      comment: this.string(null).nullable(),

      control: this.attr([]),

      // Relations
      relationshipNames: this.attr([]),

      properties: this.hasMany(ChannelProperty, 'channelId'),
      configuration: this.hasMany(ChannelConfiguration, 'channelId'),

      device: this.belongsTo(Device, 'id'),
      deviceBackward: this.hasOne(Device, 'id', 'deviceId'),

      deviceId: this.string(''),
    }
  }

  id!: string
  type!: ChannelEntityTypes

  key!: string
  identifier!: string
  name!: string | null
  comment!: string | null

  control!: string[]

  relationshipNames!: string[]

  properties!: ChannelPropertyInterface[]
  configuration!: ChannelConfigurationInterface[]

  device!: DeviceInterface | null
  deviceBackward!: DeviceInterface | null

  deviceId!: string

  get title(): string {
    if (this.name !== null) {
      return this.name
    }

    const device = Device
      .query()
      .where('id', this.deviceId)
      .first()

    const storeInstance = Channel.store()

    if (
      device !== null &&
      !device.isCustomModel &&
      Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')
    ) {
      if (this.identifier.includes('_')) {
        const channelPart = this.identifier.substring(0, (this.identifier.indexOf('_')))
        const channelNum = parseInt(this.identifier.substring(this.identifier.indexOf('_') + 1), 10)

        // @ts-ignore
        if (!storeInstance.$i18n.t(`devicesModule.vendors.${device.hardwareManufacturer}.devices.${device.hardwareModel}.channels.${channelPart}.title`).toString().includes('devicesModule.vendors.')) {
          // @ts-ignore
          return storeInstance.$i18n.t(`devicesModule.vendors.${device.hardwareManufacturer}.devices.${device.hardwareModel}.channels.${channelPart}.title`, { number: (channelNum + 1) }).toString()
        }

        // @ts-ignore
        if (!storeInstance.$i18n.t(`devicesModule.vendors.${device.hardwareManufacturer}.channels.${channelPart}.title`).toString().includes('devicesModule.vendors.')) {
          // @ts-ignore
          return storeInstance.$i18n.t(`devicesModule.vendors.${device.hardwareManufacturer}.channels.${channelPart}.title`, { number: (channelNum + 1) }).toString()
        }
      }

      // @ts-ignore
      if (!storeInstance.$i18n.t(`devicesModule.vendors.${device.hardwareManufacturer}.devices.${device.hardwareModel}.channels.${this.identifier}.title`).toString().includes('devicesModule.vendors.')) {
        // @ts-ignore
        return storeInstance.$i18n.t(`devicesModule.vendors.${device.hardwareManufacturer}.devices.${device.hardwareModel}.channels.${this.identifier}.title`).toString()
      }

      // @ts-ignore
      if (!storeInstance.$i18n.t(`devicesModule.vendors.${device.hardwareManufacturer}.channels.${this.identifier}.title`).toString().includes('devicesModule.vendors.')) {
        // @ts-ignore
        return storeInstance.$i18n.t(`devicesModule.vendors.${device.hardwareManufacturer}.channels.${this.identifier}.title`).toString()
      }
    }

    return capitalize(this.identifier)
  }

  static async get(device: DeviceInterface, id: string): Promise<boolean> {
    return await Channel.dispatch('get', {
      device,
      id,
    })
  }

  static async fetch(device: DeviceInterface): Promise<boolean> {
    return await Channel.dispatch('fetch', {
      device,
    })
  }

  static async edit(channel: ChannelInterface, data: ChannelUpdateInterface): Promise<Item<Channel>> {
    return await Channel.dispatch('edit', {
      channel,
      data,
    })
  }

  static transmitCommand(channel: ChannelInterface, command: ChannelControlAction): Promise<boolean> {
    return Channel.dispatch('transmitCommand', {
      channel,
      command,
    })
  }

  static reset(): void {
    Channel.dispatch('reset')
  }
}
