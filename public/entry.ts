import { ModuleOrigin } from '@fastybird/metadata'
import { Plugin } from '@vuex-orm/core/dist/src/plugins/use'

import Device from '@/lib/models/devices/Device'
import devices from '@/lib/models/devices'
import DeviceProperty from '@/lib/models/device-properties/DeviceProperty'
import deviceProperties from '@/lib/models/device-properties'
import DeviceControl from '@/lib/models/device-controls/DeviceControl'
import devicesControl from '@/lib/models/device-controls'
import Channel from '@/lib/models/channels/Channel'
import channels from '@/lib/models/channels'
import ChannelProperty from '@/lib/models/channel-properties/ChannelProperty'
import channelProperties from '@/lib/models/channel-properties'
import ChannelControl from '@/lib/models/channel-controls/ChannelControl'
import channelsControl from '@/lib/models/channel-controls'
import Connector from '@/lib/models/connectors/Connector'
import connectors from '@/lib/models/connectors'
import ConnectorControl from '@/lib/models/connector-controls/ConnectorControl'
import connectorsControl from '@/lib/models/connector-controls'

// Import typing
import { ComponentsInterface, GlobalConfigInterface } from '@/types/devices-module'

// install function executed by VuexORM.use()
const install: Plugin = function installVuexOrmWamp(components: ComponentsInterface, config: GlobalConfigInterface) {
  if (typeof config.originName !== 'undefined') {
    // @ts-ignore
    components.Model.$devicesModuleOrigin = config.originName
  } else {
    // @ts-ignore
    components.Model.$devicesModuleOrigin = ModuleOrigin.MODULE_DEVICES_ORIGIN
  }

  config.database.register(Device, devices)
  config.database.register(DeviceProperty, deviceProperties)
  config.database.register(DeviceControl, devicesControl)
  config.database.register(Channel, channels)
  config.database.register(ChannelProperty, channelProperties)
  config.database.register(ChannelControl, channelsControl)
  config.database.register(Connector, connectors)
  config.database.register(ConnectorControl, connectorsControl)
}

// Create module definition for VuexORM.use()
const plugin = {
  install,
}

// Default export is library as a whole, registered via VuexORM.use()
export default plugin

// Export model classes
export {
  ChannelControl,
  ChannelProperty,
  Channel,
  Connector,
  ConnectorControl,
  DeviceControl,
  DeviceProperty,
  Device,
}

export * from '@/lib/errors'

// Re-export plugin typing
export * from '@/types/devices-module'
