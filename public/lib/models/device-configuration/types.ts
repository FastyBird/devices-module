import { DataType } from '@fastybird/metadata'

import {
  TJsonApiBody,
  TJsonApiData,
  TJsonApiRelation,
  TJsonApiRelationshipData,
  TJsonApiRelationships,
} from 'jsona/lib/JsonaTypes'

import {
  DeviceDataResponseInterface,
  DeviceEntityTypes,
  DeviceInterface,
} from '@/lib/models/devices/types'
import {
  ConfigurationInterface,
  ValuesItemInterface,
} from '@/lib/models/configuration/types'

// ENTITY TYPES
// ============

export enum DeviceConfigurationEntityTypes {
  CONFIGURATION = 'devices-module/device-configuration',
}

// ENTITY INTERFACE
// ================

export interface DeviceConfigurationInterface extends ConfigurationInterface {
  type: DeviceConfigurationEntityTypes

  device: DeviceInterface | null
  deviceBackward: DeviceInterface | null

  deviceId: string

  selectValues: ValuesItemInterface[]

  formattedValue: string

  title: string
  description: string | null
}

// API RESPONSES
// =============

interface DeviceConfigurationAttributesResponseInterface {
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

interface DeviceRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: DeviceEntityTypes
}

interface DeviceRelationshipsResponseInterface extends TJsonApiRelation {
  data: DeviceRelationshipResponseInterface
}

interface DeviceConfigurationRelationshipsResponseInterface extends TJsonApiRelationships {
  device: DeviceRelationshipsResponseInterface
}

export interface DeviceConfigurationDataResponseInterface extends TJsonApiData {
  id: string
  type: DeviceConfigurationEntityTypes
  attributes: DeviceConfigurationAttributesResponseInterface
  relationships: DeviceConfigurationRelationshipsResponseInterface
}

export interface DeviceConfigurationResponseInterface extends TJsonApiBody {
  data: DeviceConfigurationDataResponseInterface
  included?: (DeviceDataResponseInterface)[]
}

export interface DeviceConfigurationsResponseInterface extends TJsonApiBody {
  data: DeviceConfigurationDataResponseInterface[]
  included?: (DeviceDataResponseInterface)[]
}

// UPDATE ENTITY INTERFACES
// ========================

export interface DeviceConfigurationUpdateInterface {
  name?: string | null
  comment?: string | null
}
