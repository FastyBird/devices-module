import {
  Fields,
  Item,
  Model,
} from '@vuex-orm/core'

import capitalize from 'lodash/capitalize'

import Device from '@/lib/models/devices/Device'
import { DeviceInterface } from '@/lib/models/devices/types'
import {
  ChannelCreateInterface,
  ChannelInterface,
  ChannelUpdateInterface,
} from '@/lib/models/channels/types'
import ChannelProperty from '@/lib/models/channel-properties/ChannelProperty'
import { ChannelPropertyInterface } from '@/lib/models/channel-properties/types'
import { ChannelControlInterface } from '@/lib/models/channel-controls/types'
import ChannelControl from '@/lib/models/channel-controls/ChannelControl'

// ENTITY MODEL
// ============

export default class Channel extends Model implements ChannelInterface {
  id!: string
  type!: string

  draft!: boolean

  identifier!: string
  name!: string | null
  comment!: string | null

  // Relations
  relationshipNames!: string[]

  controls!: ChannelControlInterface[]
  properties!: ChannelPropertyInterface[]

  device!: DeviceInterface | null
  deviceBackward!: DeviceInterface | null
  deviceId!: string

  static get entity(): string {
    return 'devices_module_channel'
  }

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

  static fields(): Fields {
    return {
      id: this.string(''),
      type: this.string(''),

      draft: this.boolean(false),

      identifier: this.string(''),
      name: this.string(null).nullable(),
      comment: this.string(null).nullable(),

      // Relations
      relationshipNames: this.attr([]),

      controls: this.hasMany(ChannelControl, 'channelId'),
      properties: this.hasMany(ChannelProperty, 'channelId'),

      device: this.belongsTo(Device, 'id'),
      deviceBackward: this.hasOne(Device, 'id', 'deviceId'),
      deviceId: this.string(''),
    }
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

  static async add(device: DeviceInterface, data: ChannelCreateInterface, id?: string | null, draft = true): Promise<Item<Channel>> {
    return await Channel.dispatch('add', {
      device,
      id,
      draft,
      data,
    })
  }

  static async edit(channel: ChannelInterface, data: ChannelUpdateInterface): Promise<Item<Channel>> {
    return await Channel.dispatch('edit', {
      channel,
      data,
    })
  }

  static async save(property: ChannelInterface): Promise<Item<Channel>> {
    return await Channel.dispatch('save', {
      property,
    })
  }

  static async remove(property: ChannelInterface): Promise<boolean> {
    return await Channel.dispatch('remove', {
      property,
    })
  }

  static reset(): Promise<void> {
    return Channel.dispatch('reset')
  }
}
