import { Fields, Model } from '@vuex-orm/core'
import { DataType, HardwareManufacturer } from '@fastybird/modules-metadata'

import {
  PropertyCommandResult,
  PropertyCommandState,
  PropertyIntegerDatatypeTypes,
  PropertyInterface,
  PropertyNumberDatatypeTypes,
  SensorNameTypes,
} from '@/lib/models/properties/types'
import { DeviceInterface } from '@/lib/models/devices/types'
import { normalizeValue } from '@/lib/helpers'

// ENTITY MODEL
// ============
export default class Property extends Model implements PropertyInterface {
  static fields(): Fields {
    return {
      id: this.string(''),

      key: this.string(''),
      identifier: this.string(''),
      name: this.string(null).nullable(),
      settable: this.boolean(false),
      queryable: this.boolean(false),
      dataType: this.string(null).nullable(),
      unit: this.string(null).nullable(),
      format: this.string(null).nullable(),
      invalid: this.string(null).nullable(),

      actualValue: this.attr(null).nullable(),
      expectedValue: this.attr(null).nullable(),
      pending: this.boolean(false),

      // Relations
      relationshipNames: this.attr([]),
    }
  }

  static beforeCreate(properties: PropertyInterface[] | PropertyInterface): PropertyInterface[] | PropertyInterface {
    if (Array.isArray(properties)) {
      return properties.map((property: PropertyInterface) => {
        if (property.dataType) {
          property.actualValue = normalizeValue(property.dataType, String(property.actualValue), property.getFormat())
          property.expectedValue = normalizeValue(property.dataType, String(property.expectedValue), property.getFormat())
        }

        return property
      })
    } else {
      if (properties.dataType) {
        properties.actualValue = normalizeValue(properties.dataType, String(properties.actualValue), properties.getFormat())
        properties.expectedValue = normalizeValue(properties.dataType, String(properties.expectedValue), properties.getFormat())
      }

      return properties
    }
  }

  static beforeUpdate(properties: PropertyInterface[] | PropertyInterface): PropertyInterface[] | PropertyInterface {
    if (Array.isArray(properties)) {
      return properties.map((property: PropertyInterface) => {
        if (property.dataType) {
          property.actualValue = normalizeValue(property.dataType, String(property.actualValue), property.getFormat())
          property.expectedValue = normalizeValue(property.dataType, String(property.expectedValue), property.getFormat())
        }

        return property
      })
    } else {
      if (properties.dataType) {
        properties.actualValue = normalizeValue(properties.dataType, String(properties.actualValue), properties.getFormat())
        properties.expectedValue = normalizeValue(properties.dataType, String(properties.expectedValue), properties.getFormat())
      }

      return properties
    }
  }

  id!: string

  key!: string
  identifier!: string
  name!: string | null
  settable!: boolean
  queryable!: boolean
  dataType!: DataType | null
  unit!: string | null
  format!: string | null
  invalid!: string | null

  actualValue!: string | number | boolean | Date | null
  expectedValue!: string | number | boolean | Date | null
  pending!: boolean

  command!: PropertyCommandState | null
  lastResult!: PropertyCommandResult | null
  backup!: string | null

  relationshipNames!: string[]

  get deviceInstance(): DeviceInterface | null {
    return null
  }

  get isAnalogSensor(): boolean {
    return !this.isSettable && this.dataType !== null && this.dataType !== DataType.BOOLEAN
  }

  get isBinarySensor(): boolean {
    return !this.isSettable && this.dataType !== null && this.dataType === DataType.BOOLEAN
  }

  get isAnalogActor(): boolean {
    return this.isSettable && this.dataType !== null && this.dataType !== DataType.BOOLEAN
  }

  get isBinaryActor(): boolean {
    return this.isSettable && this.dataType !== null && this.dataType === DataType.BOOLEAN
  }

  get isInteger(): boolean {
    return this.dataType !== null && Object.values(PropertyIntegerDatatypeTypes).includes(this.dataType)
  }

  get isFloat(): boolean {
    return this.dataType !== null && this.dataType === DataType.FLOAT
  }

  get isNumber(): boolean {
    return this.dataType !== null && Object.values(PropertyNumberDatatypeTypes).includes(this.dataType)
  }

  get isBoolean(): boolean {
    return this.dataType !== null && this.dataType === DataType.BOOLEAN
  }

  get isString(): boolean {
    return this.dataType !== null && this.dataType === DataType.STRING
  }

  get isEnum(): boolean {
    return this.dataType !== null && this.dataType === DataType.ENUM
  }

  get isColor(): boolean {
    return this.dataType !== null && this.dataType === DataType.COLOR
  }

  get isButton(): boolean {
    return this.dataType !== null && this.dataType === DataType.BUTTON
  }

  get isSwitch(): boolean {
    return this.dataType !== null && this.dataType === DataType.SWITCH
  }

  get isSettable(): boolean {
    return this.settable
  }

  get isQueryable(): boolean {
    return this.queryable
  }

  get formattedActualValue(): string {
    const storeInstance = Property.store()
    const actualValue = this.dataType ? normalizeValue(this.dataType, this.actualValue !== null ? String(this.actualValue) : null, this.getFormat()) : this.actualValue

    if (
      this.deviceInstance !== null &&
      this.deviceInstance.hardwareManufacturer === HardwareManufacturer.ITEAD &&
      Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')
    ) {
      switch (this.identifier) {
        case SensorNameTypes.AIR_QUALITY:
          if (actualValue as number > 7) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.unhealthy`).toString()
          } else if (actualValue as number > 4) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.moderate`).toString()
          }

          // @ts-ignore
          return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.good`).toString()

        case SensorNameTypes.LIGHT_LEVEL:
          if (actualValue as number > 8) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.dusky`).toString()
          } else if (actualValue as number > 4) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.normal`).toString()
          }

          // @ts-ignore
          return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.bright`).toString()

        case SensorNameTypes.NOISE_LEVEL:
          if (actualValue as number > 6) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.noisy`).toString()
          } else if (actualValue as number > 3) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.normal`).toString()
          }

          // @ts-ignore
          return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.quiet`).toString()
      }
    }

    return String(actualValue)
  }

  get formattedExpectedValue(): string | null {
    if (this.expectedValue === null) {
      return null
    }

    const storeInstance = Property.store()
    const expectedValue = this.dataType ? normalizeValue(this.dataType, this.expectedValue !== null ? String(this.expectedValue) : null, this.getFormat()) : this.expectedValue

    if (
      this.deviceInstance !== null &&
      this.deviceInstance.hardwareManufacturer === HardwareManufacturer.ITEAD &&
      Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')
    ) {
      switch (this.identifier) {
        case SensorNameTypes.AIR_QUALITY:
          if (expectedValue as number > 7) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.unhealthy`).toString()
          } else if (expectedValue as number > 4) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.moderate`).toString()
          }

          // @ts-ignore
          return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.good`).toString()

        case SensorNameTypes.LIGHT_LEVEL:
          if (expectedValue as number > 8) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.dusky`).toString()
          } else if (expectedValue as number > 4) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.normal`).toString()
          }

          // @ts-ignore
          return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.bright`).toString()

        case SensorNameTypes.NOISE_LEVEL:
          if (expectedValue as number > 6) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.noisy`).toString()
          } else if (expectedValue as number > 3) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.normal`).toString()
          }

          // @ts-ignore
          return storeInstance.$i18n.t(`devicesModule.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.quiet`).toString()
      }
    }

    return String(expectedValue)
  }

  get icon(): string {
    switch (this.identifier) {
      case SensorNameTypes.TEMPERATURE:
        return 'thermometer-half'

      case SensorNameTypes.HUMIDITY:
        return 'tint'

      case SensorNameTypes.AIR_QUALITY:
        return 'fan'

      case SensorNameTypes.LIGHT_LEVEL:
        return 'sun'

      case SensorNameTypes.NOISE_LEVEL:
        return 'microphone-alt'

      case SensorNameTypes.POWER:
        return 'plug'

      case SensorNameTypes.CURRENT:
      case SensorNameTypes.VOLTAGE:
        return 'bolt'

      case SensorNameTypes.ENERGY:
        return 'calculator'
    }

    return 'chart-bar'
  }

  getFormat(): (string | number | null)[] | null {
    if (this.dataType !== null) {
      switch (this.dataType) {
        case DataType.CHAR:
        case DataType.UCHAR:
        case DataType.SHORT:
        case DataType.USHORT:
        case DataType.INT:
        case DataType.UINT: {
          if (this.format !== null) {
            const [min, max] = this.format.split(':').concat(['', '']);

            if (min !== '' && max !== '' && parseInt(min, 10) <= parseInt(max, 10)) {
              return [parseInt(min, 10), parseInt(max, 10)];
            }

            if (min !== '' && max === '') {
              return [parseInt(min, 10), null];
            }

            if (min === '' && max !== '') {
              return [null, parseInt(max, 10)];
            }
          }

          break
        }

        case DataType.FLOAT: {
          if (this.format !== null) {
            const [min, max] = this.format.split(':').concat(['', '']);

            if (min !== '' && max !== '' && parseFloat(min) <= parseFloat(max)) {
              return [parseFloat(min), parseFloat(max)];
            }

            if (min !== '' && max === '') {
              return [parseFloat(min), null];
            }

            if (min === '' && max !== '') {
              return [null, parseFloat(max)];
            }
          }

          break
        }

        case DataType.ENUM: {
          if (this.format !== null) {
            const format = this.format
              .split(',')
              .map((item): string => {
                return item.trim()
              })

            return format.filter((item, index): boolean => {
              return format.indexOf(item) === index
            })
          }

          break
        }
      }
    }

    return null
  }

  getInvalid(): string | number | null {
    if (this.invalid === null) {
      return null
    }

    if (this.dataType !== null) {
      switch (this.dataType) {
        case DataType.CHAR:
        case DataType.UCHAR:
        case DataType.SHORT:
        case DataType.USHORT:
        case DataType.INT:
        case DataType.UINT: {
          if (!isNaN(this.invalid)) {
            return parseInt(this.invalid, 10)
          }
          break
        }

        case DataType.FLOAT: {
          if (!isNaN(this.invalid)) {
            return parseFloat(this.invalid)
          }
          break
        }
      }
    }

    return null
  }
}
