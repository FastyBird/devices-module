import {
  TJsonApiBody,
  TJsonApiData,
  TJsonApiRelation,
  TJsonApiRelationships,
  TJsonApiRelationshipData,
} from 'jsona/lib/JsonaTypes'

import { DeviceInterface } from '@/lib/models/devices/types'
import { ConnectorControlDataResponseInterface } from '@/lib/models/connector-controls/types'

// ENTITY INTERFACE
// ================

export interface ConnectorInterface {
  id: string
  type: string

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

  devices: DeviceInterface[]

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
  type: string
}

interface ConnectorDevicesRelationshipsResponseInterface extends TJsonApiRelation {
  data: ConnectorDeviceRelationshipResponseInterface[]
}

interface ConnectorControlRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: string
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
  type: string
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

export interface FbMqttConnectorUpdateInterface extends ConnectorUpdateInterface {
  server?: string | null
  port?: number | null
  securedPort?: number | null
  username?: string | null
  password?: string | null
}

export interface FbBusConnectorUpdateInterface extends ConnectorUpdateInterface {
  address?: number | null
  serialInterface?: string | null
  baudRate?: number | null
}

export interface ShellyConnectorUpdateInterface extends ConnectorUpdateInterface {
  name?: string
}

export interface TuyaConnectorUpdateInterface extends ConnectorUpdateInterface {
  name?: string
}

export interface SonoffConnectorUpdateInterface extends ConnectorUpdateInterface {
  name?: string
}

export interface ModbusConnectorUpdateInterface extends ConnectorUpdateInterface {
  serialInterface?: string | null
  baudRate?: number | null
}
