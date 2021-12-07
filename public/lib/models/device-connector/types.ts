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
import { ConnectorEntityTypes, ConnectorInterface } from '@/lib/models/connectors/types'

// ENTITY TYPES
// ============

export enum DeviceConnectorEntityTypes {
  CONNECTOR = 'devices-module/device-connector',
}

// ENTITY INTERFACE
// ================

export interface DeviceConnectorInterface {
  id: string
  type: DeviceConnectorEntityTypes

  draft: boolean

  // FB BUS Connector specific
  address: number
  maxPacketLength: number
  descriptionSupport: boolean
  settingsSupport: boolean
  configuredKeyLength: number
  pubSubPubSupport: boolean
  pubSubSubSupport: boolean
  pubSubSubMaxSubscriptions: number
  pubSubSubMaxConditions: number
  pubSubSubMaxActions: number

  // FB MQTT Connector specific
  username: string
  password: string

  relationshipNames: string[]

  device: DeviceInterface | null
  deviceBackward: DeviceInterface | null

  connector: ConnectorInterface | null
  connectorBackward: ConnectorInterface | null

  deviceId: string
  connectorId: string
}

// API RESPONSES
// =============

interface DeviceConnectorAttributesResponseInterface {
  // FB BUS Connector specific
  address?: number
  max_packet_length?: number
  description_support?: boolean
  settings_support?: boolean
  configured_key_length?: number
  pub_sub_pub_support?: boolean
  pub_sub_sub_support?: boolean
  pub_sub_sub_max_subscriptions?: number
  pub_sub_sub_max_conditions?: number
  pub_sub_sub_max_actions?: number

  // FB MQTT Connector specific
  username?: string
  password?: string
}

interface DeviceRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: DeviceEntityTypes
}

interface DeviceRelationshipsResponseInterface extends TJsonApiRelation {
  data: DeviceRelationshipResponseInterface
}

interface ConnectorRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: ConnectorEntityTypes
}

interface ConnectorRelationshipsResponseInterface extends TJsonApiRelation {
  data: ConnectorRelationshipResponseInterface
}

interface DeviceConnectorRelationshipsResponseInterface extends TJsonApiRelationships {
  device: DeviceRelationshipsResponseInterface
  connector: ConnectorRelationshipsResponseInterface
}

export interface DeviceConnectorDataResponseInterface extends TJsonApiData {
  id: string
  type: DeviceConnectorEntityTypes
  attributes: DeviceConnectorAttributesResponseInterface
  relationships: DeviceConnectorRelationshipsResponseInterface
}

export interface DeviceConnectorResponseInterface extends TJsonApiBody {
  data: DeviceConnectorDataResponseInterface
  included?: (DeviceDataResponseInterface)[]
}

// UPDATE ENTITY INTERFACES
// ========================

export interface DeviceConnectorCreateInterface {
  username?: string
  password?: string
}

export interface DeviceConnectorUpdateInterface {
  password?: string
}
