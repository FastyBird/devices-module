import { ModulePrefix } from '@fastybird/modules-metadata'

import { TJsonaModel } from 'jsona/lib/JsonaTypes'

import { DeviceEntityTypes } from '@/lib/models/devices/types'
import { DevicePropertyEntityTypes } from '@/lib/models/device-properties/types'
import { DeviceConfigurationEntityTypes } from '@/lib/models/device-configuration/types'
import { DeviceControlEntityTypes } from '@/lib/models/device-controls/types'
import { ChannelEntityTypes } from '@/lib/models/channels/types'
import { ChannelPropertyEntityTypes } from '@/lib/models/channel-properties/types'
import { ChannelConfigurationEntityTypes } from '@/lib/models/channel-configuration/types'
import { ChannelControlEntityTypes } from '@/lib/models/channel-controls/types'
import { ConnectorEntityTypes } from '@/lib/models/connectors/types'
import { ConnectorControlEntityTypes } from '@/lib/models/connector-controls/types'

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

export interface DeviceControlJsonModelInterface extends TJsonaModel {
  id: string
  type: DeviceControlEntityTypes
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

export interface ChannelControlJsonModelInterface extends TJsonaModel {
  id: string
  type: ChannelControlEntityTypes
}

export interface ConnectorJsonModelInterface extends TJsonaModel {
  id: string
  type: ConnectorEntityTypes
}

export interface ConnectorControlJsonModelInterface extends TJsonaModel {
  id: string
  type: ConnectorControlEntityTypes
}

export interface RelationInterface extends TJsonaModel {
  id: string
  type: DeviceEntityTypes | ChannelEntityTypes | DevicePropertyEntityTypes | DeviceConfigurationEntityTypes | DeviceControlEntityTypes | ChannelPropertyEntityTypes | ChannelConfigurationEntityTypes | ChannelControlEntityTypes | ConnectorEntityTypes | ConnectorControlEntityTypes
}

export const ModuleApiPrefix = `/${ModulePrefix.MODULE_DEVICES}`

// STORE
// =====

export enum SemaphoreTypes {
  FETCHING = 'fetching',
  GETTING = 'getting',
  CREATING = 'creating',
  UPDATING = 'updating',
  DELETING = 'deleting',
}
