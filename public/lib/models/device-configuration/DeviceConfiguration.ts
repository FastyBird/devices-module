import {
  Fields,
  Item,
} from '@vuex-orm/core'

import capitalize from 'lodash/capitalize'

import Device from '@/lib/models/devices/Device'
import { DeviceInterface } from '@/lib/models/devices/types'
import {
  DeviceConfigurationEntityTypes,
  DeviceConfigurationInterface,
  DeviceConfigurationUpdateInterface,
} from '@/lib/models/device-configuration/types'
import Configuration from '@/lib/models/configuration/Configuration'
import { ValuesItemInterface } from '@/lib/models/configuration/types'

// ENTITY MODEL
// ============
export default class DeviceConfiguration extends Configuration implements DeviceConfigurationInterface {
  static get entity(): string {
    return 'devices_device_configuration'
  }

  static fields(): Fields {
    return Object.assign(Configuration.fields(), {
      type: this.string(DeviceConfigurationEntityTypes.CONFIGURATION),

      device: this.belongsTo(Device, 'id'),
      deviceBackward: this.hasOne(Device, 'id', 'deviceId'),

      deviceId: this.string(''),
    })
  }

  type!: DeviceConfigurationEntityTypes

  device!: DeviceInterface | null
  deviceBackward!: DeviceInterface | null

  deviceId!: string

  get title(): string {
    if (this.name !== null) {
      return this.name
    }

    const storeInstance = DeviceConfiguration.store()

    if (
      this.device !== null &&
      !this.device.isCustomModel &&
      Object.prototype.hasOwnProperty.call(storeInstance, '$i18n') &&
      // @ts-ignore
      !storeInstance.$i18n.t(`devicesModule.vendors.${this.device.hardwareManufacturer}.identifier.${this.identifier}.title`).toString().includes('devicesModule.vendors.')
    ) {
      // @ts-ignore
      return storeInstance.$i18n.t(`devicesModule.vendors.${this.device.hardwareManufacturer}.configuration.${this.identifier}.title`).toString()
    }

    return capitalize(this.identifier)
  }

  get description(): string | null {
    if (this.comment !== null) {
      return this.comment
    }

    const storeInstance = DeviceConfiguration.store()

    if (
      this.device !== null &&
      !this.device.isCustomModel &&
      Object.prototype.hasOwnProperty.call(storeInstance, '$i18n') &&
      // @ts-ignore
      !storeInstance.$i18n.t(`devicesModule.vendors.${this.device.hardwareManufacturer}.configuration.${this.identifier}.description`).toString().includes('devicesModule.vendors.')
    ) {
      // @ts-ignore
      return storeInstance.$i18n.t(`devicesModule.vendors.${this.device.hardwareManufacturer}.configuration.${this.identifier}.description`).toString()
    }

    return null
  }

  get selectValues(): ValuesItemInterface[] {
    if (!this.isSelect) {
      throw new Error(`This field is not allowed for entity type ${this.type}`)
    }

    const storeInstance = DeviceConfiguration.store()

    if (
      this.device !== null &&
      !this.device.isCustomModel &&
      Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')
    ) {
      const items: ValuesItemInterface[] = []

      this.values
        .forEach((item) => {
          items.push({
            value: item.value,
            // @ts-ignore
            name: storeInstance.$i18n.t(`devicesModule.vendors.${this.device?.hardwareManufacturer}.configuration.${this.identifier}.values.${item.name}`).toString(),
          })
        })

      return items
    }

    return this.values
  }

  get formattedValue(): string {
    if (this.isSelect) {
      const storeInstance = DeviceConfiguration.store()

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
              if (!storeInstance.$i18n.t(`devicesModule.vendors.${this.device?.hardwareManufacturer}.configuration.${this.identifier}.values.${item.name}`).toString().includes('devicesModule.vendors.')) {
                // @ts-ignore
                return storeInstance.$i18n.t(`devicesModule.vendors.${this.device?.hardwareManufacturer}.configuration.${this.identifier}.values.${item.name}`)
              } else {
                return this.value
              }
            }
          })
      }
    }

    return this.value as string
  }

  static async get(device: DeviceInterface, id: string): Promise<boolean> {
    return await DeviceConfiguration.dispatch('get', {
      device,
      id,
    })
  }

  static async fetch(device: DeviceInterface): Promise<boolean> {
    return await DeviceConfiguration.dispatch('fetch', {
      device,
    })
  }

  static async edit(property: DeviceConfigurationInterface, data: DeviceConfigurationUpdateInterface): Promise<Item<DeviceConfiguration>> {
    return await DeviceConfiguration.dispatch('edit', {
      property,
      data,
    })
  }

  static transmitData(property: DeviceConfigurationInterface, value: string): Promise<boolean> {
    return DeviceConfiguration.dispatch('transmitData', {
      property,
      value,
    })
  }

  static reset(): Promise<void> {
    return DeviceConfiguration.dispatch('reset')
  }
}
