import { Fields, Model } from '@vuex-orm/core'
import { DataType, HardwareManufacturer, normalizeValue } from '@fastybird/metadata'

import {
  PropertyCommandResult,
  PropertyCommandState,
  PropertyIntegerDatatypeTypes,
  PropertyInterface,
  PropertyNumberDatatypeTypes,
  SensorNameTypes,
} from '@/lib/models/properties/types'
import { DeviceInterface } from '@/lib/models/devices/types'
import { cleanInvalid } from '@/lib/helpers'
import { ConnectorPropertyEntityTypes } from '@/lib/models/connector-properties/types'
import { DevicePropertyEntityTypes } from '@/lib/models/device-properties/types'
import { ChannelPropertyEntityTypes } from '@/lib/models/channel-properties/types'

// ENTITY MODEL
// ============
export default class Property extends Model implements PropertyInterface {
  id!: string
  type!: ConnectorPropertyEntityTypes | DevicePropertyEntityTypes | ChannelPropertyEntityTypes
  draft!: boolean
  identifier!: string
  name!: string | null
  settable!: boolean
  queryable!: boolean
  dataType!: DataType
  unit!: string | null
  format!: string[] | ((string | null)[])[] | (number | null)[] | null
  invalid!: string | number | null
  numberOfDecimals!: number | null
  value!: string | number | boolean | Date | null
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
    return !this.isSettable && this.dataType !== DataType.BOOLEAN
  }

  get isBinarySensor(): boolean {
    return !this.isSettable && this.dataType === DataType.BOOLEAN
  }

  get isAnalogActor(): boolean {
    return this.isSettable && this.dataType !== DataType.BOOLEAN
  }

  get isBinaryActor(): boolean {
    return this.isSettable && this.dataType === DataType.BOOLEAN
  }

  get isInteger(): boolean {
    return Object.values(PropertyIntegerDatatypeTypes).includes(this.dataType)
  }

  get isFloat(): boolean {
    return this.dataType === DataType.FLOAT
  }

  get isNumber(): boolean {
    return Object.values(PropertyNumberDatatypeTypes).includes(this.dataType)
  }

  get isBoolean(): boolean {
    return this.dataType === DataType.BOOLEAN
  }

  get isString(): boolean {
    return this.dataType === DataType.STRING
  }

  get isEnum(): boolean {
    return this.dataType === DataType.ENUM
  }

  get isColor(): boolean {
    return this.dataType === DataType.COLOR
  }

  get isButton(): boolean {
    return this.dataType === DataType.BUTTON
  }

  get isSwitch(): boolean {
    return this.dataType === DataType.SWITCH
  }

  get isSettable(): boolean {
    return this.settable
  }

  get isQueryable(): boolean {
    return this.queryable
  }

  get formattedActualValue(): string | null {
    if (
      this.type !== ConnectorPropertyEntityTypes.DYNAMIC
      && this.type !== DevicePropertyEntityTypes.DYNAMIC
      && this.type !== ChannelPropertyEntityTypes.DYNAMIC
    ) {
      return null
    }

    const storeInstance = Property.store()
    const actualValue = normalizeValue(this.dataType, this.actualValue !== null ? String(this.actualValue) : null, this.format)

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

    if (
      this.type !== ConnectorPropertyEntityTypes.DYNAMIC
      && this.type !== DevicePropertyEntityTypes.DYNAMIC
      && this.type !== ChannelPropertyEntityTypes.DYNAMIC
    ) {
      return null
    }

    const storeInstance = Property.store()
    const expectedValue = normalizeValue(this.dataType, this.expectedValue !== null ? String(this.expectedValue) : null, this.format)

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

  static fields(): Fields {
    return {
      id: this.string(''),

      draft: this.boolean(false),

      identifier: this.string(''),
      name: this.string(null).nullable(),
      settable: this.boolean(false),
      queryable: this.boolean(false),
      dataType: this.string(null).nullable(),
      unit: this.string(null).nullable(),
      format: this.attr(null).nullable(),
      invalid: this.string(null).nullable(),
      numberOfDecimals: this.number(null).nullable(),

      value: this.attr(null).nullable(),

      actualValue: this.attr(null).nullable(),
      expectedValue: this.attr(null).nullable(),
      pending: this.boolean(false),

      command: this.string(null).nullable(),
      lastResult: this.string(null).nullable(),
      backup: this.string(null).nullable(),

      // Relations
      relationshipNames: this.attr([]),
    }
  }

  static beforeCreate(properties: PropertyInterface[] | PropertyInterface): PropertyInterface[] | PropertyInterface {
    if (Array.isArray(properties)) {
      return properties.map((property: PropertyInterface) => {
        property.invalid = cleanInvalid(property.dataType, property.invalid)

        if (
          property.type === ConnectorPropertyEntityTypes.DYNAMIC
          || property.type === DevicePropertyEntityTypes.DYNAMIC
          || property.type === ChannelPropertyEntityTypes.DYNAMIC
        ) {
          property.actualValue = normalizeValue(property.dataType, String(property.actualValue), property.format)
          property.expectedValue = normalizeValue(property.dataType, String(property.expectedValue), property.format)
        }

        if (
          property.type === ConnectorPropertyEntityTypes.STATIC
          || property.type === DevicePropertyEntityTypes.STATIC
          || property.type === ChannelPropertyEntityTypes.STATIC
        ) {
          property.value = normalizeValue(property.dataType, String(property.value), property.format)
        }

        return property
      })
    } else {
      properties.invalid = cleanInvalid(properties.dataType, properties.invalid)

      if (
        properties.type === ConnectorPropertyEntityTypes.DYNAMIC
        || properties.type === DevicePropertyEntityTypes.DYNAMIC
        || properties.type === ChannelPropertyEntityTypes.DYNAMIC
      ) {
        properties.actualValue = normalizeValue(properties.dataType, String(properties.actualValue), properties.format)
        properties.expectedValue = normalizeValue(properties.dataType, String(properties.expectedValue), properties.format)
      }

      if (
        properties.type === ConnectorPropertyEntityTypes.STATIC
        || properties.type === DevicePropertyEntityTypes.STATIC
        || properties.type === ChannelPropertyEntityTypes.STATIC
      ) {
        properties.value = normalizeValue(properties.dataType, String(properties.value), properties.format)
      }

      return properties
    }
  }

  static beforeUpdate(properties: PropertyInterface[] | PropertyInterface): PropertyInterface[] | PropertyInterface {
    if (Array.isArray(properties)) {
      return properties.map((property: PropertyInterface) => {
        property.invalid = cleanInvalid(property.dataType, property.invalid)

        if (
          property.type === ConnectorPropertyEntityTypes.DYNAMIC
          || property.type === DevicePropertyEntityTypes.DYNAMIC
          || property.type === ChannelPropertyEntityTypes.DYNAMIC
        ) {
          property.actualValue = normalizeValue(property.dataType, String(property.actualValue), property.format)
          property.expectedValue = normalizeValue(property.dataType, String(property.expectedValue), property.format)
        }

        if (
          property.type === ConnectorPropertyEntityTypes.STATIC
          || property.type === DevicePropertyEntityTypes.STATIC
          || property.type === ChannelPropertyEntityTypes.STATIC
        ) {
          property.value = normalizeValue(property.dataType, String(property.value), property.format)
        }

        return property
      })
    } else {
      properties.invalid = cleanInvalid(properties.dataType, properties.invalid)

      if (
        properties.type === ConnectorPropertyEntityTypes.DYNAMIC
        || properties.type === DevicePropertyEntityTypes.DYNAMIC
        || properties.type === ChannelPropertyEntityTypes.DYNAMIC
      ) {
        properties.actualValue = normalizeValue(properties.dataType, String(properties.actualValue), properties.format)
        properties.expectedValue = normalizeValue(properties.dataType, String(properties.expectedValue), properties.format)
      }

      if (
        properties.type === ConnectorPropertyEntityTypes.STATIC
        || properties.type === DevicePropertyEntityTypes.STATIC
        || properties.type === ChannelPropertyEntityTypes.STATIC
      ) {
        properties.value = normalizeValue(properties.dataType, String(properties.value), properties.format)
      }

      return properties
    }
  }
}
