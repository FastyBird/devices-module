import {
  TJsonApiBody,
  TJsonApiData,
  TJsonApiRelation,
  TJsonApiRelationships,
  TJsonApiRelationshipData,
} from 'jsona/lib/JsonaTypes'

import { DeviceEntityTypes } from '@/lib/devices/types'
import { DeviceConnectorInterface } from '@/lib/device-connector/types'

// ENTITY TYPES
// ============

export enum ConnectorEntityTypes {
  FB_BUS = 'devices-module/connector-fb-bus',
  FB_MQTT_V1 = 'devices-module/connector-fb-mqtt-v1',
}

// ENTITY INTERFACE
// ================

export interface ConnectorInterface {
  id: string
  type: ConnectorEntityTypes

  name: string
  enabled: boolean

  control: Array<string>

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
  relationshipNames: Array<string>

  devices: Array<DeviceConnectorInterface>

  // Entity transformers
  isEnabled: boolean
  icon: string
}

// API RESPONSES
// =============

interface ConnectorAttributesResponseInterface {
  name: string
  enabled: boolean

  control: Array<string>
}

interface FbBusConnectorAttributesResponseInterface extends ConnectorAttributesResponseInterface {
  address: number | null
  serial_interface: string | null
  baud_rate: number | null
}

interface FbMqttV1ConnectorAttributesResponseInterface extends ConnectorAttributesResponseInterface {
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
  data: Array<ConnectorDeviceRelationshipResponseInterface>
}

interface ConnectorRelationshipsResponseInterface extends TJsonApiRelationships {
  devices: ConnectorDevicesRelationshipsResponseInterface
}

export interface ConnectorDataResponseInterface extends TJsonApiData {
  id: string
  type: ConnectorEntityTypes
  attributes: FbBusConnectorAttributesResponseInterface | FbMqttV1ConnectorAttributesResponseInterface
  relationships: ConnectorRelationshipsResponseInterface
}

export interface ConnectorResponseInterface extends TJsonApiBody {
  data: ConnectorDataResponseInterface
}

export interface ConnectorsResponseInterface extends TJsonApiBody {
  data: Array<ConnectorDataResponseInterface>
}

// UPDATE ENTITY INTERFACES
// ========================

export interface ConnectorUpdateInterface {
  name?: string
  enabled?: boolean
}
