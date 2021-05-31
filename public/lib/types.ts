import { TJsonaModel } from 'jsona/lib/JsonaTypes'

import { DeviceEntityTypes } from '@/lib/devices/types'
import { DevicePropertyEntityTypes } from '@/lib/device-properties/types'
import { DeviceConfigurationEntityTypes } from '@/lib/device-configuration/types'
import { DeviceConnectorEntityTypes } from '@/lib/device-connector/types'
import { ChannelEntityTypes } from '@/lib/channels/types'
import { ChannelPropertyEntityTypes } from '@/lib/channel-properties/types'
import { ChannelConfigurationEntityTypes } from '@/lib/channel-configuration/types'
import { ConnectorEntityTypes } from '@/lib/connectors/types'

export interface DeviceJsonModelInterface extends TJsonaModel {
  id: string
  type: DeviceEntityTypes
}

export interface DevicePropertyJsonModelInterface extends TJsonaModel {
  id: string
  type: DevicePropertyEntityTypes
}

export interface DeviceConfigurationJsonModelInterface extends TJsonaModel {
  id: string
  type: DeviceConfigurationEntityTypes
}

export interface DeviceConnectorJsonModelInterface extends TJsonaModel {
  id: string
  type: DeviceConnectorEntityTypes
}

export interface ChannelJsonModelInterface extends TJsonaModel {
  id: string
  type: ChannelEntityTypes
}

export interface ChannelPropertyJsonModelInterface extends TJsonaModel {
  id: string
  type: ChannelPropertyEntityTypes
}

export interface ChannelConfigurationJsonModelInterface extends TJsonaModel {
  id: string
  type: ChannelConfigurationEntityTypes
}

export interface ConnectorJsonModelInterface extends TJsonaModel {
  id: string
  type: ConnectorEntityTypes
}

export interface RelationInterface extends TJsonaModel {
  id: string
  type: DeviceEntityTypes | ChannelEntityTypes | DevicePropertyEntityTypes | DeviceConfigurationEntityTypes | DeviceConnectorEntityTypes | ChannelPropertyEntityTypes | ChannelConfigurationEntityTypes | ConnectorEntityTypes
}

declare module '@vuex-orm/core' {
    namespace Model {
        /**
         * Exchange origin name
         */
        export const $devicesModuleOrigin: string
    }
}
