import {
  TJsonApiBody,
  TJsonApiData,
  TJsonApiRelation,
  TJsonApiRelationshipData,
  TJsonApiRelationships,
} from 'jsona/lib/JsonaTypes'

import {
  ConnectorDataResponseInterface,
  ConnectorEntityTypes,
  ConnectorInterface,
  ConnectorResponseInterface,
} from '@/lib/models/connectors/types'

// ENTITY TYPES
// ============

export enum ConnectorControlEntityTypes {
  CONTROL = 'connectors-module/connector-control',
}

// ENTITY INTERFACE
// ================

export interface ConnectorControlInterface {
  id: string
  type: ConnectorControlEntityTypes

  name: string

  connector: ConnectorInterface | null
  connectorBackward: ConnectorInterface | null

  connectorId: string
}

// API RESPONSES
// =============

interface ConnectorControlAttributesResponseInterface {
  name: string
}

interface ConnectorRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: ConnectorEntityTypes
}

interface ConnectorRelationshipsResponseInterface extends TJsonApiRelation {
  data: ConnectorRelationshipResponseInterface
}

interface ConnectorControlRelationshipsResponseInterface extends TJsonApiRelationships {
  connector: ConnectorRelationshipsResponseInterface
}

export interface ConnectorControlDataResponseInterface extends TJsonApiData {
  id: string
  type: ConnectorControlEntityTypes
  attributes: ConnectorControlAttributesResponseInterface
  relationships: ConnectorControlRelationshipsResponseInterface
  included?: (ConnectorResponseInterface)[]
}

export interface ConnectorControlResponseInterface extends TJsonApiBody {
  data: ConnectorControlDataResponseInterface
  included?: (ConnectorDataResponseInterface)[]
}

export interface ConnectorControlsResponseInterface extends TJsonApiBody {
  data: ConnectorControlDataResponseInterface[]
  included?: (ConnectorDataResponseInterface)[]
}
