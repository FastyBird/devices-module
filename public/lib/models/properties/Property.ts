import { Fields, Model } from '@vuex-orm/core'
import { ButtonPayload, DataType, HardwareManufacturer, SwitchPayload } from '@fastybird/modules-metadata'

import {
  PropertyCommandResult,
  PropertyCommandState,
  PropertyIntegerDatatypeTypes,
  PropertyInterface,
  PropertyNumberDatatypeTypes,
  SensorNameTypes,
} from '@/lib/models/properties/types'
import { DeviceInterface } from '@/lib/models/devices/types'

export const normalizeValue = (property: PropertyInterface, value: string | null): number | string | boolean | null => {
  if (value === null) {
    return null
  }

  switch (property.dataType) {
    case DataType.BOOLEAN:
      return ['true', 't', 'yes', 'y', '1', 'on'].includes(value.toLocaleLowerCase())

    case DataType.FLOAT:
      return parseFloat(value)

    case DataType.CHAR:
    case DataType.UCHAR:
    case DataType.SHORT:
    case DataType.USHORT:
    case DataType.INT:
    case DataType.UINT:
      return parseInt(value, 10)

    case DataType.STRING:
      return value

    case DataType.ENUM:
      if (property.format?.split(',').includes(value.toLowerCase())) {
        return value.toLowerCase()
      }

      return null

    case DataType.COLOR:
      break

    case DataType.BUTTON:
      if (value.toLowerCase() in ButtonPayload) {
        return value.toLowerCase()
      }

      return null

    case DataType.SWITCH:
      if (value.toLowerCase() in SwitchPayload) {
        return value.toLowerCase()
      }

      return null
  }

  return value
}

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

      actualValue: this.attr(null).nullable(),
      expectedValue: this.attr(null).nullable(),
      pending: this.boolean(false),

      // Relations
      relationshipNames: this.attr([]),
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

  actualValue!: string | number | boolean | null
  expectedValue!: string | number | boolean | null
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

  get binaryValue(): boolean {
    if (this.actualValue === null) {
      return false
    }

    if (this.isBoolean) {
      if (typeof this.actualValue === 'boolean') {
        return this.actualValue
      }

      return ['true', 't', 'yes', 'y', '1', 'on'].includes(this.actualValue.toString().toLocaleLowerCase())
    }

    return false
  }

  get binaryExpected(): boolean | null {
    if (this.expectedValue === null) {
      return null
    }

    if (this.isBoolean) {
      if (typeof this.expectedValue === 'boolean') {
        return this.expectedValue
      }

      return ['true', 't', 'yes', 'y', '1', 'on'].includes(this.expectedValue.toString().toLocaleLowerCase())
    }

    return false
  }

  get analogValue(): string {
    const storeInstance = Property.store()
    const actualValue = normalizeValue(this, this.actualValue !== null ? String(this.actualValue): null)

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

  get analogExpected(): string | null {
    if (this.expectedValue === null) {
      return null
    }

    const storeInstance = Property.store()
    const expectedValue = normalizeValue(this, this.expectedValue !== null ? String(this.expectedValue): null)

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
}
