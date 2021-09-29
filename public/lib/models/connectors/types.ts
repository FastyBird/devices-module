import {
  TJsonApiBody,
  TJsonApiData,
  TJsonApiRelation,
  TJsonApiRelationships,
  TJsonApiRelationshipData,
} from 'jsona/lib/JsonaTypes'

import { DeviceEntityTypes } from '@/lib/models/devices/types'
import { DeviceConnectorInterface } from '@/lib/models/device-connector/types'
import {
  ConnectorControlDataResponseInterface,
  ConnectorControlEntityTypes,
} from '@/lib/models/connector-controls/types'

// ENTITY TYPES
// ============

export enum ConnectorEntityTypes {
  FB_BUS = 'devices-module/connector-fb-bus',
  FB_MQTT = 'devices-module/connector-fb-mqtt',
}

// ENTITY INTERFACE
// ================

export interface ConnectorInterface {
  id: string
  type: ConnectorEntityTypes

  name: string
  enabled: boolean

  // FB bus
  address: number | null
  serialInterface: string | null
  baudRate: number | null

  // FB MQTT v1
  server: string | null
  port: number | null
  securedPort: number | null
  username: string | null
  password: string | null

  // Relations
  relationshipNames: string[]

  devices: DeviceConnectorInterface[]

  // Entity transformers
  isEnabled: boolean
  icon: string
}

// API RESPONSES
// =============

interface ConnectorAttributesResponseInterface {
  name: string
  enabled: boolean
}

interface FbBusConnectorAttributesResponseInterface extends ConnectorAttributesResponseInterface {
  address: number | null
  serial_interface: string | null
  baud_rate: number | null
}

interface FbMqttConnectorAttributesResponseInterface extends ConnectorAttributesResponseInterface {
  server: string | null
  port: number | null
  secured_port: number | null
  username: string | null
  password: string | null
}

interface ConnectorDeviceRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: DeviceEntityTypes
}

interface ConnectorDevicesRelationshipsResponseInterface extends TJsonApiRelation {
  data: ConnectorDeviceRelationshipResponseInterface[]
}

interface ConnectorControlRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: ConnectorControlEntityTypes
}

interface ConnectorControlsRelationshipsResponseInterface extends TJsonApiRelation {
  data: ConnectorControlRelationshipResponseInterface[]
}

interface ConnectorRelationshipsResponseInterface extends TJsonApiRelationships {
  devices: ConnectorDevicesRelationshipsResponseInterface
  controls: ConnectorControlsRelationshipsResponseInterface
}

export interface ConnectorDataResponseInterface extends TJsonApiData {
  id: string
  type: ConnectorEntityTypes
  attributes: FbBusConnectorAttributesResponseInterface | FbMqttConnectorAttributesResponseInterface
  relationships: ConnectorRelationshipsResponseInterface
}

export interface ConnectorResponseInterface extends TJsonApiBody {
  data: ConnectorDataResponseInterface
  included?: (ConnectorControlDataResponseInterface)[]
}

export interface ConnectorsResponseInterface extends TJsonApiBody {
  data: ConnectorDataResponseInterface[]
  included?: (ConnectorControlDataResponseInterface)[]
}

// UPDATE ENTITY INTERFACES
// ========================

export interface ConnectorUpdateInterface {
  name?: string
  enabled?: boolean
}
