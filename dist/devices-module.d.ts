import { Model, Fields, Item, Database } from '@vuex-orm/core';
import { DataType, DeviceConnectionState, DeviceModel, HardwareManufacturer, FirmwareManufacturer, DeviceControlAction, ChannelControlAction, ConnectorControlAction } from '@fastybird/modules-metadata';
import { TJsonApiData, TJsonApiBody, TJsonApiRelationships, TJsonApiRelation, TJsonApiRelationshipData } from 'jsona/lib/JsonaTypes';
import { Plugin } from '@vuex-orm/core/dist/src/plugins/use';

declare enum PropertyNumberDatatypeTypes {
    CHAR = "char",
    UNSIGNED_CHAR = "uchar",
    SHORT = "short",
    UNSIGNED_SHORT = "ushort",
    INT = "int",
    UNSIGNED_INT = "uint",
    FLOAT = "float"
}
declare enum PropertyIntegerDatatypeTypes {
    CHAR = "char",
    UNSIGNED_CHAR = "uchar",
    SHORT = "short",
    UNSIGNED_SHORT = "ushort",
    INT = "int",
    UNSIGNED_INT = "uint"
}
declare enum PropertyCommandState {
    SENDING = "sending",
    COMPLETED = "completed"
}
declare enum PropertyCommandResult {
    OK = "ok",
    ERR = "err"
}
declare enum SensorNameTypes {
    SENSOR = "sensor",
    AIR_QUALITY = "air_quality",
    LIGHT_LEVEL = "light_level",
    NOISE_LEVEL = "noise_level",
    TEMPERATURE = "temperature",
    HUMIDITY = "humidity"
}
declare enum ActorNameTypes {
    ACTOR = "actor",
    SWITCH = "switch"
}
interface PropertyInterface {
    id: string;
    key: string;
    identifier: string;
    name: string | null;
    settable: boolean;
    queryable: boolean;
    dataType: DataType;
    unit: string | null;
    format: string | null;
    value: any;
    expected: any;
    pending: boolean;
    command: PropertyCommandState | null;
    lastResult: PropertyCommandResult | null;
    backup: string | null;
    relationshipNames: Array<string>;
    device: DeviceInterface | null;
    deviceBackward: DeviceInterface | null;
    deviceId: string;
    isAnalogSensor: boolean;
    isBinarySensor: boolean;
    isAnalogActor: boolean;
    isBinaryActor: boolean;
    isSwitch: boolean;
    isInteger: boolean;
    isFloat: boolean;
    isNumber: boolean;
    isBoolean: boolean;
    isString: boolean;
    isEnum: boolean;
    isColor: boolean;
    isSettable: boolean;
    isQueryable: boolean;
    binaryValue: boolean;
    binaryExpected: boolean | null;
    analogValue: string;
    analogExpected: string | null;
    formattedValue: string;
    icon: string;
}

declare enum DevicePropertyEntityTypes {
    PROPERTY = "devices-module/device-property"
}
interface DevicePropertyInterface extends PropertyInterface {
    type: DevicePropertyEntityTypes;
    title: string;
}
interface DevicePropertyAttributesResponseInterface {
    key: string;
    identifier: string;
    name: string | null;
    settable: boolean;
    queryable: boolean;
    dataType: DataType | null;
    unit: string | null;
    format: string | null;
    value: string | number | boolean | null;
    expected: string | number | boolean | null;
    pending: boolean;
}
interface DeviceRelationshipResponseInterface$3 extends TJsonApiRelationshipData {
    id: string;
    type: DeviceEntityTypes;
}
interface DeviceRelationshipsResponseInterface$2 extends TJsonApiRelation {
    data: DeviceRelationshipResponseInterface$3;
}
interface DevicePropertyRelationshipsResponseInterface extends TJsonApiRelationships {
    device: DeviceRelationshipsResponseInterface$2;
}
interface DevicePropertyDataResponseInterface extends TJsonApiData {
    id: string;
    type: DevicePropertyEntityTypes;
    attributes: DevicePropertyAttributesResponseInterface;
    relationships: DevicePropertyRelationshipsResponseInterface;
}
interface DevicePropertyResponseInterface extends TJsonApiBody {
    data: DevicePropertyDataResponseInterface;
}
interface DevicePropertiesResponseInterface extends TJsonApiBody {
    data: Array<DevicePropertyDataResponseInterface>;
}
interface DevicePropertyUpdateInterface {
    name?: string | null;
}

declare enum ConfigurationNumberDatatypeTypes {
    CHAR = "char",
    UNSIGNED_CHAR = "uchar",
    SHORT = "short",
    UNSIGNED_SHORT = "ushort",
    INT = "int",
    UNSIGNED_INT = "uint",
    FLOAT = "float"
}
declare enum ConfigurationIntegerDatatypeTypes {
    CHAR = "char",
    UNSIGNED_CHAR = "uchar",
    SHORT = "short",
    UNSIGNED_SHORT = "ushort",
    INT = "int",
    UNSIGNED_INT = "uint"
}
interface ValuesItemInterface {
    name: string;
    value: any;
}
interface ConfigurationInterface {
    id: string;
    key: string;
    identifier: string;
    name: string | null;
    comment: string | null;
    default: any;
    value: any;
    dataType: DataType;
    min: number | null;
    max: number | null;
    step: number | null;
    values: Array<ValuesItemInterface>;
    relationshipNames: Array<string>;
    isInteger: boolean;
    isFloat: boolean;
    isNumber: boolean;
    isBoolean: boolean;
    isString: boolean;
    isSelect: boolean;
}

declare enum DeviceConfigurationEntityTypes {
    CONFIGURATION = "devices-module/device-configuration"
}
interface DeviceConfigurationInterface extends ConfigurationInterface {
    type: DeviceConfigurationEntityTypes;
    device: DeviceInterface | null;
    deviceBackward: DeviceInterface | null;
    deviceId: string;
    selectValues: Array<ValuesItemInterface>;
    formattedValue: any;
    title: string;
    description: string | null;
}
interface DeviceConfigurationAttributesResponseInterface {
    key: string;
    identifier: string;
    name: string | null;
    comment: string | null;
    dataType: DataType | null;
    default: string | number | boolean | null;
    value: string | number | boolean | null;
    min?: number;
    max?: number;
    step?: number;
    values: Array<{
        name: string;
        value: string;
    }>;
}
interface DeviceRelationshipResponseInterface$2 extends TJsonApiRelationshipData {
    id: string;
    type: DeviceEntityTypes;
}
interface DeviceRelationshipsResponseInterface$1 extends TJsonApiRelation {
    data: DeviceRelationshipResponseInterface$2;
}
interface DeviceConfigurationRelationshipsResponseInterface$1 extends TJsonApiRelationships {
    device: DeviceRelationshipsResponseInterface$1;
}
interface DeviceConfigurationDataResponseInterface extends TJsonApiData {
    id: string;
    type: DeviceConfigurationEntityTypes;
    attributes: DeviceConfigurationAttributesResponseInterface;
    relationships: DeviceConfigurationRelationshipsResponseInterface$1;
}
interface DeviceConfigurationResponseInterface extends TJsonApiBody {
    data: DeviceConfigurationDataResponseInterface;
}
interface DeviceConfigurationsResponseInterface extends TJsonApiBody {
    data: Array<DeviceConfigurationDataResponseInterface>;
}
interface DeviceConfigurationUpdateInterface {
    name?: string | null;
    comment?: string | null;
}

declare enum ChannelPropertyEntityTypes {
    PROPERTY = "devices-module/channel-property"
}
interface ChannelPropertyInterface extends PropertyInterface {
    type: ChannelPropertyEntityTypes;
    channel: ChannelInterface | null;
    channelBackward: ChannelInterface | null;
    channelId: string;
    title: string;
}
interface ChannelPropertyAttributesResponseInterface {
    key: string;
    identifier: string;
    name: string | null;
    settable: boolean;
    queryable: boolean;
    dataType: DataType | null;
    unit: string | null;
    format: string | null;
    value: string | number | boolean | null;
    expected: string | number | boolean | null;
    pending: boolean;
}
interface ChannelRelationshipResponseInterface$1 extends TJsonApiRelationshipData {
    id: string;
    type: ChannelEntityTypes;
}
interface ChannelRelationshipsResponseInterface$2 extends TJsonApiRelation {
    data: ChannelRelationshipResponseInterface$1;
}
interface ChannelPropertyRelationshipsResponseInterface extends TJsonApiRelationships {
    device: ChannelRelationshipsResponseInterface$2;
}
interface ChannelPropertyDataResponseInterface extends TJsonApiData {
    id: string;
    type: ChannelPropertyEntityTypes;
    attributes: ChannelPropertyAttributesResponseInterface;
    relationships: ChannelPropertyRelationshipsResponseInterface;
}
interface ChannelPropertyResponseInterface extends TJsonApiBody {
    data: ChannelPropertyDataResponseInterface;
}
interface ChannelPropertiesResponseInterface extends TJsonApiBody {
    data: Array<ChannelPropertyDataResponseInterface>;
}
interface ChannelPropertyUpdateInterface {
    name?: string | null;
}

declare enum ChannelConfigurationEntityTypes {
    CONFIGURATION = "devices-module/channel-configuration"
}
interface ChannelConfigurationInterface extends ConfigurationInterface {
    type: ChannelConfigurationEntityTypes;
    channel: ChannelInterface | null;
    channelBackward: ChannelInterface | null;
    channelId: string;
    selectValues: Array<ValuesItemInterface>;
    formattedValue: any;
    device: DeviceInterface | null;
    title: string;
    description: string | null;
}
interface ChannelConfigurationAttributesResponseInterface {
    key: string;
    identifier: string;
    name: string | null;
    comment: string | null;
    dataType: DataType | null;
    default: string | number | boolean | null;
    value: string | number | boolean | null;
    min?: number;
    max?: number;
    step?: number;
    values: Array<{
        name: string;
        value: string;
    }>;
}
interface ChannelRelationshipResponseInterface extends TJsonApiRelationshipData {
    id: string;
    type: ChannelEntityTypes;
}
interface ChannelRelationshipsResponseInterface$1 extends TJsonApiRelation {
    data: ChannelRelationshipResponseInterface;
}
interface ChannelConfigurationRelationshipsResponseInterface$1 extends TJsonApiRelationships {
    device: ChannelRelationshipsResponseInterface$1;
}
interface ChannelConfigurationDataResponseInterface extends TJsonApiData {
    id: string;
    type: ChannelConfigurationEntityTypes;
    attributes: ChannelConfigurationAttributesResponseInterface;
    relationships: ChannelConfigurationRelationshipsResponseInterface$1;
}
interface ChannelConfigurationResponseInterface extends TJsonApiBody {
    data: ChannelConfigurationDataResponseInterface;
}
interface ChannelConfigurationsResponseInterface extends TJsonApiBody {
    data: Array<ChannelConfigurationDataResponseInterface>;
}
interface ChannelConfigurationUpdateInterface {
    name?: string | null;
    comment?: string | null;
}

declare enum ChannelEntityTypes {
    CHANNEL = "devices-module/channel"
}
interface ChannelInterface {
    id: string;
    type: ChannelEntityTypes;
    key: string;
    identifier: string;
    name: string | null;
    comment: string | null;
    control: Array<string>;
    relationshipNames: Array<string>;
    properties: Array<ChannelPropertyInterface>;
    configuration: Array<ChannelConfigurationInterface>;
    device: DeviceInterface | null;
    deviceBackward: DeviceInterface | null;
    deviceId: string;
    title: string;
}
interface ChannelAttributesResponseInterface {
    key: string;
    identifier: string;
    name: string | null;
    comment: string | null;
    control: Array<string>;
}
interface ChannelDeviceRelationshipResponseInterface extends TJsonApiRelationshipData {
    id: string;
    type: DeviceEntityTypes;
}
interface ChannelDeviceRelationshipsResponseInterface extends TJsonApiRelation {
    data: ChannelDeviceRelationshipResponseInterface;
}
interface ChannelPropertyRelationshipResponseInterface extends TJsonApiRelationshipData {
    id: string;
    type: ChannelPropertyEntityTypes;
}
interface ChannelPropertiesRelationshipsResponseInterface extends TJsonApiRelation {
    data: Array<ChannelPropertyRelationshipResponseInterface>;
}
interface ChannelConfigurationRelationshipResponseInterface extends TJsonApiRelationshipData {
    id: string;
    type: ChannelConfigurationEntityTypes;
}
interface ChannelConfigurationRelationshipsResponseInterface extends TJsonApiRelation {
    data: Array<ChannelConfigurationRelationshipResponseInterface>;
}
interface ChannelRelationshipsResponseInterface extends TJsonApiRelationships {
    device: ChannelDeviceRelationshipsResponseInterface;
    properties: ChannelPropertiesRelationshipsResponseInterface;
    configuration: ChannelConfigurationRelationshipsResponseInterface;
}
interface ChannelDataResponseInterface extends TJsonApiData {
    id: string;
    type: ChannelEntityTypes;
    attributes: ChannelAttributesResponseInterface;
    relationships: ChannelRelationshipsResponseInterface;
}
interface ChannelResponseInterface extends TJsonApiBody {
    data: ChannelDataResponseInterface;
}
interface ChannelsResponseInterface extends TJsonApiBody {
    data: Array<ChannelDataResponseInterface>;
}
interface ChannelUpdateInterface {
    name?: string | null;
    comment?: string | null;
}

declare enum ConnectorEntityTypes {
    FB_BUS = "devices-module/connector-fb-bus",
    FB_MQTT_V1 = "devices-module/connector-fb-mqtt-v1"
}
interface ConnectorInterface {
    id: string;
    type: ConnectorEntityTypes;
    name: string;
    enabled: boolean;
    control: Array<string>;
    address: number | null;
    serialInterface: string | null;
    baudRate: number | null;
    server: string | null;
    port: number | null;
    securedPort: number | null;
    username: string | null;
    password: string | null;
    relationshipNames: Array<string>;
    devices: Array<DeviceConnectorInterface>;
    isEnabled: boolean;
    icon: string;
}
interface ConnectorAttributesResponseInterface {
    name: string;
    enabled: boolean;
    control: Array<string>;
}
interface FbBusConnectorAttributesResponseInterface extends ConnectorAttributesResponseInterface {
    address: number | null;
    serial_interface: string | null;
    baud_rate: number | null;
}
interface FbMqttV1ConnectorAttributesResponseInterface extends ConnectorAttributesResponseInterface {
    server: string | null;
    port: number | null;
    secured_port: number | null;
    username: string | null;
    password: string | null;
}
interface ConnectorDeviceRelationshipResponseInterface extends TJsonApiRelationshipData {
    id: string;
    type: DeviceEntityTypes;
}
interface ConnectorDevicesRelationshipsResponseInterface extends TJsonApiRelation {
    data: Array<ConnectorDeviceRelationshipResponseInterface>;
}
interface ConnectorRelationshipsResponseInterface extends TJsonApiRelationships {
    devices: ConnectorDevicesRelationshipsResponseInterface;
}
interface ConnectorDataResponseInterface extends TJsonApiData {
    id: string;
    type: ConnectorEntityTypes;
    attributes: FbBusConnectorAttributesResponseInterface | FbMqttV1ConnectorAttributesResponseInterface;
    relationships: ConnectorRelationshipsResponseInterface;
}
interface ConnectorResponseInterface extends TJsonApiBody {
    data: ConnectorDataResponseInterface;
}
interface ConnectorsResponseInterface extends TJsonApiBody {
    data: Array<ConnectorDataResponseInterface>;
}
interface ConnectorUpdateInterface {
    name?: string;
    enabled?: boolean;
}

declare enum DeviceConnectorEntityTypes {
    CONNECTOR = "devices-module/device-connector"
}
interface DeviceConnectorInterface {
    id: string;
    type: DeviceConnectorEntityTypes;
    draft: boolean;
    address: number;
    maxPacketLength: number;
    descriptionSupport: boolean;
    settingsSupport: boolean;
    configuredKeyLength: number;
    pubSubPubSupport: boolean;
    pubSubSubSupport: boolean;
    pubSubSubMaxSubscriptions: number;
    pubSubSubMaxConditions: number;
    pubSubSubMaxActions: number;
    username: string;
    password: string;
    relationshipNames: Array<string>;
    device: DeviceInterface | null;
    deviceBackward: DeviceInterface | null;
    connector: ConnectorInterface | null;
    connectorBackward: ConnectorInterface | null;
    deviceId: string;
    connectorId: string;
}
interface DeviceConnectorAttributesResponseInterface {
    address?: number;
    max_packet_length?: number;
    description_support?: boolean;
    settings_support?: boolean;
    configured_key_length?: number;
    pub_sub_pub_support?: boolean;
    pub_sub_sub_support?: boolean;
    pub_sub_sub_max_subscriptions?: number;
    pub_sub_sub_max_conditions?: number;
    pub_sub_sub_max_actions?: number;
    username?: string;
    password?: string;
}
interface DeviceRelationshipResponseInterface$1 extends TJsonApiRelationshipData {
    id: string;
    type: DeviceEntityTypes;
}
interface DeviceRelationshipsResponseInterface extends TJsonApiRelation {
    data: DeviceRelationshipResponseInterface$1;
}
interface DeviceConnectorRelationshipsResponseInterface extends TJsonApiRelationships {
    device: DeviceRelationshipsResponseInterface;
}
interface DeviceConnectorDataResponseInterface extends TJsonApiData {
    id: string;
    type: DeviceConnectorEntityTypes;
    attributes: DeviceConnectorAttributesResponseInterface;
    relationships: DeviceConnectorRelationshipsResponseInterface;
}
interface DeviceConnectorResponseInterface extends TJsonApiBody {
    data: DeviceConnectorDataResponseInterface;
}
interface DeviceConnectorCreateInterface {
    username?: string;
    password?: string;
}
interface DeviceConnectorUpdateInterface {
    password?: string;
}

declare class DeviceConnector extends Model implements DeviceConnectorInterface {
    static get entity(): string;
    static fields(): Fields;
    id: string;
    type: DeviceConnectorEntityTypes;
    draft: boolean;
    address: number;
    maxPacketLength: number;
    descriptionSupport: boolean;
    settingsSupport: boolean;
    configuredKeyLength: number;
    pubSubPubSupport: boolean;
    pubSubSubSupport: boolean;
    pubSubSubMaxSubscriptions: number;
    pubSubSubMaxConditions: number;
    pubSubSubMaxActions: number;
    username: string;
    password: string;
    relationshipNames: Array<string>;
    device: DeviceInterface | null;
    deviceBackward: DeviceInterface | null;
    connector: ConnectorInterface | null;
    connectorBackward: ConnectorInterface | null;
    deviceId: string;
    connectorId: string;
    static get(device: DeviceInterface): Promise<boolean>;
    static add(device: DeviceInterface, connector: ConnectorInterface, data: DeviceConnectorCreateInterface, id?: string, draft?: boolean): Promise<Item<DeviceConnector>>;
    static edit(connector: DeviceConnectorInterface, data: DeviceConnectorUpdateInterface): Promise<Item<DeviceConnector>>;
    static save(connector: DeviceConnectorInterface): Promise<Item<DeviceConnector>>;
    static remove(connector: DeviceConnectorInterface): Promise<boolean>;
    static reset(): void;
}

declare enum DeviceEntityTypes {
    DEVICE = "devices-module/device"
}
interface DeviceInterface {
    id: string;
    type: DeviceEntityTypes;
    draft: boolean;
    parentId: string | null;
    key: string;
    identifier: string;
    name: string | null;
    comment: string | null;
    state: DeviceConnectionState;
    enabled: boolean;
    hardwareModel: DeviceModel;
    hardwareManufacturer: HardwareManufacturer;
    hardwareVersion: string | null;
    macAddress: string | null;
    firmwareManufacturer: FirmwareManufacturer;
    firmwareVersion: string | null;
    control: Array<string>;
    owner: string | null;
    relationshipNames: Array<string>;
    children: Array<DeviceInterface>;
    channels: Array<ChannelInterface>;
    properties: Array<DevicePropertyInterface>;
    configuration: Array<DeviceConfigurationInterface>;
    connector: DeviceConnector;
    isEnabled: boolean;
    isReady: boolean;
    icon: string;
    title: string;
    hasComment: boolean;
    isCustomModel: boolean;
}
interface DeviceAttributesResponseInterface {
    key: string;
    identifier: string;
    name: string | null;
    comment: string | null;
    state: DeviceConnectionState;
    enabled: boolean;
    control: Array<string>;
    owner: string | null;
}
interface DeviceRelationshipResponseInterface extends TJsonApiRelationshipData {
    id: string;
    type: DeviceEntityTypes;
}
interface DeviceParentRelationshipsResponseInterface extends TJsonApiRelation {
    data: DeviceRelationshipResponseInterface;
}
interface DeviceChildrenRelationshipsResponseInterface extends TJsonApiRelation {
    data: Array<DeviceRelationshipResponseInterface>;
}
interface DevicePropertyRelationshipResponseInterface extends TJsonApiRelationshipData {
    id: string;
    type: DevicePropertyEntityTypes;
}
interface DevicePropertiesRelationshipsResponseInterface extends TJsonApiRelation {
    data: Array<DevicePropertyRelationshipResponseInterface>;
}
interface DeviceConfigurationRelationshipResponseInterface extends TJsonApiRelationshipData {
    id: string;
    type: DeviceConfigurationEntityTypes;
}
interface DeviceConfigurationRelationshipsResponseInterface extends TJsonApiRelation {
    data: Array<DeviceConfigurationRelationshipResponseInterface>;
}
interface DeviceChannelRelationshipResponseInterface extends TJsonApiRelationshipData {
    id: string;
    type: ChannelEntityTypes;
}
interface DeviceChannelsRelationshipsResponseInterface extends TJsonApiRelation {
    data: Array<DeviceChannelRelationshipResponseInterface>;
}
interface PhysicalDeviceRelationshipsResponseInterface extends TJsonApiRelationships {
    parent: DeviceParentRelationshipsResponseInterface;
    children: DeviceChildrenRelationshipsResponseInterface;
    properties: DevicePropertiesRelationshipsResponseInterface;
    configuration: DeviceConfigurationRelationshipsResponseInterface;
    channels: DeviceChannelsRelationshipsResponseInterface;
}
interface DeviceDataResponseInterface extends TJsonApiData {
    id: string;
    type: DeviceEntityTypes;
    attributes: DeviceAttributesResponseInterface;
    relationships: PhysicalDeviceRelationshipsResponseInterface;
    included?: Array<ChannelResponseInterface>;
}
interface DeviceResponseInterface extends TJsonApiBody {
    data: DeviceDataResponseInterface;
}
interface DevicesResponseInterface extends TJsonApiBody {
    data: Array<DeviceDataResponseInterface>;
}
interface DeviceCreateInterface {
    identifier: string;
    name?: string | null;
    comment?: string | null;
    enabled?: boolean;
}
interface DeviceUpdateInterface {
    name?: string | null;
    comment?: string | null;
    enabled?: boolean;
}

declare class Device extends Model implements DeviceInterface {
    static get entity(): string;
    static fields(): Fields;
    id: string;
    type: DeviceEntityTypes;
    draft: boolean;
    parentId: string | null;
    key: string;
    identifier: string;
    name: string | null;
    comment: string | null;
    state: DeviceConnectionState;
    enabled: boolean;
    hardwareModel: DeviceModel;
    hardwareManufacturer: HardwareManufacturer;
    hardwareVersion: string | null;
    macAddress: string | null;
    firmwareManufacturer: FirmwareManufacturer;
    firmwareVersion: string | null;
    control: Array<string>;
    owner: string | null;
    relationshipNames: Array<string>;
    children: Array<DeviceInterface>;
    channels: Array<ChannelInterface>;
    properties: Array<DevicePropertyInterface>;
    configuration: Array<DeviceConfigurationInterface>;
    connector: DeviceConnector;
    get isEnabled(): boolean;
    get isReady(): boolean;
    get icon(): string;
    get title(): string;
    get hasComment(): boolean;
    get isCustomModel(): boolean;
    static get(id: string, includeChannels: boolean): Promise<boolean>;
    static fetch(includeChannels: boolean): Promise<boolean>;
    static add(data: DeviceCreateInterface, id?: string, draft?: boolean): Promise<Item<Device>>;
    static edit(device: DeviceInterface, data: DeviceUpdateInterface): Promise<Item<Device>>;
    static save(device: DeviceInterface): Promise<Item<Device>>;
    static remove(device: DeviceInterface): Promise<boolean>;
    static transmitCommand(device: DeviceInterface, command: DeviceControlAction): Promise<boolean>;
    static reset(): void;
}

declare class Property extends Model implements PropertyInterface {
    static fields(): Fields;
    id: string;
    key: string;
    identifier: string;
    name: string | null;
    settable: boolean;
    queryable: boolean;
    dataType: DataType;
    unit: string | null;
    format: string | null;
    value: any;
    expected: any;
    pending: boolean;
    command: PropertyCommandState | null;
    lastResult: PropertyCommandResult | null;
    backup: string | null;
    relationshipNames: Array<string>;
    device: DeviceInterface | null;
    deviceBackward: DeviceInterface | null;
    deviceId: string;
    get isAnalogSensor(): boolean;
    get isBinarySensor(): boolean;
    get isAnalogActor(): boolean;
    get isBinaryActor(): boolean;
    get isSwitch(): boolean;
    get isInteger(): boolean;
    get isFloat(): boolean;
    get isNumber(): boolean;
    get isBoolean(): boolean;
    get isString(): boolean;
    get isEnum(): boolean;
    get isColor(): boolean;
    get isSettable(): boolean;
    get isQueryable(): boolean;
    get binaryValue(): boolean;
    get binaryExpected(): boolean | null;
    get analogValue(): string;
    get analogExpected(): string | null;
    get formattedValue(): string;
    get icon(): string;
}

declare class DeviceProperty extends Property implements DevicePropertyInterface {
    static get entity(): string;
    static fields(): Fields;
    type: DevicePropertyEntityTypes;
    get title(): string;
    static get(device: DeviceInterface, id: string): Promise<boolean>;
    static fetch(device: DeviceInterface): Promise<boolean>;
    static edit(property: DevicePropertyInterface, data: DevicePropertyUpdateInterface): Promise<Item<DeviceProperty>>;
    static transmitData(property: DevicePropertyInterface, value: string): Promise<boolean>;
    static reset(): void;
}

declare class Configuration extends Model implements ConfigurationInterface {
    static fields(): Fields;
    id: string;
    key: string;
    identifier: string;
    name: string | null;
    comment: string | null;
    value: any;
    default: any;
    dataType: DataType;
    min: number | null;
    max: number | null;
    step: number | null;
    values: Array<ValuesItemInterface>;
    relationshipNames: Array<string>;
    get isInteger(): boolean;
    get isFloat(): boolean;
    get isNumber(): boolean;
    get isBoolean(): boolean;
    get isString(): boolean;
    get isSelect(): boolean;
}

declare class DeviceConfiguration extends Configuration implements DeviceConfigurationInterface {
    static get entity(): string;
    static fields(): Fields;
    type: DeviceConfigurationEntityTypes;
    device: DeviceInterface | null;
    deviceBackward: DeviceInterface | null;
    deviceId: string;
    get title(): string;
    get description(): string | null;
    get selectValues(): Array<ValuesItemInterface>;
    get formattedValue(): any;
    static get(device: DeviceInterface, id: string): Promise<boolean>;
    static fetch(device: DeviceInterface): Promise<boolean>;
    static edit(property: DeviceConfigurationInterface, data: DeviceConfigurationUpdateInterface): Promise<Item<DeviceConfiguration>>;
    static transmitData(property: DeviceConfigurationInterface, value: string): Promise<boolean>;
    static reset(): void;
}

declare class Channel extends Model implements ChannelInterface {
    static get entity(): string;
    static fields(): Fields;
    id: string;
    type: ChannelEntityTypes;
    key: string;
    identifier: string;
    name: string | null;
    comment: string | null;
    control: Array<string>;
    relationshipNames: Array<string>;
    properties: Array<ChannelPropertyInterface>;
    configuration: Array<ChannelConfigurationInterface>;
    device: DeviceInterface | null;
    deviceBackward: DeviceInterface | null;
    deviceId: string;
    get title(): string;
    static get(device: DeviceInterface, id: string): Promise<boolean>;
    static fetch(device: DeviceInterface): Promise<boolean>;
    static edit(channel: ChannelInterface, data: ChannelUpdateInterface): Promise<Item<Channel>>;
    static transmitCommand(channel: ChannelInterface, command: ChannelControlAction): Promise<boolean>;
    static reset(): void;
}

declare class ChannelProperty extends Property implements ChannelPropertyInterface {
    static get entity(): string;
    static fields(): Fields;
    type: ChannelPropertyEntityTypes;
    channel: ChannelInterface | null;
    channelBackward: ChannelInterface | null;
    channelId: string;
    get title(): string;
    get device(): DeviceInterface | null;
    static get(channel: ChannelInterface, id: string): Promise<boolean>;
    static fetch(channel: ChannelInterface): Promise<boolean>;
    static edit(property: ChannelPropertyInterface, data: ChannelPropertyUpdateInterface): Promise<Item<ChannelProperty>>;
    static transmitData(property: ChannelPropertyInterface, value: string): Promise<boolean>;
    static reset(): void;
}

declare class ChannelConfiguration extends Configuration implements ChannelConfigurationInterface {
    static get entity(): string;
    static fields(): Fields;
    type: ChannelConfigurationEntityTypes;
    channel: ChannelInterface | null;
    channelBackward: ChannelInterface | null;
    channelId: string;
    get title(): string;
    get description(): string | null;
    get selectValues(): Array<ValuesItemInterface>;
    get formattedValue(): any;
    get device(): DeviceInterface | null;
    static get(channel: ChannelInterface, id: string): Promise<boolean>;
    static fetch(channel: ChannelInterface): Promise<boolean>;
    static edit(property: ChannelConfigurationInterface, data: ChannelConfigurationUpdateInterface): Promise<Item<ChannelConfiguration>>;
    static transmitData(property: ChannelConfigurationInterface, value: string): Promise<boolean>;
    static reset(): void;
}

declare class Connector extends Model implements ConnectorInterface {
    static get entity(): string;
    static fields(): Fields;
    id: string;
    type: ConnectorEntityTypes;
    name: string;
    enabled: boolean;
    control: Array<string>;
    relationshipNames: Array<string>;
    devices: Array<DeviceConnectorInterface>;
    address: number;
    serialInterface: string;
    baudRate: number;
    server: string;
    port: number;
    securedPort: number;
    username: string;
    password: string;
    get isEnabled(): boolean;
    get icon(): string;
    static get(id: string): Promise<boolean>;
    static fetch(): Promise<boolean>;
    static edit(connector: ConnectorInterface, data: ConnectorUpdateInterface): Promise<Item<Connector>>;
    static transmitCommand(connector: ConnectorInterface, command: ConnectorControlAction): Promise<boolean>;
    static reset(): void;
}

interface InstallFunction extends Plugin {
    installed?: boolean;
}
interface GlobalConfigInterface {
    database: Database;
    originName?: string;
}
interface ComponentsInterface {
    Model: typeof Model;
}
declare module '@vuex-orm/core' {
    namespace Model {
        const $devicesModuleOrigin: string;
    }
}

declare const plugin: {
    install: InstallFunction;
};

export default plugin;
export { ActorNameTypes, Channel, ChannelConfiguration, ChannelConfigurationDataResponseInterface, ChannelConfigurationEntityTypes, ChannelConfigurationInterface, ChannelConfigurationResponseInterface, ChannelConfigurationUpdateInterface, ChannelConfigurationsResponseInterface, ChannelDataResponseInterface, ChannelEntityTypes, ChannelInterface, ChannelPropertiesResponseInterface, ChannelProperty, ChannelPropertyDataResponseInterface, ChannelPropertyEntityTypes, ChannelPropertyInterface, ChannelPropertyResponseInterface, ChannelPropertyUpdateInterface, ChannelResponseInterface, ChannelUpdateInterface, ChannelsResponseInterface, ComponentsInterface, ConfigurationIntegerDatatypeTypes, ConfigurationInterface, ConfigurationNumberDatatypeTypes, Connector, ConnectorDataResponseInterface, ConnectorEntityTypes, ConnectorInterface, ConnectorResponseInterface, ConnectorUpdateInterface, ConnectorsResponseInterface, Device, DeviceConfiguration, DeviceConfigurationDataResponseInterface, DeviceConfigurationEntityTypes, DeviceConfigurationInterface, DeviceConfigurationResponseInterface, DeviceConfigurationUpdateInterface, DeviceConfigurationsResponseInterface, DeviceConnector, DeviceConnectorCreateInterface, DeviceConnectorDataResponseInterface, DeviceConnectorEntityTypes, DeviceConnectorInterface, DeviceConnectorResponseInterface, DeviceConnectorUpdateInterface, DeviceCreateInterface, DeviceDataResponseInterface, DeviceEntityTypes, DeviceInterface, DevicePropertiesResponseInterface, DeviceProperty, DevicePropertyDataResponseInterface, DevicePropertyEntityTypes, DevicePropertyInterface, DevicePropertyResponseInterface, DevicePropertyUpdateInterface, DeviceResponseInterface, DeviceUpdateInterface, DevicesResponseInterface, GlobalConfigInterface, InstallFunction, PropertyCommandResult, PropertyCommandState, PropertyIntegerDatatypeTypes, PropertyInterface, PropertyNumberDatatypeTypes, SensorNameTypes, ValuesItemInterface };
