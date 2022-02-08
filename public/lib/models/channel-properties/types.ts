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
  ChannelInterface,
} from '@/lib/models/channels/types'
import { PropertyInterface } from '@/lib/models/properties/types'

// ENTITY INTERFACE
// ================

export interface ChannelPropertyInterface extends PropertyInterface {
  channel: ChannelInterface | null
  channelBackward: ChannelInterface | null

  channelId: string

  title: string
}

// API RESPONSES
// =============

interface ChannelPropertyAttributesResponseInterface {
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

interface ChannelRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: string
}

interface ChannelRelationshipsResponseInterface extends TJsonApiRelation {
  data: ChannelRelationshipResponseInterface
}

interface ChannelPropertyRelationshipsResponseInterface extends TJsonApiRelationships {
  channel: ChannelRelationshipsResponseInterface
}

export interface ChannelPropertyDataResponseInterface extends TJsonApiData {
  id: string
  type: string
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

// CREATE ENTITY INTERFACES
// ========================

export interface ChannelPropertyCreateInterface {
  type: string

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

export interface ChannelPropertyUpdateInterface {
  name?: string | null
  settable?: boolean
  queryable?: boolean
  dataType?: string | null
  unit?: string | null
  format?: string[] | ((string | null)[])[] | (number | null)[] | null
  invalid?: string | number | null
  numberOfDecimals?: number | null
}
