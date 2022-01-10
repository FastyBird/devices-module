import { DataType } from '@fastybird/metadata'

import {
  TJsonApiBody,
  TJsonApiData,
  TJsonApiRelation,
  TJsonApiRelationshipData,
  TJsonApiRelationships,
} from 'jsona/lib/JsonaTypes'

import {
  ChannelDataResponseInterface,
  ChannelEntityTypes,
  ChannelInterface,
} from '@/lib/models/channels/types'
import { DeviceInterface } from '@/lib/models/devices/types'
import {
  ConfigurationInterface,
  ValuesItemInterface,
} from '@/lib/models/configuration/types'

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

  selectValues: ValuesItemInterface[]

  formattedValue: string

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

  values: { name: string, value: string }[]
}

interface ChannelRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: ChannelEntityTypes
}

interface ChannelRelationshipsResponseInterface extends TJsonApiRelation {
  data: ChannelRelationshipResponseInterface
}

interface ChannelConfigurationRelationshipsResponseInterface extends TJsonApiRelationships {
  channel: ChannelRelationshipsResponseInterface
}

export interface ChannelConfigurationDataResponseInterface extends TJsonApiData {
  id: string
  type: ChannelConfigurationEntityTypes
  attributes: ChannelConfigurationAttributesResponseInterface
  relationships: ChannelConfigurationRelationshipsResponseInterface
}

export interface ChannelConfigurationResponseInterface extends TJsonApiBody {
  data: ChannelConfigurationDataResponseInterface
  included?: (ChannelDataResponseInterface)[]
}

export interface ChannelConfigurationsResponseInterface extends TJsonApiBody {
  data: ChannelConfigurationDataResponseInterface[]
  included?: (ChannelDataResponseInterface)[]
}

// UPDATE ENTITY INTERFACES
// ========================

export interface ChannelConfigurationUpdateInterface {
  name?: string | null
  comment?: string | null
}
