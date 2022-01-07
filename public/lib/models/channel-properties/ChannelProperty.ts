import {
  Fields,
  Item,
} from '@vuex-orm/core'

import capitalize from 'lodash/capitalize'

import Channel from '@/lib/models/channels/Channel'
import { ChannelInterface } from '@/lib/models/channels/types'
import {
  ChannelPropertyCreateInterface,
  ChannelPropertyEntityTypes,
  ChannelPropertyInterface,
  ChannelPropertyUpdateInterface,
} from '@/lib/models/channel-properties/types'
import Property from '@/lib/models/properties/Property'
import Device from '@/lib/models/devices/Device'
import { DeviceInterface } from '@/lib/models/devices/types'

// ENTITY MODEL
// ============
export default class ChannelProperty extends Property implements ChannelPropertyInterface {
  static get entity(): string {
    return 'devices_channel_property'
  }

  static fields(): Fields {
    return Object.assign(Property.fields(), {
      type: this.string(''),

      channel: this.belongsTo(Channel, 'id'),
      channelBackward: this.hasOne(Channel, 'id', 'channelId'),

      channelId: this.string(''),
    })
  }

  type!: ChannelPropertyEntityTypes

  channel!: ChannelInterface | null
  channelBackward!: ChannelInterface | null

  channelId!: string

  get deviceInstance(): DeviceInterface | null {
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

  get title(): string {
    if (this.name !== null) {
      return this.name
    }

    const storeInstance = ChannelProperty.store()

    if (
      this.deviceInstance !== null &&
      !this.deviceInstance.isCustomModel &&
      Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')
    ) {
      if (this.identifier.includes('_')) {
        const propertyPart = this.identifier.substring(0, (this.identifier.indexOf('_')))
        const propertyNum = parseInt(this.identifier.substring(this.identifier.indexOf('_') + 1), 10)

        // @ts-ignore
        if (!storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.devices.${this.deviceInstance.hardwareModel}.properties.${propertyPart}.title`).toString().includes('devicesModule.vendors.')) {
          // @ts-ignore
          return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.devices.${this.deviceInstance.hardwareModel}.properties.${propertyPart}.title`, { number: propertyNum }).toString()
        }

        // @ts-ignore
        if (!storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${propertyPart}.title`).toString().includes('devicesModule.vendors.')) {
          // @ts-ignore
          return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${propertyPart}.title`, { number: propertyNum }).toString()
        }
      }

      // @ts-ignore
      if (!storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.devices.${this.deviceInstance.hardwareModel}.properties.${this.identifier}.title`).toString().includes('devicesModule.vendors.')) {
        // @ts-ignore
        return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.devices.${this.deviceInstance.hardwareModel}.properties.${this.identifier}.title`).toString()
      }

      // @ts-ignore
      if (!storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.title`).toString().includes('devicesModule.vendors.')) {
        // @ts-ignore
        return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.title`).toString()
      }
    }

    return capitalize(this.identifier)
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

  static async add(channel: ChannelInterface, data: ChannelPropertyCreateInterface, id?: string | null, draft = true): Promise<Item<ChannelProperty>> {
    return await ChannelProperty.dispatch('add', {
      channel,
      id,
      draft,
      data,
    })
  }

  static async edit(property: ChannelPropertyInterface, data: ChannelPropertyUpdateInterface): Promise<Item<ChannelProperty>> {
    return await ChannelProperty.dispatch('edit', {
      property,
      data,
    })
  }

  static async save(property: ChannelPropertyInterface): Promise<Item<ChannelProperty>> {
    return await ChannelProperty.dispatch('save', {
      property,
    })
  }

  static async remove(property: ChannelPropertyInterface): Promise<boolean> {
    return await ChannelProperty.dispatch('remove', {
      property,
    })
  }

  static transmitData(property: ChannelPropertyInterface, value: string): Promise<boolean> {
    return ChannelProperty.dispatch('transmitData', {
      property,
      value,
    })
  }

  static reset(): Promise<void> {
    return ChannelProperty.dispatch('reset')
  }
}
