import {
  Fields,
  Item,
} from '@vuex-orm/core'

import capitalize from 'lodash/capitalize'

import Device from '@/lib/devices/Device'
import { DeviceInterface } from '@/lib/devices/types'
import {
  DeviceConfigurationEntityTypes,
  DeviceConfigurationInterface,
  DeviceConfigurationUpdateInterface,
} from '@/lib/device-configuration/types'
import Configuration from '@/lib/configuration/Configuration'
import { ValuesItemInterface } from '@/lib/configuration/types'

// ENTITY MODEL
// ============
export default class DeviceConfiguration extends Configuration implements DeviceConfigurationInterface {
  static get entity(): string {
    return 'device_configuration'
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

    if (
      this.device !== null &&
      !this.device.isCustomModel &&
      Object.prototype.hasOwnProperty.call(DeviceConfiguration.store(), '$i18n') &&
      !DeviceConfiguration.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.identifier.${this.identifier}.title`).toString().includes('devices.vendors.')
    ) {
      return DeviceConfiguration.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.configuration.${this.identifier}.title`).toString()
    }

    return capitalize(this.identifier)
  }

  get description(): string | null {
    if (this.comment !== null) {
      return this.comment
    }

    if (
      this.device !== null &&
      !this.device.isCustomModel &&
      Object.prototype.hasOwnProperty.call(DeviceConfiguration.store(), '$i18n') &&
      !DeviceConfiguration.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.configuration.${this.identifier}.description`).toString().includes('devices.vendors.')
    ) {
      return DeviceConfiguration.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.configuration.${this.identifier}.description`).toString()
    }

    return null
  }

  get selectValues(): Array<ValuesItemInterface> {
    if (!this.isSelect) {
      throw new Error(`This field is not allowed for entity type ${this.type}`)
    }

    if (
      this.device !== null &&
      !this.device.isCustomModel &&
      Object.prototype.hasOwnProperty.call(DeviceConfiguration.store(), '$i18n')
    ) {
      const items: Array<ValuesItemInterface> = []

      this.values
        .forEach((item) => {
          items.push({
            value: item.value,
            name: DeviceConfiguration.store().$i18n.t(`devices.vendors.${this.device?.hardwareManufacturer}.configuration.${this.identifier}.values.${item.name}`).toString(),
          })
        })

      return items
    }

    return this.values
  }

  get formattedValue(): any {
    if (this.isSelect) {
      if (
        this.device !== null &&
        !this.device.isCustomModel &&
        Object.prototype.hasOwnProperty.call(DeviceConfiguration.store(), '$i18n')
      ) {
        this.values
          .forEach((item) => {
            // eslint-disable-next-line eqeqeq
            if (item.value == this.value) {
              if (!DeviceConfiguration.store().$i18n.t(`devices.vendors.${this.device?.hardwareManufacturer}.configuration.${this.identifier}.values.${item.name}`).toString().includes('devices.vendors.')) {
                return DeviceConfiguration.store().$i18n.t(`devices.vendors.${this.device?.hardwareManufacturer}.configuration.${this.identifier}.values.${item.name}`)
              } else {
                return this.value
              }
            }
          })
      }
    }

    return this.value
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

  static reset(): void {
    DeviceConfiguration.dispatch('reset')
  }
}
