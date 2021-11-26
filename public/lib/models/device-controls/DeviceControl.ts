import {
  Fields,
  Model,
} from '@vuex-orm/core'

import Device from '@/lib/models/devices/Device'
import { DeviceInterface } from '@/lib/models/devices/types'
import {
  DeviceControlEntityTypes,
  DeviceControlInterface,
} from '@/lib/models/device-controls/types'

// ENTITY MODEL
// ============
export default class DeviceControl extends Model implements DeviceControlInterface {
  static get entity(): string {
    return 'devices_device_control'
  }

  static fields(): Fields {
    return {
      id: this.string(''),
      type: this.string(DeviceControlEntityTypes.CONTROL),

      name: this.string(''),

      device: this.belongsTo(Device, 'id'),
      deviceBackward: this.hasOne(Device, 'id', 'deviceId'),

      deviceId: this.string(''),
    }
  }

  id!: string
  type!: DeviceControlEntityTypes

  name!: string

  device!: DeviceInterface | null
  deviceBackward!: DeviceInterface | null

  deviceId!: string

  get deviceInstance(): DeviceInterface | null {
    return this.device
  }

  static async get(device: DeviceInterface, id: string): Promise<boolean> {
    return await DeviceControl.dispatch('get', {
      device,
      id,
    })
  }

  static async fetch(device: DeviceInterface): Promise<boolean> {
    return await DeviceControl.dispatch('fetch', {
      device,
    })
  }

  static transmitCommand(control: DeviceControlInterface, value?: string | number | boolean | null): Promise<boolean> {
    return DeviceControl.dispatch('transmitCommand', {
      control,
      value,
    })
  }

  static reset(): Promise<void> {
    return DeviceControl.dispatch('reset')
  }
}
