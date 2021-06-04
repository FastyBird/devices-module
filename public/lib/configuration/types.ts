import { DataType } from '@fastybird/modules-metadata'

// ENTITY TYPES
// ============

export enum ConfigurationNumberDatatypeTypes {
  CHAR = DataType.CHAR,
  UNSIGNED_CHAR = DataType.UCHAR,
  SHORT = DataType.SHORT,
  UNSIGNED_SHORT = DataType.USHORT,
  INT = DataType.INT,
  UNSIGNED_INT = DataType.UINT,
  FLOAT = DataType.FLOAT,
}

export enum ConfigurationIntegerDatatypeTypes {
  CHAR = DataType.CHAR,
  UNSIGNED_CHAR = DataType.UCHAR,
  SHORT = DataType.SHORT,
  UNSIGNED_SHORT = DataType.USHORT,
  INT = DataType.INT,
  UNSIGNED_INT = DataType.UINT,
}

// ENTITY INTERFACE
// ================

export interface ValuesItemInterface {
  name: string
  value: any
}

export interface ConfigurationInterface {
  id: string

  key: string
  identifier: string
  name: string | null
  comment: string | null

  default: any
  value: any
  dataType: DataType

  min: number | null
  max: number | null
  step: number | null

  values: Array<ValuesItemInterface>

  relationshipNames: Array<string>

  isInteger: boolean
  isFloat: boolean
  isNumber: boolean
  isBoolean: boolean
  isString: boolean
  isSelect: boolean
}
