import {
  Fields,
  Item,
} from '@vuex-orm/core'

import capitalize from 'lodash/capitalize'

import Channel from '@/lib/channels/Channel'
import { ChannelInterface } from '@/lib/channels/types'
import {
  ChannelPropertyEntityTypes,
  ChannelPropertyInterface,
  ChannelPropertyUpdateInterface,
} from '@/lib/channel-properties/types'
import Property from '@/lib/properties/Property'
import Device from '@/lib/devices/Device'
import { DeviceInterface } from '@/lib/devices/types'

// ENTITY MODEL
// ============
export default class ChannelProperty extends Property implements ChannelPropertyInterface {
  static get entity(): string {
    return 'channel_property'
  }

  static fields(): Fields {
    return Object.assign(Property.fields(), {
      type: this.string(ChannelPropertyEntityTypes.PROPERTY),

      channel: this.belongsTo(Channel, 'id'),
      channelBackward: this.hasOne(Channel, 'id', 'channelId'),

      channelId: this.string(''),
    })
  }

  type!: ChannelPropertyEntityTypes

  channel!: ChannelInterface | null
  channelBackward!: ChannelInterface | null

  channelId!: string

  get title(): string {
    if (this.name !== null) {
      return this.name
    }

    if (
      this.device !== null &&
      !this.device.isCustomModel &&
      Object.prototype.hasOwnProperty.call(ChannelProperty.store(), '$i18n')
    ) {
      if (this.identifier.includes('_')) {
        const propertyPart = this.identifier.substring(0, (this.identifier.indexOf('_')))
        const propertyNum = parseInt(this.identifier.substring(this.identifier.indexOf('_') + 1), 10)

        if (!ChannelProperty.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.devices.${this.device.hardwareModel}.properties.${propertyPart}.title`).toString().includes('devices.vendors.')) {
          return ChannelProperty.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.devices.${this.device.hardwareModel}.properties.${propertyPart}.title`, { number: propertyNum }).toString()
        }

        if (!ChannelProperty.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${propertyPart}.title`).toString().includes('devices.vendors.')) {
          return ChannelProperty.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${propertyPart}.title`, { number: propertyNum }).toString()
        }
      }

      if (!ChannelProperty.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.devices.${this.device.hardwareModel}.properties.${this.identifier}.title`).toString().includes('devices.vendors.')) {
        return ChannelProperty.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.devices.${this.device.hardwareModel}.properties.${this.identifier}.title`).toString()
      }

      if (!ChannelProperty.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.title`).toString().includes('devices.vendors.')) {
        return ChannelProperty.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.title`).toString()
      }
    }

    return capitalize(this.identifier)
  }

  // @ts-ignore
  get device(): DeviceInterface | null {
    if (this.channel === null) {
      const channel = Channel
        .query()
        .where('id', this.channelId)
        .first()

      if (channel !== null) {
        return Device
          .query()
          .where('id', channel.deviceId)
          .first()
      }

      return null
    }

    return Device
      .query()
      .where('id', this.channel.deviceId)
      .first()
  }

  static async get(channel: ChannelInterface, id: string): Promise<boolean> {
    return await ChannelProperty.dispatch('get', {
      channel,
      id,
    })
  }

  static async fetch(channel: ChannelInterface): Promise<boolean> {
    return await ChannelProperty.dispatch('fetch', {
      channel,
    })
  }

  static async edit(property: ChannelPropertyInterface, data: ChannelPropertyUpdateInterface): Promise<Item<ChannelProperty>> {
    return await ChannelProperty.dispatch('edit', {
      property,
      data,
    })
  }

  static transmitData(property: ChannelPropertyInterface, value: string): Promise<boolean> {
    return ChannelProperty.dispatch('transmitData', {
      property,
      value,
    })
  }

  static reset(): void {
    ChannelProperty.dispatch('reset')
  }
}
