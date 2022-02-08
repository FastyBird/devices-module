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

// ENTITY TYPES
// ============

export enum ChannelControlEntityTypes {
  CONTROL = 'devices-module/control/channel',
}

// ENTITY INTERFACE
// ================

export interface ChannelControlInterface {
  id: string
  type: ChannelControlEntityTypes

  name: string

  channel: ChannelInterface | null
  channelBackward: ChannelInterface | null

  channelId: string
}

// API RESPONSES
// =============

interface ChannelControlAttributesResponseInterface {
  name: string
}

interface ChannelRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: ChannelEntityTypes
}

interface ChannelRelationshipsResponseInterface extends TJsonApiRelation {
  data: ChannelRelationshipResponseInterface
}

interface ChannelControlRelationshipsResponseInterface extends TJsonApiRelationships {
  channel: ChannelRelationshipsResponseInterface
}

export interface ChannelControlDataResponseInterface extends TJsonApiData {
  id: string
  type: ChannelControlEntityTypes
  attributes: ChannelControlAttributesResponseInterface
  relationships: ChannelControlRelationshipsResponseInterface
}

export interface ChannelControlResponseInterface extends TJsonApiBody {
  data: ChannelControlDataResponseInterface
  included?: (ChannelDataResponseInterface)[]
}

export interface ChannelControlsResponseInterface extends TJsonApiBody {
  data: ChannelControlDataResponseInterface[]
  included?: (ChannelDataResponseInterface)[]
}
