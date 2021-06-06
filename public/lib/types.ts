import { ModulePrefix } from '@fastybird/modules-metadata'

import { TJsonaModel } from 'jsona/lib/JsonaTypes'

import { DeviceEntityTypes } from '@/lib/models/devices/types'
import { DevicePropertyEntityTypes } from '@/lib/models/device-properties/types'
import { DeviceConfigurationEntityTypes } from '@/lib/models/device-configuration/types'
import { DeviceConnectorEntityTypes } from '@/lib/models/device-connector/types'
import { ChannelEntityTypes } from '@/lib/models/channels/types'
import { ChannelPropertyEntityTypes } from '@/lib/models/channel-properties/types'
import { ChannelConfigurationEntityTypes } from '@/lib/models/channel-configuration/types'
import { ConnectorEntityTypes } from '@/lib/models/connectors/types'

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

export const ModuleApiPrefix = `/${ModulePrefix.MODULE_DEVICES_PREFIX}`

// STORE
// =====

export enum SemaphoreTypes {
  FETCHING = 'fetching',
  GETTING = 'getting',
  CREATING = 'creating',
  UPDATING = 'updating',
  DELETING = 'deleting',
}
