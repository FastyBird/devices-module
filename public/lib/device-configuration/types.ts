import { DataType } from '@fastybird/modules-metadata'

import {
  TJsonApiBody,
  TJsonApiData,
  TJsonApiRelation,
  TJsonApiRelationshipData,
  TJsonApiRelationships,
} from 'jsona/lib/JsonaTypes'

import {
  DeviceEntityTypes,
  DeviceInterface,
} from '@/lib/devices/types'
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

export enum DeviceConfigurationEntityTypes {
  CONFIGURATION = 'devices-module/device-configuration',
}

// ENTITY INTERFACE
// ================

export interface DeviceConfigurationInterface extends ConfigurationInterface{
  type: DeviceConfigurationEntityTypes

  device: DeviceInterface | null
  deviceBackward: DeviceInterface | null

  deviceId: string

  selectValues: Array<ValuesItemInterface>

  formattedValue: any

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

  values: Array<{name: string, value: string}>
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
}

export interface DevicePropertiesResponseInterface extends TJsonApiBody {
  data: Array<DeviceConfigurationDataResponseInterface>
}

// UPDATE ENTITY INTERFACES
// ========================

export interface DeviceConfigurationUpdateInterface {
  name?: string | null
  comment?: string | null
}
