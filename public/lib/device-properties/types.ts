import { DataType } from '@fastybird/modules-metadata'

import {
  TJsonApiBody,
  TJsonApiData,
  TJsonApiRelation,
  TJsonApiRelationshipData,
  TJsonApiRelationships,
} from 'jsona/lib/JsonaTypes'

import { DeviceEntityTypes } from '@/lib/devices/types'
import { PropertyInterface } from '@/lib/properties/types'

// ENTITY TYPES
// ============

export enum DevicePropertyEntityTypes {
  PROPERTY = 'devices-module/device-property',
}

// ENTITY INTERFACE
// ================

export interface DevicePropertyInterface extends PropertyInterface {
  type: DevicePropertyEntityTypes

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
  format: string | null

  value: string | number | boolean | null
  expected: string | number | boolean | null
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
}

export interface DevicePropertiesResponseInterface extends TJsonApiBody {
  data: Array<DevicePropertyDataResponseInterface>
}

// UPDATE ENTITY INTERFACES
// ========================

export interface DevicePropertyUpdateInterface {
  name?: string | null
}
