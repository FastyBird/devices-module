import { DataType } from '@fastybird/modules-metadata'

import { DeviceInterface } from '@/lib/devices/types'

// ENTITY TYPES
// ============

export enum PropertyNumberDatatypeTypes {
  CHAR = DataType.CHAR,
  UNSIGNED_CHAR = DataType.UCHAR,
  SHORT = DataType.SHORT,
  UNSIGNED_SHORT = DataType.USHORT,
  INT = DataType.INT,
  UNSIGNED_INT = DataType.UINT,
  FLOAT = DataType.FLOAT,
}

export enum PropertyIntegerDatatypeTypes {
  CHAR = DataType.CHAR,
  UNSIGNED_CHAR = DataType.UCHAR,
  SHORT = DataType.SHORT,
  UNSIGNED_SHORT = DataType.USHORT,
  INT = DataType.INT,
  UNSIGNED_INT = DataType.UINT,
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
}

export enum ActorNameTypes {
  ACTOR = 'actor',
  SWITCH = 'switch',
}

// ENTITY INTERFACE
// ================

export interface PropertyInterface {
  id: string

  key: string
  identifier: string
  name: string | null
  settable: boolean
  queryable: boolean
  dataType: DataType
  unit: string | null
  format: string | null

  value: any
  expected: any
  pending: boolean

  command: PropertyCommandState | null
  lastResult: PropertyCommandResult | null
  backup: string | null

  relationshipNames: Array<string>

  device: DeviceInterface | null
  deviceBackward: DeviceInterface | null

  deviceId: string

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

  binaryValue: boolean
  binaryExpected: boolean | null
  analogValue: string
  analogExpected: string | null
  formattedValue: string

  icon: string
}
