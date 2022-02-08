import { DataType } from '@fastybird/metadata'
import { ChannelPropertyEntityTypes } from '@/lib/models/channel-properties/types'
import { DevicePropertyEntityTypes } from '@/lib/models/device-properties/types'
import { ConnectorPropertyEntityTypes } from '@/lib/models/connector-properties/types'

// ENTITY TYPES
// ============

export enum PropertyIntegerDatatypeTypes {
  CHAR = DataType.CHAR,
  UNSIGNED_CHAR = DataType.UCHAR,
  SHORT = DataType.SHORT,
  UNSIGNED_SHORT = DataType.USHORT,
  INT = DataType.INT,
  UNSIGNED_INT = DataType.UINT,
}

export enum PropertyNumberDatatypeTypes {
  CHAR = DataType.CHAR,
  UNSIGNED_CHAR = DataType.UCHAR,
  SHORT = DataType.SHORT,
  UNSIGNED_SHORT = DataType.USHORT,
  INT = DataType.INT,
  UNSIGNED_INT = DataType.UINT,
  FLOAT = DataType.FLOAT,
}

export enum PropertyCommandState {
  SENDING = 'sending',
  COMPLETED = 'completed',
}

export enum PropertyCommandResult {
  OK = 'ok',
  ERR = 'err',
}

export enum SensorNameTypes {
  SENSOR = 'sensor',
  AIR_QUALITY = 'air_quality',
  LIGHT_LEVEL = 'light_level',
  NOISE_LEVEL = 'noise_level',
  TEMPERATURE = 'temperature',
  HUMIDITY = 'humidity',
  POWER = 'power',
  CURRENT = 'current',
  VOLTAGE = 'voltage',
  ENERGY = 'energy',
}

export enum ActorNameTypes {
  ACTOR = 'actor',
}

// ENTITY INTERFACE
// ================

export interface PropertyInterface {
  id: string
  type: ConnectorPropertyEntityTypes | DevicePropertyEntityTypes | ChannelPropertyEntityTypes

  draft: boolean

  identifier: string
  name: string | null
  settable: boolean
  queryable: boolean
  dataType: DataType
  unit: string | null
  format: string[] | ((string | null)[])[] | (number | null)[] | null
  invalid: string | number | null
  numberOfDecimals: number | null

  value: string | number | boolean | Date | null

  actualValue: string | number | boolean | Date | null
  expectedValue: string | number | boolean | Date | null
  pending: boolean

  command: PropertyCommandState | null
  lastResult: PropertyCommandResult | null
  backup: string | null

  relationshipNames: string[]

  isAnalogSensor: boolean
  isBinarySensor: boolean
  isAnalogActor: boolean
  isBinaryActor: boolean
  isSwitch: boolean

  isInteger: boolean
  isFloat: boolean
  isNumber: boolean
  isBoolean: boolean
  isString: boolean
  isEnum: boolean
  isColor: boolean

  isSettable: boolean
  isQueryable: boolean

  formattedActualValue: string | null
  formattedExpectedValue: string | null

  icon: string
}
