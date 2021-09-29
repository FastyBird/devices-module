import {
  Fields,
  Item,
} from '@vuex-orm/core'

import capitalize from 'lodash/capitalize'

import Device from '@/lib/models/devices/Device'
import { DeviceInterface } from '@/lib/models/devices/types'
import Property from '@/lib/models/properties/Property'
import {
  DevicePropertyEntityTypes,
  DevicePropertyInterface,
  DevicePropertyUpdateInterface,
} from '@/lib/models/device-properties/types'

// ENTITY MODEL
// ============
export default class DeviceProperty extends Property implements DevicePropertyInterface {
  static get entity(): string {
    return 'devices_device_property'
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

  get deviceInstance(): DeviceInterface | null {
    return this.device
  }

  get title(): string {
    if (this.name !== null) {
      return this.name
    }

    const storeInstance = DeviceProperty.store()

    if (
      this.device !== null &&
      !this.device.isCustomModel &&
      Object.prototype.hasOwnProperty.call(storeInstance, '$i18n') &&
      // @ts-ignore
      storeInstance.$i18n.t(`devicesModule.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.title`).toString().includes('devicesModule.vendors.')
    ) {
      // @ts-ignore
      return storeInstance.$i18n.t(`devicesModule.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.title`).toString()
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

  static reset(): Promise<void> {
    return DeviceProperty.dispatch('reset')
  }
}
