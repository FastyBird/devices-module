import {
  Fields,
  Item,
} from '@vuex-orm/core'

import capitalize from 'lodash/capitalize'

import Device from '@/lib/devices/Device'
import { DeviceInterface } from '@/lib/devices/types'
import Property from '@/lib/properties/Property'
import {
  DevicePropertyEntityTypes,
  DevicePropertyInterface,
  DevicePropertyUpdateInterface,
} from '@/lib/device-properties/types'

// ENTITY MODEL
// ============
export default class DeviceProperty extends Property implements DevicePropertyInterface {
  static get entity(): string {
    return 'device_property'
  }

  static fields(): Fields {
    return Object.assign(Property.fields(), {
      type: this.string(DevicePropertyEntityTypes.PROPERTY),

      device: this.belongsTo(Device, 'id'),
      deviceBackward: this.hasOne(Device, 'id', 'deviceId'),

      deviceId: this.string(''),
    })
  }

  type!: DevicePropertyEntityTypes

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
      Object.prototype.hasOwnProperty.call(DeviceProperty.store(), '$i18n') &&
      DeviceProperty.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.title`).toString().includes('devices.vendors.')
    ) {
      return DeviceProperty.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.title`).toString()
    }

    return capitalize(this.identifier)
  }

  static async get(device: DeviceInterface, id: string): Promise<boolean> {
    return await DeviceProperty.dispatch('get', {
      device,
      id,
    })
  }

  static async fetch(device: DeviceInterface): Promise<boolean> {
    return await DeviceProperty.dispatch('fetch', {
      device,
    })
  }

  static async edit(property: DevicePropertyInterface, data: DevicePropertyUpdateInterface): Promise<Item<DeviceProperty>> {
    return await DeviceProperty.dispatch('edit', {
      property,
      data,
    })
  }

  static transmitData(property: DevicePropertyInterface, value: string): Promise<boolean> {
    return DeviceProperty.dispatch('transmitData', {
      property,
      value,
    })
  }

  static reset(): void {
    DeviceProperty.dispatch('reset')
  }
}
