import {
  Fields,
  Item,
  Model,
} from '@vuex-orm/core'
import { ChannelControlAction } from '@fastybird/modules-metadata'

import capitalize from 'lodash/capitalize'

import Device from '@/lib/devices/Device'
import { DeviceInterface } from '@/lib/devices/types'
import {
  ChannelEntityTypes,
  ChannelInterface,
  ChannelUpdateInterface,
} from '@/lib/channels/types'
import ChannelProperty from '@/lib/channel-properties/ChannelProperty'
import { ChannelPropertyInterface } from '@/lib/channel-properties/types'
import ChannelConfiguration from '@/lib/channel-configuration/ChannelConfiguration'
import { ChannelConfigurationInterface } from '@/lib/channel-configuration/types'

// ENTITY MODEL
// ============

export default class Channel extends Model implements ChannelInterface {
  static get entity(): string {
    return 'channel'
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

  control!: Array<string>

  relationshipNames!: Array<string>

  properties!: Array<ChannelPropertyInterface>
  configuration!: Array<ChannelConfigurationInterface>

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
        if (!storeInstance.$i18n.t(`devices.vendors.${device.hardwareManufacturer}.devices.${device.hardwareModel}.channels.${channelPart}.title`).toString().includes('devices.vendors.')) {
          // @ts-ignore
          return storeInstance.$i18n.t(`devices.vendors.${device.hardwareManufacturer}.devices.${device.hardwareModel}.channels.${channelPart}.title`, {number: (channelNum + 1)}).toString()
        }

        // @ts-ignore
        if (!storeInstance.$i18n.t(`devices.vendors.${device.hardwareManufacturer}.channels.${channelPart}.title`).toString().includes('devices.vendors.')) {
          // @ts-ignore
          return storeInstance.$i18n.t(`devices.vendors.${device.hardwareManufacturer}.channels.${channelPart}.title`, {number: (channelNum + 1)}).toString()
        }
      }

      // @ts-ignore
      if (!storeInstance.$i18n.t(`devices.vendors.${device.hardwareManufacturer}.devices.${device.hardwareModel}.channels.${this.identifier}.title`).toString().includes('devices.vendors.')) {
        // @ts-ignore
        return storeInstance.$i18n.t(`devices.vendors.${device.hardwareManufacturer}.devices.${device.hardwareModel}.channels.${this.identifier}.title`).toString()
      }

      // @ts-ignore
      if (!storeInstance.$i18n.t(`devices.vendors.${device.hardwareManufacturer}.channels.${this.identifier}.title`).toString().includes('devices.vendors.')) {
        // @ts-ignore
        return storeInstance.$i18n.t(`devices.vendors.${device.hardwareManufacturer}.channels.${this.identifier}.title`).toString()
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
