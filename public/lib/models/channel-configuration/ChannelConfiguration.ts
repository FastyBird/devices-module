import {
  Fields,
  Item,
} from '@vuex-orm/core'

import capitalize from 'lodash/capitalize'

import Channel from '@/lib/models/channels/Channel'
import { ChannelInterface } from '@/lib/models/channels/types'
import {
  ChannelConfigurationEntityTypes,
  ChannelConfigurationInterface,
  ChannelConfigurationUpdateInterface,
} from '@/lib/models/channel-configuration/types'
import Device from '@/lib/models/devices/Device'
import { DeviceInterface } from '@/lib/models/devices/types'
import Configuration from '@/lib/models/configuration/Configuration'
import { ValuesItemInterface } from '@/lib/models/configuration/types'

// ENTITY MODEL
// ============
export default class ChannelConfiguration extends Configuration implements ChannelConfigurationInterface {
  static get entity(): string {
    return 'devices_channel_configuration'
  }

  static fields(): Fields {
    return Object.assign(Configuration.fields(), {
      type: this.string(''),

      channel: this.belongsTo(Channel, 'id'),
      channelBackward: this.hasOne(Channel, 'id', 'channelId'),

      channelId: this.string(''),
    })
  }

  type!: ChannelConfigurationEntityTypes

  channel!: ChannelInterface | null
  channelBackward!: ChannelInterface | null

  channelId!: string

  get title(): string {
    if (this.name !== null) {
      return this.name
    }

    const storeInstance = ChannelConfiguration.store()

    if (
      this.device !== null &&
      !this.device.isCustomModel &&
      Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')
    ) {
      if (this.identifier.includes('_')) {
        const configurationPart = this.identifier.substring(0, (this.identifier.indexOf('_'))).toLowerCase()
        const configurationNum = parseInt(this.identifier.substring(this.identifier.indexOf('_') + 1), 10)

        // @ts-ignore
        if (!storeInstance.$i18n.t(`devicesModule.vendors.${this.device.hardwareManufacturer}.devices.${this.device.hardwareModel}.configuration.${configurationPart}.title`).toString().includes('devicesModule.vendors.')) {
          // @ts-ignore
          return storeInstance.$i18n.t(`devicesModule.vendors.${this.device.hardwareManufacturer}.devices.${this.device.hardwareModel}.configuration.${configurationPart}.title`, { number: configurationNum }).toString()
        }

        // @ts-ignore
        if (!storeInstance.$i18n.t(`devicesModule.vendors.${this.device.hardwareManufacturer}.configuration.${configurationPart}.title`).toString().includes('devicesModule.vendors.')) {
          // @ts-ignore
          return storeInstance.$i18n.t(`devicesModule.vendors.${this.device.hardwareManufacturer}.configuration.${configurationPart}.title`, { number: configurationNum }).toString()
        }
      }

      // @ts-ignore
      if (!storeInstance.$i18n.t(`devicesModule.vendors.${this.device.hardwareManufacturer}.devices.${this.device.hardwareModel}.configuration.${this.identifier}.title`).toString().includes('devicesModule.vendors.')) {
        // @ts-ignore
        return storeInstance.$i18n.t(`devicesModule.vendors.${this.device.hardwareManufacturer}.devices.${this.device.hardwareModel}.configuration.${this.identifier}.title`).toString()
      }

      // @ts-ignore
      if (!storeInstance.$i18n.t(`devicesModule.vendors.${this.device.hardwareManufacturer}.configuration.${this.identifier}.title`).toString().includes('devicesModule.vendors.')) {
        // @ts-ignore
        return storeInstance.$i18n.t(`devicesModule.vendors.${this.device.hardwareManufacturer}.configuration.${this.identifier}.title`).toString()
      }
    }

    return capitalize(this.identifier)
  }

  get description(): string | null {
    if (this.comment !== null) {
      return this.comment
    }

    const storeInstance = ChannelConfiguration.store()

    if (
      this.device !== null &&
      !this.device.isCustomModel &&
      Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')
    ) {
      // @ts-ignore
      if (!storeInstance.$i18n.t(`devicesModule.vendors.${this.device.hardwareManufacturer}.devices.${this.device.hardwareModel}.configuration.${this.identifier}.description`).toString().includes('devicesModule.vendors.')) {
        // @ts-ignore
        return storeInstance.$i18n.t(`devicesModule.vendors.${this.device.hardwareManufacturer}.devices.${this.device.hardwareModel}.configuration.${this.identifier}.description`).toString()
      }

      // @ts-ignore
      if (!storeInstance.$i18n.t(`devicesModule.vendors.${this.device.hardwareManufacturer}.configuration.${this.identifier}.description`).toString().includes('devicesModule.vendors.')) {
        // @ts-ignore
        return storeInstance.$i18n.t(`devicesModule.vendors.${this.device.hardwareManufacturer}.configuration.${this.identifier}.description`).toString()
      }
    }

    return null
  }

  get selectValues(): ValuesItemInterface[] {
    if (!this.isSelect) {
      throw new Error(`This field is not allowed for entity type ${this.type}`)
    }

    const storeInstance = ChannelConfiguration.store()

    if (
      this.device !== null &&
      !this.device.isCustomModel &&
      Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')
    ) {
      const items: ValuesItemInterface[] = []

      this.values
        .forEach((item) => {
          let valueName = item.name

          // @ts-ignore
          if (!storeInstance.$i18n.t(`devicesModule.vendors.${this.device?.hardwareManufacturer}.devices.${this.device?.hardwareModel}.configuration.${this.identifier}.values.${item.name}`).toString().includes('devicesModule.vendors.')) {
            // @ts-ignore
            valueName = storeInstance.$i18n.t(`devicesModule.vendors.${this.device?.hardwareManufacturer}.devices.${this.device?.hardwareModel}.configuration.${this.identifier}.values.${item.name}`).toString()
            // @ts-ignore
          } else if (!storeInstance.$i18n.t(`devicesModule.vendors.${this.device?.hardwareManufacturer}.configuration.${this.identifier}.values.${item.name}`).toString().includes('devicesModule.vendors.')) {
            // @ts-ignore
            valueName = storeInstance.$i18n.t(`devicesModule.vendors.${this.device?.hardwareManufacturer}.configuration.${this.identifier}.values.${item.name}`).toString()
          }

          items.push({
            value: item.value,
            name: valueName,
          })
        })

      return items
    }

    return this.values
  }

  get formattedValue(): string {
    if (this.isSelect) {
      const storeInstance = ChannelConfiguration.store()

      if (
        this.device !== null &&
        !this.device.isCustomModel &&
        Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')
      ) {
        this.values
          .forEach((item) => {
            // eslint-disable-next-line eqeqeq
            if (String(item.value) === String(this.value)) {
              // @ts-ignore
              if (!storeInstance.$i18n.t(`devicesModule.vendors.${this.device?.hardwareManufacturer}.devices.${this.device?.hardwareModel}.configuration.${this.identifier}.values.${item.name}`).toString().includes('devicesModule.vendors.')) {
                // @ts-ignore
                return storeInstance.$i18n.t(`devicesModule.vendors.${this.device?.hardwareManufacturer}.devices.${this.device?.hardwareModel}.configuration.${this.identifier}.values.${item.name}`).toString()
                // @ts-ignore
              } else if (!storeInstance.$i18n.t(`devicesModule.vendors.${this.device?.hardwareManufacturer}.configuration.${this.identifier}.values.${item.name}`).toString().includes('devicesModule.vendors.')) {
                // @ts-ignore
                return storeInstance.$i18n.t(`devicesModule.vendors.${this.device?.hardwareManufacturer}.configuration.${this.identifier}.values.${item.name}`).toString()
              } else {
                return this.value
              }
            }
          })
      }
    }

    return this.value as string
  }

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
    return await ChannelConfiguration.dispatch('get', {
      channel,
      id,
    })
  }

  static async fetch(channel: ChannelInterface): Promise<boolean> {
    return await ChannelConfiguration.dispatch('fetch', {
      channel,
    })
  }

  static async edit(property: ChannelConfigurationInterface, data: ChannelConfigurationUpdateInterface): Promise<Item<ChannelConfiguration>> {
    return await ChannelConfiguration.dispatch('edit', {
      property,
      data,
    })
  }

  static transmitData(property: ChannelConfigurationInterface, value: string): Promise<boolean> {
    return ChannelConfiguration.dispatch('transmitData', {
      property,
      value,
    })
  }

  static reset(): Promise<void> {
    return ChannelConfiguration.dispatch('reset')
  }
}
