// Import library
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

// Import typing
import { ComponentsInterface, GlobalConfigInterface, InstallFunction } from '@/types/devices-module'

// install function executed by VuexORM.use()
const install: InstallFunction = function installVuexOrmWamp(components: ComponentsInterface, config: GlobalConfigInterface) {
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

// Re-export plugin typing
export * from '@/types/devices-module'
