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

// ENTITY TYPES
// ============

export enum DeviceControlEntityTypes {
  CONTROL = 'devices-module/control/device',
}

// ENTITY INTERFACE
// ================

export interface DeviceControlInterface {
  id: string
  type: DeviceControlEntityTypes

  name: string

  device: DeviceInterface | null
  deviceBackward: DeviceInterface | null

  deviceId: string
}

// API RESPONSES
// =============

interface DeviceControlAttributesResponseInterface {
  name: string
}

interface DeviceRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: DeviceEntityTypes
}

interface DeviceRelationshipsResponseInterface extends TJsonApiRelation {
  data: DeviceRelationshipResponseInterface
}

interface DeviceControlRelationshipsResponseInterface extends TJsonApiRelationships {
  device: DeviceRelationshipsResponseInterface
}

export interface DeviceControlDataResponseInterface extends TJsonApiData {
  id: string
  type: DeviceControlEntityTypes
  attributes: DeviceControlAttributesResponseInterface
  relationships: DeviceControlRelationshipsResponseInterface
}

export interface DeviceControlResponseInterface extends TJsonApiBody {
  data: DeviceControlDataResponseInterface
  included?: (DeviceDataResponseInterface)[]
}

export interface DeviceControlsResponseInterface extends TJsonApiBody {
  data: DeviceControlDataResponseInterface[]
  included?: (DeviceDataResponseInterface)[]
}
