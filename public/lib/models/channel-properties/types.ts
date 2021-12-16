import { DataType } from '@fastybird/modules-metadata'

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
import { PropertyInterface } from '@/lib/models/properties/types'

// ENTITY TYPES
// ============

export enum ChannelPropertyEntityTypes {
  PROPERTY = 'devices-module/channel-property',
}

// ENTITY INTERFACE
// ================

export interface ChannelPropertyInterface extends PropertyInterface {
  type: ChannelPropertyEntityTypes

  channel: ChannelInterface | null
  channelBackward: ChannelInterface | null

  channelId: string

  title: string
}

// API RESPONSES
// =============

interface ChannelPropertyAttributesResponseInterface {
  key: string
  identifier: string
  name: string | null
  settable: boolean
  queryable: boolean

  dataType: DataType | null
  unit: string | null
  format: string | null
  invalid: string | null

  actualValue: string | number | boolean | null
  expectedValue: string | number | boolean | null
  pending: boolean
}

interface ChannelRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: ChannelEntityTypes
}

interface ChannelRelationshipsResponseInterface extends TJsonApiRelation {
  data: ChannelRelationshipResponseInterface
}

interface ChannelPropertyRelationshipsResponseInterface extends TJsonApiRelationships {
  channel: ChannelRelationshipsResponseInterface
}

export interface ChannelPropertyDataResponseInterface extends TJsonApiData {
  id: string
  type: ChannelPropertyEntityTypes
  attributes: ChannelPropertyAttributesResponseInterface
  relationships: ChannelPropertyRelationshipsResponseInterface
}

export interface ChannelPropertyResponseInterface extends TJsonApiBody {
  data: ChannelPropertyDataResponseInterface
  included?: (ChannelDataResponseInterface)[]
}

export interface ChannelPropertiesResponseInterface extends TJsonApiBody {
  data: ChannelPropertyDataResponseInterface[]
  included?: (ChannelDataResponseInterface)[]
}

// UPDATE ENTITY INTERFACES
// ========================

export interface ChannelPropertyUpdateInterface {
  name?: string | null
}
