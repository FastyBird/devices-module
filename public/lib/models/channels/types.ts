import {
  TJsonApiBody,
  TJsonApiData,
  TJsonApiRelation,
  TJsonApiRelationships,
  TJsonApiRelationshipData,
} from 'jsona/lib/JsonaTypes'

import {
  DeviceInterface,
  DeviceEntityTypes, DeviceDataResponseInterface,
} from '@/lib/models/devices/types'
import {
  ChannelPropertyInterface,
  ChannelPropertyEntityTypes,
  ChannelPropertyDataResponseInterface,
} from '@/lib/models/channel-properties/types'
import {
  ChannelConfigurationInterface,
  ChannelConfigurationEntityTypes,
  ChannelConfigurationDataResponseInterface,
} from '@/lib/models/channel-configuration/types'
import { ChannelControlDataResponseInterface, ChannelControlEntityTypes } from '@/lib/models/channel-controls/types'

// ENTITY TYPES
// ============

export enum ChannelEntityTypes {
  CHANNEL = 'devices-module/channel',
}

// ENTITY INTERFACE
// ================

export interface ChannelInterface {
  id: string
  type: ChannelEntityTypes

  key: string
  identifier: string
  name: string | null
  comment: string | null

  relationshipNames: string[]

  properties: ChannelPropertyInterface[]
  configuration: ChannelConfigurationInterface[]

  device: DeviceInterface | null
  deviceBackward: DeviceInterface | null

  deviceId: string

  title: string
}

// API RESPONSES
// =============

interface ChannelAttributesResponseInterface {
  key: string
  identifier: string
  name: string | null
  comment: string | null
}

interface ChannelDeviceRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: DeviceEntityTypes
}

interface ChannelDeviceRelationshipsResponseInterface extends TJsonApiRelation {
  data: ChannelDeviceRelationshipResponseInterface
}

interface ChannelPropertyRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: ChannelPropertyEntityTypes
}

interface ChannelPropertiesRelationshipsResponseInterface extends TJsonApiRelation {
  data: ChannelPropertyRelationshipResponseInterface[]
}

interface ChannelConfigurationRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: ChannelConfigurationEntityTypes
}

interface ChannelConfigurationRelationshipsResponseInterface extends TJsonApiRelation {
  data: ChannelConfigurationRelationshipResponseInterface[]
}

interface ChannelControlRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: ChannelControlEntityTypes
}

interface ChannelControlsRelationshipsResponseInterface extends TJsonApiRelation {
  data: ChannelControlRelationshipResponseInterface[]
}

interface ChannelRelationshipsResponseInterface extends TJsonApiRelationships {
  device: ChannelDeviceRelationshipsResponseInterface
  properties: ChannelPropertiesRelationshipsResponseInterface
  configuration: ChannelConfigurationRelationshipsResponseInterface
  controls: ChannelControlsRelationshipsResponseInterface
}

export interface ChannelDataResponseInterface extends TJsonApiData {
  id: string
  type: ChannelEntityTypes
  attributes: ChannelAttributesResponseInterface
  relationships: ChannelRelationshipsResponseInterface
}

export interface ChannelResponseInterface extends TJsonApiBody {
  data: ChannelDataResponseInterface
  included?: (DeviceDataResponseInterface | ChannelPropertyDataResponseInterface | ChannelConfigurationDataResponseInterface | ChannelControlDataResponseInterface)[]
}

export interface ChannelsResponseInterface extends TJsonApiBody {
  data: ChannelDataResponseInterface[]
  included?: (DeviceDataResponseInterface | ChannelPropertyDataResponseInterface | ChannelConfigurationDataResponseInterface | ChannelControlDataResponseInterface)[]
}

// UPDATE ENTITY INTERFACES
// ========================

export interface ChannelUpdateInterface {
  name?: string | null
  comment?: string | null
}
