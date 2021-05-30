import { DataType } from '@fastybird/modules-metadata'

import {
  TJsonApiBody,
  TJsonApiData,
  TJsonApiRelation,
  TJsonApiRelationshipData,
  TJsonApiRelationships,
} from 'jsona/lib/JsonaTypes'

import {
  ChannelEntityTypes,
  ChannelInterface,
} from '@/lib/channels/types'
import { DeviceInterface } from '@/lib/devices/types'
import {
  ConfigurationInterface,
  ValuesItemInterface,
} from '@/lib/configuration/types'

// STORE
// =====

export enum SemaphoreTypes {
  FETCHING = 'fetching',
  GETTING = 'getting',
  CREATING = 'creating',
  UPDATING = 'updating',
  DELETING = 'deleting',
}

// ENTITY TYPES
// ============

export enum ChannelConfigurationEntityTypes {
  CONFIGURATION = 'devices-module/channel-configuration',
}

// ENTITY INTERFACE
// ================

export interface ChannelConfigurationInterface extends ConfigurationInterface {
  type: ChannelConfigurationEntityTypes

  channel: ChannelInterface | null
  channelBackward: ChannelInterface | null

  channelId: string

  selectValues: Array<ValuesItemInterface>

  formattedValue: any

  device: DeviceInterface | null

  title: string
  description: string | null
}

// API RESPONSES
// =============

interface ChannelConfigurationAttributesResponseInterface {
  key: string
  identifier: string
  name: string | null
  comment: string | null

  dataType: DataType | null

  default: string | number | boolean | null
  value: string | number | boolean | null

  min?: number
  max?: number
  step?: number

  values: Array<{name: string, value: string}>
}

interface ChannelRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: ChannelEntityTypes
}

interface ChannelRelationshipsResponseInterface extends TJsonApiRelation {
  data: ChannelRelationshipResponseInterface
}

interface ChannelConfigurationRelationshipsResponseInterface extends TJsonApiRelationships {
  device: ChannelRelationshipsResponseInterface
}

export interface ChannelConfigurationDataResponseInterface extends TJsonApiData {
  id: string
  type: ChannelConfigurationEntityTypes
  attributes: ChannelConfigurationAttributesResponseInterface
  relationships: ChannelConfigurationRelationshipsResponseInterface
}

export interface ChannelConfigurationResponseInterface extends TJsonApiBody {
  data: ChannelConfigurationDataResponseInterface
}

export interface ChannelPropertiesResponseInterface extends TJsonApiBody {
  data: Array<ChannelConfigurationDataResponseInterface>
}

// UPDATE ENTITY INTERFACES
// ========================

export interface ChannelConfigurationUpdateInterface {
  name?: string | null
  comment?: string | null
}
