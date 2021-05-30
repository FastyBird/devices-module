import {
    Database,
    Model,
} from '@vuex-orm/core'

import { ModuleOrigin } from '@fastybird/modules-metadata'

import Device from '@/lib/devices/Device'
import devices from '@/lib/devices'
import DeviceProperty from '@/lib/device-properties/DeviceProperty'
import deviceProperties from '@/lib/device-properties'
import DeviceConfiguration from '@/lib/device-configuration/DeviceConfiguration'
import devicesConfiguration from '@/lib/device-configuration'
import DeviceConnector from '@/lib/device-connector/DeviceConnector'
import deviceConnector from '@/lib/device-connector/index'
import Channel from '@/lib/channels/Channel'
import channels from '@/lib/channels'
import ChannelProperty from '@/lib/channel-properties/ChannelProperty'
import channelProperties from '@/lib/channel-properties'
import ChannelConfiguration from '@/lib/channel-configuration/ChannelConfiguration'
import channelsConfiguration from '@/lib/channel-configuration'
import Connector from '@/lib/connectors/Connector'
import connectors from '@/lib/connectors'

interface VuexOrmComponentsInterface {
    Database: Database
    Model: Model
}

// Create module definition for VuexORM.use()
export default {
    install(components: VuexOrmComponentsInterface, options: { database: Database, originName?: string }): void {
        if (typeof options.originName !== 'undefined') {
            // @ts-ignore
            components.Model.prototype.$devicesModuleOrigin = options.originName
        } else {
            // @ts-ignore
            components.Model.prototype.$devicesModuleOrigin = ModuleOrigin.MODULE_DEVICES_ORIGIN
        }

        options.database.register(Device, devices)
        options.database.register(DeviceProperty, deviceProperties)
        options.database.register(DeviceConfiguration, devicesConfiguration)
        options.database.register(DeviceConnector, deviceConnector)
        options.database.register(Channel, channels)
        options.database.register(ChannelProperty, channelProperties)
        options.database.register(ChannelConfiguration, channelsConfiguration)
        options.database.register(Connector, connectors)
    },
}
