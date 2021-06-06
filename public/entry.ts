import { ModuleOrigin } from '@fastybird/modules-metadata'
import { Plugin } from '@vuex-orm/core/dist/src/plugins/use'

import Device from '@/lib/models/devices/Device'
import devices from '@/lib/models/devices'
import DeviceProperty from '@/lib/models/device-properties/DeviceProperty'
import deviceProperties from '@/lib/models/device-properties'
import DeviceConfiguration from '@/lib/models/device-configuration/DeviceConfiguration'
import devicesConfiguration from '@/lib/models/device-configuration'
import DeviceConnector from '@/lib/models/device-connector/DeviceConnector'
import deviceConnector from '@/lib/models/device-connector/index'
import Channel from '@/lib/models/channels/Channel'
import channels from '@/lib/models/channels'
import ChannelProperty from '@/lib/models/channel-properties/ChannelProperty'
import channelProperties from '@/lib/models/channel-properties'
import ChannelConfiguration from '@/lib/models/channel-configuration/ChannelConfiguration'
import channelsConfiguration from '@/lib/models/channel-configuration'
import Connector from '@/lib/models/connectors/Connector'
import connectors from '@/lib/models/connectors'

// Import typing
import { ComponentsInterface, GlobalConfigInterface } from '@/types/devices-module'

// install function executed by VuexORM.use()
const install: Plugin = function installVuexOrmWamp(components: ComponentsInterface, config: GlobalConfigInterface) {
  if (typeof config.originName !== 'undefined') {
    // @ts-ignore
    components.Model.prototype.$devicesModuleOrigin = config.originName
  } else {
    // @ts-ignore
    components.Model.prototype.$devicesModuleOrigin = ModuleOrigin.MODULE_DEVICES_ORIGIN
  }

  config.database.register(Device, devices)
  config.database.register(DeviceProperty, deviceProperties)
  config.database.register(DeviceConfiguration, devicesConfiguration)
  config.database.register(DeviceConnector, deviceConnector)
  config.database.register(Channel, channels)
  config.database.register(ChannelProperty, channelProperties)
  config.database.register(ChannelConfiguration, channelsConfiguration)
  config.database.register(Connector, connectors)
}

// Create module definition for VuexORM.use()
const plugin = {
  install,
}

// Default export is library as a whole, registered via VuexORM.use()
export default plugin

// Export model classes
export {
  ChannelConfiguration,
  ChannelProperty,
  Channel,
  Connector,
  DeviceConfiguration,
  DeviceConnector,
  DeviceProperty,
  Device,
}

export * from '@/lib/errors'

// Re-export plugin typing
export * from '@/types/devices-module'
