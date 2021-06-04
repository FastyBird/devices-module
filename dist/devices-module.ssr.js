'use strict';Object.defineProperty(exports,'__esModule',{value:true});var modulesMetadata=require('@fastybird/modules-metadata'),core=require('@vuex-orm/core'),capitalize=require('lodash/capitalize'),exchangeEntitySchema=require('@fastybird/modules-metadata/resources/schemas/devices-module/entity.device.json'),Jsona=require('jsona'),Ajv=require('ajv'),uuid=require('uuid'),get=require('lodash/get'),uniq=require('lodash/uniq'),simplePropertyMappers=require('jsona/lib/simplePropertyMappers'),clone=require('lodash/clone'),exchangeEntitySchema$1=require('@fastybird/modules-metadata/resources/schemas/devices-module/entity.device.property.json'),exchangeEntitySchema$2=require('@fastybird/modules-metadata/resources/schemas/devices-module/entity.device.configuration.json'),exchangeEntitySchema$3=require('@fastybird/modules-metadata/resources/schemas/devices-module/entity.device.connector.json'),exchangeEntitySchema$4=require('@fastybird/modules-metadata/resources/schemas/devices-module/entity.channel.json'),exchangeEntitySchema$5=require('@fastybird/modules-metadata/resources/schemas/devices-module/entity.channel.property.json'),exchangeEntitySchema$6=require('@fastybird/modules-metadata/resources/schemas/devices-module/entity.channel.configuration.json'),exchangeEntitySchema$7=require('@fastybird/modules-metadata/resources/schemas/devices-module/entity.connector.json');function _interopDefaultLegacy(e){return e&&typeof e==='object'&&'default'in e?e:{'default':e}}function _interopNamespace(e){if(e&&e.__esModule)return e;var n=Object.create(null);if(e){Object.keys(e).forEach(function(k){if(k!=='default'){var d=Object.getOwnPropertyDescriptor(e,k);Object.defineProperty(n,k,d.get?d:{enumerable:true,get:function(){return e[k];}});}});}n['default']=e;return Object.freeze(n);}var capitalize__default=/*#__PURE__*/_interopDefaultLegacy(capitalize);var exchangeEntitySchema__namespace=/*#__PURE__*/_interopNamespace(exchangeEntitySchema);var Jsona__default=/*#__PURE__*/_interopDefaultLegacy(Jsona);var Ajv__default=/*#__PURE__*/_interopDefaultLegacy(Ajv);var get__default=/*#__PURE__*/_interopDefaultLegacy(get);var uniq__default=/*#__PURE__*/_interopDefaultLegacy(uniq);var clone__default=/*#__PURE__*/_interopDefaultLegacy(clone);var exchangeEntitySchema__namespace$1=/*#__PURE__*/_interopNamespace(exchangeEntitySchema$1);var exchangeEntitySchema__namespace$2=/*#__PURE__*/_interopNamespace(exchangeEntitySchema$2);var exchangeEntitySchema__namespace$3=/*#__PURE__*/_interopNamespace(exchangeEntitySchema$3);var exchangeEntitySchema__namespace$4=/*#__PURE__*/_interopNamespace(exchangeEntitySchema$4);var exchangeEntitySchema__namespace$5=/*#__PURE__*/_interopNamespace(exchangeEntitySchema$5);var exchangeEntitySchema__namespace$6=/*#__PURE__*/_interopNamespace(exchangeEntitySchema$6);var exchangeEntitySchema__namespace$7=/*#__PURE__*/_interopNamespace(exchangeEntitySchema$7);function _typeof(obj) {
  "@babel/helpers - typeof";

  if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
    _typeof = function (obj) {
      return typeof obj;
    };
  } else {
    _typeof = function (obj) {
      return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
    };
  }

  return _typeof(obj);
}

function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) {
  try {
    var info = gen[key](arg);
    var value = info.value;
  } catch (error) {
    reject(error);
    return;
  }

  if (info.done) {
    resolve(value);
  } else {
    Promise.resolve(value).then(_next, _throw);
  }
}

function _asyncToGenerator(fn) {
  return function () {
    var self = this,
        args = arguments;
    return new Promise(function (resolve, reject) {
      var gen = fn.apply(self, args);

      function _next(value) {
        asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value);
      }

      function _throw(err) {
        asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err);
      }

      _next(undefined);
    });
  };
}

function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, descriptor.key, descriptor);
  }
}

function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  return Constructor;
}

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

function _inherits(subClass, superClass) {
  if (typeof superClass !== "function" && superClass !== null) {
    throw new TypeError("Super expression must either be null or a function");
  }

  subClass.prototype = Object.create(superClass && superClass.prototype, {
    constructor: {
      value: subClass,
      writable: true,
      configurable: true
    }
  });
  if (superClass) _setPrototypeOf(subClass, superClass);
}

function _getPrototypeOf(o) {
  _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) {
    return o.__proto__ || Object.getPrototypeOf(o);
  };
  return _getPrototypeOf(o);
}

function _setPrototypeOf(o, p) {
  _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) {
    o.__proto__ = p;
    return o;
  };

  return _setPrototypeOf(o, p);
}

function _isNativeReflectConstruct() {
  if (typeof Reflect === "undefined" || !Reflect.construct) return false;
  if (Reflect.construct.sham) return false;
  if (typeof Proxy === "function") return true;

  try {
    Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {}));
    return true;
  } catch (e) {
    return false;
  }
}

function _construct(Parent, args, Class) {
  if (_isNativeReflectConstruct()) {
    _construct = Reflect.construct;
  } else {
    _construct = function _construct(Parent, args, Class) {
      var a = [null];
      a.push.apply(a, args);
      var Constructor = Function.bind.apply(Parent, a);
      var instance = new Constructor();
      if (Class) _setPrototypeOf(instance, Class.prototype);
      return instance;
    };
  }

  return _construct.apply(null, arguments);
}

function _isNativeFunction(fn) {
  return Function.toString.call(fn).indexOf("[native code]") !== -1;
}

function _wrapNativeSuper(Class) {
  var _cache = typeof Map === "function" ? new Map() : undefined;

  _wrapNativeSuper = function _wrapNativeSuper(Class) {
    if (Class === null || !_isNativeFunction(Class)) return Class;

    if (typeof Class !== "function") {
      throw new TypeError("Super expression must either be null or a function");
    }

    if (typeof _cache !== "undefined") {
      if (_cache.has(Class)) return _cache.get(Class);

      _cache.set(Class, Wrapper);
    }

    function Wrapper() {
      return _construct(Class, arguments, _getPrototypeOf(this).constructor);
    }

    Wrapper.prototype = Object.create(Class.prototype, {
      constructor: {
        value: Wrapper,
        enumerable: false,
        writable: true,
        configurable: true
      }
    });
    return _setPrototypeOf(Wrapper, Class);
  };

  return _wrapNativeSuper(Class);
}

function _assertThisInitialized(self) {
  if (self === void 0) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }

  return self;
}

function _possibleConstructorReturn(self, call) {
  if (call && (typeof call === "object" || typeof call === "function")) {
    return call;
  }

  return _assertThisInitialized(self);
}

function _createSuper(Derived) {
  var hasNativeReflectConstruct = _isNativeReflectConstruct();

  return function _createSuperInternal() {
    var Super = _getPrototypeOf(Derived),
        result;

    if (hasNativeReflectConstruct) {
      var NewTarget = _getPrototypeOf(this).constructor;

      result = Reflect.construct(Super, arguments, NewTarget);
    } else {
      result = Super.apply(this, arguments);
    }

    return _possibleConstructorReturn(this, result);
  };
}

function _toConsumableArray(arr) {
  return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread();
}

function _arrayWithoutHoles(arr) {
  if (Array.isArray(arr)) return _arrayLikeToArray(arr);
}

function _iterableToArray(iter) {
  if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter);
}

function _unsupportedIterableToArray(o, minLen) {
  if (!o) return;
  if (typeof o === "string") return _arrayLikeToArray(o, minLen);
  var n = Object.prototype.toString.call(o).slice(8, -1);
  if (n === "Object" && o.constructor) n = o.constructor.name;
  if (n === "Map" || n === "Set") return Array.from(o);
  if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen);
}

function _arrayLikeToArray(arr, len) {
  if (len == null || len > arr.length) len = arr.length;

  for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i];

  return arr2;
}

function _nonIterableSpread() {
  throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
}// ENTITY TYPES
// ============
exports.DeviceEntityTypes=void 0; // ENTITY INTERFACE
// ================

(function (DeviceEntityTypes) {
  DeviceEntityTypes["DEVICE"] = "devices-module/device";
})(exports.DeviceEntityTypes || (exports.DeviceEntityTypes = {}));// ENTITY TYPES
// ============
exports.PropertyNumberDatatypeTypes=void 0;

(function (PropertyNumberDatatypeTypes) {
  PropertyNumberDatatypeTypes[PropertyNumberDatatypeTypes["CHAR"] = modulesMetadata.DataType.CHAR] = "CHAR";
  PropertyNumberDatatypeTypes[PropertyNumberDatatypeTypes["UNSIGNED_CHAR"] = modulesMetadata.DataType.UCHAR] = "UNSIGNED_CHAR";
  PropertyNumberDatatypeTypes[PropertyNumberDatatypeTypes["SHORT"] = modulesMetadata.DataType.SHORT] = "SHORT";
  PropertyNumberDatatypeTypes[PropertyNumberDatatypeTypes["UNSIGNED_SHORT"] = modulesMetadata.DataType.USHORT] = "UNSIGNED_SHORT";
  PropertyNumberDatatypeTypes[PropertyNumberDatatypeTypes["INT"] = modulesMetadata.DataType.INT] = "INT";
  PropertyNumberDatatypeTypes[PropertyNumberDatatypeTypes["UNSIGNED_INT"] = modulesMetadata.DataType.UINT] = "UNSIGNED_INT";
  PropertyNumberDatatypeTypes[PropertyNumberDatatypeTypes["FLOAT"] = modulesMetadata.DataType.FLOAT] = "FLOAT";
})(exports.PropertyNumberDatatypeTypes || (exports.PropertyNumberDatatypeTypes = {}));

exports.PropertyIntegerDatatypeTypes=void 0;

(function (PropertyIntegerDatatypeTypes) {
  PropertyIntegerDatatypeTypes[PropertyIntegerDatatypeTypes["CHAR"] = modulesMetadata.DataType.CHAR] = "CHAR";
  PropertyIntegerDatatypeTypes[PropertyIntegerDatatypeTypes["UNSIGNED_CHAR"] = modulesMetadata.DataType.UCHAR] = "UNSIGNED_CHAR";
  PropertyIntegerDatatypeTypes[PropertyIntegerDatatypeTypes["SHORT"] = modulesMetadata.DataType.SHORT] = "SHORT";
  PropertyIntegerDatatypeTypes[PropertyIntegerDatatypeTypes["UNSIGNED_SHORT"] = modulesMetadata.DataType.USHORT] = "UNSIGNED_SHORT";
  PropertyIntegerDatatypeTypes[PropertyIntegerDatatypeTypes["INT"] = modulesMetadata.DataType.INT] = "INT";
  PropertyIntegerDatatypeTypes[PropertyIntegerDatatypeTypes["UNSIGNED_INT"] = modulesMetadata.DataType.UINT] = "UNSIGNED_INT";
})(exports.PropertyIntegerDatatypeTypes || (exports.PropertyIntegerDatatypeTypes = {}));

exports.PropertyCommandState=void 0;

(function (PropertyCommandState) {
  PropertyCommandState["SENDING"] = "sending";
  PropertyCommandState["COMPLETED"] = "completed";
})(exports.PropertyCommandState || (exports.PropertyCommandState = {}));

exports.PropertyCommandResult=void 0;

(function (PropertyCommandResult) {
  PropertyCommandResult["OK"] = "ok";
  PropertyCommandResult["ERR"] = "err";
})(exports.PropertyCommandResult || (exports.PropertyCommandResult = {}));

exports.SensorNameTypes=void 0;

(function (SensorNameTypes) {
  SensorNameTypes["SENSOR"] = "sensor";
  SensorNameTypes["AIR_QUALITY"] = "air_quality";
  SensorNameTypes["LIGHT_LEVEL"] = "light_level";
  SensorNameTypes["NOISE_LEVEL"] = "noise_level";
  SensorNameTypes["TEMPERATURE"] = "temperature";
  SensorNameTypes["HUMIDITY"] = "humidity";
})(exports.SensorNameTypes || (exports.SensorNameTypes = {}));

exports.ActorNameTypes=void 0; // ENTITY INTERFACE
// ================

(function (ActorNameTypes) {
  ActorNameTypes["ACTOR"] = "actor";
  ActorNameTypes["SWITCH"] = "switch";
})(exports.ActorNameTypes || (exports.ActorNameTypes = {}));// ENTITY MODEL
// ============
var Property = /*#__PURE__*/function (_Model) {
  _inherits(Property, _Model);

  var _super = _createSuper(Property);

  function Property() {
    var _this;

    _classCallCheck(this, Property);

    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    _this = _super.call.apply(_super, [this].concat(args));

    _defineProperty(_assertThisInitialized(_this), "id", void 0);

    _defineProperty(_assertThisInitialized(_this), "key", void 0);

    _defineProperty(_assertThisInitialized(_this), "identifier", void 0);

    _defineProperty(_assertThisInitialized(_this), "name", void 0);

    _defineProperty(_assertThisInitialized(_this), "settable", void 0);

    _defineProperty(_assertThisInitialized(_this), "queryable", void 0);

    _defineProperty(_assertThisInitialized(_this), "dataType", void 0);

    _defineProperty(_assertThisInitialized(_this), "unit", void 0);

    _defineProperty(_assertThisInitialized(_this), "format", void 0);

    _defineProperty(_assertThisInitialized(_this), "value", void 0);

    _defineProperty(_assertThisInitialized(_this), "expected", void 0);

    _defineProperty(_assertThisInitialized(_this), "pending", void 0);

    _defineProperty(_assertThisInitialized(_this), "command", void 0);

    _defineProperty(_assertThisInitialized(_this), "lastResult", void 0);

    _defineProperty(_assertThisInitialized(_this), "backup", void 0);

    _defineProperty(_assertThisInitialized(_this), "relationshipNames", void 0);

    _defineProperty(_assertThisInitialized(_this), "device", void 0);

    _defineProperty(_assertThisInitialized(_this), "deviceBackward", void 0);

    _defineProperty(_assertThisInitialized(_this), "deviceId", void 0);

    return _this;
  }

  _createClass(Property, [{
    key: "isAnalogSensor",
    get: function get() {
      return !this.isSettable && Object.values(exports.PropertyNumberDatatypeTypes).includes(this.dataType);
    }
  }, {
    key: "isBinarySensor",
    get: function get() {
      return !this.isSettable && [modulesMetadata.DataType.BOOLEAN].includes(this.dataType);
    }
  }, {
    key: "isAnalogActor",
    get: function get() {
      return this.isSettable && Object.values(exports.PropertyNumberDatatypeTypes).includes(this.dataType);
    }
  }, {
    key: "isBinaryActor",
    get: function get() {
      return this.isSettable && [modulesMetadata.DataType.BOOLEAN].includes(this.dataType);
    }
  }, {
    key: "isSwitch",
    get: function get() {
      return this.identifier === 'switch';
    }
  }, {
    key: "isInteger",
    get: function get() {
      return Object.values(exports.PropertyIntegerDatatypeTypes).includes(this.dataType);
    }
  }, {
    key: "isFloat",
    get: function get() {
      return this.dataType === modulesMetadata.DataType.FLOAT;
    }
  }, {
    key: "isNumber",
    get: function get() {
      return Object.values(exports.PropertyNumberDatatypeTypes).includes(this.dataType);
    }
  }, {
    key: "isBoolean",
    get: function get() {
      return this.dataType === modulesMetadata.DataType.BOOLEAN;
    }
  }, {
    key: "isString",
    get: function get() {
      return this.dataType === modulesMetadata.DataType.STRING;
    }
  }, {
    key: "isEnum",
    get: function get() {
      return this.dataType === modulesMetadata.DataType.ENUM;
    }
  }, {
    key: "isColor",
    get: function get() {
      return this.dataType === modulesMetadata.DataType.COLOR;
    }
  }, {
    key: "isSettable",
    get: function get() {
      return this.settable;
    }
  }, {
    key: "isQueryable",
    get: function get() {
      return this.queryable;
    }
  }, {
    key: "binaryValue",
    get: function get() {
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
  }, {
    key: "binaryExpected",
    get: function get() {
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
  }, {
    key: "analogValue",
    get: function get() {
      var storeInstance = Property.store();

      if (this.device !== null && this.device.hardwareManufacturer === modulesMetadata.HardwareManufacturer.ITEAD && Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')) {
        switch (this.identifier) {
          case 'air_quality':
            if (this.value > 7) {
              // @ts-ignore
              return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".values.unhealthy")).toString();
            } else if (this.value > 4) {
              // @ts-ignore
              return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".values.moderate")).toString();
            } // @ts-ignore


            return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".values.good")).toString();

          case 'light_level':
            if (this.value > 8) {
              // @ts-ignore
              return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".values.dusky")).toString();
            } else if (this.value > 4) {
              // @ts-ignore
              return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".values.normal")).toString();
            } // @ts-ignore


            return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".values.bright")).toString();

          case 'noise_level':
            if (this.value > 6) {
              // @ts-ignore
              return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".values.noisy")).toString();
            } else if (this.value > 3) {
              // @ts-ignore
              return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".values.normal")).toString();
            } // @ts-ignore


            return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".values.quiet")).toString();
        }
      }

      return this.formattedValue;
    }
  }, {
    key: "analogExpected",
    get: function get() {
      if (this.expected === null) {
        return null;
      }

      var storeInstance = Property.store();

      if (this.device !== null && this.device.hardwareManufacturer === modulesMetadata.HardwareManufacturer.ITEAD && Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')) {
        switch (this.identifier) {
          case 'air_quality':
            if (this.expected > 7) {
              // @ts-ignore
              return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".values.unhealthy")).toString();
            } else if (this.expected > 4) {
              // @ts-ignore
              return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".values.moderate")).toString();
            } // @ts-ignore


            return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".values.good")).toString();

          case 'light_level':
            if (this.expected > 8) {
              // @ts-ignore
              return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".values.dusky")).toString();
            } else if (this.expected > 4) {
              // @ts-ignore
              return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".values.normal")).toString();
            } // @ts-ignore


            return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".values.bright")).toString();

          case 'noise_level':
            if (this.expected > 6) {
              // @ts-ignore
              return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".values.noisy")).toString();
            } else if (this.expected > 3) {
              // @ts-ignore
              return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".values.normal")).toString();
            } // @ts-ignore


            return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".values.quiet")).toString();
        }
      }

      return this.formattedValue;
    }
  }, {
    key: "formattedValue",
    get: function get() {
      var number = parseFloat(this.value);
      var decimals = 2;
      var decPoint = ',';
      var thousandsSeparator = ' ';
      var cleanedNumber = "".concat(number).replace(/[^0-9+\-Ee.]/g, '');
      var n = !isFinite(+cleanedNumber) ? 0 : +cleanedNumber;
      var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
      var sep = thousandsSeparator;
      var dec = decPoint;

      var toFixedFix = function toFixedFix(fN, fPrec) {
        var k = Math.pow(10, fPrec);
        return "".concat(Math.round(fN * k) / k);
      }; // Fix for IE parseFloat(0.55).toFixed(0) = 0


      var s = (prec ? toFixedFix(n, prec) : "".concat(Math.round(n))).split('.');

      if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
      }

      if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
      }

      return s.join(dec);
    }
  }, {
    key: "icon",
    get: function get() {
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
  }], [{
    key: "fields",
    value: function fields() {
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
        device: this.belongsTo(Device, 'id'),
        deviceBackward: this.hasOne(Device, 'id', 'deviceId'),
        deviceId: this.string(''),
        // Relations
        relationshipNames: this.attr([])
      };
    }
  }]);

  return Property;
}(core.Model);// ENTITY TYPES
// ============
exports.DevicePropertyEntityTypes=void 0; // ENTITY INTERFACE
// ================

(function (DevicePropertyEntityTypes) {
  DevicePropertyEntityTypes["PROPERTY"] = "devices-module/device-property";
})(exports.DevicePropertyEntityTypes || (exports.DevicePropertyEntityTypes = {}));// ============

var DeviceProperty = /*#__PURE__*/function (_Property) {
  _inherits(DeviceProperty, _Property);

  var _super = _createSuper(DeviceProperty);

  function DeviceProperty() {
    var _this;

    _classCallCheck(this, DeviceProperty);

    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    _this = _super.call.apply(_super, [this].concat(args));

    _defineProperty(_assertThisInitialized(_this), "type", void 0);

    return _this;
  }

  _createClass(DeviceProperty, [{
    key: "title",
    get: function get() {
      if (this.name !== null) {
        return this.name;
      }

      var storeInstance = DeviceProperty.store();

      if (this.device !== null && !this.device.isCustomModel && Object.prototype.hasOwnProperty.call(storeInstance, '$i18n') && // @ts-ignore
      storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".title")).toString().includes('devices.vendors.')) {
        // @ts-ignore
        return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".title")).toString();
      }

      return capitalize__default['default'](this.identifier);
    }
  }], [{
    key: "entity",
    get: function get() {
      return 'device_property';
    }
  }, {
    key: "fields",
    value: function fields() {
      return Object.assign(Property.fields(), {
        type: this.string(exports.DevicePropertyEntityTypes.PROPERTY)
      });
    }
  }, {
    key: "get",
    value: function () {
      var _get = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(device, id) {
        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                _context.next = 2;
                return DeviceProperty.dispatch('get', {
                  device: device,
                  id: id
                });

              case 2:
                return _context.abrupt("return", _context.sent);

              case 3:
              case "end":
                return _context.stop();
            }
          }
        }, _callee);
      }));

      function get(_x, _x2) {
        return _get.apply(this, arguments);
      }

      return get;
    }()
  }, {
    key: "fetch",
    value: function () {
      var _fetch = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2(device) {
        return regeneratorRuntime.wrap(function _callee2$(_context2) {
          while (1) {
            switch (_context2.prev = _context2.next) {
              case 0:
                _context2.next = 2;
                return DeviceProperty.dispatch('fetch', {
                  device: device
                });

              case 2:
                return _context2.abrupt("return", _context2.sent);

              case 3:
              case "end":
                return _context2.stop();
            }
          }
        }, _callee2);
      }));

      function fetch(_x3) {
        return _fetch.apply(this, arguments);
      }

      return fetch;
    }()
  }, {
    key: "edit",
    value: function () {
      var _edit = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3(property, data) {
        return regeneratorRuntime.wrap(function _callee3$(_context3) {
          while (1) {
            switch (_context3.prev = _context3.next) {
              case 0:
                _context3.next = 2;
                return DeviceProperty.dispatch('edit', {
                  property: property,
                  data: data
                });

              case 2:
                return _context3.abrupt("return", _context3.sent);

              case 3:
              case "end":
                return _context3.stop();
            }
          }
        }, _callee3);
      }));

      function edit(_x4, _x5) {
        return _edit.apply(this, arguments);
      }

      return edit;
    }()
  }, {
    key: "transmitData",
    value: function transmitData(property, value) {
      return DeviceProperty.dispatch('transmitData', {
        property: property,
        value: value
      });
    }
  }, {
    key: "reset",
    value: function reset() {
      DeviceProperty.dispatch('reset');
    }
  }]);

  return DeviceProperty;
}(Property);// ENTITY TYPES
// ============
exports.DeviceConfigurationEntityTypes=void 0; // ENTITY INTERFACE
// ================

(function (DeviceConfigurationEntityTypes) {
  DeviceConfigurationEntityTypes["CONFIGURATION"] = "devices-module/device-configuration";
})(exports.DeviceConfigurationEntityTypes || (exports.DeviceConfigurationEntityTypes = {}));// ============

exports.ConfigurationNumberDatatypeTypes=void 0;

(function (ConfigurationNumberDatatypeTypes) {
  ConfigurationNumberDatatypeTypes[ConfigurationNumberDatatypeTypes["CHAR"] = modulesMetadata.DataType.CHAR] = "CHAR";
  ConfigurationNumberDatatypeTypes[ConfigurationNumberDatatypeTypes["UNSIGNED_CHAR"] = modulesMetadata.DataType.UCHAR] = "UNSIGNED_CHAR";
  ConfigurationNumberDatatypeTypes[ConfigurationNumberDatatypeTypes["SHORT"] = modulesMetadata.DataType.SHORT] = "SHORT";
  ConfigurationNumberDatatypeTypes[ConfigurationNumberDatatypeTypes["UNSIGNED_SHORT"] = modulesMetadata.DataType.USHORT] = "UNSIGNED_SHORT";
  ConfigurationNumberDatatypeTypes[ConfigurationNumberDatatypeTypes["INT"] = modulesMetadata.DataType.INT] = "INT";
  ConfigurationNumberDatatypeTypes[ConfigurationNumberDatatypeTypes["UNSIGNED_INT"] = modulesMetadata.DataType.UINT] = "UNSIGNED_INT";
  ConfigurationNumberDatatypeTypes[ConfigurationNumberDatatypeTypes["FLOAT"] = modulesMetadata.DataType.FLOAT] = "FLOAT";
})(exports.ConfigurationNumberDatatypeTypes || (exports.ConfigurationNumberDatatypeTypes = {}));

exports.ConfigurationIntegerDatatypeTypes=void 0; // ENTITY INTERFACE
// ================

(function (ConfigurationIntegerDatatypeTypes) {
  ConfigurationIntegerDatatypeTypes[ConfigurationIntegerDatatypeTypes["CHAR"] = modulesMetadata.DataType.CHAR] = "CHAR";
  ConfigurationIntegerDatatypeTypes[ConfigurationIntegerDatatypeTypes["UNSIGNED_CHAR"] = modulesMetadata.DataType.UCHAR] = "UNSIGNED_CHAR";
  ConfigurationIntegerDatatypeTypes[ConfigurationIntegerDatatypeTypes["SHORT"] = modulesMetadata.DataType.SHORT] = "SHORT";
  ConfigurationIntegerDatatypeTypes[ConfigurationIntegerDatatypeTypes["UNSIGNED_SHORT"] = modulesMetadata.DataType.USHORT] = "UNSIGNED_SHORT";
  ConfigurationIntegerDatatypeTypes[ConfigurationIntegerDatatypeTypes["INT"] = modulesMetadata.DataType.INT] = "INT";
  ConfigurationIntegerDatatypeTypes[ConfigurationIntegerDatatypeTypes["UNSIGNED_INT"] = modulesMetadata.DataType.UINT] = "UNSIGNED_INT";
})(exports.ConfigurationIntegerDatatypeTypes || (exports.ConfigurationIntegerDatatypeTypes = {}));// ============

var Configuration = /*#__PURE__*/function (_Model) {
  _inherits(Configuration, _Model);

  var _super = _createSuper(Configuration);

  function Configuration() {
    var _this;

    _classCallCheck(this, Configuration);

    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    _this = _super.call.apply(_super, [this].concat(args));

    _defineProperty(_assertThisInitialized(_this), "id", void 0);

    _defineProperty(_assertThisInitialized(_this), "key", void 0);

    _defineProperty(_assertThisInitialized(_this), "identifier", void 0);

    _defineProperty(_assertThisInitialized(_this), "name", void 0);

    _defineProperty(_assertThisInitialized(_this), "comment", void 0);

    _defineProperty(_assertThisInitialized(_this), "value", void 0);

    _defineProperty(_assertThisInitialized(_this), "default", void 0);

    _defineProperty(_assertThisInitialized(_this), "dataType", void 0);

    _defineProperty(_assertThisInitialized(_this), "min", void 0);

    _defineProperty(_assertThisInitialized(_this), "max", void 0);

    _defineProperty(_assertThisInitialized(_this), "step", void 0);

    _defineProperty(_assertThisInitialized(_this), "values", void 0);

    _defineProperty(_assertThisInitialized(_this), "relationshipNames", void 0);

    return _this;
  }

  _createClass(Configuration, [{
    key: "isInteger",
    get: function get() {
      return Object.values(exports.ConfigurationIntegerDatatypeTypes).includes(this.dataType);
    }
  }, {
    key: "isFloat",
    get: function get() {
      return this.dataType === modulesMetadata.DataType.FLOAT;
    }
  }, {
    key: "isNumber",
    get: function get() {
      return Object.values(exports.ConfigurationNumberDatatypeTypes).includes(this.dataType);
    }
  }, {
    key: "isBoolean",
    get: function get() {
      return this.dataType === modulesMetadata.DataType.BOOLEAN;
    }
  }, {
    key: "isString",
    get: function get() {
      return this.dataType === modulesMetadata.DataType.STRING;
    }
  }, {
    key: "isSelect",
    get: function get() {
      return this.dataType === modulesMetadata.DataType.ENUM;
    }
  }], [{
    key: "fields",
    value: function fields() {
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
  }]);

  return Configuration;
}(core.Model);// ENTITY MODEL
// ============
var DeviceConfiguration = /*#__PURE__*/function (_Configuration) {
  _inherits(DeviceConfiguration, _Configuration);

  var _super = _createSuper(DeviceConfiguration);

  function DeviceConfiguration() {
    var _this;

    _classCallCheck(this, DeviceConfiguration);

    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    _this = _super.call.apply(_super, [this].concat(args));

    _defineProperty(_assertThisInitialized(_this), "type", void 0);

    _defineProperty(_assertThisInitialized(_this), "device", void 0);

    _defineProperty(_assertThisInitialized(_this), "deviceBackward", void 0);

    _defineProperty(_assertThisInitialized(_this), "deviceId", void 0);

    return _this;
  }

  _createClass(DeviceConfiguration, [{
    key: "title",
    get: function get() {
      if (this.name !== null) {
        return this.name;
      }

      var storeInstance = DeviceConfiguration.store();

      if (this.device !== null && !this.device.isCustomModel && Object.prototype.hasOwnProperty.call(storeInstance, '$i18n') && // @ts-ignore
      !storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".identifier.").concat(this.identifier, ".title")).toString().includes('devices.vendors.')) {
        // @ts-ignore
        return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".configuration.").concat(this.identifier, ".title")).toString();
      }

      return capitalize__default['default'](this.identifier);
    }
  }, {
    key: "description",
    get: function get() {
      if (this.comment !== null) {
        return this.comment;
      }

      var storeInstance = DeviceConfiguration.store();

      if (this.device !== null && !this.device.isCustomModel && Object.prototype.hasOwnProperty.call(storeInstance, '$i18n') && // @ts-ignore
      !storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".configuration.").concat(this.identifier, ".description")).toString().includes('devices.vendors.')) {
        // @ts-ignore
        return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".configuration.").concat(this.identifier, ".description")).toString();
      }

      return null;
    }
  }, {
    key: "selectValues",
    get: function get() {
      var _this2 = this;

      if (!this.isSelect) {
        throw new Error("This field is not allowed for entity type ".concat(this.type));
      }

      var storeInstance = DeviceConfiguration.store();

      if (this.device !== null && !this.device.isCustomModel && Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')) {
        var items = [];
        this.values.forEach(function (item) {
          var _this2$device;

          items.push({
            value: item.value,
            // @ts-ignore
            name: storeInstance.$i18n.t("devices.vendors.".concat((_this2$device = _this2.device) === null || _this2$device === void 0 ? void 0 : _this2$device.hardwareManufacturer, ".configuration.").concat(_this2.identifier, ".values.").concat(item.name)).toString()
          });
        });
        return items;
      }

      return this.values;
    }
  }, {
    key: "formattedValue",
    get: function get() {
      var _this3 = this;

      if (this.isSelect) {
        var storeInstance = DeviceConfiguration.store();

        if (this.device !== null && !this.device.isCustomModel && Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')) {
          this.values.forEach(function (item) {
            // eslint-disable-next-line eqeqeq
            if (item.value == _this3.value) {
              var _this3$device;

              // @ts-ignore
              if (!storeInstance.$i18n.t("devices.vendors.".concat((_this3$device = _this3.device) === null || _this3$device === void 0 ? void 0 : _this3$device.hardwareManufacturer, ".configuration.").concat(_this3.identifier, ".values.").concat(item.name)).toString().includes('devices.vendors.')) {
                var _this3$device2;

                // @ts-ignore
                return storeInstance.$i18n.t("devices.vendors.".concat((_this3$device2 = _this3.device) === null || _this3$device2 === void 0 ? void 0 : _this3$device2.hardwareManufacturer, ".configuration.").concat(_this3.identifier, ".values.").concat(item.name));
              } else {
                return _this3.value;
              }
            }
          });
        }
      }

      return this.value;
    }
  }], [{
    key: "entity",
    get: function get() {
      return 'device_configuration';
    }
  }, {
    key: "fields",
    value: function fields() {
      return Object.assign(Configuration.fields(), {
        type: this.string(exports.DeviceConfigurationEntityTypes.CONFIGURATION),
        device: this.belongsTo(Device, 'id'),
        deviceBackward: this.hasOne(Device, 'id', 'deviceId'),
        deviceId: this.string('')
      });
    }
  }, {
    key: "get",
    value: function () {
      var _get = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(device, id) {
        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                _context.next = 2;
                return DeviceConfiguration.dispatch('get', {
                  device: device,
                  id: id
                });

              case 2:
                return _context.abrupt("return", _context.sent);

              case 3:
              case "end":
                return _context.stop();
            }
          }
        }, _callee);
      }));

      function get(_x, _x2) {
        return _get.apply(this, arguments);
      }

      return get;
    }()
  }, {
    key: "fetch",
    value: function () {
      var _fetch = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2(device) {
        return regeneratorRuntime.wrap(function _callee2$(_context2) {
          while (1) {
            switch (_context2.prev = _context2.next) {
              case 0:
                _context2.next = 2;
                return DeviceConfiguration.dispatch('fetch', {
                  device: device
                });

              case 2:
                return _context2.abrupt("return", _context2.sent);

              case 3:
              case "end":
                return _context2.stop();
            }
          }
        }, _callee2);
      }));

      function fetch(_x3) {
        return _fetch.apply(this, arguments);
      }

      return fetch;
    }()
  }, {
    key: "edit",
    value: function () {
      var _edit = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3(property, data) {
        return regeneratorRuntime.wrap(function _callee3$(_context3) {
          while (1) {
            switch (_context3.prev = _context3.next) {
              case 0:
                _context3.next = 2;
                return DeviceConfiguration.dispatch('edit', {
                  property: property,
                  data: data
                });

              case 2:
                return _context3.abrupt("return", _context3.sent);

              case 3:
              case "end":
                return _context3.stop();
            }
          }
        }, _callee3);
      }));

      function edit(_x4, _x5) {
        return _edit.apply(this, arguments);
      }

      return edit;
    }()
  }, {
    key: "transmitData",
    value: function transmitData(property, value) {
      return DeviceConfiguration.dispatch('transmitData', {
        property: property,
        value: value
      });
    }
  }, {
    key: "reset",
    value: function reset() {
      DeviceConfiguration.dispatch('reset');
    }
  }]);

  return DeviceConfiguration;
}(Configuration);// ENTITY TYPES
// ============
exports.ChannelEntityTypes=void 0; // ENTITY INTERFACE
// ================

(function (ChannelEntityTypes) {
  ChannelEntityTypes["CHANNEL"] = "devices-module/channel";
})(exports.ChannelEntityTypes || (exports.ChannelEntityTypes = {}));// ENTITY TYPES
// ============
exports.ChannelPropertyEntityTypes=void 0; // ENTITY INTERFACE
// ================

(function (ChannelPropertyEntityTypes) {
  ChannelPropertyEntityTypes["PROPERTY"] = "devices-module/channel-property";
})(exports.ChannelPropertyEntityTypes || (exports.ChannelPropertyEntityTypes = {}));// ENTITY MODEL
// ============
var ChannelProperty = /*#__PURE__*/function (_Property) {
  _inherits(ChannelProperty, _Property);

  var _super = _createSuper(ChannelProperty);

  function ChannelProperty() {
    var _this;

    _classCallCheck(this, ChannelProperty);

    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    _this = _super.call.apply(_super, [this].concat(args));

    _defineProperty(_assertThisInitialized(_this), "type", void 0);

    _defineProperty(_assertThisInitialized(_this), "channel", void 0);

    _defineProperty(_assertThisInitialized(_this), "channelBackward", void 0);

    _defineProperty(_assertThisInitialized(_this), "channelId", void 0);

    return _this;
  }

  _createClass(ChannelProperty, [{
    key: "title",
    get: function get() {
      if (this.name !== null) {
        return this.name;
      }

      var storeInstance = ChannelProperty.store();

      if (this.device !== null && !this.device.isCustomModel && Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')) {
        if (this.identifier.includes('_')) {
          var propertyPart = this.identifier.substring(0, this.identifier.indexOf('_'));
          var propertyNum = parseInt(this.identifier.substring(this.identifier.indexOf('_') + 1), 10); // @ts-ignore

          if (!storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".devices.").concat(this.device.hardwareModel, ".properties.").concat(propertyPart, ".title")).toString().includes('devices.vendors.')) {
            // @ts-ignore
            return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".devices.").concat(this.device.hardwareModel, ".properties.").concat(propertyPart, ".title"), {
              number: propertyNum
            }).toString();
          } // @ts-ignore


          if (!storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(propertyPart, ".title")).toString().includes('devices.vendors.')) {
            // @ts-ignore
            return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(propertyPart, ".title"), {
              number: propertyNum
            }).toString();
          }
        } // @ts-ignore


        if (!storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".devices.").concat(this.device.hardwareModel, ".properties.").concat(this.identifier, ".title")).toString().includes('devices.vendors.')) {
          // @ts-ignore
          return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".devices.").concat(this.device.hardwareModel, ".properties.").concat(this.identifier, ".title")).toString();
        } // @ts-ignore


        if (!storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".title")).toString().includes('devices.vendors.')) {
          // @ts-ignore
          return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".properties.").concat(this.identifier, ".title")).toString();
        }
      }

      return capitalize__default['default'](this.identifier);
    } // @ts-ignore

  }, {
    key: "device",
    get: function get() {
      if (this.channel === null) {
        var channel = Channel.query().where('id', this.channelId).first();

        if (channel !== null) {
          return Device.query().where('id', channel.deviceId).first();
        }

        return null;
      }

      return Device.query().where('id', this.channel.deviceId).first();
    }
  }], [{
    key: "entity",
    get: function get() {
      return 'channel_property';
    }
  }, {
    key: "fields",
    value: function fields() {
      return Object.assign(Property.fields(), {
        type: this.string(exports.ChannelPropertyEntityTypes.PROPERTY),
        channel: this.belongsTo(Channel, 'id'),
        channelBackward: this.hasOne(Channel, 'id', 'channelId'),
        channelId: this.string('')
      });
    }
  }, {
    key: "get",
    value: function () {
      var _get = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(channel, id) {
        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                _context.next = 2;
                return ChannelProperty.dispatch('get', {
                  channel: channel,
                  id: id
                });

              case 2:
                return _context.abrupt("return", _context.sent);

              case 3:
              case "end":
                return _context.stop();
            }
          }
        }, _callee);
      }));

      function get(_x, _x2) {
        return _get.apply(this, arguments);
      }

      return get;
    }()
  }, {
    key: "fetch",
    value: function () {
      var _fetch = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2(channel) {
        return regeneratorRuntime.wrap(function _callee2$(_context2) {
          while (1) {
            switch (_context2.prev = _context2.next) {
              case 0:
                _context2.next = 2;
                return ChannelProperty.dispatch('fetch', {
                  channel: channel
                });

              case 2:
                return _context2.abrupt("return", _context2.sent);

              case 3:
              case "end":
                return _context2.stop();
            }
          }
        }, _callee2);
      }));

      function fetch(_x3) {
        return _fetch.apply(this, arguments);
      }

      return fetch;
    }()
  }, {
    key: "edit",
    value: function () {
      var _edit = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3(property, data) {
        return regeneratorRuntime.wrap(function _callee3$(_context3) {
          while (1) {
            switch (_context3.prev = _context3.next) {
              case 0:
                _context3.next = 2;
                return ChannelProperty.dispatch('edit', {
                  property: property,
                  data: data
                });

              case 2:
                return _context3.abrupt("return", _context3.sent);

              case 3:
              case "end":
                return _context3.stop();
            }
          }
        }, _callee3);
      }));

      function edit(_x4, _x5) {
        return _edit.apply(this, arguments);
      }

      return edit;
    }()
  }, {
    key: "transmitData",
    value: function transmitData(property, value) {
      return ChannelProperty.dispatch('transmitData', {
        property: property,
        value: value
      });
    }
  }, {
    key: "reset",
    value: function reset() {
      ChannelProperty.dispatch('reset');
    }
  }]);

  return ChannelProperty;
}(Property);// ENTITY MODEL
// ============
var ChannelConfiguration = /*#__PURE__*/function (_Configuration) {
  _inherits(ChannelConfiguration, _Configuration);

  var _super = _createSuper(ChannelConfiguration);

  function ChannelConfiguration() {
    var _this;

    _classCallCheck(this, ChannelConfiguration);

    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    _this = _super.call.apply(_super, [this].concat(args));

    _defineProperty(_assertThisInitialized(_this), "type", void 0);

    _defineProperty(_assertThisInitialized(_this), "channel", void 0);

    _defineProperty(_assertThisInitialized(_this), "channelBackward", void 0);

    _defineProperty(_assertThisInitialized(_this), "channelId", void 0);

    return _this;
  }

  _createClass(ChannelConfiguration, [{
    key: "title",
    get: function get() {
      if (this.name !== null) {
        return this.name;
      }

      var storeInstance = ChannelConfiguration.store();

      if (this.device !== null && !this.device.isCustomModel && Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')) {
        if (this.identifier.includes('_')) {
          var configurationPart = this.identifier.substring(0, this.identifier.indexOf('_')).toLowerCase();
          var configurationNum = parseInt(this.identifier.substring(this.identifier.indexOf('_') + 1), 10); // @ts-ignore

          if (!storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".devices.").concat(this.device.hardwareModel, ".configuration.").concat(configurationPart, ".title")).toString().includes('devices.vendors.')) {
            // @ts-ignore
            return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".devices.").concat(this.device.hardwareModel, ".configuration.").concat(configurationPart, ".title"), {
              number: configurationNum
            }).toString();
          } // @ts-ignore


          if (!storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".configuration.").concat(configurationPart, ".title")).toString().includes('devices.vendors.')) {
            // @ts-ignore
            return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".configuration.").concat(configurationPart, ".title"), {
              number: configurationNum
            }).toString();
          }
        } // @ts-ignore


        if (!storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".devices.").concat(this.device.hardwareModel, ".configuration.").concat(this.identifier, ".title")).toString().includes('devices.vendors.')) {
          // @ts-ignore
          return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".devices.").concat(this.device.hardwareModel, ".configuration.").concat(this.identifier, ".title")).toString();
        } // @ts-ignore


        if (!storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".configuration.").concat(this.identifier, ".title")).toString().includes('devices.vendors.')) {
          // @ts-ignore
          return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".configuration.").concat(this.identifier, ".title")).toString();
        }
      }

      return capitalize__default['default'](this.identifier);
    }
  }, {
    key: "description",
    get: function get() {
      if (this.comment !== null) {
        return this.comment;
      }

      var storeInstance = ChannelConfiguration.store();

      if (this.device !== null && !this.device.isCustomModel && Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')) {
        // @ts-ignore
        if (!storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".devices.").concat(this.device.hardwareModel, ".configuration.").concat(this.identifier, ".description")).toString().includes('devices.vendors.')) {
          // @ts-ignore
          return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".devices.").concat(this.device.hardwareModel, ".configuration.").concat(this.identifier, ".description")).toString();
        } // @ts-ignore


        if (!storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".configuration.").concat(this.identifier, ".description")).toString().includes('devices.vendors.')) {
          // @ts-ignore
          return storeInstance.$i18n.t("devices.vendors.".concat(this.device.hardwareManufacturer, ".configuration.").concat(this.identifier, ".description")).toString();
        }
      }

      return null;
    }
  }, {
    key: "selectValues",
    get: function get() {
      var _this2 = this;

      if (!this.isSelect) {
        throw new Error("This field is not allowed for entity type ".concat(this.type));
      }

      var storeInstance = ChannelConfiguration.store();

      if (this.device !== null && !this.device.isCustomModel && Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')) {
        var items = [];
        this.values.forEach(function (item) {
          var _this2$device, _this2$device2, _this2$device5;

          var valueName = item.name; // @ts-ignore

          if (!storeInstance.$i18n.t("devices.vendors.".concat((_this2$device = _this2.device) === null || _this2$device === void 0 ? void 0 : _this2$device.hardwareManufacturer, ".devices.").concat((_this2$device2 = _this2.device) === null || _this2$device2 === void 0 ? void 0 : _this2$device2.hardwareModel, ".configuration.").concat(_this2.identifier, ".values.").concat(item.name)).toString().includes('devices.vendors.')) {
            var _this2$device3, _this2$device4;

            // @ts-ignore
            valueName = storeInstance.$i18n.t("devices.vendors.".concat((_this2$device3 = _this2.device) === null || _this2$device3 === void 0 ? void 0 : _this2$device3.hardwareManufacturer, ".devices.").concat((_this2$device4 = _this2.device) === null || _this2$device4 === void 0 ? void 0 : _this2$device4.hardwareModel, ".configuration.").concat(_this2.identifier, ".values.").concat(item.name)).toString(); // @ts-ignore
          } else if (!storeInstance.$i18n.t("devices.vendors.".concat((_this2$device5 = _this2.device) === null || _this2$device5 === void 0 ? void 0 : _this2$device5.hardwareManufacturer, ".configuration.").concat(_this2.identifier, ".values.").concat(item.name)).toString().includes('devices.vendors.')) {
            var _this2$device6;

            // @ts-ignore
            valueName = storeInstance.$i18n.t("devices.vendors.".concat((_this2$device6 = _this2.device) === null || _this2$device6 === void 0 ? void 0 : _this2$device6.hardwareManufacturer, ".configuration.").concat(_this2.identifier, ".values.").concat(item.name)).toString();
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
  }, {
    key: "formattedValue",
    get: function get() {
      var _this3 = this;

      if (this.isSelect) {
        var storeInstance = ChannelConfiguration.store();

        if (this.device !== null && !this.device.isCustomModel && Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')) {
          this.values.forEach(function (item) {
            // eslint-disable-next-line eqeqeq
            if (item.value == _this3.value) {
              var _this3$device, _this3$device2, _this3$device5;

              // @ts-ignore
              if (!storeInstance.$i18n.t("devices.vendors.".concat((_this3$device = _this3.device) === null || _this3$device === void 0 ? void 0 : _this3$device.hardwareManufacturer, ".devices.").concat((_this3$device2 = _this3.device) === null || _this3$device2 === void 0 ? void 0 : _this3$device2.hardwareModel, ".configuration.").concat(_this3.identifier, ".values.").concat(item.name)).toString().includes('devices.vendors.')) {
                var _this3$device3, _this3$device4;

                // @ts-ignore
                return storeInstance.$i18n.t("devices.vendors.".concat((_this3$device3 = _this3.device) === null || _this3$device3 === void 0 ? void 0 : _this3$device3.hardwareManufacturer, ".devices.").concat((_this3$device4 = _this3.device) === null || _this3$device4 === void 0 ? void 0 : _this3$device4.hardwareModel, ".configuration.").concat(_this3.identifier, ".values.").concat(item.name)).toString(); // @ts-ignore
              } else if (!storeInstance.$i18n.t("devices.vendors.".concat((_this3$device5 = _this3.device) === null || _this3$device5 === void 0 ? void 0 : _this3$device5.hardwareManufacturer, ".configuration.").concat(_this3.identifier, ".values.").concat(item.name)).toString().includes('devices.vendors.')) {
                var _this3$device6;

                // @ts-ignore
                return storeInstance.$i18n.t("devices.vendors.".concat((_this3$device6 = _this3.device) === null || _this3$device6 === void 0 ? void 0 : _this3$device6.hardwareManufacturer, ".configuration.").concat(_this3.identifier, ".values.").concat(item.name)).toString();
              } else {
                return _this3.value;
              }
            }
          });
        }
      }

      return this.value;
    }
  }, {
    key: "device",
    get: function get() {
      if (this.channel === null) {
        var channel = Channel.query().where('id', this.channelId).first();

        if (channel !== null) {
          return Device.query().where('id', channel.deviceId).first();
        }

        return null;
      }

      return Device.query().where('id', this.channel.deviceId).first();
    }
  }], [{
    key: "entity",
    get: function get() {
      return 'channel_configuration';
    }
  }, {
    key: "fields",
    value: function fields() {
      return Object.assign(Configuration.fields(), {
        type: this.string(''),
        channel: this.belongsTo(Channel, 'id'),
        channelBackward: this.hasOne(Channel, 'id', 'channelId'),
        channelId: this.string('')
      });
    }
  }, {
    key: "get",
    value: function () {
      var _get = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(channel, id) {
        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                _context.next = 2;
                return ChannelConfiguration.dispatch('get', {
                  channel: channel,
                  id: id
                });

              case 2:
                return _context.abrupt("return", _context.sent);

              case 3:
              case "end":
                return _context.stop();
            }
          }
        }, _callee);
      }));

      function get(_x, _x2) {
        return _get.apply(this, arguments);
      }

      return get;
    }()
  }, {
    key: "fetch",
    value: function () {
      var _fetch = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2(channel) {
        return regeneratorRuntime.wrap(function _callee2$(_context2) {
          while (1) {
            switch (_context2.prev = _context2.next) {
              case 0:
                _context2.next = 2;
                return ChannelConfiguration.dispatch('fetch', {
                  channel: channel
                });

              case 2:
                return _context2.abrupt("return", _context2.sent);

              case 3:
              case "end":
                return _context2.stop();
            }
          }
        }, _callee2);
      }));

      function fetch(_x3) {
        return _fetch.apply(this, arguments);
      }

      return fetch;
    }()
  }, {
    key: "edit",
    value: function () {
      var _edit = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3(property, data) {
        return regeneratorRuntime.wrap(function _callee3$(_context3) {
          while (1) {
            switch (_context3.prev = _context3.next) {
              case 0:
                _context3.next = 2;
                return ChannelConfiguration.dispatch('edit', {
                  property: property,
                  data: data
                });

              case 2:
                return _context3.abrupt("return", _context3.sent);

              case 3:
              case "end":
                return _context3.stop();
            }
          }
        }, _callee3);
      }));

      function edit(_x4, _x5) {
        return _edit.apply(this, arguments);
      }

      return edit;
    }()
  }, {
    key: "transmitData",
    value: function transmitData(property, value) {
      return ChannelConfiguration.dispatch('transmitData', {
        property: property,
        value: value
      });
    }
  }, {
    key: "reset",
    value: function reset() {
      ChannelConfiguration.dispatch('reset');
    }
  }]);

  return ChannelConfiguration;
}(Configuration);// ENTITY MODEL
// ============
var Channel = /*#__PURE__*/function (_Model) {
  _inherits(Channel, _Model);

  var _super = _createSuper(Channel);

  function Channel() {
    var _this;

    _classCallCheck(this, Channel);

    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    _this = _super.call.apply(_super, [this].concat(args));

    _defineProperty(_assertThisInitialized(_this), "id", void 0);

    _defineProperty(_assertThisInitialized(_this), "type", void 0);

    _defineProperty(_assertThisInitialized(_this), "key", void 0);

    _defineProperty(_assertThisInitialized(_this), "identifier", void 0);

    _defineProperty(_assertThisInitialized(_this), "name", void 0);

    _defineProperty(_assertThisInitialized(_this), "comment", void 0);

    _defineProperty(_assertThisInitialized(_this), "control", void 0);

    _defineProperty(_assertThisInitialized(_this), "relationshipNames", void 0);

    _defineProperty(_assertThisInitialized(_this), "properties", void 0);

    _defineProperty(_assertThisInitialized(_this), "configuration", void 0);

    _defineProperty(_assertThisInitialized(_this), "device", void 0);

    _defineProperty(_assertThisInitialized(_this), "deviceBackward", void 0);

    _defineProperty(_assertThisInitialized(_this), "deviceId", void 0);

    return _this;
  }

  _createClass(Channel, [{
    key: "title",
    get: function get() {
      if (this.name !== null) {
        return this.name;
      }

      var device = Device.query().where('id', this.deviceId).first();
      var storeInstance = Channel.store();

      if (device !== null && !device.isCustomModel && Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')) {
        if (this.identifier.includes('_')) {
          var channelPart = this.identifier.substring(0, this.identifier.indexOf('_'));
          var channelNum = parseInt(this.identifier.substring(this.identifier.indexOf('_') + 1), 10); // @ts-ignore

          if (!storeInstance.$i18n.t("devices.vendors.".concat(device.hardwareManufacturer, ".devices.").concat(device.hardwareModel, ".channels.").concat(channelPart, ".title")).toString().includes('devices.vendors.')) {
            // @ts-ignore
            return storeInstance.$i18n.t("devices.vendors.".concat(device.hardwareManufacturer, ".devices.").concat(device.hardwareModel, ".channels.").concat(channelPart, ".title"), {
              number: channelNum + 1
            }).toString();
          } // @ts-ignore


          if (!storeInstance.$i18n.t("devices.vendors.".concat(device.hardwareManufacturer, ".channels.").concat(channelPart, ".title")).toString().includes('devices.vendors.')) {
            // @ts-ignore
            return storeInstance.$i18n.t("devices.vendors.".concat(device.hardwareManufacturer, ".channels.").concat(channelPart, ".title"), {
              number: channelNum + 1
            }).toString();
          }
        } // @ts-ignore


        if (!storeInstance.$i18n.t("devices.vendors.".concat(device.hardwareManufacturer, ".devices.").concat(device.hardwareModel, ".channels.").concat(this.identifier, ".title")).toString().includes('devices.vendors.')) {
          // @ts-ignore
          return storeInstance.$i18n.t("devices.vendors.".concat(device.hardwareManufacturer, ".devices.").concat(device.hardwareModel, ".channels.").concat(this.identifier, ".title")).toString();
        } // @ts-ignore


        if (!storeInstance.$i18n.t("devices.vendors.".concat(device.hardwareManufacturer, ".channels.").concat(this.identifier, ".title")).toString().includes('devices.vendors.')) {
          // @ts-ignore
          return storeInstance.$i18n.t("devices.vendors.".concat(device.hardwareManufacturer, ".channels.").concat(this.identifier, ".title")).toString();
        }
      }

      return capitalize__default['default'](this.identifier);
    }
  }], [{
    key: "entity",
    get: function get() {
      return 'channel';
    }
  }, {
    key: "fields",
    value: function fields() {
      return {
        id: this.string(''),
        type: this.string(exports.ChannelEntityTypes.CHANNEL),
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
  }, {
    key: "get",
    value: function () {
      var _get = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(device, id) {
        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                _context.next = 2;
                return Channel.dispatch('get', {
                  device: device,
                  id: id
                });

              case 2:
                return _context.abrupt("return", _context.sent);

              case 3:
              case "end":
                return _context.stop();
            }
          }
        }, _callee);
      }));

      function get(_x, _x2) {
        return _get.apply(this, arguments);
      }

      return get;
    }()
  }, {
    key: "fetch",
    value: function () {
      var _fetch = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2(device) {
        return regeneratorRuntime.wrap(function _callee2$(_context2) {
          while (1) {
            switch (_context2.prev = _context2.next) {
              case 0:
                _context2.next = 2;
                return Channel.dispatch('fetch', {
                  device: device
                });

              case 2:
                return _context2.abrupt("return", _context2.sent);

              case 3:
              case "end":
                return _context2.stop();
            }
          }
        }, _callee2);
      }));

      function fetch(_x3) {
        return _fetch.apply(this, arguments);
      }

      return fetch;
    }()
  }, {
    key: "edit",
    value: function () {
      var _edit = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3(channel, data) {
        return regeneratorRuntime.wrap(function _callee3$(_context3) {
          while (1) {
            switch (_context3.prev = _context3.next) {
              case 0:
                _context3.next = 2;
                return Channel.dispatch('edit', {
                  channel: channel,
                  data: data
                });

              case 2:
                return _context3.abrupt("return", _context3.sent);

              case 3:
              case "end":
                return _context3.stop();
            }
          }
        }, _callee3);
      }));

      function edit(_x4, _x5) {
        return _edit.apply(this, arguments);
      }

      return edit;
    }()
  }, {
    key: "transmitCommand",
    value: function transmitCommand(channel, command) {
      return Channel.dispatch('transmitCommand', {
        channel: channel,
        command: command
      });
    }
  }, {
    key: "reset",
    value: function reset() {
      Channel.dispatch('reset');
    }
  }]);

  return Channel;
}(core.Model);// ENTITY TYPES
// ============
exports.DeviceConnectorEntityTypes=void 0; // ENTITY INTERFACE
// ================

(function (DeviceConnectorEntityTypes) {
  DeviceConnectorEntityTypes["CONNECTOR"] = "devices-module/device-connector";
})(exports.DeviceConnectorEntityTypes || (exports.DeviceConnectorEntityTypes = {}));// ENTITY MODEL
// ============
var Connector = /*#__PURE__*/function (_Model) {
  _inherits(Connector, _Model);

  var _super = _createSuper(Connector);

  function Connector() {
    var _this;

    _classCallCheck(this, Connector);

    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    _this = _super.call.apply(_super, [this].concat(args));

    _defineProperty(_assertThisInitialized(_this), "id", void 0);

    _defineProperty(_assertThisInitialized(_this), "type", void 0);

    _defineProperty(_assertThisInitialized(_this), "name", void 0);

    _defineProperty(_assertThisInitialized(_this), "enabled", void 0);

    _defineProperty(_assertThisInitialized(_this), "control", void 0);

    _defineProperty(_assertThisInitialized(_this), "relationshipNames", void 0);

    _defineProperty(_assertThisInitialized(_this), "devices", void 0);

    _defineProperty(_assertThisInitialized(_this), "address", void 0);

    _defineProperty(_assertThisInitialized(_this), "serialInterface", void 0);

    _defineProperty(_assertThisInitialized(_this), "baudRate", void 0);

    _defineProperty(_assertThisInitialized(_this), "server", void 0);

    _defineProperty(_assertThisInitialized(_this), "port", void 0);

    _defineProperty(_assertThisInitialized(_this), "securedPort", void 0);

    _defineProperty(_assertThisInitialized(_this), "username", void 0);

    _defineProperty(_assertThisInitialized(_this), "password", void 0);

    return _this;
  }

  _createClass(Connector, [{
    key: "isEnabled",
    get: function get() {
      return this.enabled;
    }
  }, {
    key: "icon",
    get: function get() {
      return 'magic';
    }
  }], [{
    key: "entity",
    get: function get() {
      return 'connector';
    }
  }, {
    key: "fields",
    value: function fields() {
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
  }, {
    key: "get",
    value: function () {
      var _get = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(id) {
        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                _context.next = 2;
                return Connector.dispatch('get', {
                  id: id
                });

              case 2:
                return _context.abrupt("return", _context.sent);

              case 3:
              case "end":
                return _context.stop();
            }
          }
        }, _callee);
      }));

      function get(_x) {
        return _get.apply(this, arguments);
      }

      return get;
    }()
  }, {
    key: "fetch",
    value: function () {
      var _fetch = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2() {
        return regeneratorRuntime.wrap(function _callee2$(_context2) {
          while (1) {
            switch (_context2.prev = _context2.next) {
              case 0:
                _context2.next = 2;
                return Connector.dispatch('fetch');

              case 2:
                return _context2.abrupt("return", _context2.sent);

              case 3:
              case "end":
                return _context2.stop();
            }
          }
        }, _callee2);
      }));

      function fetch() {
        return _fetch.apply(this, arguments);
      }

      return fetch;
    }()
  }, {
    key: "edit",
    value: function () {
      var _edit = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3(connector, data) {
        return regeneratorRuntime.wrap(function _callee3$(_context3) {
          while (1) {
            switch (_context3.prev = _context3.next) {
              case 0:
                _context3.next = 2;
                return Connector.dispatch('edit', {
                  connector: connector,
                  data: data
                });

              case 2:
                return _context3.abrupt("return", _context3.sent);

              case 3:
              case "end":
                return _context3.stop();
            }
          }
        }, _callee3);
      }));

      function edit(_x2, _x3) {
        return _edit.apply(this, arguments);
      }

      return edit;
    }()
  }, {
    key: "transmitCommand",
    value: function transmitCommand(connector, command) {
      return Connector.dispatch('transmitCommand', {
        connector: connector,
        command: command
      });
    }
  }, {
    key: "reset",
    value: function reset() {
      Connector.dispatch('reset');
    }
  }]);

  return Connector;
}(core.Model);// ENTITY MODEL
// ============
var DeviceConnector = /*#__PURE__*/function (_Model) {
  _inherits(DeviceConnector, _Model);

  var _super = _createSuper(DeviceConnector);

  function DeviceConnector() {
    var _this;

    _classCallCheck(this, DeviceConnector);

    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    _this = _super.call.apply(_super, [this].concat(args));

    _defineProperty(_assertThisInitialized(_this), "id", void 0);

    _defineProperty(_assertThisInitialized(_this), "type", void 0);

    _defineProperty(_assertThisInitialized(_this), "draft", void 0);

    _defineProperty(_assertThisInitialized(_this), "address", void 0);

    _defineProperty(_assertThisInitialized(_this), "maxPacketLength", void 0);

    _defineProperty(_assertThisInitialized(_this), "descriptionSupport", void 0);

    _defineProperty(_assertThisInitialized(_this), "settingsSupport", void 0);

    _defineProperty(_assertThisInitialized(_this), "configuredKeyLength", void 0);

    _defineProperty(_assertThisInitialized(_this), "pubSubPubSupport", void 0);

    _defineProperty(_assertThisInitialized(_this), "pubSubSubSupport", void 0);

    _defineProperty(_assertThisInitialized(_this), "pubSubSubMaxSubscriptions", void 0);

    _defineProperty(_assertThisInitialized(_this), "pubSubSubMaxConditions", void 0);

    _defineProperty(_assertThisInitialized(_this), "pubSubSubMaxActions", void 0);

    _defineProperty(_assertThisInitialized(_this), "username", void 0);

    _defineProperty(_assertThisInitialized(_this), "password", void 0);

    _defineProperty(_assertThisInitialized(_this), "relationshipNames", void 0);

    _defineProperty(_assertThisInitialized(_this), "device", void 0);

    _defineProperty(_assertThisInitialized(_this), "deviceBackward", void 0);

    _defineProperty(_assertThisInitialized(_this), "connector", void 0);

    _defineProperty(_assertThisInitialized(_this), "connectorBackward", void 0);

    _defineProperty(_assertThisInitialized(_this), "deviceId", void 0);

    _defineProperty(_assertThisInitialized(_this), "connectorId", void 0);

    return _this;
  }

  _createClass(DeviceConnector, null, [{
    key: "entity",
    get: function get() {
      return 'device_connector';
    }
  }, {
    key: "fields",
    value: function fields() {
      return {
        id: this.string(''),
        type: this.string(exports.DeviceConnectorEntityTypes.CONNECTOR),
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
  }, {
    key: "get",
    value: function () {
      var _get = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(device) {
        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                _context.next = 2;
                return DeviceConnector.dispatch('get', {
                  device: device
                });

              case 2:
                return _context.abrupt("return", _context.sent);

              case 3:
              case "end":
                return _context.stop();
            }
          }
        }, _callee);
      }));

      function get(_x) {
        return _get.apply(this, arguments);
      }

      return get;
    }()
  }, {
    key: "add",
    value: function () {
      var _add = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2(device, connector, data, id) {
        var draft,
            _args2 = arguments;
        return regeneratorRuntime.wrap(function _callee2$(_context2) {
          while (1) {
            switch (_context2.prev = _context2.next) {
              case 0:
                draft = _args2.length > 4 && _args2[4] !== undefined ? _args2[4] : true;
                _context2.next = 3;
                return DeviceConnector.dispatch('add', {
                  id: id,
                  draft: draft,
                  device: device,
                  connector: connector,
                  data: data
                });

              case 3:
                return _context2.abrupt("return", _context2.sent);

              case 4:
              case "end":
                return _context2.stop();
            }
          }
        }, _callee2);
      }));

      function add(_x2, _x3, _x4, _x5) {
        return _add.apply(this, arguments);
      }

      return add;
    }()
  }, {
    key: "edit",
    value: function () {
      var _edit = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3(connector, data) {
        return regeneratorRuntime.wrap(function _callee3$(_context3) {
          while (1) {
            switch (_context3.prev = _context3.next) {
              case 0:
                _context3.next = 2;
                return DeviceConnector.dispatch('edit', {
                  connector: connector,
                  data: data
                });

              case 2:
                return _context3.abrupt("return", _context3.sent);

              case 3:
              case "end":
                return _context3.stop();
            }
          }
        }, _callee3);
      }));

      function edit(_x6, _x7) {
        return _edit.apply(this, arguments);
      }

      return edit;
    }()
  }, {
    key: "save",
    value: function () {
      var _save = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee4(connector) {
        return regeneratorRuntime.wrap(function _callee4$(_context4) {
          while (1) {
            switch (_context4.prev = _context4.next) {
              case 0:
                _context4.next = 2;
                return DeviceConnector.dispatch('save', {
                  connector: connector
                });

              case 2:
                return _context4.abrupt("return", _context4.sent);

              case 3:
              case "end":
                return _context4.stop();
            }
          }
        }, _callee4);
      }));

      function save(_x8) {
        return _save.apply(this, arguments);
      }

      return save;
    }()
  }, {
    key: "remove",
    value: function () {
      var _remove = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee5(connector) {
        return regeneratorRuntime.wrap(function _callee5$(_context5) {
          while (1) {
            switch (_context5.prev = _context5.next) {
              case 0:
                _context5.next = 2;
                return DeviceConnector.dispatch('remove', {
                  connector: connector
                });

              case 2:
                return _context5.abrupt("return", _context5.sent);

              case 3:
              case "end":
                return _context5.stop();
            }
          }
        }, _callee5);
      }));

      function remove(_x9) {
        return _remove.apply(this, arguments);
      }

      return remove;
    }()
  }, {
    key: "reset",
    value: function reset() {
      DeviceConnector.dispatch('reset');
    }
  }]);

  return DeviceConnector;
}(core.Model);// ============

var Device = /*#__PURE__*/function (_Model) {
  _inherits(Device, _Model);

  var _super = _createSuper(Device);

  function Device() {
    var _this;

    _classCallCheck(this, Device);

    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    _this = _super.call.apply(_super, [this].concat(args));

    _defineProperty(_assertThisInitialized(_this), "id", void 0);

    _defineProperty(_assertThisInitialized(_this), "type", void 0);

    _defineProperty(_assertThisInitialized(_this), "draft", void 0);

    _defineProperty(_assertThisInitialized(_this), "parentId", void 0);

    _defineProperty(_assertThisInitialized(_this), "key", void 0);

    _defineProperty(_assertThisInitialized(_this), "identifier", void 0);

    _defineProperty(_assertThisInitialized(_this), "name", void 0);

    _defineProperty(_assertThisInitialized(_this), "comment", void 0);

    _defineProperty(_assertThisInitialized(_this), "state", void 0);

    _defineProperty(_assertThisInitialized(_this), "enabled", void 0);

    _defineProperty(_assertThisInitialized(_this), "hardwareModel", void 0);

    _defineProperty(_assertThisInitialized(_this), "hardwareManufacturer", void 0);

    _defineProperty(_assertThisInitialized(_this), "hardwareVersion", void 0);

    _defineProperty(_assertThisInitialized(_this), "macAddress", void 0);

    _defineProperty(_assertThisInitialized(_this), "firmwareManufacturer", void 0);

    _defineProperty(_assertThisInitialized(_this), "firmwareVersion", void 0);

    _defineProperty(_assertThisInitialized(_this), "control", void 0);

    _defineProperty(_assertThisInitialized(_this), "owner", void 0);

    _defineProperty(_assertThisInitialized(_this), "relationshipNames", void 0);

    _defineProperty(_assertThisInitialized(_this), "children", void 0);

    _defineProperty(_assertThisInitialized(_this), "channels", void 0);

    _defineProperty(_assertThisInitialized(_this), "properties", void 0);

    _defineProperty(_assertThisInitialized(_this), "configuration", void 0);

    _defineProperty(_assertThisInitialized(_this), "connector", void 0);

    return _this;
  }

  _createClass(Device, [{
    key: "isEnabled",
    get: function get() {
      return this.enabled;
    }
  }, {
    key: "isReady",
    get: function get() {
      return this.state === modulesMetadata.DeviceConnectionState.READY || this.state === modulesMetadata.DeviceConnectionState.RUNNING;
    }
  }, {
    key: "icon",
    get: function get() {
      if (this.hardwareManufacturer === modulesMetadata.HardwareManufacturer.ITEAD) {
        switch (this.hardwareModel) {
          case modulesMetadata.DeviceModel.SONOFF_SC:
            return 'thermometer-half';

          case modulesMetadata.DeviceModel.SONOFF_POW:
          case modulesMetadata.DeviceModel.SONOFF_POW_R2:
            return 'calculator';
        }
      }

      return 'plug';
    }
  }, {
    key: "title",
    get: function get() {
      if (this.name !== null) {
        return this.name;
      }

      var storeInstance = Device.store();

      if (Object.prototype.hasOwnProperty.call(storeInstance, '$i18n')) {
        if (this.isCustomModel) {
          return capitalize__default['default'](this.identifier);
        } // @ts-ignore


        if (!storeInstance.$i18n.t("devices.vendors.".concat(this.hardwareManufacturer, ".devices.").concat(this.hardwareModel, ".title")).toString().includes('devices.vendors.')) {
          // @ts-ignore
          return storeInstance.$i18n.t("devices.vendors.".concat(this.hardwareManufacturer, ".devices.").concat(this.hardwareModel, ".title")).toString();
        }
      }

      return capitalize__default['default'](this.identifier);
    }
  }, {
    key: "hasComment",
    get: function get() {
      return this.comment !== null && this.comment !== '';
    }
  }, {
    key: "isCustomModel",
    get: function get() {
      return this.hardwareModel === modulesMetadata.DeviceModel.CUSTOM;
    }
  }], [{
    key: "entity",
    get: function get() {
      return 'device';
    }
  }, {
    key: "fields",
    value: function fields() {
      return {
        id: this.string(''),
        type: this.string(exports.DeviceEntityTypes.DEVICE),
        draft: this.boolean(false),
        parentId: this.string(null).nullable(),
        key: this.string(''),
        identifier: this.string(''),
        name: this.string(null).nullable(),
        comment: this.string(null).nullable(),
        state: this.string(modulesMetadata.DeviceConnectionState.UNKNOWN),
        enabled: this.boolean(false),
        hardwareModel: this.string(modulesMetadata.DeviceModel.CUSTOM),
        hardwareManufacturer: this.string(modulesMetadata.HardwareManufacturer.GENERIC),
        hardwareVersion: this.string(null).nullable(),
        macAddress: this.string(null).nullable(),
        firmwareManufacturer: this.string(modulesMetadata.FirmwareManufacturer.GENERIC),
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
  }, {
    key: "get",
    value: function () {
      var _get = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(id, includeChannels) {
        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                _context.next = 2;
                return Device.dispatch('get', {
                  id: id,
                  includeChannels: includeChannels
                });

              case 2:
                return _context.abrupt("return", _context.sent);

              case 3:
              case "end":
                return _context.stop();
            }
          }
        }, _callee);
      }));

      function get(_x, _x2) {
        return _get.apply(this, arguments);
      }

      return get;
    }()
  }, {
    key: "fetch",
    value: function () {
      var _fetch = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2(includeChannels) {
        return regeneratorRuntime.wrap(function _callee2$(_context2) {
          while (1) {
            switch (_context2.prev = _context2.next) {
              case 0:
                _context2.next = 2;
                return Device.dispatch('fetch', {
                  includeChannels: includeChannels
                });

              case 2:
                return _context2.abrupt("return", _context2.sent);

              case 3:
              case "end":
                return _context2.stop();
            }
          }
        }, _callee2);
      }));

      function fetch(_x3) {
        return _fetch.apply(this, arguments);
      }

      return fetch;
    }()
  }, {
    key: "add",
    value: function () {
      var _add = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3(data, id) {
        var draft,
            _args3 = arguments;
        return regeneratorRuntime.wrap(function _callee3$(_context3) {
          while (1) {
            switch (_context3.prev = _context3.next) {
              case 0:
                draft = _args3.length > 2 && _args3[2] !== undefined ? _args3[2] : true;
                _context3.next = 3;
                return Device.dispatch('add', {
                  id: id,
                  draft: draft,
                  data: data
                });

              case 3:
                return _context3.abrupt("return", _context3.sent);

              case 4:
              case "end":
                return _context3.stop();
            }
          }
        }, _callee3);
      }));

      function add(_x4, _x5) {
        return _add.apply(this, arguments);
      }

      return add;
    }()
  }, {
    key: "edit",
    value: function () {
      var _edit = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee4(device, data) {
        return regeneratorRuntime.wrap(function _callee4$(_context4) {
          while (1) {
            switch (_context4.prev = _context4.next) {
              case 0:
                _context4.next = 2;
                return Device.dispatch('edit', {
                  device: device,
                  data: data
                });

              case 2:
                return _context4.abrupt("return", _context4.sent);

              case 3:
              case "end":
                return _context4.stop();
            }
          }
        }, _callee4);
      }));

      function edit(_x6, _x7) {
        return _edit.apply(this, arguments);
      }

      return edit;
    }()
  }, {
    key: "save",
    value: function () {
      var _save = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee5(device) {
        return regeneratorRuntime.wrap(function _callee5$(_context5) {
          while (1) {
            switch (_context5.prev = _context5.next) {
              case 0:
                _context5.next = 2;
                return Device.dispatch('save', {
                  device: device
                });

              case 2:
                return _context5.abrupt("return", _context5.sent);

              case 3:
              case "end":
                return _context5.stop();
            }
          }
        }, _callee5);
      }));

      function save(_x8) {
        return _save.apply(this, arguments);
      }

      return save;
    }()
  }, {
    key: "remove",
    value: function () {
      var _remove = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee6(device) {
        return regeneratorRuntime.wrap(function _callee6$(_context6) {
          while (1) {
            switch (_context6.prev = _context6.next) {
              case 0:
                _context6.next = 2;
                return Device.dispatch('remove', {
                  device: device
                });

              case 2:
                return _context6.abrupt("return", _context6.sent);

              case 3:
              case "end":
                return _context6.stop();
            }
          }
        }, _callee6);
      }));

      function remove(_x9) {
        return _remove.apply(this, arguments);
      }

      return remove;
    }()
  }, {
    key: "transmitCommand",
    value: function transmitCommand(device, command) {
      return Device.dispatch('transmitCommand', {
        device: device,
        command: command
      });
    }
  }, {
    key: "reset",
    value: function reset() {
      Device.dispatch('reset');
    }
  }]);

  return Device;
}(core.Model);var ExceptionError = /*#__PURE__*/function (_Error) {
  _inherits(ExceptionError, _Error);

  var _super = _createSuper(ExceptionError);

  function ExceptionError(type, exception) {
    var _this;

    _classCallCheck(this, ExceptionError);

    for (var _len = arguments.length, params = new Array(_len > 2 ? _len - 2 : 0), _key = 2; _key < _len; _key++) {
      params[_key - 2] = arguments[_key];
    }

    // Pass remaining arguments (including vendor specific ones) to parent constructor
    _this = _super.call.apply(_super, [this].concat(params)); // Maintains proper stack trace for where our error was thrown (only available on V8)

    _defineProperty(_assertThisInitialized(_this), "type", void 0);

    _defineProperty(_assertThisInitialized(_this), "exception", void 0);

    if (Error.captureStackTrace) {
      Error.captureStackTrace(_assertThisInitialized(_this), ExceptionError);
    } // Custom debugging information


    _this.type = type;
    _this.exception = exception;
    return _this;
  }

  return ExceptionError;
}( /*#__PURE__*/_wrapNativeSuper(Error));

var ApiError = /*#__PURE__*/function (_ExceptionError) {
  _inherits(ApiError, _ExceptionError);

  var _super2 = _createSuper(ApiError);

  function ApiError() {
    _classCallCheck(this, ApiError);

    return _super2.apply(this, arguments);
  }

  return ApiError;
}(ExceptionError);

var OrmError = /*#__PURE__*/function (_ExceptionError2) {
  _inherits(OrmError, _ExceptionError2);

  var _super3 = _createSuper(OrmError);

  function OrmError() {
    _classCallCheck(this, OrmError);

    return _super3.apply(this, arguments);
  }

  return OrmError;
}(ExceptionError);// ENTITY TYPES
// ============
exports.ChannelConfigurationEntityTypes=void 0; // ENTITY INTERFACE
// ================

(function (ChannelConfigurationEntityTypes) {
  ChannelConfigurationEntityTypes["CONFIGURATION"] = "devices-module/channel-configuration";
})(exports.ChannelConfigurationEntityTypes || (exports.ChannelConfigurationEntityTypes = {}));var RELATIONSHIP_NAMES_PROP = 'relationshipNames';
var JsonApiModelPropertiesMapper = /*#__PURE__*/function (_ModelPropertiesMappe) {
  _inherits(JsonApiModelPropertiesMapper, _ModelPropertiesMappe);

  var _super = _createSuper(JsonApiModelPropertiesMapper);

  function JsonApiModelPropertiesMapper() {
    _classCallCheck(this, JsonApiModelPropertiesMapper);

    return _super.apply(this, arguments);
  }

  _createClass(JsonApiModelPropertiesMapper, [{
    key: "getAttributes",
    value: function getAttributes(model) {
      var exceptProps = ['id', '$id', 'type', 'draft', RELATIONSHIP_NAMES_PROP];

      if (model.type === exports.ChannelEntityTypes.CHANNEL || model.type === exports.DevicePropertyEntityTypes.PROPERTY || model.type === exports.DeviceConfigurationEntityTypes.CONFIGURATION || model.type === exports.DeviceConnectorEntityTypes.CONNECTOR) {
        exceptProps.push('deviceId');
        exceptProps.push('device');
        exceptProps.push('device_backward');
      } else if (model.type === exports.ChannelPropertyEntityTypes.PROPERTY || model.type === exports.ChannelConfigurationEntityTypes.CONFIGURATION) {
        exceptProps.push('channelId');
        exceptProps.push('channel');
        exceptProps.push('channel_backward');
      } else if (model.type === exports.DeviceConnectorEntityTypes.CONNECTOR) {
        exceptProps.push('connectorId');
        exceptProps.push('connector');
        exceptProps.push('connector_backward');
      }

      if (Array.isArray(model[RELATIONSHIP_NAMES_PROP])) {
        exceptProps.push.apply(exceptProps, _toConsumableArray(model[RELATIONSHIP_NAMES_PROP]));
      }

      var attributes = {};
      Object.keys(model).forEach(function (attrName) {
        if (!exceptProps.includes(attrName)) {
          var kebabName = attrName.replace(/([a-z][A-Z0-9])/g, function (g) {
            return "".concat(g[0], "_").concat(g[1].toLowerCase());
          });
          var jsonAttributes = model[attrName];

          if (_typeof(jsonAttributes) === 'object' && jsonAttributes !== null) {
            jsonAttributes = {};
            Object.keys(model[attrName]).forEach(function (subAttrName) {
              var kebabSubName = subAttrName.replace(/([a-z][A-Z0-9])/g, function (g) {
                return "".concat(g[0], "_").concat(g[1].toLowerCase());
              });
              Object.assign(jsonAttributes, _defineProperty({}, kebabSubName, model[attrName][subAttrName]));
            });
          }

          attributes[kebabName] = jsonAttributes;
        }
      });
      return attributes;
    }
  }, {
    key: "getRelationships",
    value: function getRelationships(model) {
      if (!Object.prototype.hasOwnProperty.call(model, RELATIONSHIP_NAMES_PROP) || !Array.isArray(model[RELATIONSHIP_NAMES_PROP])) {
        return {};
      }

      var relationshipNames = model[RELATIONSHIP_NAMES_PROP];
      var relationships = {};
      relationshipNames.forEach(function (relationName) {
        var kebabName = relationName.replace(/([a-z][A-Z0-9])/g, function (g) {
          return "".concat(g[0], "_").concat(g[1].toLowerCase());
        });

        if (model[relationName] !== undefined) {
          if (Array.isArray(model[relationName])) {
            relationships[kebabName] = model[relationName].map(function (item) {
              return {
                id: item.id,
                type: item.type
              };
            });
          } else if (_typeof(model[relationName]) === 'object' && model[relationName] !== null) {
            relationships[kebabName] = {
              id: model[relationName].id,
              type: model[relationName].type
            };
          }
        }
      });

      if (Object.prototype.hasOwnProperty.call(model, 'deviceId')) {
        var device = Device.find(model.deviceId);

        if (device !== null) {
          relationships.device = {
            id: device.id,
            type: device.type
          };
        }
      }

      if (Object.prototype.hasOwnProperty.call(model, 'channelId')) {
        var channel = Channel.find(model.deviceId);

        if (channel !== null) {
          relationships.channel = {
            id: channel.id,
            type: channel.type
          };
        }
      }

      if (Object.prototype.hasOwnProperty.call(model, 'connectorId')) {
        var connector = Connector.find(model.connectorId);

        if (connector !== null) {
          relationships.connector = {
            id: connector.id,
            type: connector.type
          };
        }
      }

      return relationships;
    }
  }]);

  return JsonApiModelPropertiesMapper;
}(Jsona.ModelPropertiesMapper);
var JsonApiPropertiesMapper = /*#__PURE__*/function (_JsonPropertiesMapper) {
  _inherits(JsonApiPropertiesMapper, _JsonPropertiesMapper);

  var _super2 = _createSuper(JsonApiPropertiesMapper);

  function JsonApiPropertiesMapper() {
    var _this;

    _classCallCheck(this, JsonApiPropertiesMapper);

    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    _this = _super2.call.apply(_super2, [this].concat(args));

    _defineProperty(_assertThisInitialized(_this), "caseRegExp", '_([a-z0-9])');

    return _this;
  }

  _createClass(JsonApiPropertiesMapper, [{
    key: "createModel",
    value: function createModel(type) {
      return {
        type: type
      };
    }
  }, {
    key: "setId",
    value: function setId(model, id) {
      Object.assign(model, {
        id: id
      });
    }
  }, {
    key: "setAttributes",
    value: function setAttributes(model, attributes) {
      var regex = new RegExp(this.caseRegExp, 'g');
      Object.keys(attributes).forEach(function (propName) {
        var camelName = propName.replace(regex, function (g) {
          return g[1].toUpperCase();
        });
        var modelAttributes = attributes[propName];

        if (_typeof(modelAttributes) === 'object' && modelAttributes !== null) {
          modelAttributes = {};
          Object.keys(attributes[propName]).forEach(function (subPropName) {
            var camelSubName = subPropName.replace(regex, function (g) {
              return g[1].toUpperCase();
            });
            Object.assign(modelAttributes, _defineProperty({}, camelSubName, attributes[propName][subPropName]));
          });
        }

        if (propName === 'control') {
          modelAttributes = Object.values(attributes[propName]);
        }

        Object.assign(model, _defineProperty({}, camelName, modelAttributes));
      }); // Entity received via api is not a draft entity

      Object.assign(model, {
        draft: false
      });
    }
  }, {
    key: "setRelationships",
    value: function setRelationships(model, relationships) {
      var _this2 = this;

      Object.keys(relationships).forEach(function (propName) {
        var regex = new RegExp(_this2.caseRegExp, 'g');
        var camelName = propName.replace(regex, function (g) {
          return g[1].toUpperCase();
        });

        if (typeof relationships[propName] === 'function') {
          simplePropertyMappers.defineRelationGetter(model, propName, relationships[propName]);
        } else {
          var relation = clone__default['default'](relationships[propName]);

          if (Array.isArray(relation)) {
            Object.assign(model, _defineProperty({}, camelName, relation.map(function (item) {
              var transformed = item;
              transformed = _this2.transformDevice(transformed);
              transformed = _this2.transformChannel(transformed);
              return transformed;
            })));
          } else if (get__default['default'](relation, 'type') === exports.DeviceEntityTypes.DEVICE) {
            Object.assign(model, {
              deviceId: get__default['default'](relation, 'id')
            });
          } else if (get__default['default'](relation, 'type') === exports.ChannelEntityTypes.CHANNEL) {
            Object.assign(model, {
              channelId: get__default['default'](relation, 'id')
            });
          } else {
            Object.assign(model, _defineProperty({}, camelName, relation));
          }
        }
      });
      var newNames = Object.keys(relationships);
      var currentNames = model[RELATIONSHIP_NAMES_PROP];

      if (currentNames && currentNames.length) {
        Object.assign(model, _defineProperty({}, RELATIONSHIP_NAMES_PROP, [].concat(_toConsumableArray(currentNames), _toConsumableArray(newNames)).filter(function (value, i, self) {
          return self.indexOf(value) === i;
        })));
      } else {
        Object.assign(model, _defineProperty({}, RELATIONSHIP_NAMES_PROP, newNames));
      }
    }
  }, {
    key: "transformDevice",
    value: function transformDevice(item) {
      if (Object.prototype.hasOwnProperty.call(item, 'device')) {
        Object.assign(item, {
          deviceId: item.device.id
        });
        Reflect.deleteProperty(item, 'device');
      }

      return item;
    }
  }, {
    key: "transformChannel",
    value: function transformChannel(item) {
      if (Object.prototype.hasOwnProperty.call(item, 'channel')) {
        Object.assign(item, {
          channelId: item.channel.id
        });
        Reflect.deleteProperty(item, 'channel');
      }

      return item;
    }
  }]);

  return JsonApiPropertiesMapper;
}(Jsona.JsonPropertiesMapper);var ModuleApiPrefix = "/".concat(modulesMetadata.ModulePrefix.MODULE_DEVICES_PREFIX); // STORE
// =====

var SemaphoreTypes;

(function (SemaphoreTypes) {
  SemaphoreTypes["FETCHING"] = "fetching";
  SemaphoreTypes["GETTING"] = "getting";
  SemaphoreTypes["CREATING"] = "creating";
  SemaphoreTypes["UPDATING"] = "updating";
  SemaphoreTypes["DELETING"] = "deleting";
})(SemaphoreTypes || (SemaphoreTypes = {}));var _moduleMutations$7;
var jsonApiFormatter$7 = new Jsona__default['default']({
  modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
  jsonPropertiesMapper: new JsonApiPropertiesMapper()
});
var apiOptions$7 = {
  dataTransformer: function dataTransformer(result) {
    return jsonApiFormatter$7.deserialize(result.data);
  }
};
var jsonSchemaValidator$7 = new Ajv__default['default']();
var moduleState$7 = {
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
var moduleGetters$2 = {
  firstLoadFinished: function firstLoadFinished(state) {
    return function () {
      return !!state.firstLoad;
    };
  },
  getting: function getting(state) {
    return function (id) {
      return state.semaphore.fetching.item.includes(id);
    };
  },
  fetching: function fetching(state) {
    return function () {
      return !!state.semaphore.fetching.items;
    };
  }
};
var moduleActions$7 = {
  get: function get(_ref, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
      var state, commit, device;
      return regeneratorRuntime.wrap(function _callee$(_context) {
        while (1) {
          switch (_context.prev = _context.next) {
            case 0:
              state = _ref.state, commit = _ref.commit;

              if (!state.semaphore.fetching.item.includes(payload.id)) {
                _context.next = 3;
                break;
              }

              return _context.abrupt("return", false);

            case 3:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.GETTING,
                id: payload.id
              });
              _context.prev = 4;
              _context.next = 7;
              return Device.api().get("".concat(ModuleApiPrefix, "/v1/devices/").concat(payload.id, "?include=properties,configuration,connector"), apiOptions$7);

            case 7:
              if (!payload.includeChannels) {
                _context.next = 12;
                break;
              }

              device = Device.find(payload.id);

              if (!(device !== null)) {
                _context.next = 12;
                break;
              }

              _context.next = 12;
              return Channel.fetch(device);

            case 12:
              return _context.abrupt("return", true);

            case 15:
              _context.prev = 15;
              _context.t0 = _context["catch"](4);
              throw new ApiError('devices-module.devices.fetch.failed', _context.t0, 'Fetching devices failed.');

            case 18:
              _context.prev = 18;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.GETTING,
                id: payload.id
              });
              return _context.finish(18);

            case 21:
            case "end":
              return _context.stop();
          }
        }
      }, _callee, null, [[4, 15, 18, 21]]);
    }))();
  },
  fetch: function fetch(_ref2, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2() {
      var state, commit, devices, promises;
      return regeneratorRuntime.wrap(function _callee2$(_context2) {
        while (1) {
          switch (_context2.prev = _context2.next) {
            case 0:
              state = _ref2.state, commit = _ref2.commit;

              if (!state.semaphore.fetching.items) {
                _context2.next = 3;
                break;
              }

              return _context2.abrupt("return", false);

            case 3:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.FETCHING
              });
              _context2.prev = 4;
              _context2.next = 7;
              return Device.api().get("".concat(ModuleApiPrefix, "/v1/devices?include=properties,configuration,connector"), apiOptions$7);

            case 7:
              if (!payload.includeChannels) {
                _context2.next = 15;
                break;
              }

              _context2.next = 10;
              return Device.all();

            case 10:
              devices = _context2.sent;
              promises = [];
              devices.forEach(function (device) {
                promises.push(Channel.fetch(device));
              });
              _context2.next = 15;
              return Promise.all(promises);

            case 15:
              commit('SET_FIRST_LOAD', true);
              return _context2.abrupt("return", true);

            case 19:
              _context2.prev = 19;
              _context2.t0 = _context2["catch"](4);
              throw new ApiError('devices-module.devices.fetch.failed', _context2.t0, 'Fetching devices failed.');

            case 22:
              _context2.prev = 22;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.FETCHING
              });
              return _context2.finish(22);

            case 25:
            case "end":
              return _context2.stop();
          }
        }
      }, _callee2, null, [[4, 19, 22, 25]]);
    }))();
  },
  add: function add(_ref3, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3() {
      var commit, id, draft, createdEntity;
      return regeneratorRuntime.wrap(function _callee3$(_context3) {
        while (1) {
          switch (_context3.prev = _context3.next) {
            case 0:
              commit = _ref3.commit;
              id = typeof payload.id !== 'undefined' && payload.id !== null && payload.id !== '' ? payload.id : uuid.v4().toString();
              draft = typeof payload.draft !== 'undefined' ? payload.draft : false;
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.CREATING,
                id: id
              });
              _context3.prev = 4;
              _context3.next = 7;
              return Device.insert({
                data: Object.assign({}, payload.data, {
                  id: id,
                  draft: draft
                })
              });

            case 7:
              _context3.next = 13;
              break;

            case 9:
              _context3.prev = 9;
              _context3.t0 = _context3["catch"](4);
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.CREATING,
                id: id
              });
              throw new OrmError('devices-module.devices.create.failed', _context3.t0, 'Create new device failed.');

            case 13:
              createdEntity = Device.find(id);

              if (!(createdEntity === null)) {
                _context3.next = 19;
                break;
              }

              _context3.next = 17;
              return Device.delete(id);

            case 17:
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.CREATING,
                id: id
              });
              throw new Error('devices-module.devices.create.failed');

            case 19:
              if (!draft) {
                _context3.next = 24;
                break;
              }

              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.CREATING,
                id: id
              });
              return _context3.abrupt("return", Device.find(id));

            case 24:
              _context3.prev = 24;
              _context3.next = 27;
              return Device.api().post("".concat(ModuleApiPrefix, "/v1/devices?include=properties,configuration,connector"), jsonApiFormatter$7.serialize({
                stuff: createdEntity
              }), apiOptions$7);

            case 27:
              return _context3.abrupt("return", Device.find(id));

            case 30:
              _context3.prev = 30;
              _context3.t1 = _context3["catch"](24);
              _context3.next = 34;
              return Device.delete(id);

            case 34:
              throw new ApiError('devices-module.devices.create.failed', _context3.t1, 'Create new device failed.');

            case 35:
              _context3.prev = 35;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.CREATING,
                id: id
              });
              return _context3.finish(35);

            case 38:
            case "end":
              return _context3.stop();
          }
        }
      }, _callee3, null, [[4, 9], [24, 30, 35, 38]]);
    }))();
  },
  edit: function edit(_ref4, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee4() {
      var state, commit, updatedEntity;
      return regeneratorRuntime.wrap(function _callee4$(_context4) {
        while (1) {
          switch (_context4.prev = _context4.next) {
            case 0:
              state = _ref4.state, commit = _ref4.commit;

              if (!state.semaphore.updating.includes(payload.device.id)) {
                _context4.next = 3;
                break;
              }

              throw new Error('devices-module.devices.update.inProgress');

            case 3:
              if (Device.query().where('id', payload.device.id).exists()) {
                _context4.next = 5;
                break;
              }

              throw new Error('devices-module.devices.update.failed');

            case 5:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.device.id
              });
              _context4.prev = 6;
              _context4.next = 9;
              return Device.update({
                where: payload.device.id,
                data: payload.data
              });

            case 9:
              _context4.next = 14;
              break;

            case 11:
              _context4.prev = 11;
              _context4.t0 = _context4["catch"](6);
              throw new OrmError('devices-module.devices.update.failed', _context4.t0, 'Edit device failed.');

            case 14:
              updatedEntity = Device.find(payload.device.id);

              if (!(updatedEntity === null)) {
                _context4.next = 20;
                break;
              }

              _context4.next = 18;
              return Device.get(payload.device.id, false);

            case 18:
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.device.id
              });
              throw new Error('devices-module.devices.update.failed');

            case 20:
              if (!updatedEntity.draft) {
                _context4.next = 25;
                break;
              }

              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.device.id
              });
              return _context4.abrupt("return", Device.find(payload.device.id));

            case 25:
              _context4.prev = 25;
              _context4.next = 28;
              return Device.api().patch("".concat(ModuleApiPrefix, "/v1/devices/").concat(updatedEntity.id, "?include=properties,configuration,connector"), jsonApiFormatter$7.serialize({
                stuff: updatedEntity
              }), apiOptions$7);

            case 28:
              return _context4.abrupt("return", Device.find(payload.device.id));

            case 31:
              _context4.prev = 31;
              _context4.t1 = _context4["catch"](25);
              _context4.next = 35;
              return Device.get(payload.device.id, false);

            case 35:
              throw new ApiError('devices-module.devices.update.failed', _context4.t1, 'Edit device failed.');

            case 36:
              _context4.prev = 36;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.device.id
              });
              return _context4.finish(36);

            case 39:
            case "end":
              return _context4.stop();
          }
        }
      }, _callee4, null, [[6, 11], [25, 31, 36, 39]]);
    }))();
  },
  save: function save(_ref5, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee5() {
      var state, commit, entityToSave;
      return regeneratorRuntime.wrap(function _callee5$(_context5) {
        while (1) {
          switch (_context5.prev = _context5.next) {
            case 0:
              state = _ref5.state, commit = _ref5.commit;

              if (!state.semaphore.updating.includes(payload.device.id)) {
                _context5.next = 3;
                break;
              }

              throw new Error('devices-module.devices.save.inProgress');

            case 3:
              if (Device.query().where('id', payload.device.id).where('draft', true).exists()) {
                _context5.next = 5;
                break;
              }

              throw new Error('devices-module.devices.save.failed');

            case 5:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.device.id
              });
              entityToSave = Device.find(payload.device.id);

              if (!(entityToSave === null)) {
                _context5.next = 10;
                break;
              }

              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.device.id
              });
              throw new Error('devices-module.devices.save.failed');

            case 10:
              _context5.prev = 10;
              _context5.next = 13;
              return Device.api().post("".concat(ModuleApiPrefix, "/v1/devices?include=properties,configuration,connector"), jsonApiFormatter$7.serialize({
                stuff: entityToSave
              }), apiOptions$7);

            case 13:
              return _context5.abrupt("return", Device.find(payload.device.id));

            case 16:
              _context5.prev = 16;
              _context5.t0 = _context5["catch"](10);
              throw new ApiError('devices-module.devices.save.failed', _context5.t0, 'Save draft device failed.');

            case 19:
              _context5.prev = 19;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.device.id
              });
              return _context5.finish(19);

            case 22:
            case "end":
              return _context5.stop();
          }
        }
      }, _callee5, null, [[10, 16, 19, 22]]);
    }))();
  },
  remove: function remove(_ref6, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee6() {
      var state, commit;
      return regeneratorRuntime.wrap(function _callee6$(_context6) {
        while (1) {
          switch (_context6.prev = _context6.next) {
            case 0:
              state = _ref6.state, commit = _ref6.commit;

              if (!state.semaphore.deleting.includes(payload.device.id)) {
                _context6.next = 3;
                break;
              }

              throw new Error('devices-module.devices.delete.inProgress');

            case 3:
              if (Device.query().where('id', payload.device.id).exists()) {
                _context6.next = 5;
                break;
              }

              return _context6.abrupt("return", true);

            case 5:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.DELETING,
                id: payload.device.id
              });
              _context6.prev = 6;
              _context6.next = 9;
              return Device.delete(payload.device.id);

            case 9:
              _context6.next = 15;
              break;

            case 11:
              _context6.prev = 11;
              _context6.t0 = _context6["catch"](6);
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.DELETING,
                id: payload.device.id
              });
              throw new OrmError('devices-module.devices.delete.failed', _context6.t0, 'Delete device failed.');

            case 15:
              if (!payload.device.draft) {
                _context6.next = 20;
                break;
              }

              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.DELETING,
                id: payload.device.id
              });
              return _context6.abrupt("return", true);

            case 20:
              _context6.prev = 20;
              _context6.next = 23;
              return Device.api().delete("".concat(ModuleApiPrefix, "/v1/devices/").concat(payload.device.id), {
                save: false
              });

            case 23:
              return _context6.abrupt("return", true);

            case 26:
              _context6.prev = 26;
              _context6.t1 = _context6["catch"](20);
              _context6.next = 30;
              return Device.get(payload.device.id, false);

            case 30:
              throw new OrmError('devices-module.devices.delete.failed', _context6.t1, 'Delete device failed.');

            case 31:
              _context6.prev = 31;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.DELETING,
                id: payload.device.id
              });
              return _context6.finish(31);

            case 34:
            case "end":
              return _context6.stop();
          }
        }
      }, _callee6, null, [[6, 11], [20, 26, 31, 34]]);
    }))();
  },
  transmitCommand: function transmitCommand(_store, payload) {
    if (!Device.query().where('id', payload.device.id).exists()) {
      throw new Error('devices-module.device.transmit.failed');
    }

    return new Promise(function (resolve, reject) {
      Device.wamp().call({
        routing_key: modulesMetadata.DevicesModule.DEVICES_CONTROLS,
        origin: Device.$devicesModuleOrigin,
        data: {
          control: payload.command,
          device: payload.device.key
        }
      }).then(function (response) {
        if (get__default['default'](response.data, 'response') === 'accepted') {
          resolve(true);
        } else {
          reject(new Error('devices-module.device.transmit.failed'));
        }
      }).catch(function () {
        reject(new Error('devices-module.device.transmit.failed'));
      });
    });
  },
  socketData: function socketData(_ref7, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee7() {
      var state, commit, body, validate, entityData;
      return regeneratorRuntime.wrap(function _callee7$(_context7) {
        while (1) {
          switch (_context7.prev = _context7.next) {
            case 0:
              state = _ref7.state, commit = _ref7.commit;

              if (!(payload.origin !== modulesMetadata.ModuleOrigin.MODULE_DEVICES_ORIGIN)) {
                _context7.next = 3;
                break;
              }

              return _context7.abrupt("return", false);

            case 3:
              if ([modulesMetadata.DevicesModule.DEVICES_CREATED_ENTITY, modulesMetadata.DevicesModule.DEVICES_UPDATED_ENTITY, modulesMetadata.DevicesModule.DEVICES_DELETED_ENTITY].includes(payload.routingKey)) {
                _context7.next = 5;
                break;
              }

              return _context7.abrupt("return", false);

            case 5:
              body = JSON.parse(payload.data);
              validate = jsonSchemaValidator$7.compile(exchangeEntitySchema__namespace);

              if (!validate(body)) {
                _context7.next = 46;
                break;
              }

              if (!(!Device.query().where('id', body.id).exists() && (payload.routingKey === modulesMetadata.DevicesModule.DEVICES_UPDATED_ENTITY || payload.routingKey === modulesMetadata.DevicesModule.DEVICES_DELETED_ENTITY))) {
                _context7.next = 10;
                break;
              }

              throw new Error('devices-module.devices.update.failed');

            case 10:
              if (!(payload.routingKey === modulesMetadata.DevicesModule.DEVICES_DELETED_ENTITY)) {
                _context7.next = 25;
                break;
              }

              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.DELETING,
                id: body.id
              });
              _context7.prev = 12;
              _context7.next = 15;
              return Device.delete(body.id);

            case 15:
              _context7.next = 20;
              break;

            case 17:
              _context7.prev = 17;
              _context7.t0 = _context7["catch"](12);
              throw new OrmError('devices-module.devices.delete.failed', _context7.t0, 'Delete device failed.');

            case 20:
              _context7.prev = 20;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.DELETING,
                id: body.id
              });
              return _context7.finish(20);

            case 23:
              _context7.next = 43;
              break;

            case 25:
              if (!(payload.routingKey === modulesMetadata.DevicesModule.DEVICES_UPDATED_ENTITY && state.semaphore.updating.includes(body.id))) {
                _context7.next = 27;
                break;
              }

              return _context7.abrupt("return", true);

            case 27:
              commit('SET_SEMAPHORE', {
                type: payload.routingKey === modulesMetadata.DevicesModule.DEVICES_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
                id: body.id
              });
              entityData = {};
              Object.keys(body).forEach(function (attrName) {
                var kebabName = attrName.replace(/([a-z][A-Z0-9])/g, function (g) {
                  return "".concat(g[0], "_").concat(g[1].toLowerCase());
                });
                entityData[kebabName] = body[attrName];
              });
              _context7.prev = 30;
              _context7.next = 33;
              return Device.insertOrUpdate({
                data: entityData
              });

            case 33:
              _context7.next = 40;
              break;

            case 35:
              _context7.prev = 35;
              _context7.t1 = _context7["catch"](30);
              _context7.next = 39;
              return Device.get(body.id, false);

            case 39:
              throw new OrmError('devices-module.devices.update.failed', _context7.t1, 'Edit device failed.');

            case 40:
              _context7.prev = 40;
              commit('CLEAR_SEMAPHORE', {
                type: payload.routingKey === modulesMetadata.DevicesModule.DEVICES_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
                id: body.id
              });
              return _context7.finish(40);

            case 43:
              return _context7.abrupt("return", true);

            case 46:
              return _context7.abrupt("return", false);

            case 47:
            case "end":
              return _context7.stop();
          }
        }
      }, _callee7, null, [[12, 17, 20, 23], [30, 35, 40, 43]]);
    }))();
  },
  reset: function reset(_ref8) {
    var commit = _ref8.commit;
    commit('RESET_STATE');
    Channel.reset();
  }
};
var moduleMutations$7 = (_moduleMutations$7 = {}, _defineProperty(_moduleMutations$7, 'SET_FIRST_LOAD', function SET_FIRST_LOAD(state, action) {
  state.firstLoad = action;
}), _defineProperty(_moduleMutations$7, 'SET_SEMAPHORE', function SET_SEMAPHORE(state, action) {
  switch (action.type) {
    case SemaphoreTypes.FETCHING:
      state.semaphore.fetching.items = true;
      break;

    case SemaphoreTypes.GETTING:
      state.semaphore.fetching.item.push(get__default['default'](action, 'id', 'notValid')); // Make all keys uniq

      state.semaphore.fetching.item = uniq__default['default'](state.semaphore.fetching.item);
      break;

    case SemaphoreTypes.CREATING:
      state.semaphore.creating.push(get__default['default'](action, 'id', 'notValid')); // Make all keys uniq

      state.semaphore.creating = uniq__default['default'](state.semaphore.creating);
      break;

    case SemaphoreTypes.UPDATING:
      state.semaphore.updating.push(get__default['default'](action, 'id', 'notValid')); // Make all keys uniq

      state.semaphore.updating = uniq__default['default'](state.semaphore.updating);
      break;

    case SemaphoreTypes.DELETING:
      state.semaphore.deleting.push(get__default['default'](action, 'id', 'notValid')); // Make all keys uniq

      state.semaphore.deleting = uniq__default['default'](state.semaphore.deleting);
      break;
  }
}), _defineProperty(_moduleMutations$7, 'CLEAR_SEMAPHORE', function CLEAR_SEMAPHORE(state, action) {
  switch (action.type) {
    case SemaphoreTypes.FETCHING:
      state.semaphore.fetching.items = false;
      break;

    case SemaphoreTypes.GETTING:
      // Process all semaphore items
      state.semaphore.fetching.item.forEach(function (item, index) {
        // Find created item in reading one item semaphore...
        if (item === get__default['default'](action, 'id', 'notValid')) {
          // ...and remove it
          state.semaphore.fetching.item.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.CREATING:
      // Process all semaphore items
      state.semaphore.creating.forEach(function (item, index) {
        // Find created item in creating semaphore...
        if (item === get__default['default'](action, 'id', 'notValid')) {
          // ...and remove it
          state.semaphore.creating.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.UPDATING:
      // Process all semaphore items
      state.semaphore.updating.forEach(function (item, index) {
        // Find created item in updating semaphore...
        if (item === get__default['default'](action, 'id', 'notValid')) {
          // ...and remove it
          state.semaphore.updating.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.DELETING:
      // Process all semaphore items
      state.semaphore.deleting.forEach(function (item, index) {
        // Find removed item in removing semaphore...
        if (item === get__default['default'](action, 'id', 'notValid')) {
          // ...and remove it
          state.semaphore.deleting.splice(index, 1);
        }
      });
      break;
  }
}), _defineProperty(_moduleMutations$7, 'RESET_STATE', function RESET_STATE(state) {
  Object.assign(state, moduleState$7);
}), _moduleMutations$7);
var devices = {
  state: function state() {
    return moduleState$7;
  },
  getters: moduleGetters$2,
  actions: moduleActions$7,
  mutations: moduleMutations$7
};var _moduleMutations$6;
var jsonApiFormatter$6 = new Jsona__default['default']({
  modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
  jsonPropertiesMapper: new JsonApiPropertiesMapper()
});
var apiOptions$6 = {
  dataTransformer: function dataTransformer(result) {
    return jsonApiFormatter$6.deserialize(result.data);
  }
};
var jsonSchemaValidator$6 = new Ajv__default['default']();
var moduleState$6 = {
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
var moduleActions$6 = {
  get: function get(_ref, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
      var state, commit;
      return regeneratorRuntime.wrap(function _callee$(_context) {
        while (1) {
          switch (_context.prev = _context.next) {
            case 0:
              state = _ref.state, commit = _ref.commit;

              if (!state.semaphore.fetching.item.includes(payload.id)) {
                _context.next = 3;
                break;
              }

              return _context.abrupt("return", false);

            case 3:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.GETTING,
                id: payload.id
              });
              _context.prev = 4;
              _context.next = 7;
              return DeviceProperty.api().get("".concat(ModuleApiPrefix, "/v1/devices/").concat(payload.device.id, "/properties/").concat(payload.id), apiOptions$6);

            case 7:
              return _context.abrupt("return", true);

            case 10:
              _context.prev = 10;
              _context.t0 = _context["catch"](4);
              throw new ApiError('devices-module.device-properties.fetch.failed', _context.t0, 'Fetching device property failed.');

            case 13:
              _context.prev = 13;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.GETTING,
                id: payload.id
              });
              return _context.finish(13);

            case 16:
            case "end":
              return _context.stop();
          }
        }
      }, _callee, null, [[4, 10, 13, 16]]);
    }))();
  },
  fetch: function fetch(_ref2, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2() {
      var state, commit;
      return regeneratorRuntime.wrap(function _callee2$(_context2) {
        while (1) {
          switch (_context2.prev = _context2.next) {
            case 0:
              state = _ref2.state, commit = _ref2.commit;

              if (!state.semaphore.fetching.items.includes(payload.device.id)) {
                _context2.next = 3;
                break;
              }

              return _context2.abrupt("return", false);

            case 3:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.FETCHING,
                id: payload.device.id
              });
              _context2.prev = 4;
              _context2.next = 7;
              return DeviceProperty.api().get("".concat(ModuleApiPrefix, "/v1/devices/").concat(payload.device.id, "/properties"), apiOptions$6);

            case 7:
              return _context2.abrupt("return", true);

            case 10:
              _context2.prev = 10;
              _context2.t0 = _context2["catch"](4);
              throw new ApiError('devices-module.device-properties.fetch.failed', _context2.t0, 'Fetching device properties failed.');

            case 13:
              _context2.prev = 13;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.FETCHING,
                id: payload.device.id
              });
              return _context2.finish(13);

            case 16:
            case "end":
              return _context2.stop();
          }
        }
      }, _callee2, null, [[4, 10, 13, 16]]);
    }))();
  },
  edit: function edit(_ref3, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3() {
      var state, commit, updatedEntity, device, _device;

      return regeneratorRuntime.wrap(function _callee3$(_context3) {
        while (1) {
          switch (_context3.prev = _context3.next) {
            case 0:
              state = _ref3.state, commit = _ref3.commit;

              if (!state.semaphore.updating.includes(payload.property.id)) {
                _context3.next = 3;
                break;
              }

              throw new Error('devices-module.device-properties.update.inProgress');

            case 3:
              if (DeviceProperty.query().where('id', payload.property.id).exists()) {
                _context3.next = 5;
                break;
              }

              throw new Error('devices-module.device-properties.update.failed');

            case 5:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.property.id
              });
              _context3.prev = 6;
              _context3.next = 9;
              return DeviceProperty.update({
                where: payload.property.id,
                data: payload.data
              });

            case 9:
              _context3.next = 15;
              break;

            case 11:
              _context3.prev = 11;
              _context3.t0 = _context3["catch"](6);
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.property.id
              });
              throw new OrmError('devices-module.device-properties.update.failed', _context3.t0, 'Edit device property failed.');

            case 15:
              updatedEntity = DeviceProperty.find(payload.property.id);

              if (!(updatedEntity === null)) {
                _context3.next = 23;
                break;
              }

              device = Device.find(payload.property.deviceId);

              if (!(device !== null)) {
                _context3.next = 21;
                break;
              }

              _context3.next = 21;
              return DeviceProperty.get(device, payload.property.id);

            case 21:
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.property.id
              });
              throw new Error('devices-module.device-properties.update.failed');

            case 23:
              _context3.prev = 23;
              _context3.next = 26;
              return DeviceProperty.api().patch("".concat(ModuleApiPrefix, "/v1/devices/").concat(updatedEntity.deviceId, "/properties/").concat(updatedEntity.id), jsonApiFormatter$6.serialize({
                stuff: updatedEntity
              }), apiOptions$6);

            case 26:
              return _context3.abrupt("return", DeviceProperty.find(payload.property.id));

            case 29:
              _context3.prev = 29;
              _context3.t1 = _context3["catch"](23);
              _device = Device.find(payload.property.deviceId);

              if (!(_device !== null)) {
                _context3.next = 35;
                break;
              }

              _context3.next = 35;
              return DeviceProperty.get(_device, payload.property.id);

            case 35:
              throw new ApiError('devices-module.device-properties.update.failed', _context3.t1, 'Edit device property failed.');

            case 36:
              _context3.prev = 36;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.property.id
              });
              return _context3.finish(36);

            case 39:
            case "end":
              return _context3.stop();
          }
        }
      }, _callee3, null, [[6, 11], [23, 29, 36, 39]]);
    }))();
  },
  transmitData: function transmitData(_store, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee4() {
      var device, backupValue;
      return regeneratorRuntime.wrap(function _callee4$(_context4) {
        while (1) {
          switch (_context4.prev = _context4.next) {
            case 0:
              if (DeviceProperty.query().where('id', payload.property.id).exists()) {
                _context4.next = 2;
                break;
              }

              throw new Error('devices-module.device-properties.transmit.failed');

            case 2:
              device = Device.find(payload.property.deviceId);

              if (!(device === null)) {
                _context4.next = 5;
                break;
              }

              throw new Error('devices-module.device-properties.transmit.failed');

            case 5:
              backupValue = payload.property.value;
              _context4.prev = 6;
              _context4.next = 9;
              return DeviceProperty.update({
                where: payload.property.id,
                data: {
                  value: payload.value
                }
              });

            case 9:
              _context4.next = 14;
              break;

            case 11:
              _context4.prev = 11;
              _context4.t0 = _context4["catch"](6);
              throw new OrmError('devices-module.device-properties.transmit.failed', _context4.t0, 'Edit device property failed.');

            case 14:
              return _context4.abrupt("return", new Promise(function (resolve, reject) {
                DeviceProperty.wamp().call({
                  routing_key: modulesMetadata.DevicesModule.DEVICES_PROPERTIES_DATA,
                  origin: DeviceProperty.$devicesModuleOrigin,
                  data: {
                    device: device.key,
                    property: payload.property.key,
                    expected: payload.value
                  }
                }).then(function (response) {
                  if (get__default['default'](response.data, 'response') === 'accepted') {
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
                }).catch(function () {
                  DeviceProperty.update({
                    where: payload.property.id,
                    data: {
                      value: backupValue
                    }
                  });
                  reject(new Error('devices-module.device-properties.transmit.failed'));
                });
              }));

            case 15:
            case "end":
              return _context4.stop();
          }
        }
      }, _callee4, null, [[6, 11]]);
    }))();
  },
  socketData: function socketData(_ref4, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee5() {
      var state, commit, body, validate, entityData, failedEntity;
      return regeneratorRuntime.wrap(function _callee5$(_context5) {
        while (1) {
          switch (_context5.prev = _context5.next) {
            case 0:
              state = _ref4.state, commit = _ref4.commit;

              if (!(payload.origin !== modulesMetadata.ModuleOrigin.MODULE_DEVICES_ORIGIN)) {
                _context5.next = 3;
                break;
              }

              return _context5.abrupt("return", false);

            case 3:
              if ([modulesMetadata.DevicesModule.DEVICES_PROPERTY_CREATED_ENTITY, modulesMetadata.DevicesModule.DEVICES_PROPERTY_UPDATED_ENTITY, modulesMetadata.DevicesModule.DEVICES_PROPERTY_DELETED_ENTITY].includes(payload.routingKey)) {
                _context5.next = 5;
                break;
              }

              return _context5.abrupt("return", false);

            case 5:
              body = JSON.parse(payload.data);
              validate = jsonSchemaValidator$6.compile(exchangeEntitySchema__namespace$1);

              if (!validate(body)) {
                _context5.next = 48;
                break;
              }

              if (!(!DeviceProperty.query().where('id', body.id).exists() && (payload.routingKey === modulesMetadata.DevicesModule.DEVICES_PROPERTY_UPDATED_ENTITY || payload.routingKey === modulesMetadata.DevicesModule.DEVICES_PROPERTY_DELETED_ENTITY))) {
                _context5.next = 10;
                break;
              }

              throw new Error('devices-module.device-properties.update.failed');

            case 10:
              if (!(payload.routingKey === modulesMetadata.DevicesModule.DEVICES_PROPERTY_DELETED_ENTITY)) {
                _context5.next = 25;
                break;
              }

              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.DELETING,
                id: body.id
              });
              _context5.prev = 12;
              _context5.next = 15;
              return DeviceProperty.delete(body.id);

            case 15:
              _context5.next = 20;
              break;

            case 17:
              _context5.prev = 17;
              _context5.t0 = _context5["catch"](12);
              throw new OrmError('devices-module.device-properties.delete.failed', _context5.t0, 'Delete device property failed.');

            case 20:
              _context5.prev = 20;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.DELETING,
                id: body.id
              });
              return _context5.finish(20);

            case 23:
              _context5.next = 45;
              break;

            case 25:
              if (!(payload.routingKey === modulesMetadata.DevicesModule.DEVICES_PROPERTY_UPDATED_ENTITY && state.semaphore.updating.includes(body.id))) {
                _context5.next = 27;
                break;
              }

              return _context5.abrupt("return", true);

            case 27:
              commit('SET_SEMAPHORE', {
                type: payload.routingKey === modulesMetadata.DevicesModule.DEVICES_PROPERTY_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
                id: body.id
              });
              entityData = {
                type: exports.DevicePropertyEntityTypes.PROPERTY
              };
              Object.keys(body).forEach(function (attrName) {
                var kebabName = attrName.replace(/([a-z][A-Z0-9])/g, function (g) {
                  return "".concat(g[0], "_").concat(g[1].toLowerCase());
                });

                if (kebabName === 'device') {
                  var device = Device.query().where('identifier', body[attrName]).first();

                  if (device !== null) {
                    entityData.deviceId = device.id;
                  }
                } else {
                  entityData[kebabName] = body[attrName];
                }
              });
              _context5.prev = 30;
              _context5.next = 33;
              return DeviceProperty.insertOrUpdate({
                data: entityData
              });

            case 33:
              _context5.next = 42;
              break;

            case 35:
              _context5.prev = 35;
              _context5.t1 = _context5["catch"](30);
              failedEntity = DeviceProperty.query().with('device').where('id', body.id).first();

              if (!(failedEntity !== null && failedEntity.device !== null)) {
                _context5.next = 41;
                break;
              }

              _context5.next = 41;
              return DeviceProperty.get(failedEntity.device, body.id);

            case 41:
              throw new OrmError('devices-module.device-properties.update.failed', _context5.t1, 'Edit device property failed.');

            case 42:
              _context5.prev = 42;
              commit('CLEAR_SEMAPHORE', {
                type: payload.routingKey === modulesMetadata.DevicesModule.DEVICES_PROPERTY_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
                id: body.id
              });
              return _context5.finish(42);

            case 45:
              return _context5.abrupt("return", true);

            case 48:
              return _context5.abrupt("return", false);

            case 49:
            case "end":
              return _context5.stop();
          }
        }
      }, _callee5, null, [[12, 17, 20, 23], [30, 35, 42, 45]]);
    }))();
  },
  reset: function reset(_ref5) {
    var commit = _ref5.commit;
    commit('RESET_STATE');
  }
};
var moduleMutations$6 = (_moduleMutations$6 = {}, _defineProperty(_moduleMutations$6, 'SET_SEMAPHORE', function SET_SEMAPHORE(state, action) {
  switch (action.type) {
    case SemaphoreTypes.FETCHING:
      state.semaphore.fetching.items.push(action.id); // Make all keys uniq

      state.semaphore.fetching.items = uniq__default['default'](state.semaphore.fetching.items);
      break;

    case SemaphoreTypes.GETTING:
      state.semaphore.fetching.item.push(action.id); // Make all keys uniq

      state.semaphore.fetching.item = uniq__default['default'](state.semaphore.fetching.item);
      break;

    case SemaphoreTypes.CREATING:
      state.semaphore.creating.push(action.id); // Make all keys uniq

      state.semaphore.creating = uniq__default['default'](state.semaphore.creating);
      break;

    case SemaphoreTypes.UPDATING:
      state.semaphore.updating.push(action.id); // Make all keys uniq

      state.semaphore.updating = uniq__default['default'](state.semaphore.updating);
      break;

    case SemaphoreTypes.DELETING:
      state.semaphore.deleting.push(action.id); // Make all keys uniq

      state.semaphore.deleting = uniq__default['default'](state.semaphore.deleting);
      break;
  }
}), _defineProperty(_moduleMutations$6, 'CLEAR_SEMAPHORE', function CLEAR_SEMAPHORE(state, action) {
  switch (action.type) {
    case SemaphoreTypes.FETCHING:
      // Process all semaphore items
      state.semaphore.fetching.items.forEach(function (item, index) {
        // Find created item in reading one item semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.fetching.items.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.GETTING:
      // Process all semaphore items
      state.semaphore.fetching.item.forEach(function (item, index) {
        // Find created item in reading one item semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.fetching.item.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.CREATING:
      // Process all semaphore items
      state.semaphore.creating.forEach(function (item, index) {
        // Find created item in creating semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.creating.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.UPDATING:
      // Process all semaphore items
      state.semaphore.updating.forEach(function (item, index) {
        // Find created item in updating semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.updating.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.DELETING:
      // Process all semaphore items
      state.semaphore.deleting.forEach(function (item, index) {
        // Find removed item in removing semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.deleting.splice(index, 1);
        }
      });
      break;
  }
}), _defineProperty(_moduleMutations$6, 'RESET_STATE', function RESET_STATE(state) {
  Object.assign(state, moduleState$6);
}), _moduleMutations$6);
var deviceProperties = {
  state: function state() {
    return moduleState$6;
  },
  actions: moduleActions$6,
  mutations: moduleMutations$6
};var _moduleMutations$5;
var jsonApiFormatter$5 = new Jsona__default['default']({
  modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
  jsonPropertiesMapper: new JsonApiPropertiesMapper()
});
var apiOptions$5 = {
  dataTransformer: function dataTransformer(result) {
    return jsonApiFormatter$5.deserialize(result.data);
  }
};
var jsonSchemaValidator$5 = new Ajv__default['default']();
var moduleState$5 = {
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
var moduleActions$5 = {
  get: function get(_ref, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
      var state, commit;
      return regeneratorRuntime.wrap(function _callee$(_context) {
        while (1) {
          switch (_context.prev = _context.next) {
            case 0:
              state = _ref.state, commit = _ref.commit;

              if (!state.semaphore.fetching.item.includes(payload.id)) {
                _context.next = 3;
                break;
              }

              return _context.abrupt("return", false);

            case 3:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.GETTING,
                id: payload.id
              });
              _context.prev = 4;
              _context.next = 7;
              return DeviceConfiguration.api().get("".concat(ModuleApiPrefix, "/v1/devices/").concat(payload.device.id, "/configuration/").concat(payload.id), apiOptions$5);

            case 7:
              return _context.abrupt("return", true);

            case 10:
              _context.prev = 10;
              _context.t0 = _context["catch"](4);
              throw new ApiError('devices-module.device-configuration.fetch.failed', _context.t0, 'Fetching device configuration failed.');

            case 13:
              _context.prev = 13;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.GETTING,
                id: payload.id
              });
              return _context.finish(13);

            case 16:
            case "end":
              return _context.stop();
          }
        }
      }, _callee, null, [[4, 10, 13, 16]]);
    }))();
  },
  fetch: function fetch(_ref2, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2() {
      var state, commit;
      return regeneratorRuntime.wrap(function _callee2$(_context2) {
        while (1) {
          switch (_context2.prev = _context2.next) {
            case 0:
              state = _ref2.state, commit = _ref2.commit;

              if (!state.semaphore.fetching.items.includes(payload.device.id)) {
                _context2.next = 3;
                break;
              }

              return _context2.abrupt("return", false);

            case 3:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.FETCHING,
                id: payload.device.id
              });
              _context2.prev = 4;
              _context2.next = 7;
              return DeviceConfiguration.api().get("".concat(ModuleApiPrefix, "/v1/devices/").concat(payload.device.id, "/configuration"), apiOptions$5);

            case 7:
              return _context2.abrupt("return", true);

            case 10:
              _context2.prev = 10;
              _context2.t0 = _context2["catch"](4);
              throw new ApiError('devices-module.device-configuration.fetch.failed', _context2.t0, 'Fetching device configuration failed.');

            case 13:
              _context2.prev = 13;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.FETCHING,
                id: payload.device.id
              });
              return _context2.finish(13);

            case 16:
            case "end":
              return _context2.stop();
          }
        }
      }, _callee2, null, [[4, 10, 13, 16]]);
    }))();
  },
  edit: function edit(_ref3, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3() {
      var state, commit, updatedEntity, _device, device;

      return regeneratorRuntime.wrap(function _callee3$(_context3) {
        while (1) {
          switch (_context3.prev = _context3.next) {
            case 0:
              state = _ref3.state, commit = _ref3.commit;

              if (!state.semaphore.updating.includes(payload.configuration.id)) {
                _context3.next = 3;
                break;
              }

              throw new Error('devices-module.device-configuration.update.inProgress');

            case 3:
              if (DeviceConfiguration.query().where('id', payload.configuration.id).exists()) {
                _context3.next = 5;
                break;
              }

              throw new Error('devices-module.device-configuration.update.failed');

            case 5:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.configuration.id
              });
              _context3.prev = 6;
              _context3.next = 9;
              return DeviceConfiguration.update({
                where: payload.configuration.id,
                data: payload.data
              });

            case 9:
              _context3.next = 15;
              break;

            case 11:
              _context3.prev = 11;
              _context3.t0 = _context3["catch"](6);
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.configuration.id
              });
              throw new OrmError('devices-module.device-configuration.update.failed', _context3.t0, 'Edit device configuration failed.');

            case 15:
              updatedEntity = DeviceConfiguration.find(payload.configuration.id);

              if (!(updatedEntity === null)) {
                _context3.next = 23;
                break;
              }

              _device = Device.find(payload.configuration.deviceId);

              if (!(_device !== null)) {
                _context3.next = 21;
                break;
              }

              _context3.next = 21;
              return DeviceConfiguration.get(_device, payload.configuration.id);

            case 21:
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.configuration.id
              });
              throw new Error('devices-module.device-configuration.update.failed');

            case 23:
              device = Device.find(payload.configuration.deviceId);

              if (!(device === null)) {
                _context3.next = 26;
                break;
              }

              throw new Error('devices-module.device-configuration.update.failed');

            case 26:
              _context3.prev = 26;
              _context3.next = 29;
              return DeviceConfiguration.api().patch("".concat(ModuleApiPrefix, "/v1/devices/").concat(device.id, "/configuration/").concat(updatedEntity.id), jsonApiFormatter$5.serialize({
                stuff: updatedEntity
              }), apiOptions$5);

            case 29:
              return _context3.abrupt("return", DeviceConfiguration.find(payload.configuration.id));

            case 32:
              _context3.prev = 32;
              _context3.t1 = _context3["catch"](26);
              _context3.next = 36;
              return DeviceConfiguration.get(device, payload.configuration.id);

            case 36:
              throw new ApiError('devices-module.device-configuration.update.failed', _context3.t1, 'Edit device configuration failed.');

            case 37:
              _context3.prev = 37;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.configuration.id
              });
              return _context3.finish(37);

            case 40:
            case "end":
              return _context3.stop();
          }
        }
      }, _callee3, null, [[6, 11], [26, 32, 37, 40]]);
    }))();
  },
  transmitData: function transmitData(_store, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee4() {
      var device, backupValue;
      return regeneratorRuntime.wrap(function _callee4$(_context4) {
        while (1) {
          switch (_context4.prev = _context4.next) {
            case 0:
              if (DeviceConfiguration.query().where('id', payload.configuration.id).exists()) {
                _context4.next = 2;
                break;
              }

              throw new Error('devices-module.device-configuration.transmit.failed');

            case 2:
              device = Device.find(payload.configuration.deviceId);

              if (!(device === null)) {
                _context4.next = 5;
                break;
              }

              throw new Error('devices-module.device-configuration.transmit.failed');

            case 5:
              backupValue = payload.configuration.value;
              _context4.prev = 6;
              _context4.next = 9;
              return DeviceConfiguration.update({
                where: payload.configuration.id,
                data: {
                  value: payload.value
                }
              });

            case 9:
              _context4.next = 14;
              break;

            case 11:
              _context4.prev = 11;
              _context4.t0 = _context4["catch"](6);
              throw new OrmError('devices-module.device-configuration.transmit.failed', _context4.t0, 'Edit device configuration failed.');

            case 14:
              return _context4.abrupt("return", new Promise(function (resolve, reject) {
                DeviceConfiguration.wamp().call({
                  routing_key: modulesMetadata.DevicesModule.DEVICES_CONFIGURATION_DATA,
                  origin: DeviceConfiguration.$devicesModuleOrigin,
                  data: {
                    device: device.key,
                    configuration: payload.configuration.key,
                    expected: payload.value
                  }
                }).then(function (response) {
                  if (get__default['default'](response.data, 'response') === 'accepted') {
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
                }).catch(function () {
                  DeviceConfiguration.update({
                    where: payload.configuration.id,
                    data: {
                      value: backupValue
                    }
                  });
                  reject(new Error('devices-module.device-configuration.transmit.failed'));
                });
              }));

            case 15:
            case "end":
              return _context4.stop();
          }
        }
      }, _callee4, null, [[6, 11]]);
    }))();
  },
  socketData: function socketData(_ref4, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee5() {
      var state, commit, body, validate, entityData, failedEntity;
      return regeneratorRuntime.wrap(function _callee5$(_context5) {
        while (1) {
          switch (_context5.prev = _context5.next) {
            case 0:
              state = _ref4.state, commit = _ref4.commit;

              if (!(payload.origin !== modulesMetadata.ModuleOrigin.MODULE_DEVICES_ORIGIN)) {
                _context5.next = 3;
                break;
              }

              return _context5.abrupt("return", false);

            case 3:
              if ([modulesMetadata.DevicesModule.DEVICES_CONFIGURATION_CREATED_ENTITY, modulesMetadata.DevicesModule.DEVICES_CONFIGURATION_UPDATED_ENTITY, modulesMetadata.DevicesModule.DEVICES_CONFIGURATION_DELETED_ENTITY].includes(payload.routingKey)) {
                _context5.next = 5;
                break;
              }

              return _context5.abrupt("return", false);

            case 5:
              body = JSON.parse(payload.data);
              validate = jsonSchemaValidator$5.compile(exchangeEntitySchema__namespace$2);

              if (!validate(body)) {
                _context5.next = 48;
                break;
              }

              if (!(!DeviceConfiguration.query().where('id', body.id).exists() && (payload.routingKey === modulesMetadata.DevicesModule.DEVICES_CONFIGURATION_UPDATED_ENTITY || payload.routingKey === modulesMetadata.DevicesModule.DEVICES_CONFIGURATION_DELETED_ENTITY))) {
                _context5.next = 10;
                break;
              }

              throw new Error('devices-module.device-configuration.update.failed');

            case 10:
              if (!(payload.routingKey === modulesMetadata.DevicesModule.DEVICES_CONFIGURATION_DELETED_ENTITY)) {
                _context5.next = 25;
                break;
              }

              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.DELETING,
                id: body.id
              });
              _context5.prev = 12;
              _context5.next = 15;
              return DeviceConfiguration.delete(body.id);

            case 15:
              _context5.next = 20;
              break;

            case 17:
              _context5.prev = 17;
              _context5.t0 = _context5["catch"](12);
              throw new OrmError('devices-module.device-configuration.delete.failed', _context5.t0, 'Delete device configuration failed.');

            case 20:
              _context5.prev = 20;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.DELETING,
                id: body.id
              });
              return _context5.finish(20);

            case 23:
              _context5.next = 45;
              break;

            case 25:
              if (!(payload.routingKey === modulesMetadata.DevicesModule.DEVICES_CONFIGURATION_UPDATED_ENTITY && state.semaphore.updating.includes(body.id))) {
                _context5.next = 27;
                break;
              }

              return _context5.abrupt("return", true);

            case 27:
              commit('SET_SEMAPHORE', {
                type: payload.routingKey === modulesMetadata.DevicesModule.DEVICES_CONFIGURATION_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
                id: body.id
              });
              entityData = {
                type: exports.DeviceConfigurationEntityTypes.CONFIGURATION
              };
              Object.keys(body).forEach(function (attrName) {
                var kebabName = attrName.replace(/([a-z][A-Z0-9])/g, function (g) {
                  return "".concat(g[0], "_").concat(g[1].toLowerCase());
                });

                if (kebabName === 'device') {
                  var device = Device.query().where('device', body[attrName]).first();

                  if (device !== null) {
                    entityData.deviceId = device.id;
                  }
                } else {
                  entityData[kebabName] = body[attrName];
                }
              });
              _context5.prev = 30;
              _context5.next = 33;
              return DeviceConfiguration.insertOrUpdate({
                data: entityData
              });

            case 33:
              _context5.next = 42;
              break;

            case 35:
              _context5.prev = 35;
              _context5.t1 = _context5["catch"](30);
              failedEntity = DeviceConfiguration.query().with('device').where('id', body.id).first();

              if (!(failedEntity !== null && failedEntity.device !== null)) {
                _context5.next = 41;
                break;
              }

              _context5.next = 41;
              return DeviceConfiguration.get(failedEntity.device, body.id);

            case 41:
              throw new OrmError('devices-module.device-configuration.update.failed', _context5.t1, 'Edit device configuration failed.');

            case 42:
              _context5.prev = 42;
              commit('CLEAR_SEMAPHORE', {
                type: payload.routingKey === modulesMetadata.DevicesModule.DEVICES_CONFIGURATION_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
                id: body.id
              });
              return _context5.finish(42);

            case 45:
              return _context5.abrupt("return", true);

            case 48:
              return _context5.abrupt("return", false);

            case 49:
            case "end":
              return _context5.stop();
          }
        }
      }, _callee5, null, [[12, 17, 20, 23], [30, 35, 42, 45]]);
    }))();
  },
  reset: function reset(_ref5) {
    var commit = _ref5.commit;
    commit('RESET_STATE');
  }
};
var moduleMutations$5 = (_moduleMutations$5 = {}, _defineProperty(_moduleMutations$5, 'SET_SEMAPHORE', function SET_SEMAPHORE(state, action) {
  switch (action.type) {
    case SemaphoreTypes.FETCHING:
      state.semaphore.fetching.items.push(action.id); // Make all keys uniq

      state.semaphore.fetching.items = uniq__default['default'](state.semaphore.fetching.items);
      break;

    case SemaphoreTypes.GETTING:
      state.semaphore.fetching.item.push(action.id); // Make all keys uniq

      state.semaphore.fetching.item = uniq__default['default'](state.semaphore.fetching.item);
      break;

    case SemaphoreTypes.CREATING:
      state.semaphore.creating.push(action.id); // Make all keys uniq

      state.semaphore.creating = uniq__default['default'](state.semaphore.creating);
      break;

    case SemaphoreTypes.UPDATING:
      state.semaphore.updating.push(action.id); // Make all keys uniq

      state.semaphore.updating = uniq__default['default'](state.semaphore.updating);
      break;

    case SemaphoreTypes.DELETING:
      state.semaphore.deleting.push(action.id); // Make all keys uniq

      state.semaphore.deleting = uniq__default['default'](state.semaphore.deleting);
      break;
  }
}), _defineProperty(_moduleMutations$5, 'CLEAR_SEMAPHORE', function CLEAR_SEMAPHORE(state, action) {
  switch (action.type) {
    case SemaphoreTypes.FETCHING:
      // Process all semaphore items
      state.semaphore.fetching.items.forEach(function (item, index) {
        // Find created item in reading one item semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.fetching.items.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.GETTING:
      // Process all semaphore items
      state.semaphore.fetching.item.forEach(function (item, index) {
        // Find created item in reading one item semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.fetching.item.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.CREATING:
      // Process all semaphore items
      state.semaphore.creating.forEach(function (item, index) {
        // Find created item in creating semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.creating.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.UPDATING:
      // Process all semaphore items
      state.semaphore.updating.forEach(function (item, index) {
        // Find created item in updating semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.updating.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.DELETING:
      // Process all semaphore items
      state.semaphore.deleting.forEach(function (item, index) {
        // Find removed item in removing semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.deleting.splice(index, 1);
        }
      });
      break;
  }
}), _defineProperty(_moduleMutations$5, 'RESET_STATE', function RESET_STATE(state) {
  Object.assign(state, moduleState$5);
}), _moduleMutations$5);
var devicesConfiguration = {
  state: function state() {
    return moduleState$5;
  },
  actions: moduleActions$5,
  mutations: moduleMutations$5
};var _moduleMutations$4;
var jsonApiFormatter$4 = new Jsona__default['default']({
  modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
  jsonPropertiesMapper: new JsonApiPropertiesMapper()
});
var apiOptions$4 = {
  dataTransformer: function dataTransformer(result) {
    return jsonApiFormatter$4.deserialize(result.data);
  }
};
var jsonSchemaValidator$4 = new Ajv__default['default']();
var moduleState$4 = {
  semaphore: {
    fetching: {
      item: []
    },
    creating: [],
    updating: [],
    deleting: []
  }
};
var moduleActions$4 = {
  get: function get(_ref, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
      var state, commit;
      return regeneratorRuntime.wrap(function _callee$(_context) {
        while (1) {
          switch (_context.prev = _context.next) {
            case 0:
              state = _ref.state, commit = _ref.commit;

              if (!state.semaphore.fetching.item.includes(payload.device.id)) {
                _context.next = 3;
                break;
              }

              return _context.abrupt("return", false);

            case 3:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.GETTING,
                id: payload.device.id
              });
              _context.prev = 4;
              _context.next = 7;
              return DeviceConnector.api().get("".concat(ModuleApiPrefix, "/v1/devices/").concat(payload.device.id, "/connector"), apiOptions$4);

            case 7:
              return _context.abrupt("return", true);

            case 10:
              _context.prev = 10;
              _context.t0 = _context["catch"](4);
              throw new ApiError('devices-module.device-connector.get.failed', _context.t0, 'Fetching device connector failed.');

            case 13:
              _context.prev = 13;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.GETTING,
                id: payload.device.id
              });
              return _context.finish(13);

            case 16:
            case "end":
              return _context.stop();
          }
        }
      }, _callee, null, [[4, 10, 13, 16]]);
    }))();
  },
  add: function add(_ref2, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2() {
      var commit, id, draft, createdEntity;
      return regeneratorRuntime.wrap(function _callee2$(_context2) {
        while (1) {
          switch (_context2.prev = _context2.next) {
            case 0:
              commit = _ref2.commit;
              id = typeof payload.id !== 'undefined' && payload.id !== null && payload.id !== '' ? payload.id : uuid.v4().toString();
              draft = typeof payload.draft !== 'undefined' ? payload.draft : false;
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.CREATING,
                id: id
              });
              _context2.prev = 4;
              _context2.next = 7;
              return DeviceConnector.insert({
                data: Object.assign({}, payload.data, {
                  id: id,
                  draft: draft,
                  deviceId: payload.device.id,
                  connectorId: payload.connector.id
                })
              });

            case 7:
              _context2.next = 13;
              break;

            case 9:
              _context2.prev = 9;
              _context2.t0 = _context2["catch"](4);
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.CREATING,
                id: id
              });
              throw new OrmError('devices-module.device-connector.create.failed', _context2.t0, 'Create device connector failed.');

            case 13:
              createdEntity = DeviceConnector.find(id);

              if (!(createdEntity === null)) {
                _context2.next = 19;
                break;
              }

              _context2.next = 17;
              return DeviceConnector.delete(id);

            case 17:
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.CREATING,
                id: id
              });
              throw new Error('devices-module.device-connector.create.failed');

            case 19:
              if (!draft) {
                _context2.next = 24;
                break;
              }

              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.CREATING,
                id: id
              });
              return _context2.abrupt("return", DeviceConnector.find(id));

            case 24:
              _context2.prev = 24;
              _context2.next = 27;
              return DeviceConnector.api().post("".concat(ModuleApiPrefix, "/v1/devices/").concat(payload.device.id, "/connector"), jsonApiFormatter$4.serialize({
                stuff: createdEntity
              }), apiOptions$4);

            case 27:
              return _context2.abrupt("return", DeviceConnector.find(id));

            case 30:
              _context2.prev = 30;
              _context2.t1 = _context2["catch"](24);
              _context2.next = 34;
              return DeviceConnector.delete(id);

            case 34:
              throw new ApiError('devices-module.device-connector.create.failed', _context2.t1, 'Create device connector failed.');

            case 35:
              _context2.prev = 35;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.CREATING,
                id: id
              });
              return _context2.finish(35);

            case 38:
            case "end":
              return _context2.stop();
          }
        }
      }, _callee2, null, [[4, 9], [24, 30, 35, 38]]);
    }))();
  },
  edit: function edit(_ref3, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3() {
      var state, commit, updatedEntity, device, _device;

      return regeneratorRuntime.wrap(function _callee3$(_context3) {
        while (1) {
          switch (_context3.prev = _context3.next) {
            case 0:
              state = _ref3.state, commit = _ref3.commit;

              if (!state.semaphore.updating.includes(payload.connector.id)) {
                _context3.next = 3;
                break;
              }

              throw new Error('devices-module.device-connector.update.inProgress');

            case 3:
              if (DeviceConnector.query().where('id', payload.connector.id).exists()) {
                _context3.next = 5;
                break;
              }

              throw new Error('devices-module.device-connector.update.failed');

            case 5:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.connector.id
              });
              _context3.prev = 6;
              _context3.next = 9;
              return DeviceConnector.update({
                where: payload.connector.id,
                data: payload.data
              });

            case 9:
              _context3.next = 15;
              break;

            case 11:
              _context3.prev = 11;
              _context3.t0 = _context3["catch"](6);
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.connector.id
              });
              throw new OrmError('devices-module.device-connector.update.failed', _context3.t0, 'Edit device connector failed.');

            case 15:
              updatedEntity = DeviceConnector.find(payload.connector.id);

              if (!(updatedEntity === null)) {
                _context3.next = 23;
                break;
              }

              device = Device.find(payload.connector.deviceId);

              if (!(device !== null)) {
                _context3.next = 21;
                break;
              }

              _context3.next = 21;
              return DeviceConnector.get(device);

            case 21:
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.connector.id
              });
              throw new Error('devices-module.device-connector.update.failed');

            case 23:
              if (!updatedEntity.draft) {
                _context3.next = 28;
                break;
              }

              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.connector.id
              });
              return _context3.abrupt("return", DeviceConnector.find(payload.connector.id));

            case 28:
              _context3.prev = 28;
              _context3.next = 31;
              return DeviceConnector.api().patch("".concat(ModuleApiPrefix, "/v1/devices/").concat(updatedEntity.deviceId, "/connector"), jsonApiFormatter$4.serialize({
                stuff: updatedEntity
              }), apiOptions$4);

            case 31:
              return _context3.abrupt("return", DeviceConnector.find(payload.connector.id));

            case 34:
              _context3.prev = 34;
              _context3.t1 = _context3["catch"](28);
              _device = Device.find(payload.connector.deviceId);

              if (!(_device !== null)) {
                _context3.next = 40;
                break;
              }

              _context3.next = 40;
              return DeviceConnector.get(_device);

            case 40:
              throw new ApiError('devices-module.device-connector.update.failed', _context3.t1, 'Edit device connector failed.');

            case 41:
              _context3.prev = 41;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.connector.id
              });
              return _context3.finish(41);

            case 44:
            case "end":
              return _context3.stop();
          }
        }
      }, _callee3, null, [[6, 11], [28, 34, 41, 44]]);
    }))();
  },
  save: function save(_ref4, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee4() {
      var state, commit, entityToSave;
      return regeneratorRuntime.wrap(function _callee4$(_context4) {
        while (1) {
          switch (_context4.prev = _context4.next) {
            case 0:
              state = _ref4.state, commit = _ref4.commit;

              if (!state.semaphore.updating.includes(payload.connector.id)) {
                _context4.next = 3;
                break;
              }

              throw new Error('devices-module.device-connector.save.inProgress');

            case 3:
              if (DeviceConnector.query().where('id', payload.connector.id).where('draft', true).exists()) {
                _context4.next = 5;
                break;
              }

              throw new Error('devices-module.device-connector.save.failed');

            case 5:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.connector.id
              });
              entityToSave = DeviceConnector.find(payload.connector.id);

              if (!(entityToSave === null)) {
                _context4.next = 10;
                break;
              }

              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.connector.id
              });
              throw new Error('devices-module.device-connector.save.failed');

            case 10:
              _context4.prev = 10;
              _context4.next = 13;
              return DeviceConnector.api().post("".concat(ModuleApiPrefix, "/v1/devices/").concat(entityToSave.deviceId, "/connector"), jsonApiFormatter$4.serialize({
                stuff: entityToSave
              }), apiOptions$4);

            case 13:
              return _context4.abrupt("return", DeviceConnector.find(payload.connector.id));

            case 16:
              _context4.prev = 16;
              _context4.t0 = _context4["catch"](10);
              throw new ApiError('devices-module.device-connector.save.failed', _context4.t0, 'Save draft device connector failed.');

            case 19:
              _context4.prev = 19;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.connector.id
              });
              return _context4.finish(19);

            case 22:
            case "end":
              return _context4.stop();
          }
        }
      }, _callee4, null, [[10, 16, 19, 22]]);
    }))();
  },
  socketData: function socketData(_ref5, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee5() {
      var state, commit, body, validate, entityData, failedEntity;
      return regeneratorRuntime.wrap(function _callee5$(_context5) {
        while (1) {
          switch (_context5.prev = _context5.next) {
            case 0:
              state = _ref5.state, commit = _ref5.commit;

              if (!(payload.origin !== modulesMetadata.ModuleOrigin.MODULE_DEVICES_ORIGIN)) {
                _context5.next = 3;
                break;
              }

              return _context5.abrupt("return", false);

            case 3:
              if ([modulesMetadata.DevicesModule.DEVICES_CONNECTOR_CREATED_ENTITY, modulesMetadata.DevicesModule.DEVICES_CONNECTOR_UPDATED_ENTITY, modulesMetadata.DevicesModule.DEVICES_CONNECTOR_DELETED_ENTITY].includes(payload.routingKey)) {
                _context5.next = 5;
                break;
              }

              return _context5.abrupt("return", false);

            case 5:
              body = JSON.parse(payload.data);
              validate = jsonSchemaValidator$4.compile(exchangeEntitySchema__namespace$3);

              if (!validate(body)) {
                _context5.next = 48;
                break;
              }

              if (!(!DeviceConnector.query().where('id', body.id).exists() && (payload.routingKey === modulesMetadata.DevicesModule.DEVICES_PROPERTY_UPDATED_ENTITY || payload.routingKey === modulesMetadata.DevicesModule.DEVICES_PROPERTY_DELETED_ENTITY))) {
                _context5.next = 10;
                break;
              }

              throw new Error('devices-module.device-connector.update.failed');

            case 10:
              if (!(payload.routingKey === modulesMetadata.DevicesModule.DEVICES_PROPERTY_DELETED_ENTITY)) {
                _context5.next = 25;
                break;
              }

              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.DELETING,
                id: body.id
              });
              _context5.prev = 12;
              _context5.next = 15;
              return DeviceConnector.delete(body.id);

            case 15:
              _context5.next = 20;
              break;

            case 17:
              _context5.prev = 17;
              _context5.t0 = _context5["catch"](12);
              throw new OrmError('devices-module.device-connector.delete.failed', _context5.t0, 'Delete device connector failed.');

            case 20:
              _context5.prev = 20;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.DELETING,
                id: body.id
              });
              return _context5.finish(20);

            case 23:
              _context5.next = 45;
              break;

            case 25:
              if (!(payload.routingKey === modulesMetadata.DevicesModule.DEVICES_PROPERTY_UPDATED_ENTITY && state.semaphore.updating.includes(body.id))) {
                _context5.next = 27;
                break;
              }

              return _context5.abrupt("return", true);

            case 27:
              commit('SET_SEMAPHORE', {
                type: payload.routingKey === modulesMetadata.DevicesModule.DEVICES_PROPERTY_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
                id: body.id
              });
              entityData = {
                type: exports.DeviceConnectorEntityTypes.CONNECTOR
              };
              Object.keys(body).forEach(function (attrName) {
                var kebabName = attrName.replace(/([a-z][A-Z0-9])/g, function (g) {
                  return "".concat(g[0], "_").concat(g[1].toLowerCase());
                });

                if (kebabName === 'device') {
                  var device = Device.query().where('identifier', body[attrName]).first();

                  if (device !== null) {
                    entityData.deviceId = device.id;
                  }
                } else {
                  entityData[kebabName] = body[attrName];
                }
              });
              _context5.prev = 30;
              _context5.next = 33;
              return DeviceConnector.insertOrUpdate({
                data: entityData
              });

            case 33:
              _context5.next = 42;
              break;

            case 35:
              _context5.prev = 35;
              _context5.t1 = _context5["catch"](30);
              failedEntity = DeviceConnector.query().with('device').where('id', body.id).first();

              if (!(failedEntity !== null && failedEntity.device !== null)) {
                _context5.next = 41;
                break;
              }

              _context5.next = 41;
              return DeviceConnector.get(failedEntity.device);

            case 41:
              throw new OrmError('devices-module.device-connector.update.failed', _context5.t1, 'Edit device connector failed.');

            case 42:
              _context5.prev = 42;
              commit('CLEAR_SEMAPHORE', {
                type: payload.routingKey === modulesMetadata.DevicesModule.DEVICES_PROPERTY_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
                id: body.id
              });
              return _context5.finish(42);

            case 45:
              return _context5.abrupt("return", true);

            case 48:
              return _context5.abrupt("return", false);

            case 49:
            case "end":
              return _context5.stop();
          }
        }
      }, _callee5, null, [[12, 17, 20, 23], [30, 35, 42, 45]]);
    }))();
  },
  reset: function reset(_ref6) {
    var commit = _ref6.commit;
    commit('RESET_STATE');
  }
};
var moduleMutations$4 = (_moduleMutations$4 = {}, _defineProperty(_moduleMutations$4, 'SET_SEMAPHORE', function SET_SEMAPHORE(state, action) {
  switch (action.type) {
    case SemaphoreTypes.GETTING:
      state.semaphore.fetching.item.push(action.id); // Make all keys uniq

      state.semaphore.fetching.item = uniq__default['default'](state.semaphore.fetching.item);
      break;

    case SemaphoreTypes.CREATING:
      state.semaphore.creating.push(action.id); // Make all keys uniq

      state.semaphore.creating = uniq__default['default'](state.semaphore.creating);
      break;

    case SemaphoreTypes.UPDATING:
      state.semaphore.updating.push(action.id); // Make all keys uniq

      state.semaphore.updating = uniq__default['default'](state.semaphore.updating);
      break;

    case SemaphoreTypes.DELETING:
      state.semaphore.deleting.push(action.id); // Make all keys uniq

      state.semaphore.deleting = uniq__default['default'](state.semaphore.deleting);
      break;
  }
}), _defineProperty(_moduleMutations$4, 'CLEAR_SEMAPHORE', function CLEAR_SEMAPHORE(state, action) {
  switch (action.type) {
    case SemaphoreTypes.GETTING:
      // Process all semaphore items
      state.semaphore.fetching.item.forEach(function (item, index) {
        // Find created item in reading one item semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.fetching.item.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.CREATING:
      // Process all semaphore items
      state.semaphore.creating.forEach(function (item, index) {
        // Find created item in creating semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.creating.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.UPDATING:
      // Process all semaphore items
      state.semaphore.updating.forEach(function (item, index) {
        // Find created item in updating semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.updating.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.DELETING:
      // Process all semaphore items
      state.semaphore.deleting.forEach(function (item, index) {
        // Find removed item in removing semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.deleting.splice(index, 1);
        }
      });
      break;
  }
}), _defineProperty(_moduleMutations$4, 'RESET_STATE', function RESET_STATE(state) {
  Object.assign(state, moduleState$4);
}), _moduleMutations$4);
var deviceConnector = {
  state: function state() {
    return moduleState$4;
  },
  actions: moduleActions$4,
  mutations: moduleMutations$4
};var _moduleMutations$3;
var jsonApiFormatter$3 = new Jsona__default['default']({
  modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
  jsonPropertiesMapper: new JsonApiPropertiesMapper()
});
var apiOptions$3 = {
  dataTransformer: function dataTransformer(result) {
    return jsonApiFormatter$3.deserialize(result.data);
  }
};
var jsonSchemaValidator$3 = new Ajv__default['default']();
var moduleState$3 = {
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
var moduleGetters$1 = {
  firstLoadFinished: function firstLoadFinished(state) {
    return function (deviceId) {
      return state.firstLoad.includes(deviceId);
    };
  },
  getting: function getting(state) {
    return function (channelId) {
      return state.semaphore.fetching.item.includes(channelId);
    };
  },
  fetching: function fetching(state) {
    return function (deviceId) {
      return deviceId !== null ? state.semaphore.fetching.items.includes(deviceId) : state.semaphore.fetching.items.length > 0;
    };
  }
};
var moduleActions$3 = {
  get: function get(_ref, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
      var state, commit;
      return regeneratorRuntime.wrap(function _callee$(_context) {
        while (1) {
          switch (_context.prev = _context.next) {
            case 0:
              state = _ref.state, commit = _ref.commit;

              if (!state.semaphore.fetching.item.includes(payload.id)) {
                _context.next = 3;
                break;
              }

              return _context.abrupt("return", false);

            case 3:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.GETTING,
                id: payload.id
              });
              _context.prev = 4;
              _context.next = 7;
              return Channel.api().get("".concat(ModuleApiPrefix, "/v1/devices/").concat(payload.device.id, "/channels/").concat(payload.id, "?include=properties,configuration"), apiOptions$3);

            case 7:
              return _context.abrupt("return", true);

            case 10:
              _context.prev = 10;
              _context.t0 = _context["catch"](4);
              throw new ApiError('devices-module.channels.get.failed', _context.t0, 'Fetching channel failed.');

            case 13:
              _context.prev = 13;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.GETTING,
                id: payload.id
              });
              return _context.finish(13);

            case 16:
            case "end":
              return _context.stop();
          }
        }
      }, _callee, null, [[4, 10, 13, 16]]);
    }))();
  },
  fetch: function fetch(_ref2, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2() {
      var state, commit;
      return regeneratorRuntime.wrap(function _callee2$(_context2) {
        while (1) {
          switch (_context2.prev = _context2.next) {
            case 0:
              state = _ref2.state, commit = _ref2.commit;

              if (!(state.semaphore.fetching.items.includes(payload.device.id) || payload.device.draft)) {
                _context2.next = 3;
                break;
              }

              return _context2.abrupt("return", false);

            case 3:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.FETCHING,
                id: payload.device.id
              });
              _context2.prev = 4;
              _context2.next = 7;
              return Channel.api().get("".concat(ModuleApiPrefix, "/v1/devices/").concat(payload.device.id, "/channels?include=properties,configuration"), apiOptions$3);

            case 7:
              commit('SET_FIRST_LOAD', {
                id: payload.device.id
              });
              return _context2.abrupt("return", true);

            case 11:
              _context2.prev = 11;
              _context2.t0 = _context2["catch"](4);
              throw new ApiError('devices-module.channels.fetch.failed', _context2.t0, 'Fetching channels failed.');

            case 14:
              _context2.prev = 14;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.FETCHING,
                id: payload.device.id
              });
              return _context2.finish(14);

            case 17:
            case "end":
              return _context2.stop();
          }
        }
      }, _callee2, null, [[4, 11, 14, 17]]);
    }))();
  },
  edit: function edit(_ref3, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3() {
      var state, commit, updatedEntity, device, _device;

      return regeneratorRuntime.wrap(function _callee3$(_context3) {
        while (1) {
          switch (_context3.prev = _context3.next) {
            case 0:
              state = _ref3.state, commit = _ref3.commit;

              if (!state.semaphore.updating.includes(payload.channel.id)) {
                _context3.next = 3;
                break;
              }

              throw new Error('devices-module.channels.update.inProgress');

            case 3:
              if (Channel.query().where('id', payload.channel.id).exists()) {
                _context3.next = 5;
                break;
              }

              throw new Error('devices-module.channels.update.failed');

            case 5:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.channel.id
              });
              _context3.prev = 6;
              _context3.next = 9;
              return Channel.update({
                where: payload.channel.id,
                data: payload.data
              });

            case 9:
              _context3.next = 15;
              break;

            case 11:
              _context3.prev = 11;
              _context3.t0 = _context3["catch"](6);
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.channel.id
              });
              throw new OrmError('devices-module.channels.edit.failed', _context3.t0, 'Edit channel failed.');

            case 15:
              updatedEntity = Channel.find(payload.channel.id);

              if (!(updatedEntity === null)) {
                _context3.next = 23;
                break;
              }

              device = Device.find(payload.channel.deviceId);

              if (!(device !== null)) {
                _context3.next = 21;
                break;
              }

              _context3.next = 21;
              return Channel.get(device, payload.channel.id);

            case 21:
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.channel.id
              });
              throw new Error('devices-module.channels.update.failed');

            case 23:
              _context3.prev = 23;
              _context3.next = 26;
              return Channel.api().patch("".concat(ModuleApiPrefix, "/v1/devices/").concat(updatedEntity.deviceId, "/channels/").concat(updatedEntity.id, "?include=properties,configuration"), jsonApiFormatter$3.serialize({
                stuff: updatedEntity
              }), apiOptions$3);

            case 26:
              return _context3.abrupt("return", Channel.find(payload.channel.id));

            case 29:
              _context3.prev = 29;
              _context3.t1 = _context3["catch"](23);
              _device = Device.find(payload.channel.deviceId);

              if (!(_device !== null)) {
                _context3.next = 35;
                break;
              }

              _context3.next = 35;
              return Channel.get(_device, payload.channel.id);

            case 35:
              throw new ApiError('devices-module.channels.update.failed', _context3.t1, 'Edit channel failed.');

            case 36:
              _context3.prev = 36;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.channel.id
              });
              return _context3.finish(36);

            case 39:
            case "end":
              return _context3.stop();
          }
        }
      }, _callee3, null, [[6, 11], [23, 29, 36, 39]]);
    }))();
  },
  transmitCommand: function transmitCommand(_store, payload) {
    if (!Channel.query().where('id', payload.channel.id).exists()) {
      throw new Error('devices-module.channel.transmit.failed');
    }

    var device = Device.find(payload.channel.deviceId);

    if (device === null) {
      throw new Error('devices-module.channel.transmit.failed');
    }

    return new Promise(function (resolve, reject) {
      Channel.wamp().call({
        routing_key: modulesMetadata.DevicesModule.CHANNELS_CONTROLS,
        origin: Channel.$devicesModuleOrigin,
        data: {
          control: payload.command,
          device: device.key,
          channel: payload.channel.key
        }
      }).then(function (response) {
        if (get__default['default'](response.data, 'response') === 'accepted') {
          resolve(true);
        } else {
          reject(new Error('devices-module.channel.transmit.failed'));
        }
      }).catch(function () {
        reject(new Error('devices-module.channel.transmit.failed'));
      });
    });
  },
  socketData: function socketData(_ref4, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee4() {
      var state, commit, body, validate, entityData, failedEntity;
      return regeneratorRuntime.wrap(function _callee4$(_context4) {
        while (1) {
          switch (_context4.prev = _context4.next) {
            case 0:
              state = _ref4.state, commit = _ref4.commit;

              if (!(payload.origin !== modulesMetadata.ModuleOrigin.MODULE_DEVICES_ORIGIN)) {
                _context4.next = 3;
                break;
              }

              return _context4.abrupt("return", false);

            case 3:
              if ([modulesMetadata.DevicesModule.CHANNELS_CREATED_ENTITY, modulesMetadata.DevicesModule.CHANNELS_UPDATED_ENTITY, modulesMetadata.DevicesModule.CHANNELS_DELETED_ENTITY].includes(payload.routingKey)) {
                _context4.next = 5;
                break;
              }

              return _context4.abrupt("return", false);

            case 5:
              body = JSON.parse(payload.data);
              validate = jsonSchemaValidator$3.compile(exchangeEntitySchema__namespace$4);

              if (!validate(body)) {
                _context4.next = 48;
                break;
              }

              if (!(!Channel.query().where('id', body.id).exists() && (payload.routingKey === modulesMetadata.DevicesModule.CHANNELS_UPDATED_ENTITY || payload.routingKey === modulesMetadata.DevicesModule.CHANNELS_DELETED_ENTITY))) {
                _context4.next = 10;
                break;
              }

              throw new Error('devices-module.channels.update.failed');

            case 10:
              if (!(payload.routingKey === modulesMetadata.DevicesModule.CHANNELS_DELETED_ENTITY)) {
                _context4.next = 25;
                break;
              }

              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.DELETING,
                id: body.id
              });
              _context4.prev = 12;
              _context4.next = 15;
              return Channel.delete(body.id);

            case 15:
              _context4.next = 20;
              break;

            case 17:
              _context4.prev = 17;
              _context4.t0 = _context4["catch"](12);
              throw new OrmError('devices-module.channels.delete.failed', _context4.t0, 'Delete channel failed.');

            case 20:
              _context4.prev = 20;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.DELETING,
                id: body.id
              });
              return _context4.finish(20);

            case 23:
              _context4.next = 45;
              break;

            case 25:
              if (!(payload.routingKey === modulesMetadata.DevicesModule.CHANNELS_UPDATED_ENTITY && state.semaphore.updating.includes(body.id))) {
                _context4.next = 27;
                break;
              }

              return _context4.abrupt("return", true);

            case 27:
              commit('SET_SEMAPHORE', {
                type: payload.routingKey === modulesMetadata.DevicesModule.CHANNELS_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
                id: body.id
              });
              entityData = {
                type: exports.ChannelEntityTypes.CHANNEL
              };
              Object.keys(body).forEach(function (attrName) {
                var kebabName = attrName.replace(/([a-z][A-Z0-9])/g, function (g) {
                  return "".concat(g[0], "_").concat(g[1].toLowerCase());
                });

                if (kebabName === 'device') {
                  var device = Device.query().where('identifier', body[attrName]).first();

                  if (device !== null) {
                    entityData.deviceId = device.id;
                  }
                } else {
                  entityData[kebabName] = body[attrName];
                }
              });
              _context4.prev = 30;
              _context4.next = 33;
              return Channel.insertOrUpdate({
                data: entityData
              });

            case 33:
              _context4.next = 42;
              break;

            case 35:
              _context4.prev = 35;
              _context4.t1 = _context4["catch"](30);
              failedEntity = Channel.query().with('device').where('id', body.id).first();

              if (!(failedEntity !== null && failedEntity.device !== null)) {
                _context4.next = 41;
                break;
              }

              _context4.next = 41;
              return Channel.get(failedEntity.device, body.id);

            case 41:
              throw new OrmError('devices-module.channels.update.failed', _context4.t1, 'Edit channel failed.');

            case 42:
              _context4.prev = 42;
              commit('CLEAR_SEMAPHORE', {
                type: payload.routingKey === modulesMetadata.DevicesModule.CHANNELS_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
                id: body.id
              });
              return _context4.finish(42);

            case 45:
              return _context4.abrupt("return", true);

            case 48:
              return _context4.abrupt("return", false);

            case 49:
            case "end":
              return _context4.stop();
          }
        }
      }, _callee4, null, [[12, 17, 20, 23], [30, 35, 42, 45]]);
    }))();
  },
  reset: function reset(_ref5) {
    var commit = _ref5.commit;
    commit('RESET_STATE');
  }
};
var moduleMutations$3 = (_moduleMutations$3 = {}, _defineProperty(_moduleMutations$3, 'SET_FIRST_LOAD', function SET_FIRST_LOAD(state, action) {
  state.firstLoad.push(action.id); // Make all keys uniq

  state.firstLoad = uniq__default['default'](state.firstLoad);
}), _defineProperty(_moduleMutations$3, 'SET_SEMAPHORE', function SET_SEMAPHORE(state, action) {
  switch (action.type) {
    case SemaphoreTypes.FETCHING:
      state.semaphore.fetching.items.push(action.id); // Make all keys uniq

      state.semaphore.fetching.items = uniq__default['default'](state.semaphore.fetching.items);
      break;

    case SemaphoreTypes.GETTING:
      state.semaphore.fetching.item.push(action.id); // Make all keys uniq

      state.semaphore.fetching.item = uniq__default['default'](state.semaphore.fetching.item);
      break;

    case SemaphoreTypes.CREATING:
      state.semaphore.creating.push(action.id); // Make all keys uniq

      state.semaphore.creating = uniq__default['default'](state.semaphore.creating);
      break;

    case SemaphoreTypes.UPDATING:
      state.semaphore.updating.push(action.id); // Make all keys uniq

      state.semaphore.updating = uniq__default['default'](state.semaphore.updating);
      break;

    case SemaphoreTypes.DELETING:
      state.semaphore.deleting.push(action.id); // Make all keys uniq

      state.semaphore.deleting = uniq__default['default'](state.semaphore.deleting);
      break;
  }
}), _defineProperty(_moduleMutations$3, 'CLEAR_SEMAPHORE', function CLEAR_SEMAPHORE(state, action) {
  switch (action.type) {
    case SemaphoreTypes.FETCHING:
      // Process all semaphore items
      state.semaphore.fetching.items.forEach(function (item, index) {
        // Find created item in reading one item semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.fetching.items.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.GETTING:
      // Process all semaphore items
      state.semaphore.fetching.item.forEach(function (item, index) {
        // Find created item in reading one item semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.fetching.item.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.CREATING:
      // Process all semaphore items
      state.semaphore.creating.forEach(function (item, index) {
        // Find created item in creating semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.creating.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.UPDATING:
      // Process all semaphore items
      state.semaphore.updating.forEach(function (item, index) {
        // Find created item in updating semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.updating.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.DELETING:
      // Process all semaphore items
      state.semaphore.deleting.forEach(function (item, index) {
        // Find created item in deleting semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.deleting.splice(index, 1);
        }
      });
      break;
  }
}), _defineProperty(_moduleMutations$3, 'RESET_STATE', function RESET_STATE(state) {
  Object.assign(state, moduleState$3);
}), _moduleMutations$3);
var channels = {
  state: function state() {
    return moduleState$3;
  },
  getters: moduleGetters$1,
  actions: moduleActions$3,
  mutations: moduleMutations$3
};var _moduleMutations$2;
var jsonApiFormatter$2 = new Jsona__default['default']({
  modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
  jsonPropertiesMapper: new JsonApiPropertiesMapper()
});
var apiOptions$2 = {
  dataTransformer: function dataTransformer(result) {
    return jsonApiFormatter$2.deserialize(result.data);
  }
};
var jsonSchemaValidator$2 = new Ajv__default['default']();
var moduleState$2 = {
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
var moduleActions$2 = {
  get: function get(_ref, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
      var state, commit;
      return regeneratorRuntime.wrap(function _callee$(_context) {
        while (1) {
          switch (_context.prev = _context.next) {
            case 0:
              state = _ref.state, commit = _ref.commit;

              if (!state.semaphore.fetching.item.includes(payload.id)) {
                _context.next = 3;
                break;
              }

              return _context.abrupt("return", false);

            case 3:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.GETTING,
                id: payload.id
              });
              _context.prev = 4;
              _context.next = 7;
              return ChannelProperty.api().get("".concat(ModuleApiPrefix, "/v1/devices/").concat(payload.channel.deviceId, "/channels/").concat(payload.channel.id, "/properties/").concat(payload.id), apiOptions$2);

            case 7:
              return _context.abrupt("return", true);

            case 10:
              _context.prev = 10;
              _context.t0 = _context["catch"](4);
              throw new ApiError('devices-module.channel-properties.fetch.failed', _context.t0, 'Fetching channel property failed.');

            case 13:
              _context.prev = 13;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.GETTING,
                id: payload.id
              });
              return _context.finish(13);

            case 16:
            case "end":
              return _context.stop();
          }
        }
      }, _callee, null, [[4, 10, 13, 16]]);
    }))();
  },
  fetch: function fetch(_ref2, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2() {
      var state, commit;
      return regeneratorRuntime.wrap(function _callee2$(_context2) {
        while (1) {
          switch (_context2.prev = _context2.next) {
            case 0:
              state = _ref2.state, commit = _ref2.commit;

              if (!state.semaphore.fetching.items.includes(payload.channel.id)) {
                _context2.next = 3;
                break;
              }

              return _context2.abrupt("return", false);

            case 3:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.FETCHING,
                id: payload.channel.id
              });
              _context2.prev = 4;
              _context2.next = 7;
              return ChannelProperty.api().get("".concat(ModuleApiPrefix, "/v1/devices/").concat(payload.channel.deviceId, "/channels/").concat(payload.channel.id, "/properties"), apiOptions$2);

            case 7:
              return _context2.abrupt("return", true);

            case 10:
              _context2.prev = 10;
              _context2.t0 = _context2["catch"](4);
              throw new ApiError('devices-module.channel-properties.fetch.failed', _context2.t0, 'Fetching channel properties failed.');

            case 13:
              _context2.prev = 13;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.FETCHING,
                id: payload.channel.id
              });
              return _context2.finish(13);

            case 16:
            case "end":
              return _context2.stop();
          }
        }
      }, _callee2, null, [[4, 10, 13, 16]]);
    }))();
  },
  edit: function edit(_ref3, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3() {
      var state, commit, updatedEntity, _channel, channel;

      return regeneratorRuntime.wrap(function _callee3$(_context3) {
        while (1) {
          switch (_context3.prev = _context3.next) {
            case 0:
              state = _ref3.state, commit = _ref3.commit;

              if (!state.semaphore.updating.includes(payload.property.id)) {
                _context3.next = 3;
                break;
              }

              throw new Error('devices-module.channel-properties.update.inProgress');

            case 3:
              if (ChannelProperty.query().where('id', payload.property.id).exists()) {
                _context3.next = 5;
                break;
              }

              throw new Error('devices-module.channel-properties.update.failed');

            case 5:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.property.id
              });
              _context3.prev = 6;
              _context3.next = 9;
              return ChannelProperty.update({
                where: payload.property.id,
                data: payload.data
              });

            case 9:
              _context3.next = 15;
              break;

            case 11:
              _context3.prev = 11;
              _context3.t0 = _context3["catch"](6);
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.property.id
              });
              throw new OrmError('devices-module.channel-properties.update.failed', _context3.t0, 'Edit channel property failed.');

            case 15:
              updatedEntity = ChannelProperty.find(payload.property.id);

              if (!(updatedEntity === null)) {
                _context3.next = 23;
                break;
              }

              _channel = Channel.find(payload.property.channelId);

              if (!(_channel !== null)) {
                _context3.next = 21;
                break;
              }

              _context3.next = 21;
              return ChannelProperty.get(_channel, payload.property.id);

            case 21:
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.property.id
              });
              throw new Error('devices-module.channel-properties.update.failed');

            case 23:
              channel = Channel.find(payload.property.channelId);

              if (!(channel === null)) {
                _context3.next = 26;
                break;
              }

              throw new Error('devices-module.channel-properties.update.failed');

            case 26:
              _context3.prev = 26;
              _context3.next = 29;
              return ChannelProperty.api().patch("".concat(ModuleApiPrefix, "/v1/devices/").concat(channel.deviceId, "/channels/").concat(updatedEntity.channelId, "/properties/").concat(updatedEntity.id), jsonApiFormatter$2.serialize({
                stuff: updatedEntity
              }), apiOptions$2);

            case 29:
              return _context3.abrupt("return", ChannelProperty.find(payload.property.id));

            case 32:
              _context3.prev = 32;
              _context3.t1 = _context3["catch"](26);
              _context3.next = 36;
              return ChannelProperty.get(channel, payload.property.id);

            case 36:
              throw new ApiError('devices-module.channel-properties.update.failed', _context3.t1, 'Edit channel property failed.');

            case 37:
              _context3.prev = 37;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.property.id
              });
              return _context3.finish(37);

            case 40:
            case "end":
              return _context3.stop();
          }
        }
      }, _callee3, null, [[6, 11], [26, 32, 37, 40]]);
    }))();
  },
  transmitData: function transmitData(_store, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee4() {
      var channel, device, backupValue;
      return regeneratorRuntime.wrap(function _callee4$(_context4) {
        while (1) {
          switch (_context4.prev = _context4.next) {
            case 0:
              if (ChannelProperty.query().where('id', payload.property.id).exists()) {
                _context4.next = 2;
                break;
              }

              throw new Error('devices-module.channel-properties.transmit.failed');

            case 2:
              channel = Channel.find(payload.property.channelId);

              if (!(channel === null)) {
                _context4.next = 5;
                break;
              }

              throw new Error('devices-module.channel-properties.transmit.failed');

            case 5:
              device = Device.find(channel.deviceId);

              if (!(device === null)) {
                _context4.next = 8;
                break;
              }

              throw new Error('devices-module.channel-properties.transmit.failed');

            case 8:
              backupValue = payload.property.value;
              _context4.prev = 9;
              _context4.next = 12;
              return ChannelProperty.update({
                where: payload.property.id,
                data: {
                  value: payload.value
                }
              });

            case 12:
              _context4.next = 17;
              break;

            case 14:
              _context4.prev = 14;
              _context4.t0 = _context4["catch"](9);
              throw new OrmError('devices-module.channel-properties.transmit.failed', _context4.t0, 'Edit channel property failed.');

            case 17:
              return _context4.abrupt("return", new Promise(function (resolve, reject) {
                ChannelProperty.wamp().call({
                  routing_key: modulesMetadata.DevicesModule.CHANNELS_PROPERTIES_DATA,
                  origin: ChannelProperty.$devicesModuleOrigin,
                  data: {
                    device: device.key,
                    channel: channel.key,
                    property: payload.property.key,
                    expected: payload.value
                  }
                }).then(function (response) {
                  if (get__default['default'](response.data, 'response') === 'accepted') {
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
                }).catch(function () {
                  ChannelProperty.update({
                    where: payload.property.id,
                    data: {
                      value: backupValue
                    }
                  });
                  reject(new Error('devices-module.channel-properties.transmit.failed'));
                });
              }));

            case 18:
            case "end":
              return _context4.stop();
          }
        }
      }, _callee4, null, [[9, 14]]);
    }))();
  },
  socketData: function socketData(_ref4, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee5() {
      var state, commit, body, validate, entityData, failedEntity;
      return regeneratorRuntime.wrap(function _callee5$(_context5) {
        while (1) {
          switch (_context5.prev = _context5.next) {
            case 0:
              state = _ref4.state, commit = _ref4.commit;

              if (!(payload.origin !== modulesMetadata.ModuleOrigin.MODULE_DEVICES_ORIGIN)) {
                _context5.next = 3;
                break;
              }

              return _context5.abrupt("return", false);

            case 3:
              if ([modulesMetadata.DevicesModule.CHANNELS_PROPERTY_CREATED_ENTITY, modulesMetadata.DevicesModule.CHANNELS_PROPERTY_UPDATED_ENTITY, modulesMetadata.DevicesModule.CHANNELS_PROPERTY_DELETED_ENTITY].includes(payload.routingKey)) {
                _context5.next = 5;
                break;
              }

              return _context5.abrupt("return", false);

            case 5:
              body = JSON.parse(payload.data);
              validate = jsonSchemaValidator$2.compile(exchangeEntitySchema__namespace$5);

              if (!validate(body)) {
                _context5.next = 48;
                break;
              }

              if (!(!ChannelProperty.query().where('id', body.id).exists() && (payload.routingKey === modulesMetadata.DevicesModule.CHANNELS_PROPERTY_UPDATED_ENTITY || payload.routingKey === modulesMetadata.DevicesModule.CHANNELS_PROPERTY_DELETED_ENTITY))) {
                _context5.next = 10;
                break;
              }

              throw new Error('devices-module.channel-properties.update.failed');

            case 10:
              if (!(payload.routingKey === modulesMetadata.DevicesModule.CHANNELS_PROPERTY_DELETED_ENTITY)) {
                _context5.next = 25;
                break;
              }

              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.DELETING,
                id: body.id
              });
              _context5.prev = 12;
              _context5.next = 15;
              return ChannelProperty.delete(body.id);

            case 15:
              _context5.next = 20;
              break;

            case 17:
              _context5.prev = 17;
              _context5.t0 = _context5["catch"](12);
              throw new OrmError('devices-module.channel-properties.delete.failed', _context5.t0, 'Delete channel property failed.');

            case 20:
              _context5.prev = 20;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.DELETING,
                id: body.id
              });
              return _context5.finish(20);

            case 23:
              _context5.next = 45;
              break;

            case 25:
              if (!(payload.routingKey === modulesMetadata.DevicesModule.CHANNELS_PROPERTY_UPDATED_ENTITY && state.semaphore.updating.includes(body.id))) {
                _context5.next = 27;
                break;
              }

              return _context5.abrupt("return", true);

            case 27:
              commit('SET_SEMAPHORE', {
                type: payload.routingKey === modulesMetadata.DevicesModule.CHANNELS_PROPERTY_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
                id: body.id
              });
              entityData = {
                type: exports.ChannelPropertyEntityTypes.PROPERTY
              };
              Object.keys(body).forEach(function (attrName) {
                var kebabName = attrName.replace(/([a-z][A-Z0-9])/g, function (g) {
                  return "".concat(g[0], "_").concat(g[1].toLowerCase());
                });

                if (kebabName === 'channel') {
                  var channel = Channel.query().where('channel', body[attrName]).first();

                  if (channel !== null) {
                    entityData.channelId = channel.id;
                  }
                } else {
                  entityData[kebabName] = body[attrName];
                }
              });
              _context5.prev = 30;
              _context5.next = 33;
              return ChannelProperty.insertOrUpdate({
                data: entityData
              });

            case 33:
              _context5.next = 42;
              break;

            case 35:
              _context5.prev = 35;
              _context5.t1 = _context5["catch"](30);
              failedEntity = ChannelProperty.query().with('channel').where('id', body.id).first();

              if (!(failedEntity !== null && failedEntity.channel !== null)) {
                _context5.next = 41;
                break;
              }

              _context5.next = 41;
              return ChannelProperty.get(failedEntity.channel, body.id);

            case 41:
              throw new OrmError('devices-module.channel-properties.update.failed', _context5.t1, 'Edit channel property failed.');

            case 42:
              _context5.prev = 42;
              commit('CLEAR_SEMAPHORE', {
                type: payload.routingKey === modulesMetadata.DevicesModule.CHANNELS_PROPERTY_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
                id: body.id
              });
              return _context5.finish(42);

            case 45:
              return _context5.abrupt("return", true);

            case 48:
              return _context5.abrupt("return", false);

            case 49:
            case "end":
              return _context5.stop();
          }
        }
      }, _callee5, null, [[12, 17, 20, 23], [30, 35, 42, 45]]);
    }))();
  },
  reset: function reset(_ref5) {
    var commit = _ref5.commit;
    commit('RESET_STATE');
  }
};
var moduleMutations$2 = (_moduleMutations$2 = {}, _defineProperty(_moduleMutations$2, 'SET_SEMAPHORE', function SET_SEMAPHORE(state, action) {
  switch (action.type) {
    case SemaphoreTypes.FETCHING:
      state.semaphore.fetching.items.push(action.id); // Make all keys uniq

      state.semaphore.fetching.items = uniq__default['default'](state.semaphore.fetching.items);
      break;

    case SemaphoreTypes.GETTING:
      state.semaphore.fetching.item.push(action.id); // Make all keys uniq

      state.semaphore.fetching.item = uniq__default['default'](state.semaphore.fetching.item);
      break;

    case SemaphoreTypes.CREATING:
      state.semaphore.creating.push(action.id); // Make all keys uniq

      state.semaphore.creating = uniq__default['default'](state.semaphore.creating);
      break;

    case SemaphoreTypes.UPDATING:
      state.semaphore.updating.push(action.id); // Make all keys uniq

      state.semaphore.updating = uniq__default['default'](state.semaphore.updating);
      break;

    case SemaphoreTypes.DELETING:
      state.semaphore.deleting.push(action.id); // Make all keys uniq

      state.semaphore.deleting = uniq__default['default'](state.semaphore.deleting);
      break;
  }
}), _defineProperty(_moduleMutations$2, 'CLEAR_SEMAPHORE', function CLEAR_SEMAPHORE(state, action) {
  switch (action.type) {
    case SemaphoreTypes.FETCHING:
      // Process all semaphore items
      state.semaphore.fetching.items.forEach(function (item, index) {
        // Find created item in reading one item semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.fetching.items.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.GETTING:
      // Process all semaphore items
      state.semaphore.fetching.item.forEach(function (item, index) {
        // Find created item in reading one item semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.fetching.item.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.CREATING:
      // Process all semaphore items
      state.semaphore.creating.forEach(function (item, index) {
        // Find created item in creating semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.creating.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.UPDATING:
      // Process all semaphore items
      state.semaphore.updating.forEach(function (item, index) {
        // Find created item in updating semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.updating.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.DELETING:
      // Process all semaphore items
      state.semaphore.deleting.forEach(function (item, index) {
        // Find removed item in removing semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.deleting.splice(index, 1);
        }
      });
      break;
  }
}), _defineProperty(_moduleMutations$2, 'RESET_STATE', function RESET_STATE(state) {
  Object.assign(state, moduleState$2);
}), _moduleMutations$2);
var channelProperties = {
  state: function state() {
    return moduleState$2;
  },
  actions: moduleActions$2,
  mutations: moduleMutations$2
};var _moduleMutations$1;
var jsonApiFormatter$1 = new Jsona__default['default']({
  modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
  jsonPropertiesMapper: new JsonApiPropertiesMapper()
});
var apiOptions$1 = {
  dataTransformer: function dataTransformer(result) {
    return jsonApiFormatter$1.deserialize(result.data);
  }
};
var jsonSchemaValidator$1 = new Ajv__default['default']();
var moduleState$1 = {
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
var moduleActions$1 = {
  get: function get(_ref, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
      var state, commit;
      return regeneratorRuntime.wrap(function _callee$(_context) {
        while (1) {
          switch (_context.prev = _context.next) {
            case 0:
              state = _ref.state, commit = _ref.commit;

              if (!state.semaphore.fetching.item.includes(payload.id)) {
                _context.next = 3;
                break;
              }

              return _context.abrupt("return", false);

            case 3:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.GETTING,
                id: payload.id
              });
              _context.prev = 4;
              _context.next = 7;
              return ChannelConfiguration.api().get("".concat(ModuleApiPrefix, "/v1/devices/").concat(payload.channel.deviceId, "/channels/").concat(payload.channel.id, "/configuration/").concat(payload.id), apiOptions$1);

            case 7:
              return _context.abrupt("return", true);

            case 10:
              _context.prev = 10;
              _context.t0 = _context["catch"](4);
              throw new ApiError('devices-module.channel-configuration.fetch.failed', _context.t0, 'Fetching channel configuration failed.');

            case 13:
              _context.prev = 13;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.GETTING,
                id: payload.id
              });
              return _context.finish(13);

            case 16:
            case "end":
              return _context.stop();
          }
        }
      }, _callee, null, [[4, 10, 13, 16]]);
    }))();
  },
  fetch: function fetch(_ref2, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2() {
      var state, commit;
      return regeneratorRuntime.wrap(function _callee2$(_context2) {
        while (1) {
          switch (_context2.prev = _context2.next) {
            case 0:
              state = _ref2.state, commit = _ref2.commit;

              if (!state.semaphore.fetching.items.includes(payload.channel.id)) {
                _context2.next = 3;
                break;
              }

              return _context2.abrupt("return", false);

            case 3:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.FETCHING,
                id: payload.channel.id
              });
              _context2.prev = 4;
              _context2.next = 7;
              return ChannelConfiguration.api().get("".concat(ModuleApiPrefix, "/v1/devices/").concat(payload.channel.deviceId, "/channels/").concat(payload.channel.id, "/configuration"), apiOptions$1);

            case 7:
              return _context2.abrupt("return", true);

            case 10:
              _context2.prev = 10;
              _context2.t0 = _context2["catch"](4);
              throw new ApiError('devices-module.channel-configuration.fetch.failed', _context2.t0, 'Fetching channel configuration failed.');

            case 13:
              _context2.prev = 13;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.FETCHING,
                id: payload.channel.id
              });
              return _context2.finish(13);

            case 16:
            case "end":
              return _context2.stop();
          }
        }
      }, _callee2, null, [[4, 10, 13, 16]]);
    }))();
  },
  edit: function edit(_ref3, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3() {
      var state, commit, updatedEntity, _channel, channel;

      return regeneratorRuntime.wrap(function _callee3$(_context3) {
        while (1) {
          switch (_context3.prev = _context3.next) {
            case 0:
              state = _ref3.state, commit = _ref3.commit;

              if (!state.semaphore.updating.includes(payload.configuration.id)) {
                _context3.next = 3;
                break;
              }

              throw new Error('devices-module.channel-configuration.update.inProgress');

            case 3:
              if (ChannelConfiguration.query().where('id', payload.configuration.id).exists()) {
                _context3.next = 5;
                break;
              }

              throw new Error('devices-module.channel-configuration.update.failed');

            case 5:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.configuration.id
              });
              _context3.prev = 6;
              _context3.next = 9;
              return ChannelConfiguration.update({
                where: payload.configuration.id,
                data: payload.data
              });

            case 9:
              _context3.next = 15;
              break;

            case 11:
              _context3.prev = 11;
              _context3.t0 = _context3["catch"](6);
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.configuration.id
              });
              throw new OrmError('devices-module.channel-configuration.update.failed', _context3.t0, 'Edit channel configuration failed.');

            case 15:
              updatedEntity = ChannelConfiguration.find(payload.configuration.id);

              if (!(updatedEntity === null)) {
                _context3.next = 23;
                break;
              }

              _channel = Channel.find(payload.configuration.channelId);

              if (!(_channel !== null)) {
                _context3.next = 21;
                break;
              }

              _context3.next = 21;
              return ChannelConfiguration.get(_channel, payload.configuration.id);

            case 21:
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.configuration.id
              });
              throw new Error('devices-module.channel-configuration.update.failed');

            case 23:
              channel = Channel.find(payload.configuration.channelId);

              if (!(channel === null)) {
                _context3.next = 26;
                break;
              }

              throw new Error('devices-module.channel-configuration.update.failed');

            case 26:
              _context3.prev = 26;
              _context3.next = 29;
              return ChannelConfiguration.api().patch("".concat(ModuleApiPrefix, "/v1/devices/").concat(channel.deviceId, "/channels/").concat(updatedEntity.channelId, "/configuration/").concat(updatedEntity.id), jsonApiFormatter$1.serialize({
                stuff: updatedEntity
              }), apiOptions$1);

            case 29:
              return _context3.abrupt("return", ChannelConfiguration.find(payload.configuration.id));

            case 32:
              _context3.prev = 32;
              _context3.t1 = _context3["catch"](26);
              _context3.next = 36;
              return ChannelConfiguration.get(channel, payload.configuration.id);

            case 36:
              throw new ApiError('devices-module.channel-configuration.update.failed', _context3.t1, 'Edit channel configuration failed.');

            case 37:
              _context3.prev = 37;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.configuration.id
              });
              return _context3.finish(37);

            case 40:
            case "end":
              return _context3.stop();
          }
        }
      }, _callee3, null, [[6, 11], [26, 32, 37, 40]]);
    }))();
  },
  transmitData: function transmitData(_store, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee4() {
      var channel, device, backupValue;
      return regeneratorRuntime.wrap(function _callee4$(_context4) {
        while (1) {
          switch (_context4.prev = _context4.next) {
            case 0:
              if (ChannelConfiguration.query().where('id', payload.configuration.id).exists()) {
                _context4.next = 2;
                break;
              }

              throw new Error('devices-module.channel-configuration.transmit.failed');

            case 2:
              channel = Channel.find(payload.configuration.channelId);

              if (!(channel === null)) {
                _context4.next = 5;
                break;
              }

              throw new Error('devices-module.channel-configuration.transmit.failed');

            case 5:
              device = Device.find(channel.deviceId);

              if (!(device === null)) {
                _context4.next = 8;
                break;
              }

              throw new Error('devices-module.channel-configuration.transmit.failed');

            case 8:
              backupValue = payload.configuration.value;
              _context4.prev = 9;
              _context4.next = 12;
              return ChannelConfiguration.update({
                where: payload.configuration.id,
                data: {
                  value: payload.value
                }
              });

            case 12:
              _context4.next = 17;
              break;

            case 14:
              _context4.prev = 14;
              _context4.t0 = _context4["catch"](9);
              throw new OrmError('devices-module.channel-configuration.transmit.failed', _context4.t0, 'Edit channel configuration failed.');

            case 17:
              return _context4.abrupt("return", new Promise(function (resolve, reject) {
                ChannelConfiguration.wamp().call({
                  routing_key: modulesMetadata.DevicesModule.CHANNELS_CONFIGURATION_DATA,
                  origin: ChannelConfiguration.$devicesModuleOrigin,
                  data: {
                    device: device.key,
                    channel: channel.key,
                    configuration: payload.configuration.key,
                    expected: payload.value
                  }
                }).then(function (response) {
                  if (get__default['default'](response.data, 'response') === 'accepted') {
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
                }).catch(function () {
                  ChannelConfiguration.update({
                    where: payload.configuration.id,
                    data: {
                      value: backupValue
                    }
                  });
                  reject(new Error('devices-module.channel-configuration.transmit.failed'));
                });
              }));

            case 18:
            case "end":
              return _context4.stop();
          }
        }
      }, _callee4, null, [[9, 14]]);
    }))();
  },
  socketData: function socketData(_ref4, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee5() {
      var state, commit, body, validate, entityData, failedEntity;
      return regeneratorRuntime.wrap(function _callee5$(_context5) {
        while (1) {
          switch (_context5.prev = _context5.next) {
            case 0:
              state = _ref4.state, commit = _ref4.commit;

              if (!(payload.origin !== modulesMetadata.ModuleOrigin.MODULE_DEVICES_ORIGIN)) {
                _context5.next = 3;
                break;
              }

              return _context5.abrupt("return", false);

            case 3:
              if ([modulesMetadata.DevicesModule.CHANNELS_CONFIGURATION_CREATED_ENTITY, modulesMetadata.DevicesModule.CHANNELS_CONFIGURATION_UPDATED_ENTITY, modulesMetadata.DevicesModule.CHANNELS_CONFIGURATION_DELETED_ENTITY].includes(payload.routingKey)) {
                _context5.next = 5;
                break;
              }

              return _context5.abrupt("return", false);

            case 5:
              body = JSON.parse(payload.data);
              validate = jsonSchemaValidator$1.compile(exchangeEntitySchema__namespace$6);

              if (!validate(body)) {
                _context5.next = 48;
                break;
              }

              if (!(!ChannelConfiguration.query().where('id', body.id).exists() && (payload.routingKey === modulesMetadata.DevicesModule.CHANNELS_CONFIGURATION_UPDATED_ENTITY || payload.routingKey === modulesMetadata.DevicesModule.CHANNELS_CONFIGURATION_DELETED_ENTITY))) {
                _context5.next = 10;
                break;
              }

              throw new Error('devices-module.channel-configuration.update.failed');

            case 10:
              if (!(payload.routingKey === modulesMetadata.DevicesModule.CHANNELS_CONFIGURATION_DELETED_ENTITY)) {
                _context5.next = 25;
                break;
              }

              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.DELETING,
                id: body.id
              });
              _context5.prev = 12;
              _context5.next = 15;
              return ChannelConfiguration.delete(body.id);

            case 15:
              _context5.next = 20;
              break;

            case 17:
              _context5.prev = 17;
              _context5.t0 = _context5["catch"](12);
              throw new OrmError('devices-module.channel-configuration.delete.failed', _context5.t0, 'Delete channel configuration failed.');

            case 20:
              _context5.prev = 20;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.DELETING,
                id: body.id
              });
              return _context5.finish(20);

            case 23:
              _context5.next = 45;
              break;

            case 25:
              if (!(payload.routingKey === modulesMetadata.DevicesModule.CHANNELS_CONFIGURATION_UPDATED_ENTITY && state.semaphore.updating.includes(body.id))) {
                _context5.next = 27;
                break;
              }

              return _context5.abrupt("return", true);

            case 27:
              commit('SET_SEMAPHORE', {
                type: payload.routingKey === modulesMetadata.DevicesModule.CHANNELS_CONFIGURATION_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
                id: body.id
              });
              entityData = {
                type: exports.ChannelConfigurationEntityTypes.CONFIGURATION
              };
              Object.keys(body).forEach(function (attrName) {
                var kebabName = attrName.replace(/([a-z][A-Z0-9])/g, function (g) {
                  return "".concat(g[0], "_").concat(g[1].toLowerCase());
                });

                if (kebabName === 'channel') {
                  var channel = Channel.query().where('channel', body[attrName]).first();

                  if (channel !== null) {
                    entityData.channelId = channel.id;
                  }
                } else {
                  entityData[kebabName] = body[attrName];
                }
              });
              _context5.prev = 30;
              _context5.next = 33;
              return ChannelConfiguration.insertOrUpdate({
                data: entityData
              });

            case 33:
              _context5.next = 42;
              break;

            case 35:
              _context5.prev = 35;
              _context5.t1 = _context5["catch"](30);
              failedEntity = ChannelConfiguration.query().with('channel').where('id', body.id).first();

              if (!(failedEntity !== null && failedEntity.channel !== null)) {
                _context5.next = 41;
                break;
              }

              _context5.next = 41;
              return ChannelConfiguration.get(failedEntity.channel, body.id);

            case 41:
              throw new OrmError('devices-module.channel-configuration.update.failed', _context5.t1, 'Edit channel configuration failed.');

            case 42:
              _context5.prev = 42;
              commit('CLEAR_SEMAPHORE', {
                type: payload.routingKey === modulesMetadata.DevicesModule.CHANNELS_CONFIGURATION_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
                id: body.id
              });
              return _context5.finish(42);

            case 45:
              return _context5.abrupt("return", true);

            case 48:
              return _context5.abrupt("return", false);

            case 49:
            case "end":
              return _context5.stop();
          }
        }
      }, _callee5, null, [[12, 17, 20, 23], [30, 35, 42, 45]]);
    }))();
  },
  reset: function reset(_ref5) {
    var commit = _ref5.commit;
    commit('RESET_STATE');
  }
};
var moduleMutations$1 = (_moduleMutations$1 = {}, _defineProperty(_moduleMutations$1, 'SET_SEMAPHORE', function SET_SEMAPHORE(state, action) {
  switch (action.type) {
    case SemaphoreTypes.FETCHING:
      state.semaphore.fetching.items.push(action.id); // Make all keys uniq

      state.semaphore.fetching.items = uniq__default['default'](state.semaphore.fetching.items);
      break;

    case SemaphoreTypes.GETTING:
      state.semaphore.fetching.item.push(action.id); // Make all keys uniq

      state.semaphore.fetching.item = uniq__default['default'](state.semaphore.fetching.item);
      break;

    case SemaphoreTypes.CREATING:
      state.semaphore.creating.push(action.id); // Make all keys uniq

      state.semaphore.creating = uniq__default['default'](state.semaphore.creating);
      break;

    case SemaphoreTypes.UPDATING:
      state.semaphore.updating.push(action.id); // Make all keys uniq

      state.semaphore.updating = uniq__default['default'](state.semaphore.updating);
      break;

    case SemaphoreTypes.DELETING:
      state.semaphore.deleting.push(action.id); // Make all keys uniq

      state.semaphore.deleting = uniq__default['default'](state.semaphore.deleting);
      break;
  }
}), _defineProperty(_moduleMutations$1, 'CLEAR_SEMAPHORE', function CLEAR_SEMAPHORE(state, action) {
  switch (action.type) {
    case SemaphoreTypes.FETCHING:
      // Process all semaphore items
      state.semaphore.fetching.items.forEach(function (item, index) {
        // Find created item in reading one item semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.fetching.items.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.GETTING:
      // Process all semaphore items
      state.semaphore.fetching.item.forEach(function (item, index) {
        // Find created item in reading one item semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.fetching.item.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.CREATING:
      // Process all semaphore items
      state.semaphore.creating.forEach(function (item, index) {
        // Find created item in creating semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.creating.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.UPDATING:
      // Process all semaphore items
      state.semaphore.updating.forEach(function (item, index) {
        // Find created item in updating semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.updating.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.DELETING:
      // Process all semaphore items
      state.semaphore.deleting.forEach(function (item, index) {
        // Find removed item in removing semaphore...
        if (item === action.id) {
          // ...and remove it
          state.semaphore.deleting.splice(index, 1);
        }
      });
      break;
  }
}), _defineProperty(_moduleMutations$1, 'RESET_STATE', function RESET_STATE(state) {
  Object.assign(state, moduleState$1);
}), _moduleMutations$1);
var channelsConfiguration = {
  state: function state() {
    return moduleState$1;
  },
  actions: moduleActions$1,
  mutations: moduleMutations$1
};var _moduleMutations;
var jsonApiFormatter = new Jsona__default['default']({
  modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
  jsonPropertiesMapper: new JsonApiPropertiesMapper()
});
var apiOptions = {
  dataTransformer: function dataTransformer(result) {
    return jsonApiFormatter.deserialize(result.data);
  }
};
var jsonSchemaValidator = new Ajv__default['default']();
var moduleState = {
  semaphore: {
    fetching: {
      items: false,
      item: []
    },
    updating: []
  },
  firstLoad: false
};
var moduleGetters = {
  firstLoadFinished: function firstLoadFinished(state) {
    return function () {
      return !!state.firstLoad;
    };
  },
  getting: function getting(state) {
    return function (id) {
      return state.semaphore.fetching.item.includes(id);
    };
  },
  fetching: function fetching(state) {
    return function () {
      return state.semaphore.fetching.items;
    };
  }
};
var moduleActions = {
  get: function get(_ref, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
      var state, commit;
      return regeneratorRuntime.wrap(function _callee$(_context) {
        while (1) {
          switch (_context.prev = _context.next) {
            case 0:
              state = _ref.state, commit = _ref.commit;

              if (!state.semaphore.fetching.item.includes(payload.id)) {
                _context.next = 3;
                break;
              }

              return _context.abrupt("return", false);

            case 3:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.GETTING,
                id: payload.id
              });
              _context.prev = 4;
              _context.next = 7;
              return Connector.api().get("".concat(ModuleApiPrefix, "/v1/connectors/").concat(payload.id), apiOptions);

            case 7:
              return _context.abrupt("return", true);

            case 10:
              _context.prev = 10;
              _context.t0 = _context["catch"](4);
              throw new ApiError('devices-module.connectors.fetch.failed', _context.t0, 'Fetching connectors failed.');

            case 13:
              _context.prev = 13;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.GETTING,
                id: payload.id
              });
              return _context.finish(13);

            case 16:
            case "end":
              return _context.stop();
          }
        }
      }, _callee, null, [[4, 10, 13, 16]]);
    }))();
  },
  fetch: function fetch(_ref2) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2() {
      var state, commit;
      return regeneratorRuntime.wrap(function _callee2$(_context2) {
        while (1) {
          switch (_context2.prev = _context2.next) {
            case 0:
              state = _ref2.state, commit = _ref2.commit;

              if (!state.semaphore.fetching.items) {
                _context2.next = 3;
                break;
              }

              return _context2.abrupt("return", false);

            case 3:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.FETCHING
              });
              _context2.prev = 4;
              _context2.next = 7;
              return Connector.api().get("".concat(ModuleApiPrefix, "/v1/connectors"), apiOptions);

            case 7:
              commit('SET_FIRST_LOAD', true);
              return _context2.abrupt("return", true);

            case 11:
              _context2.prev = 11;
              _context2.t0 = _context2["catch"](4);
              throw new ApiError('devices-module.connectors.fetch.failed', _context2.t0, 'Fetching connectors failed.');

            case 14:
              _context2.prev = 14;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.FETCHING
              });
              return _context2.finish(14);

            case 17:
            case "end":
              return _context2.stop();
          }
        }
      }, _callee2, null, [[4, 11, 14, 17]]);
    }))();
  },
  edit: function edit(_ref3, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3() {
      var state, commit, updatedEntity;
      return regeneratorRuntime.wrap(function _callee3$(_context3) {
        while (1) {
          switch (_context3.prev = _context3.next) {
            case 0:
              state = _ref3.state, commit = _ref3.commit;

              if (!state.semaphore.updating.includes(payload.connector.id)) {
                _context3.next = 3;
                break;
              }

              throw new Error('devices-module.connectors.update.inProgress');

            case 3:
              if (Connector.query().where('id', payload.connector.id).exists()) {
                _context3.next = 5;
                break;
              }

              throw new Error('devices-module.connectors.update.failed');

            case 5:
              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.connector.id
              });
              _context3.prev = 6;
              _context3.next = 9;
              return Connector.update({
                where: payload.connector.id,
                data: payload.data
              });

            case 9:
              _context3.next = 15;
              break;

            case 11:
              _context3.prev = 11;
              _context3.t0 = _context3["catch"](6);
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.connector.id
              });
              throw new OrmError('devices-module.connectors.update.failed', _context3.t0, 'Edit connector failed.');

            case 15:
              updatedEntity = Connector.find(payload.connector.id);

              if (!(updatedEntity === null)) {
                _context3.next = 21;
                break;
              }

              _context3.next = 19;
              return Connector.get(payload.connector.id);

            case 19:
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.connector.id
              });
              throw new Error('devices-module.connectors.update.failed');

            case 21:
              _context3.prev = 21;
              _context3.next = 24;
              return Connector.api().patch("".concat(ModuleApiPrefix, "/v1/connectors/").concat(updatedEntity.id), jsonApiFormatter.serialize({
                stuff: updatedEntity
              }), apiOptions);

            case 24:
              return _context3.abrupt("return", Connector.find(payload.connector.id));

            case 27:
              _context3.prev = 27;
              _context3.t1 = _context3["catch"](21);
              _context3.next = 31;
              return Connector.get(payload.connector.id);

            case 31:
              throw new ApiError('devices-module.connectors.update.failed', _context3.t1, 'Edit connector failed.');

            case 32:
              _context3.prev = 32;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.UPDATING,
                id: payload.connector.id
              });
              return _context3.finish(32);

            case 35:
            case "end":
              return _context3.stop();
          }
        }
      }, _callee3, null, [[6, 11], [21, 27, 32, 35]]);
    }))();
  },
  transmitCommand: function transmitCommand(_store, payload) {
    if (!Connector.query().where('id', payload.connector.id).exists()) {
      throw new Error('devices-module.connector.transmit.failed');
    }

    return new Promise(function (resolve, reject) {
      Connector.wamp().call({
        routing_key: modulesMetadata.DevicesModule.CONNECTOR_CONTROLS,
        origin: Connector.$devicesModuleOrigin,
        data: {
          control: payload.command,
          connector: payload.connector.id
        }
      }).then(function (response) {
        if (get__default['default'](response.data, 'response') === 'accepted') {
          resolve(true);
        } else {
          reject(new Error('devices-module.connector.transmit.failed'));
        }
      }).catch(function () {
        reject(new Error('devices-module.connector.transmit.failed'));
      });
    });
  },
  socketData: function socketData(_ref4, payload) {
    return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee4() {
      var state, commit, body, validate, entityData;
      return regeneratorRuntime.wrap(function _callee4$(_context4) {
        while (1) {
          switch (_context4.prev = _context4.next) {
            case 0:
              state = _ref4.state, commit = _ref4.commit;

              if (!(payload.origin !== modulesMetadata.ModuleOrigin.MODULE_DEVICES_ORIGIN)) {
                _context4.next = 3;
                break;
              }

              return _context4.abrupt("return", false);

            case 3:
              if ([modulesMetadata.DevicesModule.CONNECTOR_CREATED_ENTITY, modulesMetadata.DevicesModule.CONNECTOR_UPDATED_ENTITY, modulesMetadata.DevicesModule.CONNECTOR_DELETED_ENTITY].includes(payload.routingKey)) {
                _context4.next = 5;
                break;
              }

              return _context4.abrupt("return", false);

            case 5:
              body = JSON.parse(payload.data);
              validate = jsonSchemaValidator.compile(exchangeEntitySchema__namespace$7);

              if (!validate(body)) {
                _context4.next = 46;
                break;
              }

              if (!(!Connector.query().where('id', body.id).exists() && (payload.routingKey === modulesMetadata.DevicesModule.CONNECTOR_UPDATED_ENTITY || payload.routingKey === modulesMetadata.DevicesModule.CONNECTOR_DELETED_ENTITY))) {
                _context4.next = 10;
                break;
              }

              throw new Error('devices-module.connectors.update.failed');

            case 10:
              if (!(payload.routingKey === modulesMetadata.DevicesModule.CONNECTOR_DELETED_ENTITY)) {
                _context4.next = 25;
                break;
              }

              commit('SET_SEMAPHORE', {
                type: SemaphoreTypes.DELETING,
                id: body.id
              });
              _context4.prev = 12;
              _context4.next = 15;
              return Connector.delete(body.id);

            case 15:
              _context4.next = 20;
              break;

            case 17:
              _context4.prev = 17;
              _context4.t0 = _context4["catch"](12);
              throw new OrmError('devices-module.connectors.delete.failed', _context4.t0, 'Delete connector failed.');

            case 20:
              _context4.prev = 20;
              commit('CLEAR_SEMAPHORE', {
                type: SemaphoreTypes.DELETING,
                id: body.id
              });
              return _context4.finish(20);

            case 23:
              _context4.next = 43;
              break;

            case 25:
              if (!(payload.routingKey === modulesMetadata.DevicesModule.CONNECTOR_UPDATED_ENTITY && state.semaphore.updating.includes(body.id))) {
                _context4.next = 27;
                break;
              }

              return _context4.abrupt("return", true);

            case 27:
              commit('SET_SEMAPHORE', {
                type: payload.routingKey === modulesMetadata.DevicesModule.CONNECTOR_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
                id: body.id
              });
              entityData = {};
              Object.keys(body).forEach(function (attrName) {
                var kebabName = attrName.replace(/([a-z][A-Z0-9])/g, function (g) {
                  return "".concat(g[0], "_").concat(g[1].toLowerCase());
                });
                entityData[kebabName] = body[attrName];
              });
              _context4.prev = 30;
              _context4.next = 33;
              return Connector.insertOrUpdate({
                data: entityData
              });

            case 33:
              _context4.next = 40;
              break;

            case 35:
              _context4.prev = 35;
              _context4.t1 = _context4["catch"](30);
              _context4.next = 39;
              return Connector.get(body.id);

            case 39:
              throw new OrmError('devices-module.connectors.update.failed', _context4.t1, 'Edit connector failed.');

            case 40:
              _context4.prev = 40;
              commit('CLEAR_SEMAPHORE', {
                type: payload.routingKey === modulesMetadata.DevicesModule.CONNECTOR_UPDATED_ENTITY ? SemaphoreTypes.UPDATING : SemaphoreTypes.CREATING,
                id: body.id
              });
              return _context4.finish(40);

            case 43:
              return _context4.abrupt("return", true);

            case 46:
              return _context4.abrupt("return", false);

            case 47:
            case "end":
              return _context4.stop();
          }
        }
      }, _callee4, null, [[12, 17, 20, 23], [30, 35, 40, 43]]);
    }))();
  },
  reset: function reset(_ref5) {
    var commit = _ref5.commit;
    commit('RESET_STATE');
  }
};
var moduleMutations = (_moduleMutations = {}, _defineProperty(_moduleMutations, 'SET_FIRST_LOAD', function SET_FIRST_LOAD(state, action) {
  state.firstLoad = action;
}), _defineProperty(_moduleMutations, 'SET_SEMAPHORE', function SET_SEMAPHORE(state, action) {
  switch (action.type) {
    case SemaphoreTypes.FETCHING:
      state.semaphore.fetching.items = true;
      break;

    case SemaphoreTypes.GETTING:
      state.semaphore.fetching.item.push(get__default['default'](action, 'id', 'notValid')); // Make all keys uniq

      state.semaphore.fetching.item = uniq__default['default'](state.semaphore.fetching.item);
      break;

    case SemaphoreTypes.UPDATING:
      state.semaphore.updating.push(get__default['default'](action, 'id', 'notValid')); // Make all keys uniq

      state.semaphore.updating = uniq__default['default'](state.semaphore.updating);
      break;
  }
}), _defineProperty(_moduleMutations, 'CLEAR_SEMAPHORE', function CLEAR_SEMAPHORE(state, action) {
  switch (action.type) {
    case SemaphoreTypes.FETCHING:
      state.semaphore.fetching.items = false;
      break;

    case SemaphoreTypes.GETTING:
      // Process all semaphore items
      state.semaphore.fetching.item.forEach(function (item, index) {
        // Find created item in reading one item semaphore...
        if (item === get__default['default'](action, 'id', 'notValid')) {
          // ...and remove it
          state.semaphore.fetching.item.splice(index, 1);
        }
      });
      break;

    case SemaphoreTypes.UPDATING:
      // Process all semaphore items
      state.semaphore.updating.forEach(function (item, index) {
        // Find created item in updating semaphore...
        if (item === get__default['default'](action, 'id', 'notValid')) {
          // ...and remove it
          state.semaphore.updating.splice(index, 1);
        }
      });
      break;
  }
}), _defineProperty(_moduleMutations, 'RESET_STATE', function RESET_STATE(state) {
  Object.assign(state, moduleState);
}), _moduleMutations);
var connectors = {
  state: function state() {
    return moduleState;
  },
  getters: moduleGetters,
  actions: moduleActions,
  mutations: moduleMutations
};// ENTITY TYPES
// ============
exports.ConnectorEntityTypes=void 0; // ENTITY INTERFACE
// ================

(function (ConnectorEntityTypes) {
  ConnectorEntityTypes["FB_BUS"] = "devices-module/connector-fb-bus";
  ConnectorEntityTypes["FB_MQTT_V1"] = "devices-module/connector-fb-mqtt-v1";
})(exports.ConnectorEntityTypes || (exports.ConnectorEntityTypes = {}));// Import library

// install function executed by VuexORM.use()
var install = function installVuexOrmWamp(components, config) {
  if (install.installed) return;
  install.installed = true;

  if (typeof config.originName !== 'undefined') {
    // @ts-ignore
    components.Model.prototype.$devicesModuleOrigin = config.originName;
  } else {
    // @ts-ignore
    components.Model.prototype.$devicesModuleOrigin = modulesMetadata.ModuleOrigin.MODULE_DEVICES_ORIGIN;
  }

  config.database.register(Device, devices);
  config.database.register(DeviceProperty, deviceProperties);
  config.database.register(DeviceConfiguration, devicesConfiguration);
  config.database.register(DeviceConnector, deviceConnector);
  config.database.register(Channel, channels);
  config.database.register(ChannelProperty, channelProperties);
  config.database.register(ChannelConfiguration, channelsConfiguration);
  config.database.register(Connector, connectors);
}; // Create module definition for VuexORM.use()


var plugin = {
  install: install
}; // Default export is library as a whole, registered via VuexORM.use()
exports.Channel=Channel;exports.ChannelConfiguration=ChannelConfiguration;exports.ChannelProperty=ChannelProperty;exports.Connector=Connector;exports.Device=Device;exports.DeviceConfiguration=DeviceConfiguration;exports.DeviceConnector=DeviceConnector;exports.DeviceProperty=DeviceProperty;exports.default=plugin;