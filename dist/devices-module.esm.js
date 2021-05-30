import { DataType, HardwareManufacturer, DeviceConnectionState, DeviceModel, FirmwareManufacturer, ModulePrefix, DevicesModule, ModuleOrigin } from '@fastybird/modules-metadata';
import { Model } from '@vuex-orm/core';
import capitalize from 'lodash/capitalize';
import * as exchangeEntitySchema from '@fastybird/modules-metadata/resources/schemas/devices-module/entity.device.json';
import Jsona, { ModelPropertiesMapper, JsonPropertiesMapper } from 'jsona';
import Ajv from 'ajv';
import uuid from 'uuid';
import get from 'lodash/get';
import uniq from 'lodash/uniq';
import { defineRelationGetter } from 'jsona/lib/simplePropertyMappers';
import clone from 'lodash/clone';
import * as exchangeEntitySchema$1 from '@fastybird/modules-metadata/resources/schemas/devices-module/entity.device.property.json';
import * as exchangeEntitySchema$2 from '@fastybird/modules-metadata/resources/schemas/devices-module/entity.device.configuration.json';
import * as exchangeEntitySchema$3 from '@fastybird/modules-metadata/resources/schemas/devices-module/entity.device.connector.json';
import * as exchangeEntitySchema$4 from '@fastybird/modules-metadata/resources/schemas/devices-module/entity.channel.json';
import * as exchangeEntitySchema$5 from '@fastybird/modules-metadata/resources/schemas/devices-module/entity.channel.property.json';
import * as exchangeEntitySchema$6 from '@fastybird/modules-metadata/resources/schemas/devices-module/entity.channel.configuration.json';
import * as exchangeEntitySchema$7 from '@fastybird/modules-metadata/resources/schemas/devices-module/entity.connector.json';

function _defineProperty(obj, key, value) {
  if (key in obj) {
    Object.defineProperty(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
  } else {
    obj[key] = value;
  }

  return obj;
}

// STORE
// =====
let SemaphoreTypes$7; // ENTITY TYPES
// ============

(function (SemaphoreTypes) {
  SemaphoreTypes["FETCHING"] = "fetching";
  SemaphoreTypes["GETTING"] = "getting";
  SemaphoreTypes["CREATING"] = "creating";
  SemaphoreTypes["UPDATING"] = "updating";
  SemaphoreTypes["DELETING"] = "deleting";
})(SemaphoreTypes$7 || (SemaphoreTypes$7 = {}));

let DeviceEntityTypes; // ENTITY INTERFACE
// ================

(function (DeviceEntityTypes) {
  DeviceEntityTypes["DEVICE"] = "devices-module/device";
})(DeviceEntityTypes || (DeviceEntityTypes = {}));

// ENTITY TYPES
// ============
let PropertyNumberDatatypeTypes;

(function (PropertyNumberDatatypeTypes) {
  PropertyNumberDatatypeTypes[PropertyNumberDatatypeTypes["CHAR"] = DataType.CHAR] = "CHAR";
  PropertyNumberDatatypeTypes[PropertyNumberDatatypeTypes["UNSIGNED_CHAR"] = DataType.UCHAR] = "UNSIGNED_CHAR";
  PropertyNumberDatatypeTypes[PropertyNumberDatatypeTypes["SHORT"] = DataType.SHORT] = "SHORT";
  PropertyNumberDatatypeTypes[PropertyNumberDatatypeTypes["UNSIGNED_SHORT"] = DataType.USHORT] = "UNSIGNED_SHORT";
  PropertyNumberDatatypeTypes[PropertyNumberDatatypeTypes["INT"] = DataType.INT] = "INT";
  PropertyNumberDatatypeTypes[PropertyNumberDatatypeTypes["UNSIGNED_INT"] = DataType.UINT] = "UNSIGNED_INT";
  PropertyNumberDatatypeTypes[PropertyNumberDatatypeTypes["FLOAT"] = DataType.FLOAT] = "FLOAT";
})(PropertyNumberDatatypeTypes || (PropertyNumberDatatypeTypes = {}));

let PropertyIntegerDatatypeTypes;

(function (PropertyIntegerDatatypeTypes) {
  PropertyIntegerDatatypeTypes[PropertyIntegerDatatypeTypes["CHAR"] = DataType.CHAR] = "CHAR";
  PropertyIntegerDatatypeTypes[PropertyIntegerDatatypeTypes["UNSIGNED_CHAR"] = DataType.UCHAR] = "UNSIGNED_CHAR";
  PropertyIntegerDatatypeTypes[PropertyIntegerDatatypeTypes["SHORT"] = DataType.SHORT] = "SHORT";
  PropertyIntegerDatatypeTypes[PropertyIntegerDatatypeTypes["UNSIGNED_SHORT"] = DataType.USHORT] = "UNSIGNED_SHORT";
  PropertyIntegerDatatypeTypes[PropertyIntegerDatatypeTypes["INT"] = DataType.INT] = "INT";
  PropertyIntegerDatatypeTypes[PropertyIntegerDatatypeTypes["UNSIGNED_INT"] = DataType.UINT] = "UNSIGNED_INT";
})(PropertyIntegerDatatypeTypes || (PropertyIntegerDatatypeTypes = {}));

let PropertyCommandState;

(function (PropertyCommandState) {
  PropertyCommandState["SENDING"] = "sending";
  PropertyCommandState["COMPLETED"] = "completed";
})(PropertyCommandState || (PropertyCommandState = {}));

let PropertyCommandResult;

(function (PropertyCommandResult) {
  PropertyCommandResult["OK"] = "ok";
  PropertyCommandResult["ERR"] = "err";
})(PropertyCommandResult || (PropertyCommandResult = {}));

let SensorNameTypes;

(function (SensorNameTypes) {
  SensorNameTypes["SENSOR"] = "sensor";
  SensorNameTypes["AIR_QUALITY"] = "air_quality";
  SensorNameTypes["LIGHT_LEVEL"] = "light_level";
  SensorNameTypes["NOISE_LEVEL"] = "noise_level";
  SensorNameTypes["TEMPERATURE"] = "temperature";
  SensorNameTypes["HUMIDITY"] = "humidity";
})(SensorNameTypes || (SensorNameTypes = {}));

let ActorNameTypes; // ENTITY INTERFACE
// ================

(function (ActorNameTypes) {
  ActorNameTypes["ACTOR"] = "actor";
  ActorNameTypes["SWITCH"] = "switch";
})(ActorNameTypes || (ActorNameTypes = {}));

// ENTITY MODEL
// ============
class Property extends Model {
  constructor(...args) {
    super(...args);

    _defineProperty(this, "id", void 0);

    _defineProperty(this, "key", void 0);

    _defineProperty(this, "identifier", void 0);

    _defineProperty(this, "name", void 0);

    _defineProperty(this, "settable", void 0);

    _defineProperty(this, "queryable", void 0);

    _defineProperty(this, "dataType", void 0);

    _defineProperty(this, "unit", void 0);

    _defineProperty(this, "format", void 0);

    _defineProperty(this, "value", void 0);

    _defineProperty(this, "expected", void 0);

    _defineProperty(this, "pending", void 0);

    _defineProperty(this, "command", void 0);

    _defineProperty(this, "lastResult", void 0);

    _defineProperty(this, "backup", void 0);

    _defineProperty(this, "relationshipNames", void 0);

    _defineProperty(this, "device", void 0);
  }

  static fields() {
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
      relationshipNames: this.attr([])
    };
  }

  get isAnalogSensor() {
    return !this.isSettable && Object.values(PropertyNumberDatatypeTypes).includes(this.dataType);
  }

  get isBinarySensor() {
    return !this.isSettable && [DataType.BOOLEAN].includes(this.dataType);
  }

  get isAnalogActor() {
    return this.isSettable && Object.values(PropertyNumberDatatypeTypes).includes(this.dataType);
  }

  get isBinaryActor() {
    return this.isSettable && [DataType.BOOLEAN].includes(this.dataType);
  }

  get isSwitch() {
    return this.identifier === 'switch';
  }

  get isInteger() {
    return Object.values(PropertyIntegerDatatypeTypes).includes(this.dataType);
  }

  get isFloat() {
    return this.dataType === DataType.FLOAT;
  }

  get isNumber() {
    return Object.values(PropertyNumberDatatypeTypes).includes(this.dataType);
  }

  get isBoolean() {
    return this.dataType === DataType.BOOLEAN;
  }

  get isString() {
    return this.dataType === DataType.STRING;
  }

  get isEnum() {
    return this.dataType === DataType.ENUM;
  }

  get isColor() {
    return this.dataType === DataType.COLOR;
  }

  get isSettable() {
    return this.settable;
  }

  get isQueryable() {
    return this.queryable;
  }

  get binaryValue() {
    if (this.value === null) {
      return false;
    }

    if (this.isBoolean) {
      if (typeof this.value === 'boolean') {
        return this.value;
      }

      return ['true', '1', 't', 'y', 'yes'].includes(this.value.toString().toLocaleLowerCase());
    } else if (this.isEnum) {
      return this.value === 'on';
    }

    return false;
  }

  get binaryExpected() {
    if (this.expected === null) {
      return null;
    }

    if (this.isBoolean) {
      if (typeof this.expected === 'boolean') {
        return this.expected;
      }

      return ['true', '1', 't', 'y', 'yes'].includes(this.expected.toString().toLocaleLowerCase());
    } else if (this.isEnum) {
      return this.expected === 'on';
    }

    return false;
  }

  get analogValue() {
    if (this.device !== null && this.device.hardwareManufacturer === HardwareManufacturer.ITEAD) {
      switch (this.identifier) {
        case 'air_quality':
          if (this.value > 7) {
            return Property.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.values.unhealthy`).toString();
          } else if (this.value > 4) {
            return Property.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.values.moderate`).toString();
          }

          return Property.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.values.good`).toString();

        case 'light_level':
          if (this.value > 8) {
            return Property.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.values.dusky`).toString();
          } else if (this.value > 4) {
            return Property.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.values.normal`).toString();
          }

          return Property.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.values.bright`).toString();

        case 'noise_level':
          if (this.value > 6) {
            return Property.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.values.noisy`).toString();
          } else if (this.value > 3) {
            return Property.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.values.normal`).toString();
          }

          return Property.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.values.quiet`).toString();
      }
    }

    return this.formattedValue;
  }

  get analogExpected() {
    if (this.expected === null) {
      return null;
    }

    if (this.device !== null && this.device.hardwareManufacturer === HardwareManufacturer.ITEAD) {
      switch (this.identifier) {
        case 'air_quality':
          if (this.expected > 7) {
            return Property.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.values.unhealthy`).toString();
          } else if (this.expected > 4) {
            return Property.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.values.moderate`).toString();
          }

          return Property.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.values.good`).toString();

        case 'light_level':
          if (this.expected > 8) {
            return Property.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.values.dusky`).toString();
          } else if (this.expected > 4) {
            return Property.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.values.normal`).toString();
          }

          return Property.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.values.bright`).toString();

        case 'noise_level':
          if (this.expected > 6) {
            return Property.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.values.noisy`).toString();
          } else if (this.expected > 3) {
            return Property.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.values.normal`).toString();
          }

          return Property.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.values.quiet`).toString();
      }
    }

    return this.formattedValue;
  }

  get formattedValue() {
    const number = parseFloat(this.value);
    const decimals = 2;
    const decPoint = ',';
    const thousandsSeparator = ' ';
    const cleanedNumber = `${number}`.replace(/[^0-9+\-Ee.]/g, '');
    const n = !isFinite(+cleanedNumber) ? 0 : +cleanedNumber;
    const prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
    const sep = thousandsSeparator;
    const dec = decPoint;

    const toFixedFix = (fN, fPrec) => {
      const k = 10 ** fPrec;
      return `${Math.round(fN * k) / k}`;
    }; // Fix for IE parseFloat(0.55).toFixed(0) = 0


    const s = (prec ? toFixedFix(n, prec) : `${Math.round(n)}`).split('.');

    if (s[0].length > 3) {
      s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }

    if ((s[1] || '').length < prec) {
      s[1] = s[1] || '';
      s[1] += new Array(prec - s[1].length + 1).join('0');
    }

    return s.join(dec);
  }

  get icon() {
    switch (this.identifier) {
      case 'temperature':
        return 'thermometer-half';

      case 'humidity':
        return 'tint';

      case 'air_quality':
        return 'fan';

      case 'light_level':
        return 'sun';

      case 'noise_level':
        return 'microphone-alt';

      case 'power':
        return 'plug';

      case 'current':
      case 'voltage':
        return 'bolt';

      case 'energy':
        return 'calculator';
    }

    return 'chart-bar';
  }

}

// STORE
// =====
let SemaphoreTypes$6; // ENTITY TYPES
// ============

(function (SemaphoreTypes) {
  SemaphoreTypes["FETCHING"] = "fetching";
  SemaphoreTypes["GETTING"] = "getting";
  SemaphoreTypes["CREATING"] = "creating";
  SemaphoreTypes["UPDATING"] = "updating";
  SemaphoreTypes["DELETING"] = "deleting";
})(SemaphoreTypes$6 || (SemaphoreTypes$6 = {}));

let DevicePropertyEntityTypes; // ENTITY INTERFACE
// ================

(function (DevicePropertyEntityTypes) {
  DevicePropertyEntityTypes["PROPERTY"] = "devices-module/device-property";
})(DevicePropertyEntityTypes || (DevicePropertyEntityTypes = {}));

// ============

class DeviceProperty extends Property {
  constructor(...args) {
    super(...args);

    _defineProperty(this, "type", void 0);

    _defineProperty(this, "device", void 0);

    _defineProperty(this, "deviceBackward", void 0);

    _defineProperty(this, "deviceId", void 0);
  }

  static get entity() {
    return 'device_property';
  }

  static fields() {
    return Object.assign(Property.fields(), {
      type: this.string(DevicePropertyEntityTypes.PROPERTY),
      device: this.belongsTo(Device, 'id'),
      deviceBackward: this.hasOne(Device, 'id', 'deviceId'),
      deviceId: this.string('')
    });
  }

  get title() {
    if (this.name !== null) {
      return this.name;
    }

    if (this.device !== null && !this.device.isCustomModel && Object.prototype.hasOwnProperty.call(DeviceProperty.store(), '$i18n') && DeviceProperty.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.title`).toString().includes('devices.vendors.')) {
      return DeviceProperty.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.title`).toString();
    }

    return capitalize(this.identifier);
  }

  static async get(device, id) {
    return await DeviceProperty.dispatch('get', {
      device,
      id
    });
  }

  static async fetch(device) {
    return await DeviceProperty.dispatch('fetch', {
      device
    });
  }

  static async edit(property, data) {
    return await DeviceProperty.dispatch('edit', {
      property,
      data
    });
  }

  static transmitData(property, value) {
    return DeviceProperty.dispatch('transmitData', {
      property,
      value
    });
  }

  static reset() {
    DeviceProperty.dispatch('reset');
  }

}

// STORE
// =====
let SemaphoreTypes$5; // ENTITY TYPES
// ============

(function (SemaphoreTypes) {
  SemaphoreTypes["FETCHING"] = "fetching";
  SemaphoreTypes["GETTING"] = "getting";
  SemaphoreTypes["CREATING"] = "creating";
  SemaphoreTypes["UPDATING"] = "updating";
  SemaphoreTypes["DELETING"] = "deleting";
})(SemaphoreTypes$5 || (SemaphoreTypes$5 = {}));

let DeviceConfigurationEntityTypes; // ENTITY INTERFACE
// ================

(function (DeviceConfigurationEntityTypes) {
  DeviceConfigurationEntityTypes["CONFIGURATION"] = "devices-module/device-configuration";
})(DeviceConfigurationEntityTypes || (DeviceConfigurationEntityTypes = {}));

// ============

let ConfigurationNumberDatatypeTypes;

(function (ConfigurationNumberDatatypeTypes) {
  ConfigurationNumberDatatypeTypes[ConfigurationNumberDatatypeTypes["CHAR"] = DataType.CHAR] = "CHAR";
  ConfigurationNumberDatatypeTypes[ConfigurationNumberDatatypeTypes["UNSIGNED_CHAR"] = DataType.UCHAR] = "UNSIGNED_CHAR";
  ConfigurationNumberDatatypeTypes[ConfigurationNumberDatatypeTypes["SHORT"] = DataType.SHORT] = "SHORT";
  ConfigurationNumberDatatypeTypes[ConfigurationNumberDatatypeTypes["UNSIGNED_SHORT"] = DataType.USHORT] = "UNSIGNED_SHORT";
  ConfigurationNumberDatatypeTypes[ConfigurationNumberDatatypeTypes["INT"] = DataType.INT] = "INT";
  ConfigurationNumberDatatypeTypes[ConfigurationNumberDatatypeTypes["UNSIGNED_INT"] = DataType.UINT] = "UNSIGNED_INT";
  ConfigurationNumberDatatypeTypes[ConfigurationNumberDatatypeTypes["FLOAT"] = DataType.FLOAT] = "FLOAT";
})(ConfigurationNumberDatatypeTypes || (ConfigurationNumberDatatypeTypes = {}));

let ConfigurationIntegerDatatypeTypes; // ENTITY INTERFACE
// ================

(function (ConfigurationIntegerDatatypeTypes) {
  ConfigurationIntegerDatatypeTypes[ConfigurationIntegerDatatypeTypes["CHAR"] = DataType.CHAR] = "CHAR";
  ConfigurationIntegerDatatypeTypes[ConfigurationIntegerDatatypeTypes["UNSIGNED_CHAR"] = DataType.UCHAR] = "UNSIGNED_CHAR";
  ConfigurationIntegerDatatypeTypes[ConfigurationIntegerDatatypeTypes["SHORT"] = DataType.SHORT] = "SHORT";
  ConfigurationIntegerDatatypeTypes[ConfigurationIntegerDatatypeTypes["UNSIGNED_SHORT"] = DataType.USHORT] = "UNSIGNED_SHORT";
  ConfigurationIntegerDatatypeTypes[ConfigurationIntegerDatatypeTypes["INT"] = DataType.INT] = "INT";
  ConfigurationIntegerDatatypeTypes[ConfigurationIntegerDatatypeTypes["UNSIGNED_INT"] = DataType.UINT] = "UNSIGNED_INT";
})(ConfigurationIntegerDatatypeTypes || (ConfigurationIntegerDatatypeTypes = {}));

// ============

class Configuration extends Model {
  constructor(...args) {
    super(...args);

    _defineProperty(this, "id", void 0);

    _defineProperty(this, "key", void 0);

    _defineProperty(this, "identifier", void 0);

    _defineProperty(this, "name", void 0);

    _defineProperty(this, "comment", void 0);

    _defineProperty(this, "value", void 0);

    _defineProperty(this, "default", void 0);

    _defineProperty(this, "dataType", void 0);

    _defineProperty(this, "min", void 0);

    _defineProperty(this, "max", void 0);

    _defineProperty(this, "step", void 0);

    _defineProperty(this, "values", void 0);

    _defineProperty(this, "relationshipNames", void 0);
  }

  static fields() {
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
      relationshipNames: this.attr([])
    };
  }

  get isInteger() {
    return Object.values(ConfigurationIntegerDatatypeTypes).includes(this.dataType);
  }

  get isFloat() {
    return this.dataType === DataType.FLOAT;
  }

  get isNumber() {
    return Object.values(ConfigurationNumberDatatypeTypes).includes(this.dataType);
  }

  get isBoolean() {
    return this.dataType === DataType.BOOLEAN;
  }

  get isString() {
    return this.dataType === DataType.STRING;
  }

  get isSelect() {
    return this.dataType === DataType.ENUM;
  }

}

// ENTITY MODEL
// ============
class DeviceConfiguration extends Configuration {
  constructor(...args) {
    super(...args);

    _defineProperty(this, "type", void 0);

    _defineProperty(this, "device", void 0);

    _defineProperty(this, "deviceBackward", void 0);

    _defineProperty(this, "deviceId", void 0);
  }

  static get entity() {
    return 'device_configuration';
  }

  static fields() {
    return Object.assign(Configuration.fields(), {
      type: this.string(DeviceConfigurationEntityTypes.CONFIGURATION),
      device: this.belongsTo(Device, 'id'),
      deviceBackward: this.hasOne(Device, 'id', 'deviceId'),
      deviceId: this.string('')
    });
  }

  get title() {
    if (this.name !== null) {
      return this.name;
    }

    if (this.device !== null && !this.device.isCustomModel && Object.prototype.hasOwnProperty.call(DeviceConfiguration.store(), '$i18n') && !DeviceConfiguration.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.identifier.${this.identifier}.title`).toString().includes('devices.vendors.')) {
      return DeviceConfiguration.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.configuration.${this.identifier}.title`).toString();
    }

    return capitalize(this.identifier);
  }

  get description() {
    if (this.comment !== null) {
      return this.comment;
    }

    if (this.device !== null && !this.device.isCustomModel && Object.prototype.hasOwnProperty.call(DeviceConfiguration.store(), '$i18n') && !DeviceConfiguration.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.configuration.${this.identifier}.description`).toString().includes('devices.vendors.')) {
      return DeviceConfiguration.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.configuration.${this.identifier}.description`).toString();
    }

    return null;
  }

  get selectValues() {
    if (!this.isSelect) {
      throw new Error(`This field is not allowed for entity type ${this.type}`);
    }

    if (this.device !== null && !this.device.isCustomModel && Object.prototype.hasOwnProperty.call(DeviceConfiguration.store(), '$i18n')) {
      const items = [];
      this.values.forEach(item => {
        var _this$device;

        items.push({
          value: item.value,
          name: DeviceConfiguration.store().$i18n.t(`devices.vendors.${(_this$device = this.device) === null || _this$device === void 0 ? void 0 : _this$device.hardwareManufacturer}.configuration.${this.identifier}.values.${item.name}`).toString()
        });
      });
      return items;
    }

    return this.values;
  }

  get formattedValue() {
    if (this.isSelect) {
      if (this.device !== null && !this.device.isCustomModel && Object.prototype.hasOwnProperty.call(DeviceConfiguration.store(), '$i18n')) {
        this.values.forEach(item => {
          // eslint-disable-next-line eqeqeq
          if (item.value == this.value) {
            var _this$device2;

            if (!DeviceConfiguration.store().$i18n.t(`devices.vendors.${(_this$device2 = this.device) === null || _this$device2 === void 0 ? void 0 : _this$device2.hardwareManufacturer}.configuration.${this.identifier}.values.${item.name}`).toString().includes('devices.vendors.')) {
              var _this$device3;

              return DeviceConfiguration.store().$i18n.t(`devices.vendors.${(_this$device3 = this.device) === null || _this$device3 === void 0 ? void 0 : _this$device3.hardwareManufacturer}.configuration.${this.identifier}.values.${item.name}`);
            } else {
              return this.value;
            }
          }
        });
      }
    }

    return this.value;
  }

  static async get(device, id) {
    return await DeviceConfiguration.dispatch('get', {
      device,
      id
    });
  }

  static async fetch(device) {
    return await DeviceConfiguration.dispatch('fetch', {
      device
    });
  }

  static async edit(property, data) {
    return await DeviceConfiguration.dispatch('edit', {
      property,
      data
    });
  }

  static transmitData(property, value) {
    return DeviceConfiguration.dispatch('transmitData', {
      property,
      value
    });
  }

  static reset() {
    DeviceConfiguration.dispatch('reset');
  }

}

// STORE
// =====
let SemaphoreTypes$4; // ENTITY TYPES
// ============

(function (SemaphoreTypes) {
  SemaphoreTypes["FETCHING"] = "fetching";
  SemaphoreTypes["GETTING"] = "getting";
  SemaphoreTypes["CREATING"] = "creating";
  SemaphoreTypes["UPDATING"] = "updating";
  SemaphoreTypes["DELETING"] = "deleting";
})(SemaphoreTypes$4 || (SemaphoreTypes$4 = {}));

let ChannelEntityTypes; // ENTITY INTERFACE
// ================

(function (ChannelEntityTypes) {
  ChannelEntityTypes["CHANNEL"] = "devices-module/channel";
})(ChannelEntityTypes || (ChannelEntityTypes = {}));

// STORE
// =====
let SemaphoreTypes$3; // ENTITY TYPES
// ============

(function (SemaphoreTypes) {
  SemaphoreTypes["FETCHING"] = "fetching";
  SemaphoreTypes["GETTING"] = "getting";
  SemaphoreTypes["CREATING"] = "creating";
  SemaphoreTypes["UPDATING"] = "updating";
  SemaphoreTypes["DELETING"] = "deleting";
})(SemaphoreTypes$3 || (SemaphoreTypes$3 = {}));

let ChannelPropertyEntityTypes; // ENTITY INTERFACE
// ================

(function (ChannelPropertyEntityTypes) {
  ChannelPropertyEntityTypes["PROPERTY"] = "devices-module/channel-property";
})(ChannelPropertyEntityTypes || (ChannelPropertyEntityTypes = {}));

// ENTITY MODEL
// ============
class ChannelProperty extends Property {
  constructor(...args) {
    super(...args);

    _defineProperty(this, "type", void 0);

    _defineProperty(this, "channel", void 0);

    _defineProperty(this, "channelBackward", void 0);

    _defineProperty(this, "channelId", void 0);
  }

  static get entity() {
    return 'channel_property';
  }

  static fields() {
    return Object.assign(Property.fields(), {
      type: this.string(ChannelPropertyEntityTypes.PROPERTY),
      channel: this.belongsTo(Channel, 'id'),
      channelBackward: this.hasOne(Channel, 'id', 'channelId'),
      channelId: this.string('')
    });
  }

  get title() {
    if (this.name !== null) {
      return this.name;
    }

    if (this.device !== null && !this.device.isCustomModel && Object.prototype.hasOwnProperty.call(ChannelProperty.store(), '$i18n')) {
      if (this.identifier.includes('_')) {
        const propertyPart = this.identifier.substring(0, this.identifier.indexOf('_'));
        const propertyNum = parseInt(this.identifier.substring(this.identifier.indexOf('_') + 1), 10);

        if (!ChannelProperty.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.devices.${this.device.hardwareModel}.properties.${propertyPart}.title`).toString().includes('devices.vendors.')) {
          return ChannelProperty.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.devices.${this.device.hardwareModel}.properties.${propertyPart}.title`, {
            number: propertyNum
          }).toString();
        }

        if (!ChannelProperty.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${propertyPart}.title`).toString().includes('devices.vendors.')) {
          return ChannelProperty.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${propertyPart}.title`, {
            number: propertyNum
          }).toString();
        }
      }

      if (!ChannelProperty.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.devices.${this.device.hardwareModel}.properties.${this.identifier}.title`).toString().includes('devices.vendors.')) {
        return ChannelProperty.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.devices.${this.device.hardwareModel}.properties.${this.identifier}.title`).toString();
      }

      if (!ChannelProperty.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.title`).toString().includes('devices.vendors.')) {
        return ChannelProperty.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.properties.${this.identifier}.title`).toString();
      }
    }

    return capitalize(this.identifier);
  } // @ts-ignore


  get device() {
    if (this.channel === null) {
      const channel = Channel.query().where('id', this.channelId).first();

      if (channel !== null) {
        return Device.query().where('id', channel.deviceId).first();
      }

      return null;
    }

    return Device.query().where('id', this.channel.deviceId).first();
  }

  static async get(channel, id) {
    return await ChannelProperty.dispatch('get', {
      channel,
      id
    });
  }

  static async fetch(channel) {
    return await ChannelProperty.dispatch('fetch', {
      channel
    });
  }

  static async edit(property, data) {
    return await ChannelProperty.dispatch('edit', {
      property,
      data
    });
  }

  static transmitData(property, value) {
    return ChannelProperty.dispatch('transmitData', {
      property,
      value
    });
  }

  static reset() {
    ChannelProperty.dispatch('reset');
  }

}

// ENTITY MODEL
// ============
class ChannelConfiguration extends Configuration {
  constructor(...args) {
    super(...args);

    _defineProperty(this, "type", void 0);

    _defineProperty(this, "channel", void 0);

    _defineProperty(this, "channelBackward", void 0);

    _defineProperty(this, "channelId", void 0);
  }

  static get entity() {
    return 'channel_configuration';
  }

  static fields() {
    return Object.assign(Configuration.fields(), {
      type: this.string(''),
      channel: this.belongsTo(Channel, 'id'),
      channelBackward: this.hasOne(Channel, 'id', 'channelId'),
      channelId: this.string('')
    });
  }

  get title() {
    if (this.name !== null) {
      return this.name;
    }

    if (this.device !== null && !this.device.isCustomModel && Object.prototype.hasOwnProperty.call(ChannelConfiguration.store(), '$i18n')) {
      if (this.identifier.includes('_')) {
        const configurationPart = this.identifier.substring(0, this.identifier.indexOf('_')).toLowerCase();
        const configurationNum = parseInt(this.identifier.substring(this.identifier.indexOf('_') + 1), 10);

        if (!ChannelConfiguration.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.devices.${this.device.hardwareModel}.configuration.${configurationPart}.title`).toString().includes('devices.vendors.')) {
          return ChannelConfiguration.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.devices.${this.device.hardwareModel}.configuration.${configurationPart}.title`, {
            number: configurationNum
          }).toString();
        }

        if (!ChannelConfiguration.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.configuration.${configurationPart}.title`).toString().includes('devices.vendors.')) {
          return ChannelConfiguration.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.configuration.${configurationPart}.title`, {
            number: configurationNum
          }).toString();
        }
      }

      if (!ChannelConfiguration.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.devices.${this.device.hardwareModel}.configuration.${this.identifier}.title`).toString().includes('devices.vendors.')) {
        return ChannelConfiguration.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.devices.${this.device.hardwareModel}.configuration.${this.identifier}.title`).toString();
      }

      if (!ChannelConfiguration.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.configuration.${this.identifier}.title`).toString().includes('devices.vendors.')) {
        return ChannelConfiguration.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.configuration.${this.identifier}.title`).toString();
      }
    }

    return capitalize(this.identifier);
  }

  get description() {
    if (this.comment !== null) {
      return this.comment;
    }

    if (this.device !== null && !this.device.isCustomModel && Object.prototype.hasOwnProperty.call(ChannelConfiguration.store(), '$i18n')) {
      if (!ChannelConfiguration.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.devices.${this.device.hardwareModel}.configuration.${this.identifier}.description`).toString().includes('devices.vendors.')) {
        return ChannelConfiguration.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.devices.${this.device.hardwareModel}.configuration.${this.identifier}.description`).toString();
      }

      if (!ChannelConfiguration.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.configuration.${this.identifier}.description`).toString().includes('devices.vendors.')) {
        return ChannelConfiguration.store().$i18n.t(`devices.vendors.${this.device.hardwareManufacturer}.configuration.${this.identifier}.description`).toString();
      }
    }

    return null;
  }

  get selectValues() {
    if (!this.isSelect) {
      throw new Error(`This field is not allowed for entity type ${this.type}`);
    }

    if (this.device !== null && !this.device.isCustomModel && Object.prototype.hasOwnProperty.call(ChannelConfiguration.store(), '$i18n')) {
      const items = [];
      this.values.forEach(item => {
        var _this$device, _this$device2, _this$device5;

        let valueName = item.name;

        if (!ChannelConfiguration.store().$i18n.t(`devices.vendors.${(_this$device = this.device) === null || _this$device === void 0 ? void 0 : _this$device.hardwareManufacturer}.devices.${(_this$device2 = this.device) === null || _this$device2 === void 0 ? void 0 : _this$device2.hardwareModel}.configuration.${this.identifier}.values.${item.name}`).toString().includes('devices.vendors.')) {
          var _this$device3, _this$device4;

          valueName = ChannelConfiguration.store().$i18n.t(`devices.vendors.${(_this$device3 = this.device) === null || _this$device3 === void 0 ? void 0 : _this$device3.hardwareManufacturer}.devices.${(_this$device4 = this.device) === null || _this$device4 === void 0 ? void 0 : _this$device4.hardwareModel}.configuration.${this.identifier}.values.${item.name}`).toString();
        } else if (!ChannelConfiguration.store().$i18n.t(`devices.vendors.${(_this$device5 = this.device) === null || _this$device5 === void 0 ? void 0 : _this$device5.hardwareManufacturer}.configuration.${this.identifier}.values.${item.name}`).toString().includes('devices.vendors.')) {
          var _this$device6;

          valueName = ChannelConfiguration.store().$i18n.t(`devices.vendors.${(_this$device6 = this.device) === null || _this$device6 === void 0 ? void 0 : _this$device6.hardwareManufacturer}.configuration.${this.identifier}.values.${item.name}`).toString();
        }

        items.push({
          value: item.value,
          name: valueName
        });
      });
      return items;
    }

    return this.values;
  }

  get formattedValue() {
    if (this.isSelect) {
      if (this.device !== null && !this.device.isCustomModel && Object.prototype.hasOwnProperty.call(ChannelConfiguration.store(), '$i18n')) {
        this.values.forEach(item => {
          // eslint-disable-next-line eqeqeq
          if (item.value == this.value) {
            var _this$device7, _this$device8, _this$device11;

            if (!ChannelConfiguration.store().$i18n.t(`devices.vendors.${(_this$device7 = this.device) === null || _this$device7 === void 0 ? void 0 : _this$device7.hardwareManufacturer}.devices.${(_this$device8 = this.device) === null || _this$device8 === void 0 ? void 0 : _this$device8.hardwareModel}.configuration.${this.identifier}.values.${item.name}`).toString().includes('devices.vendors.')) {
              var _this$device9, _this$device10;

              return ChannelConfiguration.store().$i18n.t(`devices.vendors.${(_this$device9 = this.device) === null || _this$device9 === void 0 ? void 0 : _this$device9.hardwareManufacturer}.devices.${(_this$device10 = this.device) === null || _this$device10 === void 0 ? void 0 : _this$device10.hardwareModel}.configuration.${this.identifier}.values.${item.name}`).toString();
            } else if (!ChannelConfiguration.store().$i18n.t(`devices.vendors.${(_this$device11 = this.device) === null || _this$device11 === void 0 ? void 0 : _this$device11.hardwareManufacturer}.configuration.${this.identifier}.values.${item.name}`).toString().includes('devices.vendors.')) {
              var _this$device12;

              return ChannelConfiguration.store().$i18n.t(`devices.vendors.${(_this$device12 = this.device) === null || _this$device12 === void 0 ? void 0 : _this$device12.hardwareManufacturer}.configuration.${this.identifier}.values.${item.name}`).toString();
            } else {
              return this.value;
            }
          }
        });
      }
    }

    return this.value;
  }

  get device() {
    if (this.channel === null) {
      const channel = Channel.query().where('id', this.channelId).first();

      if (channel !== null) {
        return Device.query().where('id', channel.deviceId).first();
      }

      return null;
    }

    return Device.query().where('id', this.channel.deviceId).first();
  }

  static async get(channel, id) {
    return await ChannelConfiguration.dispatch('get', {
      channel,
      id
    });
  }

  static async fetch(channel) {
    return await ChannelConfiguration.dispatch('fetch', {
      channel
    });
  }

  static async edit(property, data) {
    return await ChannelConfiguration.dispatch('edit', {
      property,
      data
    });
  }

  static transmitData(property, value) {
    return ChannelConfiguration.dispatch('transmitData', {
      property,
      value
    });
  }

  static reset() {
    ChannelConfiguration.dispatch('reset');
  }

}

// ENTITY MODEL
// ============
class Channel extends Model {
  constructor(...args) {
    super(...args);

    _defineProperty(this, "id", void 0);

    _defineProperty(this, "type", void 0);

    _defineProperty(this, "key", void 0);

    _defineProperty(this, "identifier", void 0);

    _defineProperty(this, "name", void 0);

    _defineProperty(this, "comment", void 0);

    _defineProperty(this, "control", void 0);

    _defineProperty(this, "relationshipNames", void 0);

    _defineProperty(this, "properties", void 0);

    _defineProperty(this, "configuration", void 0);

    _defineProperty(this, "device", void 0);

    _defineProperty(this, "deviceBackward", void 0);

    _defineProperty(this, "deviceId", void 0);
  }

  static get entity() {
    return 'channel';
  }

  static fields() {
    return {
      id: this.string(''),
      type: this.string(ChannelEntityTypes.CHANNEL),
      key: this.string(''),
      identifier: this.string(''),
      name: this.string(null).nullable(),
      comment: this.string(null).nullable(),
      control: this.attr([]),
      // Relations
      relationshipNames: this.attr([]),
      properties: this.hasMany(ChannelProperty, 'channelId'),
      configuration: this.hasMany(ChannelConfiguration, 'channelId'),
      device: this.belongsTo(Device, 'id'),
      deviceBackward: this.hasOne(Device, 'id', 'deviceId'),
      deviceId: this.string('')
    };
  }

  get title() {
    if (this.name !== null) {
      return this.name;
    }

    const device = Device.query().where('id', this.deviceId).first();

    if (device !== null && !device.isCustomModel && Object.prototype.hasOwnProperty.call(Channel.store(), '$i18n')) {
      if (this.identifier.includes('_')) {
        const channelPart = this.identifier.substring(0, this.identifier.indexOf('_'));
        const channelNum = parseInt(this.identifier.substring(this.identifier.indexOf('_') + 1), 10);

        if (!Channel.store().$i18n.t(`devices.vendors.${device.hardwareManufacturer}.devices.${device.hardwareModel}.channels.${channelPart}.title`).toString().includes('devices.vendors.')) {
          return Channel.store().$i18n.t(`devices.vendors.${device.hardwareManufacturer}.devices.${device.hardwareModel}.channels.${channelPart}.title`, {
            number: channelNum + 1
          }).toString();
        }

        if (!Channel.store().$i18n.t(`devices.vendors.${device.hardwareManufacturer}.channels.${channelPart}.title`).toString().includes('devices.vendors.')) {
          return Channel.store().$i18n.t(`devices.vendors.${device.hardwareManufacturer}.channels.${channelPart}.title`, {
            number: channelNum + 1
          }).toString();
        }
      }

      if (!Channel.store().$i18n.t(`devices.vendors.${device.hardwareManufacturer}.devices.${device.hardwareModel}.channels.${this.identifier}.title`).toString().includes('devices.vendors.')) {
        return Channel.store().$i18n.t(`devices.vendors.${device.hardwareManufacturer}.devices.${device.hardwareModel}.channels.${this.identifier}.title`).toString();
      }

      if (!Channel.store().$i18n.t(`devices.vendors.${device.hardwareManufacturer}.channels.${this.identifier}.title`).toString().includes('devices.vendors.')) {
        return Channel.store().$i18n.t(`devices.vendors.${device.hardwareManufacturer}.channels.${this.identifier}.title`).toString();
      }
    }

    return capitalize(this.identifier);
  }

  static async get(device, id) {
    return await Channel.dispatch('get', {
      device,
      id
    });
  }

  static async fetch(device) {
    return await Channel.dispatch('fetch', {
      device
    });
  }

  static async edit(channel, data) {
    return await Channel.dispatch('edit', {
      channel,
      data
    });
  }

  static transmitCommand(channel, command) {
    return Channel.dispatch('transmitCommand', {
      channel,
      command
    });
  }

  static reset() {
    Channel.dispatch('reset');
  }

}

// STORE
// =====
let SemaphoreTypes$2; // ENTITY TYPES
// ============

(function (SemaphoreTypes) {
  SemaphoreTypes["FETCHING"] = "fetching";
  SemaphoreTypes["GETTING"] = "getting";
  SemaphoreTypes["CREATING"] = "creating";
  SemaphoreTypes["UPDATING"] = "updating";
  SemaphoreTypes["DELETING"] = "deleting";
})(SemaphoreTypes$2 || (SemaphoreTypes$2 = {}));

let DeviceConnectorEntityTypes; // ENTITY INTERFACE
// ================

(function (DeviceConnectorEntityTypes) {
  DeviceConnectorEntityTypes["CONNECTOR"] = "devices-module/device-connector";
})(DeviceConnectorEntityTypes || (DeviceConnectorEntityTypes = {}));

// ENTITY MODEL
// ============
class Connector extends Model {
  constructor(...args) {
    super(...args);

    _defineProperty(this, "id", void 0);

    _defineProperty(this, "type", void 0);

    _defineProperty(this, "name", void 0);

    _defineProperty(this, "enabled", void 0);

    _defineProperty(this, "control", void 0);

    _defineProperty(this, "relationshipNames", void 0);

    _defineProperty(this, "devices", void 0);

    _defineProperty(this, "address", void 0);

    _defineProperty(this, "serialInterface", void 0);

    _defineProperty(this, "baudRate", void 0);

    _defineProperty(this, "server", void 0);

    _defineProperty(this, "port", void 0);

    _defineProperty(this, "securedPort", void 0);

    _defineProperty(this, "username", void 0);

    _defineProperty(this, "password", void 0);
  }

  static get entity() {
    return 'connector';
  }

  static fields() {
    return {
      id: this.string(''),
      type: this.string(''),
      name: this.string(''),
      enabled: this.boolean(true),
      // Relations
      relationshipNames: this.attr([]),
      devices: this.hasMany(DeviceConnector, 'connectorId'),
      // FB bus
      address: this.number(null).nullable(),
      serialInterface: this.string(null).nullable(),
      baudRate: this.number(null).nullable(),
      // FB MQTT v1
      server: this.string(null).nullable(),
      port: this.number(null).nullable(),
      securedPort: this.number(null).nullable(),
      username: this.string(null).nullable(),
      password: this.string(null).nullable()
    };
  }

  get isEnabled() {
    return this.enabled;
  }

  get icon() {
    return 'magic';
  }

  static async get(id) {
    return await Connector.dispatch('get', {
      id
    });
  }

  static async fetch() {
    return await Connector.dispatch('fetch');
  }

  static async edit(connector, data) {
    return await Connector.dispatch('edit', {
      connector,
      data
    });
  }

  static transmitCommand(connector, command) {
    return Connector.dispatch('transmitCommand', {
      connector,
      command
    });
  }

  static reset() {
    Connector.dispatch('reset');
  }

}

// ENTITY MODEL
// ============
class DeviceConnector extends Model {
  constructor(...args) {
    super(...args);

    _defineProperty(this, "id", void 0);

    _defineProperty(this, "type", void 0);

    _defineProperty(this, "draft", void 0);

    _defineProperty(this, "address", void 0);

    _defineProperty(this, "maxPacketLength", void 0);

    _defineProperty(this, "descriptionSupport", void 0);

    _defineProperty(this, "settingsSupport", void 0);

    _defineProperty(this, "configuredKeyLength", void 0);

    _defineProperty(this, "pubSubPubSupport", void 0);

    _defineProperty(this, "pubSubSubSupport", void 0);

    _defineProperty(this, "pubSubSubMaxSubscriptions", void 0);

    _defineProperty(this, "pubSubSubMaxConditions", void 0);

    _defineProperty(this, "pubSubSubMaxActions", void 0);

    _defineProperty(this, "username", void 0);

    _defineProperty(this, "password", void 0);

    _defineProperty(this, "relationshipNames", void 0);

    _defineProperty(this, "device", void 0);

    _defineProperty(this, "deviceBackward", void 0);

    _defineProperty(this, "connector", void 0);

    _defineProperty(this, "connectorBackward", void 0);

    _defineProperty(this, "deviceId", void 0);

    _defineProperty(this, "connectorId", void 0);
  }

  static get entity() {
    return 'device_connector';
  }

  static fields() {
    return {
      id: this.string(''),
      type: this.string(DeviceConnectorEntityTypes.CONNECTOR),
      draft: this.boolean(false),
      // FB bus Connector specific
      address: this.number(0),
      maxPacketLength: this.number(0),
      descriptionSupport: this.boolean(false),
      settingsSupport: this.boolean(false),
      configuredKeyLength: this.number(0),
      pubSubPubSupport: this.boolean(false),
      pubSubSubSupport: this.boolean(false),
      pubSubSubMaxSubscriptions: this.number(0),
      pubSubSubMaxConditions: this.number(0),
      pubSubSubMaxActions: this.number(0),
      // MQTT Connector specific
      username: this.string(''),
      password: this.string(''),
      // Relations
      relationshipNames: this.attr([]),
      device: this.belongsTo(Device, 'id'),
      deviceBackward: this.hasOne(Device, 'id', 'deviceId'),
      connector: this.belongsTo(Connector, 'id'),
      connectorBackward: this.hasOne(Connector, 'id', 'connectorId'),
      deviceId: this.string(''),
      connectorId: this.string('')
    };
  }

  static async get(device) {
    return await DeviceConnector.dispatch('get', {
      device
    });
  }

  static async add(device, connector, data, id, draft = true) {
    return await DeviceConnector.dispatch('add', {
      id,
      draft,
      device,
      connector,
      data
    });
  }

  static async edit(connector, data) {
    return await DeviceConnector.dispatch('edit', {
      connector,
      data
    });
  }

  static async save(connector) {
    return await DeviceConnector.dispatch('save', {
      connector
    });
  }

  static async remove(connector) {
    return await DeviceConnector.dispatch('remove', {
      connector
    });
  }

  static reset() {
    DeviceConnector.dispatch('reset');
  }

}

// ============

class Device extends Model {
  constructor(...args) {
    super(...args);

    _defineProperty(this, "id", void 0);

    _defineProperty(this, "type", void 0);

    _defineProperty(this, "draft", void 0);

    _defineProperty(this, "parentId", void 0);

    _defineProperty(this, "key", void 0);

    _defineProperty(this, "identifier", void 0);

    _defineProperty(this, "name", void 0);

    _defineProperty(this, "comment", void 0);

    _defineProperty(this, "state", void 0);

    _defineProperty(this, "enabled", void 0);

    _defineProperty(this, "hardwareModel", void 0);

    _defineProperty(this, "hardwareManufacturer", void 0);

    _defineProperty(this, "hardwareVersion", void 0);

    _defineProperty(this, "macAddress", void 0);

    _defineProperty(this, "firmwareManufacturer", void 0);

    _defineProperty(this, "firmwareVersion", void 0);

    _defineProperty(this, "control", void 0);

    _defineProperty(this, "owner", void 0);

    _defineProperty(this, "relationshipNames", void 0);

    _defineProperty(this, "children", void 0);

    _defineProperty(this, "channels", void 0);

    _defineProperty(this, "properties", void 0);

    _defineProperty(this, "configuration", void 0);

    _defineProperty(this, "connector", void 0);
  }

  static get entity() {
    return 'device';
  }

  static fields() {
    return {
      id: this.string(''),
      type: this.string(DeviceEntityTypes.DEVICE),
      draft: this.boolean(false),
      parentId: this.string(null).nullable(),
      key: this.string(''),
      identifier: this.string(''),
      name: this.string(null).nullable(),
      comment: this.string(null).nullable(),
      state: this.string(DeviceConnectionState.UNKNOWN),
      enabled: this.boolean(false),
      hardwareModel: this.string(DeviceModel.CUSTOM),
      hardwareManufacturer: this.string(HardwareManufacturer.GENERIC),
      hardwareVersion: this.string(null).nullable(),
      macAddress: this.string(null).nullable(),
      firmwareManufacturer: this.string(FirmwareManufacturer.GENERIC),
      firmwareVersion: this.string(null).nullable(),
      control: this.attr([]),
      owner: this.string(null).nullable(),
      // Relations
      relationshipNames: this.attr([]),
      children: this.hasMany(Device, 'parentId'),
      channels: this.hasMany(Channel, 'deviceId'),
      properties: this.hasMany(DeviceProperty, 'deviceId'),
      configuration: this.hasMany(DeviceConfiguration, 'deviceId'),
      connector: this.hasOne(DeviceConnector, 'deviceId')
    };
  }

  get isEnabled() {
    return this.enabled;
  }

  get isReady() {
    return this.state === DeviceConnectionState.READY || this.state === DeviceConnectionState.RUNNING;
  }

  get icon() {
    if (this.hardwareManufacturer === HardwareManufacturer.ITEAD) {
      switch (this.hardwareModel) {
        case DeviceModel.SONOFF_SC:
          return 'thermometer-half';

        case DeviceModel.SONOFF_POW:
        case DeviceModel.SONOFF_POW_R2:
          return 'calculator';
      }
    }

    return 'plug';
  }

  get title() {
    if (this.name !== null) {
      return this.name;
    }

    if (Object.prototype.hasOwnProperty.call(Device.store(), '$i18n')) {
      if (this.isCustomModel) {
        return capitalize(this.identifier);
      }

      if (!Device.store().$i18n.t(`devices.vendors.${this.hardwareManufacturer}.devices.${this.hardwareModel}.title`).toString().includes('devices.vendors.')) {
        return Device.store().$i18n.t(`devices.vendors.${this.hardwareManufacturer}.devices.${this.hardwareModel}.title`).toString();
      }
    }

    return capitalize(this.identifier);
  }

  get hasComment() {
    return this.comment !== null && this.comment !== '';
  }

  get isCustomModel() {
    return this.hardwareModel === DeviceModel.CUSTOM;
  }

  static async get(id, includeChannels) {
    return await Device.dispatch('get', {
      id,
      includeChannels
    });
  }

  static async fetch(includeChannels) {
    return await Device.dispatch('fetch', {
      includeChannels
    });
  }

  static async add(data, id, draft = true) {
    return await Device.dispatch('add', {
      id,
      draft,
      data
    });
  }

  static async edit(device, data) {
    return await Device.dispatch('edit', {
      device,
      data
    });
  }

  static async save(device) {
    return await Device.dispatch('save', {
      device
    });
  }

  static async remove(device) {
    return await Device.dispatch('remove', {
      device
    });
  }

  static transmitCommand(device, command) {
    return Device.dispatch('transmitCommand', {
      device,
      command
    });
  }

  static reset() {
    Device.dispatch('reset');
  }

}

class ExceptionError extends Error {
  constructor(type, exception, ...params) {
    // Pass remaining arguments (including vendor specific ones) to parent constructor
    super(...params); // Maintains proper stack trace for where our error was thrown (only available on V8)

    _defineProperty(this, "type", void 0);

    _defineProperty(this, "exception", void 0);

    if (Error.captureStackTrace) {
      Error.captureStackTrace(this, ExceptionError);
    } // Custom debugging information


    this.type = type;
    this.exception = exception;
  }

}

class ApiError extends ExceptionError {}

class OrmError extends ExceptionError {}

// STORE
// =====
let SemaphoreTypes$1; // ENTITY TYPES
// ============

(function (SemaphoreTypes) {
  SemaphoreTypes["FETCHING"] = "fetching";
  SemaphoreTypes["GETTING"] = "getting";
  SemaphoreTypes["CREATING"] = "creating";
  SemaphoreTypes["UPDATING"] = "updating";
  SemaphoreTypes["DELETING"] = "deleting";
})(SemaphoreTypes$1 || (SemaphoreTypes$1 = {}));

let ChannelConfigurationEntityTypes; // ENTITY INTERFACE
// ================

(function (ChannelConfigurationEntityTypes) {
  ChannelConfigurationEntityTypes["CONFIGURATION"] = "devices-module/channel-configuration";
})(ChannelConfigurationEntityTypes || (ChannelConfigurationEntityTypes = {}));

const RELATIONSHIP_NAMES_PROP = 'relationshipNames';
class JsonApiModelPropertiesMapper extends ModelPropertiesMapper {
  getAttributes(model) {
    const exceptProps = ['id', '$id', 'type', 'draft', RELATIONSHIP_NAMES_PROP];

    if (model.type === ChannelEntityTypes.CHANNEL || model.type === DevicePropertyEntityTypes.PROPERTY || model.type === DeviceConfigurationEntityTypes.CONFIGURATION || model.type === DeviceConnectorEntityTypes.CONNECTOR) {
      exceptProps.push('deviceId');
      exceptProps.push('device');
      exceptProps.push('device_backward');
    } else if (model.type === ChannelPropertyEntityTypes.PROPERTY || model.type === ChannelConfigurationEntityTypes.CONFIGURATION) {
      exceptProps.push('channelId');
      exceptProps.push('channel');
      exceptProps.push('channel_backward');
    } else if (model.type === DeviceConnectorEntityTypes.CONNECTOR) {
      exceptProps.push('connectorId');
      exceptProps.push('connector');
      exceptProps.push('connector_backward');
    }

    if (Array.isArray(model[RELATIONSHIP_NAMES_PROP])) {
      exceptProps.push(...model[RELATIONSHIP_NAMES_PROP]);
    }

    const attributes = {};
    Object.keys(model).forEach(attrName => {
      if (!exceptProps.includes(attrName)) {
        const kebabName = attrName.replace(/([a-z][A-Z0-9])/g, g => `${g[0]}_${g[1].toLowerCase()}`);
        let jsonAttributes = model[attrName];

        if (typeof jsonAttributes === 'object' && jsonAttributes !== null) {
          jsonAttributes = {};
          Object.keys(model[attrName]).forEach(subAttrName => {
            const kebabSubName = subAttrName.replace(/([a-z][A-Z0-9])/g, g => `${g[0]}_${g[1].toLowerCase()}`);
            Object.assign(jsonAttributes, {
              [kebabSubName]: model[attrName][subAttrName]
            });
          });
        }

        attributes[kebabName] = jsonAttributes;
      }
    });
    return attributes;
  }

  getRelationships(model) {
    if (!Object.prototype.hasOwnProperty.call(model, RELATIONSHIP_NAMES_PROP) || !Array.isArray(model[RELATIONSHIP_NAMES_PROP])) {
      return {};
    }

    const relationshipNames = model[RELATIONSHIP_NAMES_PROP];
    const relationships = {};
    relationshipNames.forEach(relationName => {
      const kebabName = relationName.replace(/([a-z][A-Z0-9])/g, g => `${g[0]}_${g[1].toLowerCase()}`);

      if (model[relationName] !== undefined) {
        if (Array.isArray(model[relationName])) {
          relationships[kebabName] = model[relationName].map(item => {
            return {
              id: item.id,
              type: item.type
            };
          });
        } else if (typeof model[relationName] === 'object' && model[relationName] !== null) {
          relationships[kebabName] = {
            id: model[relationName].id,
            type: model[relationName].type
          };
        }
      }
    });

    if (Object.prototype.hasOwnProperty.call(model, 'deviceId')) {
      const device = Device.find(model.deviceId);

      if (device !== null) {
        relationships.device = {
          id: device.id,
          type: device.type
        };
      }
    }

    if (Object.prototype.hasOwnProperty.call(model, 'channelId')) {
      const channel = Channel.find(model.deviceId);

      if (channel !== null) {
        relationships.channel = {
          id: channel.id,
          type: channel.type
        };
      }
    }

    if (Object.prototype.hasOwnProperty.call(model, 'connectorId')) {
      const connector = Connector.find(model.connectorId);

      if (connector !== null) {
        relationships.connector = {
          id: connector.id,
          type: connector.type
        };
      }
    }

    return relationships;
  }

}
class JsonApiPropertiesMapper extends JsonPropertiesMapper {
  constructor(...args) {
    super(...args);

    _defineProperty(this, "caseRegExp", '_([a-z0-9])');
  }

  createModel(type) {
    return {
      type
    };
  }

  setId(model, id) {
    Object.assign(model, {
      id
    });
  }

  setAttributes(model, attributes) {
    const regex = new RegExp(this.caseRegExp, 'g');
    Object.keys(attributes).forEach(propName => {
      const camelName = propName.replace(regex, g => g[1].toUpperCase());
      let modelAttributes = attributes[propName];

      if (typeof modelAttributes === 'object' && modelAttributes !== null) {
        modelAttributes = {};
        Object.keys(attributes[propName]).forEach(subPropName => {
          const camelSubName = subPropName.replace(regex, g => g[1].toUpperCase());
          Object.assign(modelAttributes, {
            [camelSubName]: attributes[propName][subPropName]
          });
        });
      }

      if (propName === 'control') {
        modelAttributes = Object.values(attributes[propName]);
      }

      Object.assign(model, {
        [camelName]: modelAttributes
      });
    }); // Entity received via api is not a draft entity

    Object.assign(model, {
      draft: false
    });
  }

  setRelationships(model, relationships) {
    Object.keys(relationships).forEach(propName => {
      const regex = new RegExp(this.caseRegExp, 'g');
      const camelName = propName.replace(regex, g => g[1].toUpperCase());

      if (typeof relationships[propName] === 'function') {
        defineRelationGetter(model, propName, relationships[propName]);
      } else {
        const relation = clone(relationships[propName]);

        if (Array.isArray(relation)) {
          Object.assign(model, {
            [camelName]: relation.map(item => {
              let transformed = item;
              transformed = this.transformDevice(transformed);
              transformed = this.transformChannel(transformed);
              return transformed;
            })
          });
        } else if (get(relation, 'type') === DeviceEntityTypes.DEVICE) {
          Object.assign(model, {
            deviceId: get(relation, 'id')
          });
        } else if (get(relation, 'type') === ChannelEntityTypes.CHANNEL) {
          Object.assign(model, {
            channelId: get(relation, 'id')
          });
        } else {
          Object.assign(model, {
            [camelName]: relation
          });
        }
      }
    });
    const newNames = Object.keys(relationships);
    const currentNames = model[RELATIONSHIP_NAMES_PROP];

    if (currentNames && currentNames.length) {
      Object.assign(model, {
        [RELATIONSHIP_NAMES_PROP]: [...currentNames, ...newNames].filter((value, i, self) => self.indexOf(value) === i)
      });
    } else {
      Object.assign(model, {
        [RELATIONSHIP_NAMES_PROP]: newNames
      });
    }
  }

  transformDevice(item) {
    if (Object.prototype.hasOwnProperty.call(item, 'device')) {
      Object.assign(item, {
        deviceId: item.device.id
      });
      Reflect.deleteProperty(item, 'device');
    }

    return item;
  }

  transformChannel(item) {
    if (Object.prototype.hasOwnProperty.call(item, 'channel')) {
      Object.assign(item, {
        channelId: item.channel.id
      });
      Reflect.deleteProperty(item, 'channel');
    }

    return item;
  }

}

const ModuleApiPrefix = `/${ModulePrefix.MODULE_DEVICES_PREFIX}`;

const jsonApiFormatter$7 = new Jsona({
  modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
  jsonPropertiesMapper: new JsonApiPropertiesMapper()
});
const apiOptions$7 = {
  dataTransformer: result => jsonApiFormatter$7.deserialize(result.data)
};
const jsonSchemaValidator$7 = new Ajv();
const moduleState$7 = {
  semaphore: {
    fetching: {
      items: false,
      item: []
    },
    creating: [],
    updating: [],
    deleting: []
  },
  firstLoad: false
};
const moduleGetters$2 = {
  firstLoadFinished: state => () => {
    return !!state.firstLoad;
  },
  getting: state => id => {
    return state.semaphore.fetching.item.includes(id);
  },
  fetching: state => () => {
    return !!state.semaphore.fetching.items;
  }
};
const moduleActions$7 = {
  async get({
    state,
    commit
  }, payload) {
    if (state.semaphore.fetching.item.includes(payload.id)) {
      return false;
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$7.GETTING,
      id: payload.id
    });

    try {
      await Device.api().get(`${ModuleApiPrefix}/v1/devices/${payload.id}?include=properties,configuration,connector`, apiOptions$7);

      if (payload.includeChannels) {
        const device = Device.find(payload.id);

        if (device !== null) {
          await Channel.fetch(device);
        }
      }

      return true;
    } catch (e) {
      throw new ApiError('devices-module.devices.fetch.failed', e, 'Fetching devices failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$7.GETTING,
        id: payload.id
      });
    }
  },

  async fetch({
    state,
    commit
  }, payload) {
    if (state.semaphore.fetching.items) {
      return false;
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$7.FETCHING
    });

    try {
      await Device.api().get(`${ModuleApiPrefix}/v1/devices?include=properties,configuration,connector`, apiOptions$7);

      if (payload.includeChannels) {
        const devices = await Device.all();
        const promises = [];
        devices.forEach(device => {
          promises.push(Channel.fetch(device));
        });
        await Promise.all(promises);
      }

      commit('SET_FIRST_LOAD', true);
      return true;
    } catch (e) {
      throw new ApiError('devices-module.devices.fetch.failed', e, 'Fetching devices failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$7.FETCHING
      });
    }
  },

  async add({
    commit
  }, payload) {
    const id = typeof payload.id !== 'undefined' && payload.id !== null && payload.id !== '' ? payload.id : uuid.v4().toString();
    const draft = typeof payload.draft !== 'undefined' ? payload.draft : false;
    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$7.CREATING,
      id
    });

    try {
      await Device.insert({
        data: Object.assign({}, payload.data, {
          id,
          draft
        })
      });
    } catch (e) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$7.CREATING,
        id
      });
      throw new OrmError('devices-module.devices.create.failed', e, 'Create new device failed.');
    }

    const createdEntity = Device.find(id);

    if (createdEntity === null) {
      await Device.delete(id);
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$7.CREATING,
        id
      });
      throw new Error('devices-module.devices.create.failed');
    }

    if (draft) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$7.CREATING,
        id
      });
      return Device.find(id);
    } else {
      try {
        await Device.api().post(`${ModuleApiPrefix}/v1/devices?include=properties,configuration,connector`, jsonApiFormatter$7.serialize({
          stuff: createdEntity
        }), apiOptions$7);
        return Device.find(id);
      } catch (e) {
        // Entity could not be created on api, we have to remove it from database
        await Device.delete(id);
        throw new ApiError('devices-module.devices.create.failed', e, 'Create new device failed.');
      } finally {
        commit('CLEAR_SEMAPHORE', {
          type: SemaphoreTypes$7.CREATING,
          id
        });
      }
    }
  },

  async edit({
    state,
    commit
  }, payload) {
    if (state.semaphore.updating.includes(payload.device.id)) {
      throw new Error('devices-module.devices.update.inProgress');
    }

    if (!Device.query().where('id', payload.device.id).exists()) {
      throw new Error('devices-module.devices.update.failed');
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$7.UPDATING,
      id: payload.device.id
    });

    try {
      await Device.update({
        where: payload.device.id,
        data: payload.data
      });
    } catch (e) {
      throw new OrmError('devices-module.devices.update.failed', e, 'Edit device failed.');
    }

    const updatedEntity = Device.find(payload.device.id);

    if (updatedEntity === null) {
      // Updated entity could not be loaded from database
      await Device.get(payload.device.id, false);
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$7.UPDATING,
        id: payload.device.id
      });
      throw new Error('devices-module.devices.update.failed');
    }

    if (updatedEntity.draft) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$7.UPDATING,
        id: payload.device.id
      });
      return Device.find(payload.device.id);
    } else {
      try {
        await Device.api().patch(`${ModuleApiPrefix}/v1/devices/${updatedEntity.id}?include=properties,configuration,connector`, jsonApiFormatter$7.serialize({
          stuff: updatedEntity
        }), apiOptions$7);
        return Device.find(payload.device.id);
      } catch (e) {
        // Updating entity on api failed, we need to refresh entity
        await Device.get(payload.device.id, false);
        throw new ApiError('devices-module.devices.update.failed', e, 'Edit device failed.');
      } finally {
        commit('CLEAR_SEMAPHORE', {
          type: SemaphoreTypes$7.UPDATING,
          id: payload.device.id
        });
      }
    }
  },

  async save({
    state,
    commit
  }, payload) {
    if (state.semaphore.updating.includes(payload.device.id)) {
      throw new Error('devices-module.devices.save.inProgress');
    }

    if (!Device.query().where('id', payload.device.id).where('draft', true).exists()) {
      throw new Error('devices-module.devices.save.failed');
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$7.UPDATING,
      id: payload.device.id
    });
    const entityToSave = Device.find(payload.device.id);

    if (entityToSave === null) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$7.UPDATING,
        id: payload.device.id
      });
      throw new Error('devices-module.devices.save.failed');
    }

    try {
      await Device.api().post(`${ModuleApiPrefix}/v1/devices?include=properties,configuration,connector`, jsonApiFormatter$7.serialize({
        stuff: entityToSave
      }), apiOptions$7);
      return Device.find(payload.device.id);
    } catch (e) {
      throw new ApiError('devices-module.devices.save.failed', e, 'Save draft device failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$7.UPDATING,
        id: payload.device.id
      });
    }
  },

  async remove({
    state,
    commit
  }, payload) {
    if (state.semaphore.deleting.includes(payload.device.id)) {
      throw new Error('devices-module.devices.delete.inProgress');
    }

    if (!Device.query().where('id', payload.device.id).exists()) {
      return true;
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$7.DELETING,
      id: payload.device.id
    });

    try {
      await Device.delete(payload.device.id);
    } catch (e) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$7.DELETING,
        id: payload.device.id
      });
      throw new OrmError('devices-module.devices.delete.failed', e, 'Delete device failed.');
    }

    if (payload.device.draft) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$7.DELETING,
        id: payload.device.id
      });
      return true;
    } else {
      try {
        await Device.api().delete(`${ModuleApiPrefix}/v1/devices/${payload.device.id}`, {
          save: false
        });
        return true;
      } catch (e) {
        // Deleting entity on api failed, we need to refresh entity
        await Device.get(payload.device.id, false);
        throw new OrmError('devices-module.devices.delete.failed', e, 'Delete device failed.');
      } finally {
        commit('CLEAR_SEMAPHORE', {
          type: SemaphoreTypes$7.DELETING,
          id: payload.device.id
        });
      }
    }
  },

  transmitCommand(_store, payload) {
    if (!Device.query().where('id', payload.device.id).exists()) {
      throw new Error('devices-module.device.transmit.failed');
    }

    return new Promise((resolve, reject) => {
      Device.wamp().call({
        routing_key: DevicesModule.DEVICES_CONTROLS,
        origin: Device.$devicesModuleOrigin,
        data: {
          control: payload.command,
          device: payload.device.key
        }
      }).then(response => {
        if (get(response.data, 'response') === 'accepted') {
          resolve(true);
        } else {
          reject(new Error('devices-module.device.transmit.failed'));
        }
      }).catch(() => {
        reject(new Error('devices-module.device.transmit.failed'));
      });
    });
  },

  async socketData({
    state,
    commit
  }, payload) {
    if (payload.origin !== ModuleOrigin.MODULE_DEVICES_ORIGIN) {
      return false;
    }

    if (![DevicesModule.DEVICES_CREATED_ENTITY, DevicesModule.DEVICES_UPDATED_ENTITY, DevicesModule.DEVICES_DELETED_ENTITY].includes(payload.routingKey)) {
      return false;
    }

    const body = JSON.parse(payload.data);
    const validate = jsonSchemaValidator$7.compile(exchangeEntitySchema);

    if (validate(body)) {
      if (!Device.query().where('id', body.id).exists() && (payload.routingKey === DevicesModule.DEVICES_UPDATED_ENTITY || payload.routingKey === DevicesModule.DEVICES_DELETED_ENTITY)) {
        throw new Error('devices-module.devices.update.failed');
      }

      if (payload.routingKey === DevicesModule.DEVICES_DELETED_ENTITY) {
        commit('SET_SEMAPHORE', {
          type: SemaphoreTypes$7.DELETING,
          id: body.id
        });

        try {
          await Device.delete(body.id);
        } catch (e) {
          throw new OrmError('devices-module.devices.delete.failed', e, 'Delete device failed.');
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: SemaphoreTypes$7.DELETING,
            id: body.id
          });
        }
      } else {
        if (payload.routingKey === DevicesModule.DEVICES_UPDATED_ENTITY && state.semaphore.updating.includes(body.id)) {
          return true;
        }

        commit('SET_SEMAPHORE', {
          type: payload.routingKey === DevicesModule.DEVICES_UPDATED_ENTITY ? SemaphoreTypes$7.UPDATING : SemaphoreTypes$7.CREATING,
          id: body.id
        });
        const entityData = {};
        Object.keys(body).forEach(attrName => {
          const kebabName = attrName.replace(/([a-z][A-Z0-9])/g, g => `${g[0]}_${g[1].toLowerCase()}`);
          entityData[kebabName] = body[attrName];
        });

        try {
          await Device.insertOrUpdate({
            data: entityData
          });
        } catch (e) {
          // Updating entity on api failed, we need to refresh entity
          await Device.get(body.id, false);
          throw new OrmError('devices-module.devices.update.failed', e, 'Edit device failed.');
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: payload.routingKey === DevicesModule.DEVICES_UPDATED_ENTITY ? SemaphoreTypes$7.UPDATING : SemaphoreTypes$7.CREATING,
            id: body.id
          });
        }
      }

      return true;
    } else {
      return false;
    }
  },

  reset({
    commit
  }) {
    commit('RESET_STATE');
    Channel.reset();
  }

};
const moduleMutations$7 = {
  ['SET_FIRST_LOAD'](state, action) {
    state.firstLoad = action;
  },

  ['SET_SEMAPHORE'](state, action) {
    switch (action.type) {
      case SemaphoreTypes$7.FETCHING:
        state.semaphore.fetching.items = true;
        break;

      case SemaphoreTypes$7.GETTING:
        state.semaphore.fetching.item.push(get(action, 'id', 'notValid')); // Make all keys uniq

        state.semaphore.fetching.item = uniq(state.semaphore.fetching.item);
        break;

      case SemaphoreTypes$7.CREATING:
        state.semaphore.creating.push(get(action, 'id', 'notValid')); // Make all keys uniq

        state.semaphore.creating = uniq(state.semaphore.creating);
        break;

      case SemaphoreTypes$7.UPDATING:
        state.semaphore.updating.push(get(action, 'id', 'notValid')); // Make all keys uniq

        state.semaphore.updating = uniq(state.semaphore.updating);
        break;

      case SemaphoreTypes$7.DELETING:
        state.semaphore.deleting.push(get(action, 'id', 'notValid')); // Make all keys uniq

        state.semaphore.deleting = uniq(state.semaphore.deleting);
        break;
    }
  },

  ['CLEAR_SEMAPHORE'](state, action) {
    switch (action.type) {
      case SemaphoreTypes$7.FETCHING:
        state.semaphore.fetching.items = false;
        break;

      case SemaphoreTypes$7.GETTING:
        // Process all semaphore items
        state.semaphore.fetching.item.forEach((item, index) => {
          // Find created item in reading one item semaphore...
          if (item === get(action, 'id', 'notValid')) {
            // ...and remove it
            state.semaphore.fetching.item.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$7.CREATING:
        // Process all semaphore items
        state.semaphore.creating.forEach((item, index) => {
          // Find created item in creating semaphore...
          if (item === get(action, 'id', 'notValid')) {
            // ...and remove it
            state.semaphore.creating.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$7.UPDATING:
        // Process all semaphore items
        state.semaphore.updating.forEach((item, index) => {
          // Find created item in updating semaphore...
          if (item === get(action, 'id', 'notValid')) {
            // ...and remove it
            state.semaphore.updating.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$7.DELETING:
        // Process all semaphore items
        state.semaphore.deleting.forEach((item, index) => {
          // Find removed item in removing semaphore...
          if (item === get(action, 'id', 'notValid')) {
            // ...and remove it
            state.semaphore.deleting.splice(index, 1);
          }
        });
        break;
    }
  },

  ['RESET_STATE'](state) {
    Object.assign(state, moduleState$7);
  }

};
var devices = {
  state: () => moduleState$7,
  getters: moduleGetters$2,
  actions: moduleActions$7,
  mutations: moduleMutations$7
};

const jsonApiFormatter$6 = new Jsona({
  modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
  jsonPropertiesMapper: new JsonApiPropertiesMapper()
});
const apiOptions$6 = {
  dataTransformer: result => jsonApiFormatter$6.deserialize(result.data)
};
const jsonSchemaValidator$6 = new Ajv();
const moduleState$6 = {
  semaphore: {
    fetching: {
      items: [],
      item: []
    },
    creating: [],
    updating: [],
    deleting: []
  }
};
const moduleActions$6 = {
  async get({
    state,
    commit
  }, payload) {
    if (state.semaphore.fetching.item.includes(payload.id)) {
      return false;
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$6.GETTING,
      id: payload.id
    });

    try {
      await DeviceProperty.api().get(`${ModuleApiPrefix}/v1/devices/${payload.device.id}/properties/${payload.id}`, apiOptions$6);
      return true;
    } catch (e) {
      throw new ApiError('devices-module.device-properties.fetch.failed', e, 'Fetching device property failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$6.GETTING,
        id: payload.id
      });
    }
  },

  async fetch({
    state,
    commit
  }, payload) {
    if (state.semaphore.fetching.items.includes(payload.device.id)) {
      return false;
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$6.FETCHING,
      id: payload.device.id
    });

    try {
      await DeviceProperty.api().get(`${ModuleApiPrefix}/v1/devices/${payload.device.id}/properties`, apiOptions$6);
      return true;
    } catch (e) {
      throw new ApiError('devices-module.device-properties.fetch.failed', e, 'Fetching device properties failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$6.FETCHING,
        id: payload.device.id
      });
    }
  },

  async edit({
    state,
    commit
  }, payload) {
    if (state.semaphore.updating.includes(payload.property.id)) {
      throw new Error('devices-module.device-properties.update.inProgress');
    }

    if (!DeviceProperty.query().where('id', payload.property.id).exists()) {
      throw new Error('devices-module.device-properties.update.failed');
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$6.UPDATING,
      id: payload.property.id
    });

    try {
      await DeviceProperty.update({
        where: payload.property.id,
        data: payload.data
      });
    } catch (e) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$6.UPDATING,
        id: payload.property.id
      });
      throw new OrmError('devices-module.device-properties.update.failed', e, 'Edit device property failed.');
    }

    const updatedEntity = DeviceProperty.find(payload.property.id);

    if (updatedEntity === null) {
      const device = Device.find(payload.property.deviceId);

      if (device !== null) {
        // Updated entity could not be loaded from database
        await DeviceProperty.get(device, payload.property.id);
      }

      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$6.UPDATING,
        id: payload.property.id
      });
      throw new Error('devices-module.device-properties.update.failed');
    }

    try {
      await DeviceProperty.api().patch(`${ModuleApiPrefix}/v1/devices/${updatedEntity.deviceId}/properties/${updatedEntity.id}`, jsonApiFormatter$6.serialize({
        stuff: updatedEntity
      }), apiOptions$6);
      return DeviceProperty.find(payload.property.id);
    } catch (e) {
      const device = Device.find(payload.property.deviceId);

      if (device !== null) {
        // Updating entity on api failed, we need to refresh entity
        await DeviceProperty.get(device, payload.property.id);
      }

      throw new ApiError('devices-module.device-properties.update.failed', e, 'Edit device property failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$6.UPDATING,
        id: payload.property.id
      });
    }
  },

  async transmitData(_store, payload) {
    if (!DeviceProperty.query().where('id', payload.property.id).exists()) {
      throw new Error('devices-module.device-properties.transmit.failed');
    }

    const device = Device.find(payload.property.deviceId);

    if (device === null) {
      throw new Error('devices-module.device-properties.transmit.failed');
    }

    const backupValue = payload.property.value;

    try {
      await DeviceProperty.update({
        where: payload.property.id,
        data: {
          value: payload.value
        }
      });
    } catch (e) {
      throw new OrmError('devices-module.device-properties.transmit.failed', e, 'Edit device property failed.');
    }

    return new Promise((resolve, reject) => {
      DeviceProperty.wamp().call({
        routing_key: DevicesModule.DEVICES_PROPERTIES_DATA,
        origin: DeviceProperty.$devicesModuleOrigin,
        data: {
          device: device.key,
          property: payload.property.key,
          expected: payload.value
        }
      }).then(response => {
        if (get(response.data, 'response') === 'accepted') {
          resolve(true);
        } else {
          DeviceProperty.update({
            where: payload.property.id,
            data: {
              value: backupValue
            }
          });
          reject(new Error('devices-module.device-properties.transmit.failed'));
        }
      }).catch(() => {
        DeviceProperty.update({
          where: payload.property.id,
          data: {
            value: backupValue
          }
        });
        reject(new Error('devices-module.device-properties.transmit.failed'));
      });
    });
  },

  async socketData({
    state,
    commit
  }, payload) {
    if (payload.origin !== ModuleOrigin.MODULE_DEVICES_ORIGIN) {
      return false;
    }

    if (![DevicesModule.DEVICES_PROPERTY_CREATED_ENTITY, DevicesModule.DEVICES_PROPERTY_UPDATED_ENTITY, DevicesModule.DEVICES_PROPERTY_DELETED_ENTITY].includes(payload.routingKey)) {
      return false;
    }

    const body = JSON.parse(payload.data);
    const validate = jsonSchemaValidator$6.compile(exchangeEntitySchema$1);

    if (validate(body)) {
      if (!DeviceProperty.query().where('id', body.id).exists() && (payload.routingKey === DevicesModule.DEVICES_PROPERTY_UPDATED_ENTITY || payload.routingKey === DevicesModule.DEVICES_PROPERTY_DELETED_ENTITY)) {
        throw new Error('devices-module.device-properties.update.failed');
      }

      if (payload.routingKey === DevicesModule.DEVICES_PROPERTY_DELETED_ENTITY) {
        commit('SET_SEMAPHORE', {
          type: SemaphoreTypes$6.DELETING,
          id: body.id
        });

        try {
          await DeviceProperty.delete(body.id);
        } catch (e) {
          throw new OrmError('devices-module.device-properties.delete.failed', e, 'Delete device property failed.');
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: SemaphoreTypes$6.DELETING,
            id: body.id
          });
        }
      } else {
        if (payload.routingKey === DevicesModule.DEVICES_PROPERTY_UPDATED_ENTITY && state.semaphore.updating.includes(body.id)) {
          return true;
        }

        commit('SET_SEMAPHORE', {
          type: payload.routingKey === DevicesModule.DEVICES_PROPERTY_UPDATED_ENTITY ? SemaphoreTypes$6.UPDATING : SemaphoreTypes$6.CREATING,
          id: body.id
        });
        const entityData = {
          type: DevicePropertyEntityTypes.PROPERTY
        };
        Object.keys(body).forEach(attrName => {
          const kebabName = attrName.replace(/([a-z][A-Z0-9])/g, g => `${g[0]}_${g[1].toLowerCase()}`);

          if (kebabName === 'device') {
            const device = Device.query().where('identifier', body[attrName]).first();

            if (device !== null) {
              entityData.deviceId = device.id;
            }
          } else {
            entityData[kebabName] = body[attrName];
          }
        });

        try {
          await DeviceProperty.insertOrUpdate({
            data: entityData
          });
        } catch (e) {
          const failedEntity = DeviceProperty.query().with('device').where('id', body.id).first();

          if (failedEntity !== null && failedEntity.device !== null) {
            // Updating entity on api failed, we need to refresh entity
            await DeviceProperty.get(failedEntity.device, body.id);
          }

          throw new OrmError('devices-module.device-properties.update.failed', e, 'Edit device property failed.');
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: payload.routingKey === DevicesModule.DEVICES_PROPERTY_UPDATED_ENTITY ? SemaphoreTypes$6.UPDATING : SemaphoreTypes$6.CREATING,
            id: body.id
          });
        }
      }

      return true;
    } else {
      return false;
    }
  },

  reset({
    commit
  }) {
    commit('RESET_STATE');
  }

};
const moduleMutations$6 = {
  ['SET_SEMAPHORE'](state, action) {
    switch (action.type) {
      case SemaphoreTypes$6.FETCHING:
        state.semaphore.fetching.items.push(action.id); // Make all keys uniq

        state.semaphore.fetching.items = uniq(state.semaphore.fetching.items);
        break;

      case SemaphoreTypes$6.GETTING:
        state.semaphore.fetching.item.push(action.id); // Make all keys uniq

        state.semaphore.fetching.item = uniq(state.semaphore.fetching.item);
        break;

      case SemaphoreTypes$6.CREATING:
        state.semaphore.creating.push(action.id); // Make all keys uniq

        state.semaphore.creating = uniq(state.semaphore.creating);
        break;

      case SemaphoreTypes$6.UPDATING:
        state.semaphore.updating.push(action.id); // Make all keys uniq

        state.semaphore.updating = uniq(state.semaphore.updating);
        break;

      case SemaphoreTypes$6.DELETING:
        state.semaphore.deleting.push(action.id); // Make all keys uniq

        state.semaphore.deleting = uniq(state.semaphore.deleting);
        break;
    }
  },

  ['CLEAR_SEMAPHORE'](state, action) {
    switch (action.type) {
      case SemaphoreTypes$6.FETCHING:
        // Process all semaphore items
        state.semaphore.fetching.items.forEach((item, index) => {
          // Find created item in reading one item semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.fetching.items.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$6.GETTING:
        // Process all semaphore items
        state.semaphore.fetching.item.forEach((item, index) => {
          // Find created item in reading one item semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.fetching.item.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$6.CREATING:
        // Process all semaphore items
        state.semaphore.creating.forEach((item, index) => {
          // Find created item in creating semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.creating.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$6.UPDATING:
        // Process all semaphore items
        state.semaphore.updating.forEach((item, index) => {
          // Find created item in updating semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.updating.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$6.DELETING:
        // Process all semaphore items
        state.semaphore.deleting.forEach((item, index) => {
          // Find removed item in removing semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.deleting.splice(index, 1);
          }
        });
        break;
    }
  },

  ['RESET_STATE'](state) {
    Object.assign(state, moduleState$6);
  }

};
var deviceProperties = {
  state: () => moduleState$6,
  actions: moduleActions$6,
  mutations: moduleMutations$6
};

const jsonApiFormatter$5 = new Jsona({
  modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
  jsonPropertiesMapper: new JsonApiPropertiesMapper()
});
const apiOptions$5 = {
  dataTransformer: result => jsonApiFormatter$5.deserialize(result.data)
};
const jsonSchemaValidator$5 = new Ajv();
const moduleState$5 = {
  semaphore: {
    fetching: {
      items: [],
      item: []
    },
    creating: [],
    updating: [],
    deleting: []
  }
};
const moduleActions$5 = {
  async get({
    state,
    commit
  }, payload) {
    if (state.semaphore.fetching.item.includes(payload.id)) {
      return false;
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$5.GETTING,
      id: payload.id
    });

    try {
      await DeviceConfiguration.api().get(`${ModuleApiPrefix}/v1/devices/${payload.device.id}/configuration/${payload.id}`, apiOptions$5);
      return true;
    } catch (e) {
      throw new ApiError('devices-module.device-configuration.fetch.failed', e, 'Fetching device configuration failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$5.GETTING,
        id: payload.id
      });
    }
  },

  async fetch({
    state,
    commit
  }, payload) {
    if (state.semaphore.fetching.items.includes(payload.device.id)) {
      return false;
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$5.FETCHING,
      id: payload.device.id
    });

    try {
      await DeviceConfiguration.api().get(`${ModuleApiPrefix}/v1/devices/${payload.device.id}/configuration`, apiOptions$5);
      return true;
    } catch (e) {
      throw new ApiError('devices-module.device-configuration.fetch.failed', e, 'Fetching device configuration failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$5.FETCHING,
        id: payload.device.id
      });
    }
  },

  async edit({
    state,
    commit
  }, payload) {
    if (state.semaphore.updating.includes(payload.configuration.id)) {
      throw new Error('devices-module.device-configuration.update.inProgress');
    }

    if (!DeviceConfiguration.query().where('id', payload.configuration.id).exists()) {
      throw new Error('devices-module.device-configuration.update.failed');
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$5.UPDATING,
      id: payload.configuration.id
    });

    try {
      await DeviceConfiguration.update({
        where: payload.configuration.id,
        data: payload.data
      });
    } catch (e) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$5.UPDATING,
        id: payload.configuration.id
      });
      throw new OrmError('devices-module.device-configuration.update.failed', e, 'Edit device configuration failed.');
    }

    const updatedEntity = DeviceConfiguration.find(payload.configuration.id);

    if (updatedEntity === null) {
      const device = Device.find(payload.configuration.deviceId);

      if (device !== null) {
        // Updated entity could not be loaded from database
        await DeviceConfiguration.get(device, payload.configuration.id);
      }

      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$5.UPDATING,
        id: payload.configuration.id
      });
      throw new Error('devices-module.device-configuration.update.failed');
    }

    const device = Device.find(payload.configuration.deviceId);

    if (device === null) {
      throw new Error('devices-module.device-configuration.update.failed');
    }

    try {
      await DeviceConfiguration.api().patch(`${ModuleApiPrefix}/v1/devices/${device.id}/configuration/${updatedEntity.id}`, jsonApiFormatter$5.serialize({
        stuff: updatedEntity
      }), apiOptions$5);
      return DeviceConfiguration.find(payload.configuration.id);
    } catch (e) {
      // Updating entity on api failed, we need to refresh entity
      await DeviceConfiguration.get(device, payload.configuration.id);
      throw new ApiError('devices-module.device-configuration.update.failed', e, 'Edit device configuration failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$5.UPDATING,
        id: payload.configuration.id
      });
    }
  },

  async transmitData(_store, payload) {
    if (!DeviceConfiguration.query().where('id', payload.configuration.id).exists()) {
      throw new Error('devices-module.device-configuration.transmit.failed');
    }

    const device = Device.find(payload.configuration.deviceId);

    if (device === null) {
      throw new Error('devices-module.device-configuration.transmit.failed');
    }

    const backupValue = payload.configuration.value;

    try {
      await DeviceConfiguration.update({
        where: payload.configuration.id,
        data: {
          value: payload.value
        }
      });
    } catch (e) {
      throw new OrmError('devices-module.device-configuration.transmit.failed', e, 'Edit device configuration failed.');
    }

    return new Promise((resolve, reject) => {
      DeviceConfiguration.wamp().call({
        routing_key: DevicesModule.DEVICES_CONFIGURATION_DATA,
        origin: DeviceConfiguration.$devicesModuleOrigin,
        data: {
          device: device.key,
          configuration: payload.configuration.key,
          expected: payload.value
        }
      }).then(response => {
        if (get(response.data, 'response') === 'accepted') {
          resolve(true);
        } else {
          DeviceConfiguration.update({
            where: payload.configuration.id,
            data: {
              value: backupValue
            }
          });
          reject(new Error('devices-module.device-configuration.transmit.failed'));
        }
      }).catch(() => {
        DeviceConfiguration.update({
          where: payload.configuration.id,
          data: {
            value: backupValue
          }
        });
        reject(new Error('devices-module.device-configuration.transmit.failed'));
      });
    });
  },

  async socketData({
    state,
    commit
  }, payload) {
    if (payload.origin !== ModuleOrigin.MODULE_DEVICES_ORIGIN) {
      return false;
    }

    if (![DevicesModule.DEVICES_CONFIGURATION_CREATED_ENTITY, DevicesModule.DEVICES_CONFIGURATION_UPDATED_ENTITY, DevicesModule.DEVICES_CONFIGURATION_DELETED_ENTITY].includes(payload.routingKey)) {
      return false;
    }

    const body = JSON.parse(payload.data);
    const validate = jsonSchemaValidator$5.compile(exchangeEntitySchema$2);

    if (validate(body)) {
      if (!DeviceConfiguration.query().where('id', body.id).exists() && (payload.routingKey === DevicesModule.DEVICES_CONFIGURATION_UPDATED_ENTITY || payload.routingKey === DevicesModule.DEVICES_CONFIGURATION_DELETED_ENTITY)) {
        throw new Error('devices-module.device-configuration.update.failed');
      }

      if (payload.routingKey === DevicesModule.DEVICES_CONFIGURATION_DELETED_ENTITY) {
        commit('SET_SEMAPHORE', {
          type: SemaphoreTypes$5.DELETING,
          id: body.id
        });

        try {
          await DeviceConfiguration.delete(body.id);
        } catch (e) {
          throw new OrmError('devices-module.device-configuration.delete.failed', e, 'Delete device configuration failed.');
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: SemaphoreTypes$5.DELETING,
            id: body.id
          });
        }
      } else {
        if (payload.routingKey === DevicesModule.DEVICES_CONFIGURATION_UPDATED_ENTITY && state.semaphore.updating.includes(body.id)) {
          return true;
        }

        commit('SET_SEMAPHORE', {
          type: payload.routingKey === DevicesModule.DEVICES_CONFIGURATION_UPDATED_ENTITY ? SemaphoreTypes$5.UPDATING : SemaphoreTypes$5.CREATING,
          id: body.id
        });
        const entityData = {
          type: DeviceConfigurationEntityTypes.CONFIGURATION
        };
        Object.keys(body).forEach(attrName => {
          const kebabName = attrName.replace(/([a-z][A-Z0-9])/g, g => `${g[0]}_${g[1].toLowerCase()}`);

          if (kebabName === 'device') {
            const device = Device.query().where('device', body[attrName]).first();

            if (device !== null) {
              entityData.deviceId = device.id;
            }
          } else {
            entityData[kebabName] = body[attrName];
          }
        });

        try {
          await DeviceConfiguration.insertOrUpdate({
            data: entityData
          });
        } catch (e) {
          const failedEntity = DeviceConfiguration.query().with('device').where('id', body.id).first();

          if (failedEntity !== null && failedEntity.device !== null) {
            // Updating entity on api failed, we need to refresh entity
            await DeviceConfiguration.get(failedEntity.device, body.id);
          }

          throw new OrmError('devices-module.device-configuration.update.failed', e, 'Edit device configuration failed.');
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: payload.routingKey === DevicesModule.DEVICES_CONFIGURATION_UPDATED_ENTITY ? SemaphoreTypes$5.UPDATING : SemaphoreTypes$5.CREATING,
            id: body.id
          });
        }
      }

      return true;
    } else {
      return false;
    }
  },

  reset({
    commit
  }) {
    commit('RESET_STATE');
  }

};
const moduleMutations$5 = {
  ['SET_SEMAPHORE'](state, action) {
    switch (action.type) {
      case SemaphoreTypes$5.FETCHING:
        state.semaphore.fetching.items.push(action.id); // Make all keys uniq

        state.semaphore.fetching.items = uniq(state.semaphore.fetching.items);
        break;

      case SemaphoreTypes$5.GETTING:
        state.semaphore.fetching.item.push(action.id); // Make all keys uniq

        state.semaphore.fetching.item = uniq(state.semaphore.fetching.item);
        break;

      case SemaphoreTypes$5.CREATING:
        state.semaphore.creating.push(action.id); // Make all keys uniq

        state.semaphore.creating = uniq(state.semaphore.creating);
        break;

      case SemaphoreTypes$5.UPDATING:
        state.semaphore.updating.push(action.id); // Make all keys uniq

        state.semaphore.updating = uniq(state.semaphore.updating);
        break;

      case SemaphoreTypes$5.DELETING:
        state.semaphore.deleting.push(action.id); // Make all keys uniq

        state.semaphore.deleting = uniq(state.semaphore.deleting);
        break;
    }
  },

  ['CLEAR_SEMAPHORE'](state, action) {
    switch (action.type) {
      case SemaphoreTypes$5.FETCHING:
        // Process all semaphore items
        state.semaphore.fetching.items.forEach((item, index) => {
          // Find created item in reading one item semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.fetching.items.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$5.GETTING:
        // Process all semaphore items
        state.semaphore.fetching.item.forEach((item, index) => {
          // Find created item in reading one item semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.fetching.item.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$5.CREATING:
        // Process all semaphore items
        state.semaphore.creating.forEach((item, index) => {
          // Find created item in creating semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.creating.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$5.UPDATING:
        // Process all semaphore items
        state.semaphore.updating.forEach((item, index) => {
          // Find created item in updating semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.updating.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$5.DELETING:
        // Process all semaphore items
        state.semaphore.deleting.forEach((item, index) => {
          // Find removed item in removing semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.deleting.splice(index, 1);
          }
        });
        break;
    }
  },

  ['RESET_STATE'](state) {
    Object.assign(state, moduleState$5);
  }

};
var devicesConfiguration = {
  state: () => moduleState$5,
  actions: moduleActions$5,
  mutations: moduleMutations$5
};

const jsonApiFormatter$4 = new Jsona({
  modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
  jsonPropertiesMapper: new JsonApiPropertiesMapper()
});
const apiOptions$4 = {
  dataTransformer: result => jsonApiFormatter$4.deserialize(result.data)
};
const jsonSchemaValidator$4 = new Ajv();
const moduleState$4 = {
  semaphore: {
    fetching: {
      item: []
    },
    creating: [],
    updating: [],
    deleting: []
  }
};
const moduleActions$4 = {
  async get({
    state,
    commit
  }, payload) {
    if (state.semaphore.fetching.item.includes(payload.device.id)) {
      return false;
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$2.GETTING,
      id: payload.device.id
    });

    try {
      await DeviceConnector.api().get(`${ModuleApiPrefix}/v1/devices/${payload.device.id}/connector`, apiOptions$4);
      return true;
    } catch (e) {
      throw new ApiError('devices-module.device-connector.get.failed', e, 'Fetching device connector failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$2.GETTING,
        id: payload.device.id
      });
    }
  },

  async add({
    commit
  }, payload) {
    const id = typeof payload.id !== 'undefined' && payload.id !== null && payload.id !== '' ? payload.id : uuid.v4().toString();
    const draft = typeof payload.draft !== 'undefined' ? payload.draft : false;
    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$2.CREATING,
      id
    });

    try {
      await DeviceConnector.insert({
        data: Object.assign({}, payload.data, {
          id,
          draft,
          deviceId: payload.device.id,
          connectorId: payload.connector.id
        })
      });
    } catch (e) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$2.CREATING,
        id
      });
      throw new OrmError('devices-module.device-connector.create.failed', e, 'Create device connector failed.');
    }

    const createdEntity = DeviceConnector.find(id);

    if (createdEntity === null) {
      await DeviceConnector.delete(id);
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$2.CREATING,
        id
      });
      throw new Error('devices-module.device-connector.create.failed');
    }

    if (draft) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$2.CREATING,
        id
      });
      return DeviceConnector.find(id);
    } else {
      try {
        await DeviceConnector.api().post(`${ModuleApiPrefix}/v1/devices/${payload.device.id}/connector`, jsonApiFormatter$4.serialize({
          stuff: createdEntity
        }), apiOptions$4);
        return DeviceConnector.find(id);
      } catch (e) {
        // Entity could not be created on api, we have to remove it from database
        await DeviceConnector.delete(id);
        throw new ApiError('devices-module.device-connector.create.failed', e, 'Create device connector failed.');
      } finally {
        commit('CLEAR_SEMAPHORE', {
          type: SemaphoreTypes$2.CREATING,
          id
        });
      }
    }
  },

  async edit({
    state,
    commit
  }, payload) {
    if (state.semaphore.updating.includes(payload.connector.id)) {
      throw new Error('devices-module.device-connector.update.inProgress');
    }

    if (!DeviceConnector.query().where('id', payload.connector.id).exists()) {
      throw new Error('devices-module.device-connector.update.failed');
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$2.UPDATING,
      id: payload.connector.id
    });

    try {
      await DeviceConnector.update({
        where: payload.connector.id,
        data: payload.data
      });
    } catch (e) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$2.UPDATING,
        id: payload.connector.id
      });
      throw new OrmError('devices-module.device-connector.update.failed', e, 'Edit device connector failed.');
    }

    const updatedEntity = DeviceConnector.find(payload.connector.id);

    if (updatedEntity === null) {
      const device = Device.find(payload.connector.deviceId);

      if (device !== null) {
        // Updated entity could not be loaded from database
        await DeviceConnector.get(device);
      }

      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$2.UPDATING,
        id: payload.connector.id
      });
      throw new Error('devices-module.device-connector.update.failed');
    }

    if (updatedEntity.draft) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$2.UPDATING,
        id: payload.connector.id
      });
      return DeviceConnector.find(payload.connector.id);
    } else {
      try {
        await DeviceConnector.api().patch(`${ModuleApiPrefix}/v1/devices/${updatedEntity.deviceId}/connector`, jsonApiFormatter$4.serialize({
          stuff: updatedEntity
        }), apiOptions$4);
        return DeviceConnector.find(payload.connector.id);
      } catch (e) {
        const device = Device.find(payload.connector.deviceId);

        if (device !== null) {
          // Updating entity on api failed, we need to refresh entity
          await DeviceConnector.get(device);
        }

        throw new ApiError('devices-module.device-connector.update.failed', e, 'Edit device connector failed.');
      } finally {
        commit('CLEAR_SEMAPHORE', {
          type: SemaphoreTypes$2.UPDATING,
          id: payload.connector.id
        });
      }
    }
  },

  async save({
    state,
    commit
  }, payload) {
    if (state.semaphore.updating.includes(payload.connector.id)) {
      throw new Error('devices-module.device-connector.save.inProgress');
    }

    if (!DeviceConnector.query().where('id', payload.connector.id).where('draft', true).exists()) {
      throw new Error('devices-module.device-connector.save.failed');
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$2.UPDATING,
      id: payload.connector.id
    });
    const entityToSave = DeviceConnector.find(payload.connector.id);

    if (entityToSave === null) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$2.UPDATING,
        id: payload.connector.id
      });
      throw new Error('devices-module.device-connector.save.failed');
    }

    try {
      await DeviceConnector.api().post(`${ModuleApiPrefix}/v1/devices/${entityToSave.deviceId}/connector`, jsonApiFormatter$4.serialize({
        stuff: entityToSave
      }), apiOptions$4);
      return DeviceConnector.find(payload.connector.id);
    } catch (e) {
      throw new ApiError('devices-module.device-connector.save.failed', e, 'Save draft device connector failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$2.UPDATING,
        id: payload.connector.id
      });
    }
  },

  async socketData({
    state,
    commit
  }, payload) {
    if (payload.origin !== ModuleOrigin.MODULE_DEVICES_ORIGIN) {
      return false;
    }

    if (![DevicesModule.DEVICES_CONNECTOR_CREATED_ENTITY, DevicesModule.DEVICES_CONNECTOR_UPDATED_ENTITY, DevicesModule.DEVICES_CONNECTOR_DELETED_ENTITY].includes(payload.routingKey)) {
      return false;
    }

    const body = JSON.parse(payload.data);
    const validate = jsonSchemaValidator$4.compile(exchangeEntitySchema$3);

    if (validate(body)) {
      if (!DeviceConnector.query().where('id', body.id).exists() && (payload.routingKey === DevicesModule.DEVICES_PROPERTY_UPDATED_ENTITY || payload.routingKey === DevicesModule.DEVICES_PROPERTY_DELETED_ENTITY)) {
        throw new Error('devices-module.device-connector.update.failed');
      }

      if (payload.routingKey === DevicesModule.DEVICES_PROPERTY_DELETED_ENTITY) {
        commit('SET_SEMAPHORE', {
          type: SemaphoreTypes$2.DELETING,
          id: body.id
        });

        try {
          await DeviceConnector.delete(body.id);
        } catch (e) {
          throw new OrmError('devices-module.device-connector.delete.failed', e, 'Delete device connector failed.');
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: SemaphoreTypes$2.DELETING,
            id: body.id
          });
        }
      } else {
        if (payload.routingKey === DevicesModule.DEVICES_PROPERTY_UPDATED_ENTITY && state.semaphore.updating.includes(body.id)) {
          return true;
        }

        commit('SET_SEMAPHORE', {
          type: payload.routingKey === DevicesModule.DEVICES_PROPERTY_UPDATED_ENTITY ? SemaphoreTypes$2.UPDATING : SemaphoreTypes$2.CREATING,
          id: body.id
        });
        const entityData = {
          type: DeviceConnectorEntityTypes.CONNECTOR
        };
        Object.keys(body).forEach(attrName => {
          const kebabName = attrName.replace(/([a-z][A-Z0-9])/g, g => `${g[0]}_${g[1].toLowerCase()}`);

          if (kebabName === 'device') {
            const device = Device.query().where('identifier', body[attrName]).first();

            if (device !== null) {
              entityData.deviceId = device.id;
            }
          } else {
            entityData[kebabName] = body[attrName];
          }
        });

        try {
          await DeviceConnector.insertOrUpdate({
            data: entityData
          });
        } catch (e) {
          const failedEntity = DeviceConnector.query().with('device').where('id', body.id).first();

          if (failedEntity !== null && failedEntity.device !== null) {
            // Updating entity on api failed, we need to refresh entity
            await DeviceConnector.get(failedEntity.device);
          }

          throw new OrmError('devices-module.device-connector.update.failed', e, 'Edit device connector failed.');
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: payload.routingKey === DevicesModule.DEVICES_PROPERTY_UPDATED_ENTITY ? SemaphoreTypes$2.UPDATING : SemaphoreTypes$2.CREATING,
            id: body.id
          });
        }
      }

      return true;
    } else {
      return false;
    }
  },

  reset({
    commit
  }) {
    commit('RESET_STATE');
  }

};
const moduleMutations$4 = {
  ['SET_SEMAPHORE'](state, action) {
    switch (action.type) {
      case SemaphoreTypes$2.GETTING:
        state.semaphore.fetching.item.push(action.id); // Make all keys uniq

        state.semaphore.fetching.item = uniq(state.semaphore.fetching.item);
        break;

      case SemaphoreTypes$2.CREATING:
        state.semaphore.creating.push(action.id); // Make all keys uniq

        state.semaphore.creating = uniq(state.semaphore.creating);
        break;

      case SemaphoreTypes$2.UPDATING:
        state.semaphore.updating.push(action.id); // Make all keys uniq

        state.semaphore.updating = uniq(state.semaphore.updating);
        break;

      case SemaphoreTypes$2.DELETING:
        state.semaphore.deleting.push(action.id); // Make all keys uniq

        state.semaphore.deleting = uniq(state.semaphore.deleting);
        break;
    }
  },

  ['CLEAR_SEMAPHORE'](state, action) {
    switch (action.type) {
      case SemaphoreTypes$2.GETTING:
        // Process all semaphore items
        state.semaphore.fetching.item.forEach((item, index) => {
          // Find created item in reading one item semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.fetching.item.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$2.CREATING:
        // Process all semaphore items
        state.semaphore.creating.forEach((item, index) => {
          // Find created item in creating semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.creating.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$2.UPDATING:
        // Process all semaphore items
        state.semaphore.updating.forEach((item, index) => {
          // Find created item in updating semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.updating.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$2.DELETING:
        // Process all semaphore items
        state.semaphore.deleting.forEach((item, index) => {
          // Find removed item in removing semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.deleting.splice(index, 1);
          }
        });
        break;
    }
  },

  ['RESET_STATE'](state) {
    Object.assign(state, moduleState$4);
  }

};
var deviceConnector = {
  state: () => moduleState$4,
  actions: moduleActions$4,
  mutations: moduleMutations$4
};

const jsonApiFormatter$3 = new Jsona({
  modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
  jsonPropertiesMapper: new JsonApiPropertiesMapper()
});
const apiOptions$3 = {
  dataTransformer: result => jsonApiFormatter$3.deserialize(result.data)
};
const jsonSchemaValidator$3 = new Ajv();
const moduleState$3 = {
  semaphore: {
    fetching: {
      items: [],
      item: []
    },
    creating: [],
    updating: [],
    deleting: []
  },
  firstLoad: []
};
const moduleGetters$1 = {
  firstLoadFinished: state => deviceId => {
    return state.firstLoad.includes(deviceId);
  },
  getting: state => channelId => {
    return state.semaphore.fetching.item.includes(channelId);
  },
  fetching: state => deviceId => {
    return deviceId !== null ? state.semaphore.fetching.items.includes(deviceId) : state.semaphore.fetching.items.length > 0;
  }
};
const moduleActions$3 = {
  async get({
    state,
    commit
  }, payload) {
    if (state.semaphore.fetching.item.includes(payload.id)) {
      return false;
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$4.GETTING,
      id: payload.id
    });

    try {
      await Channel.api().get(`${ModuleApiPrefix}/v1/devices/${payload.device.id}/channels/${payload.id}?include=properties,configuration`, apiOptions$3);
      return true;
    } catch (e) {
      throw new ApiError('devices-module.channels.get.failed', e, 'Fetching channel failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$4.GETTING,
        id: payload.id
      });
    }
  },

  async fetch({
    state,
    commit
  }, payload) {
    if (state.semaphore.fetching.items.includes(payload.device.id) || payload.device.draft) {
      return false;
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$4.FETCHING,
      id: payload.device.id
    });

    try {
      await Channel.api().get(`${ModuleApiPrefix}/v1/devices/${payload.device.id}/channels?include=properties,configuration`, apiOptions$3);
      commit('SET_FIRST_LOAD', {
        id: payload.device.id
      });
      return true;
    } catch (e) {
      throw new ApiError('devices-module.channels.fetch.failed', e, 'Fetching channels failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$4.FETCHING,
        id: payload.device.id
      });
    }
  },

  async edit({
    state,
    commit
  }, payload) {
    if (state.semaphore.updating.includes(payload.channel.id)) {
      throw new Error('devices-module.channels.update.inProgress');
    }

    if (!Channel.query().where('id', payload.channel.id).exists()) {
      throw new Error('devices-module.channels.update.failed');
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$4.UPDATING,
      id: payload.channel.id
    });

    try {
      await Channel.update({
        where: payload.channel.id,
        data: payload.data
      });
    } catch (e) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$4.UPDATING,
        id: payload.channel.id
      });
      throw new OrmError('devices-module.channels.edit.failed', e, 'Edit channel failed.');
    }

    const updatedEntity = Channel.find(payload.channel.id);

    if (updatedEntity === null) {
      const device = Device.find(payload.channel.deviceId);

      if (device !== null) {
        // Updated entity could not be loaded from database
        await Channel.get(device, payload.channel.id);
      }

      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$4.UPDATING,
        id: payload.channel.id
      });
      throw new Error('devices-module.channels.update.failed');
    }

    try {
      await Channel.api().patch(`${ModuleApiPrefix}/v1/devices/${updatedEntity.deviceId}/channels/${updatedEntity.id}?include=properties,configuration`, jsonApiFormatter$3.serialize({
        stuff: updatedEntity
      }), apiOptions$3);
      return Channel.find(payload.channel.id);
    } catch (e) {
      const device = Device.find(payload.channel.deviceId);

      if (device !== null) {
        // Updating entity on api failed, we need to refresh entity
        await Channel.get(device, payload.channel.id);
      }

      throw new ApiError('devices-module.channels.update.failed', e, 'Edit channel failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$4.UPDATING,
        id: payload.channel.id
      });
    }
  },

  transmitCommand(_store, payload) {
    if (!Channel.query().where('id', payload.channel.id).exists()) {
      throw new Error('devices-module.channel.transmit.failed');
    }

    const device = Device.find(payload.channel.deviceId);

    if (device === null) {
      throw new Error('devices-module.channel.transmit.failed');
    }

    return new Promise((resolve, reject) => {
      Channel.wamp().call({
        routing_key: DevicesModule.CHANNELS_CONTROLS,
        origin: Channel.$devicesModuleOrigin,
        data: {
          control: payload.command,
          device: device.key,
          channel: payload.channel.key
        }
      }).then(response => {
        if (get(response.data, 'response') === 'accepted') {
          resolve(true);
        } else {
          reject(new Error('devices-module.channel.transmit.failed'));
        }
      }).catch(() => {
        reject(new Error('devices-module.channel.transmit.failed'));
      });
    });
  },

  async socketData({
    state,
    commit
  }, payload) {
    if (payload.origin !== ModuleOrigin.MODULE_DEVICES_ORIGIN) {
      return false;
    }

    if (![DevicesModule.CHANNELS_CREATED_ENTITY, DevicesModule.CHANNELS_UPDATED_ENTITY, DevicesModule.CHANNELS_DELETED_ENTITY].includes(payload.routingKey)) {
      return false;
    }

    const body = JSON.parse(payload.data);
    const validate = jsonSchemaValidator$3.compile(exchangeEntitySchema$4);

    if (validate(body)) {
      if (!Channel.query().where('id', body.id).exists() && (payload.routingKey === DevicesModule.CHANNELS_UPDATED_ENTITY || payload.routingKey === DevicesModule.CHANNELS_DELETED_ENTITY)) {
        throw new Error('devices-module.channels.update.failed');
      }

      if (payload.routingKey === DevicesModule.CHANNELS_DELETED_ENTITY) {
        commit('SET_SEMAPHORE', {
          type: SemaphoreTypes$4.DELETING,
          id: body.id
        });

        try {
          await Channel.delete(body.id);
        } catch (e) {
          throw new OrmError('devices-module.channels.delete.failed', e, 'Delete channel failed.');
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: SemaphoreTypes$4.DELETING,
            id: body.id
          });
        }
      } else {
        if (payload.routingKey === DevicesModule.CHANNELS_UPDATED_ENTITY && state.semaphore.updating.includes(body.id)) {
          return true;
        }

        commit('SET_SEMAPHORE', {
          type: payload.routingKey === DevicesModule.CHANNELS_UPDATED_ENTITY ? SemaphoreTypes$4.UPDATING : SemaphoreTypes$4.CREATING,
          id: body.id
        });
        const entityData = {
          type: ChannelEntityTypes.CHANNEL
        };
        Object.keys(body).forEach(attrName => {
          const kebabName = attrName.replace(/([a-z][A-Z0-9])/g, g => `${g[0]}_${g[1].toLowerCase()}`);

          if (kebabName === 'device') {
            const device = Device.query().where('identifier', body[attrName]).first();

            if (device !== null) {
              entityData.deviceId = device.id;
            }
          } else {
            entityData[kebabName] = body[attrName];
          }
        });

        try {
          await Channel.insertOrUpdate({
            data: entityData
          });
        } catch (e) {
          const failedEntity = Channel.query().with('device').where('id', body.id).first();

          if (failedEntity !== null && failedEntity.device !== null) {
            // Updating entity on api failed, we need to refresh entity
            await Channel.get(failedEntity.device, body.id);
          }

          throw new OrmError('devices-module.channels.update.failed', e, 'Edit channel failed.');
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: payload.routingKey === DevicesModule.CHANNELS_UPDATED_ENTITY ? SemaphoreTypes$4.UPDATING : SemaphoreTypes$4.CREATING,
            id: body.id
          });
        }
      }

      return true;
    } else {
      return false;
    }
  },

  reset({
    commit
  }) {
    commit('RESET_STATE');
  }

};
const moduleMutations$3 = {
  ['SET_FIRST_LOAD'](state, action) {
    state.firstLoad.push(action.id); // Make all keys uniq

    state.firstLoad = uniq(state.firstLoad);
  },

  ['SET_SEMAPHORE'](state, action) {
    switch (action.type) {
      case SemaphoreTypes$4.FETCHING:
        state.semaphore.fetching.items.push(action.id); // Make all keys uniq

        state.semaphore.fetching.items = uniq(state.semaphore.fetching.items);
        break;

      case SemaphoreTypes$4.GETTING:
        state.semaphore.fetching.item.push(action.id); // Make all keys uniq

        state.semaphore.fetching.item = uniq(state.semaphore.fetching.item);
        break;

      case SemaphoreTypes$4.CREATING:
        state.semaphore.creating.push(action.id); // Make all keys uniq

        state.semaphore.creating = uniq(state.semaphore.creating);
        break;

      case SemaphoreTypes$4.UPDATING:
        state.semaphore.updating.push(action.id); // Make all keys uniq

        state.semaphore.updating = uniq(state.semaphore.updating);
        break;

      case SemaphoreTypes$4.DELETING:
        state.semaphore.deleting.push(action.id); // Make all keys uniq

        state.semaphore.deleting = uniq(state.semaphore.deleting);
        break;
    }
  },

  ['CLEAR_SEMAPHORE'](state, action) {
    switch (action.type) {
      case SemaphoreTypes$4.FETCHING:
        // Process all semaphore items
        state.semaphore.fetching.items.forEach((item, index) => {
          // Find created item in reading one item semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.fetching.items.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$4.GETTING:
        // Process all semaphore items
        state.semaphore.fetching.item.forEach((item, index) => {
          // Find created item in reading one item semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.fetching.item.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$4.CREATING:
        // Process all semaphore items
        state.semaphore.creating.forEach((item, index) => {
          // Find created item in creating semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.creating.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$4.UPDATING:
        // Process all semaphore items
        state.semaphore.updating.forEach((item, index) => {
          // Find created item in updating semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.updating.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$4.DELETING:
        // Process all semaphore items
        state.semaphore.deleting.forEach((item, index) => {
          // Find created item in deleting semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.deleting.splice(index, 1);
          }
        });
        break;
    }
  },

  ['RESET_STATE'](state) {
    Object.assign(state, moduleState$3);
  }

};
var channels = {
  state: () => moduleState$3,
  getters: moduleGetters$1,
  actions: moduleActions$3,
  mutations: moduleMutations$3
};

const jsonApiFormatter$2 = new Jsona({
  modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
  jsonPropertiesMapper: new JsonApiPropertiesMapper()
});
const apiOptions$2 = {
  dataTransformer: result => jsonApiFormatter$2.deserialize(result.data)
};
const jsonSchemaValidator$2 = new Ajv();
const moduleState$2 = {
  semaphore: {
    fetching: {
      items: [],
      item: []
    },
    creating: [],
    updating: [],
    deleting: []
  }
};
const moduleActions$2 = {
  async get({
    state,
    commit
  }, payload) {
    if (state.semaphore.fetching.item.includes(payload.id)) {
      return false;
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$3.GETTING,
      id: payload.id
    });

    try {
      await ChannelProperty.api().get(`${ModuleApiPrefix}/v1/devices/${payload.channel.deviceId}/channels/${payload.channel.id}/properties/${payload.id}`, apiOptions$2);
      return true;
    } catch (e) {
      throw new ApiError('devices-module.channel-properties.fetch.failed', e, 'Fetching channel property failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$3.GETTING,
        id: payload.id
      });
    }
  },

  async fetch({
    state,
    commit
  }, payload) {
    if (state.semaphore.fetching.items.includes(payload.channel.id)) {
      return false;
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$3.FETCHING,
      id: payload.channel.id
    });

    try {
      await ChannelProperty.api().get(`${ModuleApiPrefix}/v1/devices/${payload.channel.deviceId}/channels/${payload.channel.id}/properties`, apiOptions$2);
      return true;
    } catch (e) {
      throw new ApiError('devices-module.channel-properties.fetch.failed', e, 'Fetching channel properties failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$3.FETCHING,
        id: payload.channel.id
      });
    }
  },

  async edit({
    state,
    commit
  }, payload) {
    if (state.semaphore.updating.includes(payload.property.id)) {
      throw new Error('devices-module.channel-properties.update.inProgress');
    }

    if (!ChannelProperty.query().where('id', payload.property.id).exists()) {
      throw new Error('devices-module.channel-properties.update.failed');
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$3.UPDATING,
      id: payload.property.id
    });

    try {
      await ChannelProperty.update({
        where: payload.property.id,
        data: payload.data
      });
    } catch (e) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$3.UPDATING,
        id: payload.property.id
      });
      throw new OrmError('devices-module.channel-properties.update.failed', e, 'Edit channel property failed.');
    }

    const updatedEntity = ChannelProperty.find(payload.property.id);

    if (updatedEntity === null) {
      const channel = Channel.find(payload.property.channelId);

      if (channel !== null) {
        // Updated entity could not be loaded from database
        await ChannelProperty.get(channel, payload.property.id);
      }

      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$3.UPDATING,
        id: payload.property.id
      });
      throw new Error('devices-module.channel-properties.update.failed');
    }

    const channel = Channel.find(payload.property.channelId);

    if (channel === null) {
      throw new Error('devices-module.channel-properties.update.failed');
    }

    try {
      await ChannelProperty.api().patch(`${ModuleApiPrefix}/v1/devices/${channel.deviceId}/channels/${updatedEntity.channelId}/properties/${updatedEntity.id}`, jsonApiFormatter$2.serialize({
        stuff: updatedEntity
      }), apiOptions$2);
      return ChannelProperty.find(payload.property.id);
    } catch (e) {
      // Updating entity on api failed, we need to refresh entity
      await ChannelProperty.get(channel, payload.property.id);
      throw new ApiError('devices-module.channel-properties.update.failed', e, 'Edit channel property failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$3.UPDATING,
        id: payload.property.id
      });
    }
  },

  async transmitData(_store, payload) {
    if (!ChannelProperty.query().where('id', payload.property.id).exists()) {
      throw new Error('devices-module.channel-properties.transmit.failed');
    }

    const channel = Channel.find(payload.property.channelId);

    if (channel === null) {
      throw new Error('devices-module.channel-properties.transmit.failed');
    }

    const device = Device.find(channel.deviceId);

    if (device === null) {
      throw new Error('devices-module.channel-properties.transmit.failed');
    }

    const backupValue = payload.property.value;

    try {
      await ChannelProperty.update({
        where: payload.property.id,
        data: {
          value: payload.value
        }
      });
    } catch (e) {
      throw new OrmError('devices-module.channel-properties.transmit.failed', e, 'Edit channel property failed.');
    }

    return new Promise((resolve, reject) => {
      ChannelProperty.wamp().call({
        routing_key: DevicesModule.CHANNELS_PROPERTIES_DATA,
        origin: ChannelProperty.$devicesModuleOrigin,
        data: {
          device: device.key,
          channel: channel.key,
          property: payload.property.key,
          expected: payload.value
        }
      }).then(response => {
        if (get(response.data, 'response') === 'accepted') {
          resolve(true);
        } else {
          ChannelProperty.update({
            where: payload.property.id,
            data: {
              value: backupValue
            }
          });
          reject(new Error('devices-module.channel-properties.transmit.failed'));
        }
      }).catch(() => {
        ChannelProperty.update({
          where: payload.property.id,
          data: {
            value: backupValue
          }
        });
        reject(new Error('devices-module.channel-properties.transmit.failed'));
      });
    });
  },

  async socketData({
    state,
    commit
  }, payload) {
    if (payload.origin !== ModuleOrigin.MODULE_DEVICES_ORIGIN) {
      return false;
    }

    if (![DevicesModule.CHANNELS_PROPERTY_CREATED_ENTITY, DevicesModule.CHANNELS_PROPERTY_UPDATED_ENTITY, DevicesModule.CHANNELS_PROPERTY_DELETED_ENTITY].includes(payload.routingKey)) {
      return false;
    }

    const body = JSON.parse(payload.data);
    const validate = jsonSchemaValidator$2.compile(exchangeEntitySchema$5);

    if (validate(body)) {
      if (!ChannelProperty.query().where('id', body.id).exists() && (payload.routingKey === DevicesModule.CHANNELS_PROPERTY_UPDATED_ENTITY || payload.routingKey === DevicesModule.CHANNELS_PROPERTY_DELETED_ENTITY)) {
        throw new Error('devices-module.channel-properties.update.failed');
      }

      if (payload.routingKey === DevicesModule.CHANNELS_PROPERTY_DELETED_ENTITY) {
        commit('SET_SEMAPHORE', {
          type: SemaphoreTypes$3.DELETING,
          id: body.id
        });

        try {
          await ChannelProperty.delete(body.id);
        } catch (e) {
          throw new OrmError('devices-module.channel-properties.delete.failed', e, 'Delete channel property failed.');
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: SemaphoreTypes$3.DELETING,
            id: body.id
          });
        }
      } else {
        if (payload.routingKey === DevicesModule.CHANNELS_PROPERTY_UPDATED_ENTITY && state.semaphore.updating.includes(body.id)) {
          return true;
        }

        commit('SET_SEMAPHORE', {
          type: payload.routingKey === DevicesModule.CHANNELS_PROPERTY_UPDATED_ENTITY ? SemaphoreTypes$3.UPDATING : SemaphoreTypes$3.CREATING,
          id: body.id
        });
        const entityData = {
          type: ChannelPropertyEntityTypes.PROPERTY
        };
        Object.keys(body).forEach(attrName => {
          const kebabName = attrName.replace(/([a-z][A-Z0-9])/g, g => `${g[0]}_${g[1].toLowerCase()}`);

          if (kebabName === 'channel') {
            const channel = Channel.query().where('channel', body[attrName]).first();

            if (channel !== null) {
              entityData.channelId = channel.id;
            }
          } else {
            entityData[kebabName] = body[attrName];
          }
        });

        try {
          await ChannelProperty.insertOrUpdate({
            data: entityData
          });
        } catch (e) {
          const failedEntity = ChannelProperty.query().with('channel').where('id', body.id).first();

          if (failedEntity !== null && failedEntity.channel !== null) {
            // Updating entity on api failed, we need to refresh entity
            await ChannelProperty.get(failedEntity.channel, body.id);
          }

          throw new OrmError('devices-module.channel-properties.update.failed', e, 'Edit channel property failed.');
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: payload.routingKey === DevicesModule.CHANNELS_PROPERTY_UPDATED_ENTITY ? SemaphoreTypes$3.UPDATING : SemaphoreTypes$3.CREATING,
            id: body.id
          });
        }
      }

      return true;
    } else {
      return false;
    }
  },

  reset({
    commit
  }) {
    commit('RESET_STATE');
  }

};
const moduleMutations$2 = {
  ['SET_SEMAPHORE'](state, action) {
    switch (action.type) {
      case SemaphoreTypes$3.FETCHING:
        state.semaphore.fetching.items.push(action.id); // Make all keys uniq

        state.semaphore.fetching.items = uniq(state.semaphore.fetching.items);
        break;

      case SemaphoreTypes$3.GETTING:
        state.semaphore.fetching.item.push(action.id); // Make all keys uniq

        state.semaphore.fetching.item = uniq(state.semaphore.fetching.item);
        break;

      case SemaphoreTypes$3.CREATING:
        state.semaphore.creating.push(action.id); // Make all keys uniq

        state.semaphore.creating = uniq(state.semaphore.creating);
        break;

      case SemaphoreTypes$3.UPDATING:
        state.semaphore.updating.push(action.id); // Make all keys uniq

        state.semaphore.updating = uniq(state.semaphore.updating);
        break;

      case SemaphoreTypes$3.DELETING:
        state.semaphore.deleting.push(action.id); // Make all keys uniq

        state.semaphore.deleting = uniq(state.semaphore.deleting);
        break;
    }
  },

  ['CLEAR_SEMAPHORE'](state, action) {
    switch (action.type) {
      case SemaphoreTypes$3.FETCHING:
        // Process all semaphore items
        state.semaphore.fetching.items.forEach((item, index) => {
          // Find created item in reading one item semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.fetching.items.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$3.GETTING:
        // Process all semaphore items
        state.semaphore.fetching.item.forEach((item, index) => {
          // Find created item in reading one item semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.fetching.item.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$3.CREATING:
        // Process all semaphore items
        state.semaphore.creating.forEach((item, index) => {
          // Find created item in creating semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.creating.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$3.UPDATING:
        // Process all semaphore items
        state.semaphore.updating.forEach((item, index) => {
          // Find created item in updating semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.updating.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$3.DELETING:
        // Process all semaphore items
        state.semaphore.deleting.forEach((item, index) => {
          // Find removed item in removing semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.deleting.splice(index, 1);
          }
        });
        break;
    }
  },

  ['RESET_STATE'](state) {
    Object.assign(state, moduleState$2);
  }

};
var channelProperties = {
  state: () => moduleState$2,
  actions: moduleActions$2,
  mutations: moduleMutations$2
};

const jsonApiFormatter$1 = new Jsona({
  modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
  jsonPropertiesMapper: new JsonApiPropertiesMapper()
});
const apiOptions$1 = {
  dataTransformer: result => jsonApiFormatter$1.deserialize(result.data)
};
const jsonSchemaValidator$1 = new Ajv();
const moduleState$1 = {
  semaphore: {
    fetching: {
      items: [],
      item: []
    },
    creating: [],
    updating: [],
    deleting: []
  }
};
const moduleActions$1 = {
  async get({
    state,
    commit
  }, payload) {
    if (state.semaphore.fetching.item.includes(payload.id)) {
      return false;
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$1.GETTING,
      id: payload.id
    });

    try {
      await ChannelConfiguration.api().get(`${ModuleApiPrefix}/v1/devices/${payload.channel.deviceId}/channels/${payload.channel.id}/configuration/${payload.id}`, apiOptions$1);
      return true;
    } catch (e) {
      throw new ApiError('devices-module.channel-configuration.fetch.failed', e, 'Fetching channel configuration failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$1.GETTING,
        id: payload.id
      });
    }
  },

  async fetch({
    state,
    commit
  }, payload) {
    if (state.semaphore.fetching.items.includes(payload.channel.id)) {
      return false;
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$1.FETCHING,
      id: payload.channel.id
    });

    try {
      await ChannelConfiguration.api().get(`${ModuleApiPrefix}/v1/devices/${payload.channel.deviceId}/channels/${payload.channel.id}/configuration`, apiOptions$1);
      return true;
    } catch (e) {
      throw new ApiError('devices-module.channel-configuration.fetch.failed', e, 'Fetching channel configuration failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$1.FETCHING,
        id: payload.channel.id
      });
    }
  },

  async edit({
    state,
    commit
  }, payload) {
    if (state.semaphore.updating.includes(payload.configuration.id)) {
      throw new Error('devices-module.channel-configuration.update.inProgress');
    }

    if (!ChannelConfiguration.query().where('id', payload.configuration.id).exists()) {
      throw new Error('devices-module.channel-configuration.update.failed');
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes$1.UPDATING,
      id: payload.configuration.id
    });

    try {
      await ChannelConfiguration.update({
        where: payload.configuration.id,
        data: payload.data
      });
    } catch (e) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$1.UPDATING,
        id: payload.configuration.id
      });
      throw new OrmError('devices-module.channel-configuration.update.failed', e, 'Edit channel configuration failed.');
    }

    const updatedEntity = ChannelConfiguration.find(payload.configuration.id);

    if (updatedEntity === null) {
      const channel = Channel.find(payload.configuration.channelId);

      if (channel !== null) {
        // Updated entity could not be loaded from database
        await ChannelConfiguration.get(channel, payload.configuration.id);
      }

      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$1.UPDATING,
        id: payload.configuration.id
      });
      throw new Error('devices-module.channel-configuration.update.failed');
    }

    const channel = Channel.find(payload.configuration.channelId);

    if (channel === null) {
      throw new Error('devices-module.channel-configuration.update.failed');
    }

    try {
      await ChannelConfiguration.api().patch(`${ModuleApiPrefix}/v1/devices/${channel.deviceId}/channels/${updatedEntity.channelId}/configuration/${updatedEntity.id}`, jsonApiFormatter$1.serialize({
        stuff: updatedEntity
      }), apiOptions$1);
      return ChannelConfiguration.find(payload.configuration.id);
    } catch (e) {
      // Updating entity on api failed, we need to refresh entity
      await ChannelConfiguration.get(channel, payload.configuration.id);
      throw new ApiError('devices-module.channel-configuration.update.failed', e, 'Edit channel configuration failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes$1.UPDATING,
        id: payload.configuration.id
      });
    }
  },

  async transmitData(_store, payload) {
    if (!ChannelConfiguration.query().where('id', payload.configuration.id).exists()) {
      throw new Error('devices-module.channel-configuration.transmit.failed');
    }

    const channel = Channel.find(payload.configuration.channelId);

    if (channel === null) {
      throw new Error('devices-module.channel-configuration.transmit.failed');
    }

    const device = Device.find(channel.deviceId);

    if (device === null) {
      throw new Error('devices-module.channel-configuration.transmit.failed');
    }

    const backupValue = payload.configuration.value;

    try {
      await ChannelConfiguration.update({
        where: payload.configuration.id,
        data: {
          value: payload.value
        }
      });
    } catch (e) {
      throw new OrmError('devices-module.channel-configuration.transmit.failed', e, 'Edit channel configuration failed.');
    }

    return new Promise((resolve, reject) => {
      ChannelConfiguration.wamp().call({
        routing_key: DevicesModule.CHANNELS_CONFIGURATION_DATA,
        origin: ChannelConfiguration.$devicesModuleOrigin,
        data: {
          device: device.key,
          channel: channel.key,
          configuration: payload.configuration.key,
          expected: payload.value
        }
      }).then(response => {
        if (get(response.data, 'response') === 'accepted') {
          resolve(true);
        } else {
          ChannelConfiguration.update({
            where: payload.configuration.id,
            data: {
              value: backupValue
            }
          });
          reject(new Error('devices-module.channel-configuration.transmit.failed'));
        }
      }).catch(() => {
        ChannelConfiguration.update({
          where: payload.configuration.id,
          data: {
            value: backupValue
          }
        });
        reject(new Error('devices-module.channel-configuration.transmit.failed'));
      });
    });
  },

  async socketData({
    state,
    commit
  }, payload) {
    if (payload.origin !== ModuleOrigin.MODULE_DEVICES_ORIGIN) {
      return false;
    }

    if (![DevicesModule.CHANNELS_CONFIGURATION_CREATED_ENTITY, DevicesModule.CHANNELS_CONFIGURATION_UPDATED_ENTITY, DevicesModule.CHANNELS_CONFIGURATION_DELETED_ENTITY].includes(payload.routingKey)) {
      return false;
    }

    const body = JSON.parse(payload.data);
    const validate = jsonSchemaValidator$1.compile(exchangeEntitySchema$6);

    if (validate(body)) {
      if (!ChannelConfiguration.query().where('id', body.id).exists() && (payload.routingKey === DevicesModule.CHANNELS_CONFIGURATION_UPDATED_ENTITY || payload.routingKey === DevicesModule.CHANNELS_CONFIGURATION_DELETED_ENTITY)) {
        throw new Error('devices-module.channel-configuration.update.failed');
      }

      if (payload.routingKey === DevicesModule.CHANNELS_CONFIGURATION_DELETED_ENTITY) {
        commit('SET_SEMAPHORE', {
          type: SemaphoreTypes$1.DELETING,
          id: body.id
        });

        try {
          await ChannelConfiguration.delete(body.id);
        } catch (e) {
          throw new OrmError('devices-module.channel-configuration.delete.failed', e, 'Delete channel configuration failed.');
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: SemaphoreTypes$1.DELETING,
            id: body.id
          });
        }
      } else {
        if (payload.routingKey === DevicesModule.CHANNELS_CONFIGURATION_UPDATED_ENTITY && state.semaphore.updating.includes(body.id)) {
          return true;
        }

        commit('SET_SEMAPHORE', {
          type: payload.routingKey === DevicesModule.CHANNELS_CONFIGURATION_UPDATED_ENTITY ? SemaphoreTypes$1.UPDATING : SemaphoreTypes$1.CREATING,
          id: body.id
        });
        const entityData = {
          type: ChannelConfigurationEntityTypes.CONFIGURATION
        };
        Object.keys(body).forEach(attrName => {
          const kebabName = attrName.replace(/([a-z][A-Z0-9])/g, g => `${g[0]}_${g[1].toLowerCase()}`);

          if (kebabName === 'channel') {
            const channel = Channel.query().where('channel', body[attrName]).first();

            if (channel !== null) {
              entityData.channelId = channel.id;
            }
          } else {
            entityData[kebabName] = body[attrName];
          }
        });

        try {
          await ChannelConfiguration.insertOrUpdate({
            data: entityData
          });
        } catch (e) {
          const failedEntity = ChannelConfiguration.query().with('channel').where('id', body.id).first();

          if (failedEntity !== null && failedEntity.channel !== null) {
            // Updated entity could not be loaded from database
            await ChannelConfiguration.get(failedEntity.channel, body.id);
          }

          throw new OrmError('devices-module.channel-configuration.update.failed', e, 'Edit channel configuration failed.');
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: payload.routingKey === DevicesModule.CHANNELS_CONFIGURATION_UPDATED_ENTITY ? SemaphoreTypes$1.UPDATING : SemaphoreTypes$1.CREATING,
            id: body.id
          });
        }
      }

      return true;
    } else {
      return false;
    }
  },

  reset({
    commit
  }) {
    commit('RESET_STATE');
  }

};
const moduleMutations$1 = {
  ['SET_SEMAPHORE'](state, action) {
    switch (action.type) {
      case SemaphoreTypes$1.FETCHING:
        state.semaphore.fetching.items.push(action.id); // Make all keys uniq

        state.semaphore.fetching.items = uniq(state.semaphore.fetching.items);
        break;

      case SemaphoreTypes$1.GETTING:
        state.semaphore.fetching.item.push(action.id); // Make all keys uniq

        state.semaphore.fetching.item = uniq(state.semaphore.fetching.item);
        break;

      case SemaphoreTypes$1.CREATING:
        state.semaphore.creating.push(action.id); // Make all keys uniq

        state.semaphore.creating = uniq(state.semaphore.creating);
        break;

      case SemaphoreTypes$1.UPDATING:
        state.semaphore.updating.push(action.id); // Make all keys uniq

        state.semaphore.updating = uniq(state.semaphore.updating);
        break;

      case SemaphoreTypes$1.DELETING:
        state.semaphore.deleting.push(action.id); // Make all keys uniq

        state.semaphore.deleting = uniq(state.semaphore.deleting);
        break;
    }
  },

  ['CLEAR_SEMAPHORE'](state, action) {
    switch (action.type) {
      case SemaphoreTypes$1.FETCHING:
        // Process all semaphore items
        state.semaphore.fetching.items.forEach((item, index) => {
          // Find created item in reading one item semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.fetching.items.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$1.GETTING:
        // Process all semaphore items
        state.semaphore.fetching.item.forEach((item, index) => {
          // Find created item in reading one item semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.fetching.item.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$1.CREATING:
        // Process all semaphore items
        state.semaphore.creating.forEach((item, index) => {
          // Find created item in creating semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.creating.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$1.UPDATING:
        // Process all semaphore items
        state.semaphore.updating.forEach((item, index) => {
          // Find created item in updating semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.updating.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes$1.DELETING:
        // Process all semaphore items
        state.semaphore.deleting.forEach((item, index) => {
          // Find removed item in removing semaphore...
          if (item === action.id) {
            // ...and remove it
            state.semaphore.deleting.splice(index, 1);
          }
        });
        break;
    }
  },

  ['RESET_STATE'](state) {
    Object.assign(state, moduleState$1);
  }

};
var channelsConfiguration = {
  state: () => moduleState$1,
  actions: moduleActions$1,
  mutations: moduleMutations$1
};

// STORE
// =====
let SemaphoreTypes; // ENTITY TYPES
// ============

(function (SemaphoreTypes) {
  SemaphoreTypes["FETCHING"] = "fetching";
  SemaphoreTypes["GETTING"] = "getting";
  SemaphoreTypes["CREATING"] = "creating";
  SemaphoreTypes["UPDATING"] = "updating";
  SemaphoreTypes["DELETING"] = "deleting";
})(SemaphoreTypes || (SemaphoreTypes = {}));

let ConnectorEntityTypes; // ENTITY INTERFACE
// ================

(function (ConnectorEntityTypes) {
  ConnectorEntityTypes["FB_BUS"] = "devices-module/connector-fb-bus";
  ConnectorEntityTypes["FB_MQTT_V1"] = "devices-module/connector-fb-mqtt-v1";
})(ConnectorEntityTypes || (ConnectorEntityTypes = {}));

const jsonApiFormatter = new Jsona({
  modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
  jsonPropertiesMapper: new JsonApiPropertiesMapper()
});
const apiOptions = {
  dataTransformer: result => jsonApiFormatter.deserialize(result.data)
};
const jsonSchemaValidator = new Ajv();
const moduleState = {
  semaphore: {
    fetching: {
      items: false,
      item: []
    },
    updating: []
  },
  firstLoad: false
};
const moduleGetters = {
  firstLoadFinished: state => () => {
    return !!state.firstLoad;
  },
  getting: state => id => {
    return state.semaphore.fetching.item.includes(id);
  },
  fetching: state => () => {
    return state.semaphore.fetching.items;
  }
};
const moduleActions = {
  async get({
    state,
    commit
  }, payload) {
    if (state.semaphore.fetching.item.includes(payload.id)) {
      return false;
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.GETTING,
      id: payload.id
    });

    try {
      await Connector.api().get(`${ModuleApiPrefix}/v1/connectors/${payload.id}`, apiOptions);
      return true;
    } catch (e) {
      throw new ApiError('devices-module.connectors.fetch.failed', e, 'Fetching connectors failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.GETTING,
        id: payload.id
      });
    }
  },

  async fetch({
    state,
    commit
  }) {
    if (state.semaphore.fetching.items) {
      return false;
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.FETCHING
    });

    try {
      await Connector.api().get(`${ModuleApiPrefix}/v1/connectors`, apiOptions);
      commit('SET_FIRST_LOAD', true);
      return true;
    } catch (e) {
      throw new ApiError('devices-module.connectors.fetch.failed', e, 'Fetching connectors failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.FETCHING
      });
    }
  },

  async edit({
    state,
    commit
  }, payload) {
    if (state.semaphore.updating.includes(payload.connector.id)) {
      throw new Error('devices-module.connectors.update.inProgress');
    }

    if (!Connector.query().where('id', payload.connector.id).exists()) {
      throw new Error('devices-module.connectors.update.failed');
    }

    commit('SET_SEMAPHORE', {
      type: SemaphoreTypes.UPDATING,
      id: payload.connector.id
    });

    try {
      await Connector.update({
        where: payload.connector.id,
        data: payload.data
      });
    } catch (e) {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.connector.id
      });
      throw new OrmError('devices-module.connectors.update.failed', e, 'Edit connector failed.');
    }

    const updatedEntity = Connector.find(payload.connector.id);

    if (updatedEntity === null) {
      // Updated entity could not be loaded from database
      await Connector.get(payload.connector.id);
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.connector.id
      });
      throw new Error('devices-module.connectors.update.failed');
    }

    try {
      await Connector.api().patch(`${ModuleApiPrefix}/v1/connectors/${updatedEntity.id}`, jsonApiFormatter.serialize({
        stuff: updatedEntity
      }), apiOptions);
      return Connector.find(payload.connector.id);
    } catch (e) {
      // Updating entity on api failed, we need to refresh entity
      await Connector.get(payload.connector.id);
      throw new ApiError('devices-module.connectors.update.failed', e, 'Edit connector failed.');
    } finally {
      commit('CLEAR_SEMAPHORE', {
        type: SemaphoreTypes.UPDATING,
        id: payload.connector.id
      });
    }
  },

  transmitCommand(_store, payload) {
    if (!Connector.query().where('id', payload.connector.id).exists()) {
      throw new Error('devices-module.connector.transmit.failed');
    }

    return new Promise((resolve, reject) => {
      Connector.wamp().call({
        routing_key: DevicesModule.CONNECTOR_CONTROLS,
        origin: Connector.$devicesModuleOrigin,
        data: {
          control: payload.command,
          connector: payload.connector.id
        }
      }).then(response => {
        if (get(response.data, 'response') === 'accepted') {
          resolve(true);
        } else {
          reject(new Error('devices-module.connector.transmit.failed'));
        }
      }).catch(() => {
        reject(new Error('devices-module.connector.transmit.failed'));
      });
    });
  },

  async socketData({
    state,
    commit
  }, payload) {
    if (payload.origin !== ModuleOrigin.MODULE_DEVICES_ORIGIN) {
      return false;
    }

    if (![DevicesModule.CONNECTOR_CREATED_ENTITY, DevicesModule.CONNECTOR_UPDATED_ENTITY, DevicesModule.CONNECTOR_DELETED_ENTITY].includes(payload.routingKey)) {
      return false;
    }

    const body = JSON.parse(payload.data);
    const validate = jsonSchemaValidator.compile(exchangeEntitySchema$7);

    if (validate(body)) {
      if (!Connector.query().where('id', body.id).exists() && (payload.routingKey === DevicesModule.CONNECTOR_UPDATED_ENTITY || payload.routingKey === DevicesModule.CONNECTOR_DELETED_ENTITY)) {
        throw new Error('devices-module.connectors.update.failed');
      }

      if (payload.routingKey === DevicesModule.CONNECTOR_DELETED_ENTITY) {
        commit('SET_SEMAPHORE', {
          type: SemaphoreTypes.DELETING,
          id: body.id
        });

        try {
          await Connector.delete(body.id);
        } catch (e) {
          throw new OrmError('devices-module.connectors.delete.failed', e, 'Delete connector failed.');
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: SemaphoreTypes.DELETING,
            id: body.id
          });
        }
      } else {
        if (payload.routingKey === DevicesModule.CONNECTOR_UPDATED_ENTITY && state.semaphore.updating.includes(body.id)) {
          return true;
        }

        commit('SET_SEMAPHORE', {
          type: payload.routingKey === DevicesModule.CONNECTOR_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
          id: body.id
        });
        const entityData = {};
        Object.keys(body).forEach(attrName => {
          const kebabName = attrName.replace(/([a-z][A-Z0-9])/g, g => `${g[0]}_${g[1].toLowerCase()}`);
          entityData[kebabName] = body[attrName];
        });

        try {
          await Connector.insertOrUpdate({
            data: entityData
          });
        } catch (e) {
          // Updating entity on api failed, we need to refresh entity
          await Connector.get(body.id);
          throw new OrmError('devices-module.connectors.update.failed', e, 'Edit connector failed.');
        } finally {
          commit('CLEAR_SEMAPHORE', {
            type: payload.routingKey === DevicesModule.CONNECTOR_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
            id: body.id
          });
        }
      }

      return true;
    } else {
      return false;
    }
  },

  reset({
    commit
  }) {
    commit('RESET_STATE');
  }

};
const moduleMutations = {
  ['SET_FIRST_LOAD'](state, action) {
    state.firstLoad = action;
  },

  ['SET_SEMAPHORE'](state, action) {
    switch (action.type) {
      case SemaphoreTypes.FETCHING:
        state.semaphore.fetching.items = true;
        break;

      case SemaphoreTypes.GETTING:
        state.semaphore.fetching.item.push(get(action, 'id', 'notValid')); // Make all keys uniq

        state.semaphore.fetching.item = uniq(state.semaphore.fetching.item);
        break;

      case SemaphoreTypes.UPDATING:
        state.semaphore.updating.push(get(action, 'id', 'notValid')); // Make all keys uniq

        state.semaphore.updating = uniq(state.semaphore.updating);
        break;
    }
  },

  ['CLEAR_SEMAPHORE'](state, action) {
    switch (action.type) {
      case SemaphoreTypes.FETCHING:
        state.semaphore.fetching.items = false;
        break;

      case SemaphoreTypes.GETTING:
        // Process all semaphore items
        state.semaphore.fetching.item.forEach((item, index) => {
          // Find created item in reading one item semaphore...
          if (item === get(action, 'id', 'notValid')) {
            // ...and remove it
            state.semaphore.fetching.item.splice(index, 1);
          }
        });
        break;

      case SemaphoreTypes.UPDATING:
        // Process all semaphore items
        state.semaphore.updating.forEach((item, index) => {
          // Find created item in updating semaphore...
          if (item === get(action, 'id', 'notValid')) {
            // ...and remove it
            state.semaphore.updating.splice(index, 1);
          }
        });
        break;
    }
  },

  ['RESET_STATE'](state) {
    Object.assign(state, moduleState);
  }

};
var connectors = {
  state: () => moduleState,
  getters: moduleGetters,
  actions: moduleActions,
  mutations: moduleMutations
};

// Create module definition for VuexORM.use()
var entry = {
  install(components, options) {
    if (typeof options.originName !== 'undefined') {
      // @ts-ignore
      components.Model.prototype.$devicesModuleOrigin = options.originName;
    } else {
      // @ts-ignore
      components.Model.prototype.$devicesModuleOrigin = ModuleOrigin.MODULE_DEVICES_ORIGIN;
    }

    options.database.register(Device, devices);
    options.database.register(DeviceProperty, deviceProperties);
    options.database.register(DeviceConfiguration, devicesConfiguration);
    options.database.register(DeviceConnector, deviceConnector);
    options.database.register(Channel, channels);
    options.database.register(ChannelProperty, channelProperties);
    options.database.register(ChannelConfiguration, channelsConfiguration);
    options.database.register(Connector, connectors);
  }

};

export default entry;
