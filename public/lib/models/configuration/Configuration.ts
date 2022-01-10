import {
  Model,
  Fields,
} from '@vuex-orm/core'
import { DataType, normalizeValue } from '@fastybird/metadata'

import {
  ConfigurationIntegerDatatypeTypes,
  ConfigurationInterface,
  ConfigurationNumberDatatypeTypes,
  ValuesItemInterface,
} from '@/lib/models/configuration/types'

// ENTITY MODEL
// ============
export default class Configuration extends Model implements ConfigurationInterface {
  static fields(): Fields {
    return {
      id: this.string(''),

      key: this.string(''),
      identifier: this.string(''),
      name: this.string(null).nullable(),
      comment: this.string(null).nullable(),

      value: this.attr(null).nullable(),
      default: this.attr(null).nullable(),
      dataType: this.string(''),

      // Specific configuration
      min: this.number(null).nullable(),
      max: this.number(null).nullable(),
      step: this.number(null).nullable(),
      values: this.attr([]),

      // Relations
      relationshipNames: this.attr([]),
    }
  }

  static beforeCreate(configurations: ConfigurationInterface[] | ConfigurationInterface): ConfigurationInterface[] | ConfigurationInterface {
    if (Array.isArray(configurations)) {
      return configurations.map((configuration: ConfigurationInterface) => {
        configuration.value = normalizeValue(configuration.dataType, String(configuration.value))

        return configuration
      })
    } else {
      configurations.value = normalizeValue(configurations.dataType, String(configurations.value))

      return configurations
    }
  }

  static beforeUpdate(configurations: ConfigurationInterface[] | ConfigurationInterface): ConfigurationInterface[] | ConfigurationInterface {
    if (Array.isArray(configurations)) {
      return configurations.map((configuration: ConfigurationInterface) => {
        configuration.value = normalizeValue(configuration.dataType, String(configuration.value))

        return configuration
      })
    } else {
      configurations.value = normalizeValue(configurations.dataType, String(configurations.value))

      return configurations
    }
  }

  id!: string

  key!: string
  identifier!: string
  name!: string | null
  comment!: string | null

  value!: string | number | boolean | Date | null
  default!: string | number | boolean | null
  dataType!: DataType

  min!: number | null
  max!: number | null
  step!: number | null
  values!: ValuesItemInterface[]

  relationshipNames!: string[]

  get isInteger(): boolean {
    return Object.values(ConfigurationIntegerDatatypeTypes).includes(this.dataType)
  }

  get isFloat(): boolean {
    return this.dataType === DataType.FLOAT
  }

  get isNumber(): boolean {
    return Object.values(ConfigurationNumberDatatypeTypes).includes(this.dataType)
  }

  get isBoolean(): boolean {
    return this.dataType === DataType.BOOLEAN
  }

  get isString(): boolean {
    return this.dataType === DataType.STRING
  }

  get isSelect(): boolean {
    return this.dataType === DataType.ENUM
  }
}
