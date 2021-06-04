import {
  Model,
  Fields,
} from '@vuex-orm/core'
import { DataType, HardwareManufacturer } from '@fastybird/modules-metadata'

import {
  PropertyCommandResult,
  PropertyCommandState,
  PropertyIntegerDatatypeTypes,
  PropertyInterface,
  PropertyNumberDatatypeTypes,
} from '@/lib/properties/types'
import { DeviceInterface } from '@/lib/devices/types'

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
      dataType: this.string(''),
      unit: this.string(null).nullable(),
      format: this.string(null).nullable(),

      value: this.attr(null).nullable(),
      expected: this.attr(null).nullable(),
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
  dataType!: DataType
  unit!: string | null
  format!: string | null

  value!: any
  expected!: any
  pending!: boolean

  command!: PropertyCommandState | null
  lastResult!: PropertyCommandResult | null
  backup!: string | null

  relationshipNames!: Array<string>

  get deviceInstance(): DeviceInterface | null {
    return null
  }

  get isAnalogSensor(): boolean {
    return !this.isSettable &&
      Object.values(PropertyNumberDatatypeTypes).includes(this.dataType)
  }

  get isBinarySensor(): boolean {
    return !this.isSettable &&
      [DataType.BOOLEAN].includes(this.dataType)
  }

  get isAnalogActor(): boolean {
    return this.isSettable &&
      Object.values(PropertyNumberDatatypeTypes).includes(this.dataType)
  }

  get isBinaryActor(): boolean {
    return this.isSettable &&
      [DataType.BOOLEAN].includes(this.dataType)
  }

  get isSwitch(): boolean {
    return this.identifier === 'switch'
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

  get isSettable(): boolean {
    return this.settable
  }

  get isQueryable(): boolean {
    return this.queryable
  }

  get binaryValue(): boolean {
    if (this.value === null) {
      return false
    }

    if (this.isBoolean) {
      if (typeof this.value === 'boolean') {
        return this.value
      }

      return ['true', '1', 't', 'y', 'yes'].includes(this.value.toString().toLocaleLowerCase())
    } else if (this.isEnum) {
      return this.value === 'on'
    }

    return false
  }

  get binaryExpected(): boolean | null {
    if (this.expected === null) {
      return null
    }

    if (this.isBoolean) {
      if (typeof this.expected === 'boolean') {
        return this.expected
      }

      return ['true', '1', 't', 'y', 'yes'].includes(this.expected.toString().toLocaleLowerCase())
    } else if (this.isEnum) {
      return this.expected === 'on'
    }

    return false
  }

  get analogValue(): string {
    const storeInstance = Property.store()

    if (
      this.deviceInstance !== null &&
      this.deviceInstance.hardwareManufacturer === HardwareManufacturer.ITEAD &&
      Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')
    ) {
      switch (this.identifier) {
        case 'air_quality':
          if (this.value > 7) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devices.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.unhealthy`).toString()
          } else if (this.value > 4) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devices.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.moderate`).toString()
          }

          // @ts-ignore
          return storeInstance.$i18n.t(`devices.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.good`).toString()

        case 'light_level':
          if (this.value > 8) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devices.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.dusky`).toString()
          } else if (this.value > 4) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devices.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.normal`).toString()
          }

          // @ts-ignore
          return storeInstance.$i18n.t(`devices.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.bright`).toString()

        case 'noise_level':
          if (this.value > 6) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devices.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.noisy`).toString()
          } else if (this.value > 3) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devices.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.normal`).toString()
          }

          // @ts-ignore
          return storeInstance.$i18n.t(`devices.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.quiet`).toString()
      }
    }

    return this.formattedValue
  }

  get analogExpected(): string | null {
    if (this.expected === null) {
      return null
    }

    const storeInstance = Property.store()

    if (
      this.deviceInstance !== null &&
      this.deviceInstance.hardwareManufacturer === HardwareManufacturer.ITEAD &&
      Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')
    ) {
      switch (this.identifier) {
        case 'air_quality':
          if (this.expected > 7) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devices.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.unhealthy`).toString()
          } else if (this.expected > 4) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devices.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.moderate`).toString()
          }

          // @ts-ignore
          return storeInstance.$i18n.t(`devices.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.good`).toString()

        case 'light_level':
          if (this.expected > 8) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devices.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.dusky`).toString()
          } else if (this.expected > 4) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devices.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.normal`).toString()
          }

          // @ts-ignore
          return storeInstance.$i18n.t(`devices.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.bright`).toString()

        case 'noise_level':
          if (this.expected > 6) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devices.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.noisy`).toString()
          } else if (this.expected > 3) {
            // @ts-ignore
            return storeInstance.$i18n.t(`devices.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.normal`).toString()
          }

          // @ts-ignore
          return storeInstance.$i18n.t(`devices.vendors.${this.deviceInstance.hardwareManufacturer}.properties.${this.identifier}.values.quiet`).toString()
      }
    }

    return this.formattedValue
  }

  get formattedValue(): string {
    const number = parseFloat(this.value)
    const decimals = 2
    const decPoint = ','
    const thousandsSeparator = ' '

    const cleanedNumber = (`${number}`).replace(/[^0-9+\-Ee.]/g, '')

    const n = !isFinite(+cleanedNumber) ? 0 : +cleanedNumber
    const prec = !isFinite(+decimals) ? 0 : Math.abs(decimals)

    const sep = typeof thousandsSeparator === 'undefined' ? ',' : thousandsSeparator
    const dec = typeof decPoint === 'undefined' ? '.' : decPoint

    const toFixedFix = (fN: number, fPrec: number): string => {
      const k = 10 ** fPrec

      return `${Math.round(fN * k) / k}`
    }

    // Fix for IE parseFloat(0.55).toFixed(0) = 0
    const s = (prec ? toFixedFix(n, prec) : `${Math.round(n)}`).split('.')

    if (s[0].length > 3) {
      s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
    }

    if ((s[1] || '').length < prec) {
      s[1] = s[1] || ''
      s[1] += new Array(prec - s[1].length + 1).join('0')
    }

    return s.join(dec)
  }

  get icon(): string {
    switch (this.identifier) {
      case 'temperature':
        return 'thermometer-half'

      case 'humidity':
        return 'tint'

      case 'air_quality':
        return 'fan'

      case 'light_level':
        return 'sun'

      case 'noise_level':
        return 'microphone-alt'

      case 'power':
        return 'plug'

      case 'current':
      case 'voltage':
        return 'bolt'

      case 'energy':
        return 'calculator'
    }

    return 'chart-bar'
  }
}
