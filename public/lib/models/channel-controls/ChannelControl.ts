import {
  Fields,
  Model,
} from '@vuex-orm/core'

import Channel from '@/lib/models/channels/Channel'
import { ChannelInterface } from '@/lib/models/channels/types'
import {
  ChannelControlEntityTypes,
  ChannelControlInterface,
} from '@/lib/models/channel-controls/types'
import Device from '@/lib/models/devices/Device'
import { DeviceInterface } from '@/lib/models/devices/types'

// ENTITY MODEL
// ============
export default class ChannelControl extends Model implements ChannelControlInterface {
  static get entity(): string {
    return 'devices_channel_control'
  }

  static fields(): Fields {
    return {
      id: this.string(''),
      type: this.string(ChannelControlEntityTypes.CONTROL),

      name: this.string(''),

      channel: this.belongsTo(Channel, 'id'),
      channelBackward: this.hasOne(Channel, 'id', 'channelId'),

      channelId: this.string(''),
    }
  }

  id!: string
  type!: ChannelControlEntityTypes

  name!: string

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

  static async get(channel: ChannelInterface, id: string): Promise<boolean> {
    return await ChannelControl.dispatch('get', {
      channel,
      id,
    })
  }

  static async fetch(channel: ChannelInterface): Promise<boolean> {
    return await ChannelControl.dispatch('fetch', {
      channel,
    })
  }

  static transmitCommand(control: ChannelControlInterface, value?: string | number | boolean | null): Promise<boolean> {
    return ChannelControl.dispatch('transmitCommand', {
      control,
      value,
    })
  }

  static reset(): Promise<void> {
    return ChannelControl.dispatch('reset')
  }
}
