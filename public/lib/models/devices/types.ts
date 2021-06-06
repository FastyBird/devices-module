import {
  DeviceConnectionState,
  DeviceModel,
  HardwareManufacturer,
  FirmwareManufacturer,
} from '@fastybird/modules-metadata'

import {
  TJsonApiBody,
  TJsonApiData,
  TJsonApiRelation,
  TJsonApiRelationships,
  TJsonApiRelationshipData,
} from 'jsona/lib/JsonaTypes'

import {
  DevicePropertyInterface,
  DevicePropertyEntityTypes,
} from '@/lib/models/device-properties/types'
import {
  DeviceConfigurationEntityTypes,
  DeviceConfigurationInterface,
} from '@/lib/models/device-configuration/types'
import {
  ChannelInterface,
  ChannelEntityTypes,
  ChannelResponseInterface,
} from '@/lib/models/channels/types'
import DeviceConnector from '@/lib/models/device-connector/DeviceConnector'

// ENTITY TYPES
// ============

export enum DeviceEntityTypes {
  DEVICE = 'devices-module/device',
}

// ENTITY INTERFACE
// ================

export interface DeviceInterface {
  id: string
  type: DeviceEntityTypes

  draft: boolean

  parentId: string | null

  key: string
  identifier: string
  name: string | null
  comment: string | null

  state: DeviceConnectionState
  enabled: boolean

  hardwareModel: DeviceModel
  hardwareManufacturer: HardwareManufacturer
  hardwareVersion: string | null
  macAddress: string | null

  firmwareManufacturer: FirmwareManufacturer
  firmwareVersion: string | null

  control: string[]

  owner: string | null

  relationshipNames: string[]

  children: DeviceInterface[]
  channels: ChannelInterface[]
  properties: DevicePropertyInterface[]
  configuration: DeviceConfigurationInterface[]
  connector: DeviceConnector

  isEnabled: boolean
  isReady: boolean
  icon: string
  title: string
  hasComment: boolean
  isCustomModel: boolean
}

// API RESPONSES
// =============

interface DeviceAttributesResponseInterface {
  key: string
  identifier: string
  name: string | null
  comment: string | null

  state: DeviceConnectionState
  enabled: boolean

  control: string[]

  owner: string | null
}

interface DeviceRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: DeviceEntityTypes
}

interface DeviceParentRelationshipsResponseInterface extends TJsonApiRelation {
  data: DeviceRelationshipResponseInterface
}

interface DeviceChildrenRelationshipsResponseInterface extends TJsonApiRelation {
  data: DeviceRelationshipResponseInterface[]
}

interface DevicePropertyRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: DevicePropertyEntityTypes
}

interface DevicePropertiesRelationshipsResponseInterface extends TJsonApiRelation {
  data: DevicePropertyRelationshipResponseInterface[]
}

interface DeviceConfigurationRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: DeviceConfigurationEntityTypes
}

interface DeviceConfigurationRelationshipsResponseInterface extends TJsonApiRelation {
  data: DeviceConfigurationRelationshipResponseInterface[]
}

interface DeviceChannelRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: ChannelEntityTypes
}

interface DeviceChannelsRelationshipsResponseInterface extends TJsonApiRelation {
  data: DeviceChannelRelationshipResponseInterface[]
}

interface PhysicalDeviceRelationshipsResponseInterface extends TJsonApiRelationships {
  parent: DeviceParentRelationshipsResponseInterface
  children: DeviceChildrenRelationshipsResponseInterface
  properties: DevicePropertiesRelationshipsResponseInterface
  configuration: DeviceConfigurationRelationshipsResponseInterface
  channels: DeviceChannelsRelationshipsResponseInterface
}

export interface DeviceDataResponseInterface extends TJsonApiData {
  id: string
  type: DeviceEntityTypes
  attributes: DeviceAttributesResponseInterface
  relationships: PhysicalDeviceRelationshipsResponseInterface
  included?: ChannelResponseInterface[]
}

export interface DeviceResponseInterface extends TJsonApiBody {
  data: DeviceDataResponseInterface
}

export interface DevicesResponseInterface extends TJsonApiBody {
  data: DeviceDataResponseInterface[]
}

// CREATE ENTITY INTERFACES
// ========================

export interface DeviceCreateInterface {
  identifier: string
  name?: string | null
  comment?: string | null
  enabled?: boolean
}

// UPDATE ENTITY INTERFACES
// ========================

export interface DeviceUpdateInterface {
  name?: string | null
  comment?: string | null
  enabled?: boolean
}
