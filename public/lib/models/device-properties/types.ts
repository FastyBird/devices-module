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
import { PropertyInterface } from '@/lib/models/properties/types'

// ENTITY TYPES
// ============

export enum DevicePropertyEntityTypes {
  DYNAMIC = 'devices-module/device-property-dynamic',
  STATIC = 'devices-module/device-property-static',
}

// ENTITY INTERFACE
// ================

export interface DevicePropertyInterface extends PropertyInterface {
  type: DevicePropertyEntityTypes

  device: DeviceInterface | null
  deviceBackward: DeviceInterface | null

  deviceId: string

  title: string
}

// API RESPONSES
// =============

interface DevicePropertyAttributesResponseInterface {
  key: string
  identifier: string
  name: string | null
  settable: boolean
  queryable: boolean
  dataType: DataType | null
  unit: string | null
  format: string[] | ((string | null)[])[] | (number | null)[] | null
  invalid: string | number | null
  numberOfDecimals: number | null

  value: string | number | boolean | null

  actualValue: string | number | boolean | null
  expectedValue: string | number | boolean | null
  pending: boolean
}

interface DeviceRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: DeviceEntityTypes
}

interface DeviceRelationshipsResponseInterface extends TJsonApiRelation {
  data: DeviceRelationshipResponseInterface
}

interface DevicePropertyRelationshipsResponseInterface extends TJsonApiRelationships {
  device: DeviceRelationshipsResponseInterface
}

export interface DevicePropertyDataResponseInterface extends TJsonApiData {
  id: string
  type: DevicePropertyEntityTypes
  attributes: DevicePropertyAttributesResponseInterface
  relationships: DevicePropertyRelationshipsResponseInterface
}

export interface DevicePropertyResponseInterface extends TJsonApiBody {
  data: DevicePropertyDataResponseInterface
  included?: (DeviceDataResponseInterface)[]
}

export interface DevicePropertiesResponseInterface extends TJsonApiBody {
  data: DevicePropertyDataResponseInterface[]
  included?: (DeviceDataResponseInterface)[]
}

// CREATE ENTITY INTERFACES
// ========================

export interface DevicePropertyCreateInterface {
  type: DevicePropertyEntityTypes

  identifier: string
  name?: string | null
  settable?: boolean
  queryable?: boolean
  dataType?: string | null
  unit?: string | null
  format?: string[] | ((string | null)[])[] | (number | null)[] | null
  invalid?: string | number | null
  numberOfDecimals?: number | null
}

// UPDATE ENTITY INTERFACES
// ========================

export interface DevicePropertyUpdateInterface {
  name?: string | null
  settable?: boolean
  queryable?: boolean
  dataType?: string | null
  unit?: string | null
  format?: string[] | ((string | null)[])[] | (number | null)[] | null
  invalid?: string | number | null
  numberOfDecimals?: number | null
}
