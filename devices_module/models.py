#!/usr/bin/python3

#     Copyright 2021. FastyBird s.r.o.
#
#     Licensed under the Apache License, Version 2.0 (the "License");
#     you may not use this file except in compliance with the License.
#     You may obtain a copy of the License at
#
#         http://www.apache.org/licenses/LICENSE-2.0
#
#     Unless required by applicable law or agreed to in writing, software
#     distributed under the License is distributed on an "AS IS" BASIS,
#     WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#     See the License for the specific language governing permissions and
#     limitations under the License.

# App dependencies
import uuid
import datetime
from application_events.database import DatabaseEntityChangedEvent, EntityChangedType
from application_events.dispatcher import app_dispatcher
from modules_metadata.types import DataType, ModuleOrigin
from pony.orm import Database, PrimaryKey, Required, Optional, Set, Json
from typing import List, Dict


def define_entities(db: Database):
    class ConnectorEntity(db.Entity):
        _table_: str = "fb_connectors"

        connector_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="connector_id")
        name: str or None = Required(str, column="connector_name", nullable=False)
        key: str = Required(str, column="connector_key", unique=True, max_len=50, nullable=False)
        type: str or None = Required(str, column="connector_type", nullable=False)
        enabled: bool = Required(bool, column="connector_enabled", nullable=False, default=True)
        params: Json or None = Optional(Json, column="params", nullable=True)
        created_at: datetime.datetime or None = Optional(datetime.datetime, column="created_at", nullable=True)
        updated_at: datetime.datetime or None = Optional(datetime.datetime, column="updated_at", nullable=True)

        devices: List["DeviceConnectorEntity"] = Set("DeviceConnectorEntity", reverse="connector")

        def before_insert(self) -> None:
            self.created_at = datetime.datetime.now()

        def before_update(self) -> None:
            self.updated_at = datetime.datetime.now()

    class DeviceEntity(db.Entity):
        _table_: str = "fb_devices"

        device_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="device_id")
        identifier: str = Required(str, column="device_identifier", unique=True, max_len=50, nullable=False)
        key: str = Required(str, column="device_key", unique=True, max_len=50, nullable=False)
        parent: "DeviceEntity" = Optional("DeviceEntity", reverse="children", column="parent_id", nullable=True)
        children: List["DeviceEntity"] = Set("DeviceEntity", reverse="parent")
        name: str or None = Optional(str, column="device_name", nullable=True)
        comment: str or None = Optional(str, column="device_comment", nullable=True)
        enabled: bool = Required(bool, column="device_enabled", default=False, nullable=False)
        hardware_manufacturer: str or None = Optional(
            str,
            column="device_hardware_manufacturer",
            max_len=150,
            default="generic",
            nullable=False,
        )
        hardware_model: str or None = Optional(
            str,
            column="device_hardware_model",
            max_len=150,
            default="custom",
            nullable=False,
        )
        hardware_version: str or None = Optional(str, column="device_hardware_version", max_len=150, nullable=True)
        mac_address: str or None = Optional(str, column="device_mac_address", max_len=15, nullable=True)
        firmware_manufacturer: str or None = Optional(
            str,
            column="device_firmware_manufacturer",
            max_len=150,
            default="generic",
            nullable=False,
        )
        firmware_version: str or None = Optional(str, column="device_firmware_version", max_len=150, nullable=True)
        params: Json or None = Optional(Json, column="params", nullable=True)
        created_at: datetime.datetime or None = Optional(datetime.datetime, column="created_at", nullable=True)
        updated_at: datetime.datetime or None = Optional(datetime.datetime, column="updated_at", nullable=True)

        channels: List["ChannelEntity"] = Set("ChannelEntity", reverse="device")
        properties: List["DevicePropertyEntity"] = Set("DevicePropertyEntity", reverse="device")
        configuration: List["DeviceConfigurationEntity"] = Set("DeviceConfigurationEntity", reverse="device")
        controls: List["DeviceControlEntity"] = Set("DeviceControlEntity", reverse="device")
        connector: "DeviceConnectorEntity" or None = Optional("DeviceConnectorEntity", reverse="device")

        def to_array(self) -> Dict[str, str or int or bool or None]:
            parent_id: str or None = self.parent.device_id.__str__() if self.parent is not None else None

            return {
                "id": self.device_id.__str__(),
                "key": self.key,
                "identifier": self.identifier,
                "parent": parent_id,
                "name": self.name,
                "comment": self.comment,
                "enabled": self.enabled,
                "hardware_version": self.hardware_version,
                "hardware_manufacturer": self.hardware_manufacturer,
                "hardware_model": self.hardware_model,
                "mac_address": self.mac_address,
                "firmware_manufacturer": self.firmware_manufacturer,
                "firmware_version": self.firmware_version,
                "control": self.get_plain_controls(),
                "params": self.params,
            }

        def get_plain_controls(self) -> List[str]:
            controls: List[str] = []

            for control in self.controls:
                controls.append(control.name)

            return controls

        def before_insert(self) -> None:
            self.hardware_model = self.hardware_model.lower()
            self.hardware_manufacturer = self.hardware_manufacturer.lower()
            self.firmware_manufacturer = self.firmware_manufacturer.lower()

            self.created_at = datetime.datetime.now()

        def after_insert(self) -> None:
            app_dispatcher.dispatch(
                DatabaseEntityChangedEvent.EVENT_NAME,
                DatabaseEntityChangedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                    EntityChangedType(EntityChangedType.ENTITY_CREATED),
                ),
            )

        def before_update(self) -> None:
            self.hardware_model = self.hardware_model.lower()
            self.hardware_manufacturer = self.hardware_manufacturer.lower()
            self.firmware_manufacturer = self.firmware_manufacturer.lower()

            self.updated_at = datetime.datetime.now()

        def after_update(self) -> None:
            app_dispatcher.dispatch(
                DatabaseEntityChangedEvent.EVENT_NAME,
                DatabaseEntityChangedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                    EntityChangedType(EntityChangedType.ENTITY_UPDATED),
                ),
            )

    class DevicePropertyEntity(db.Entity):
        _table_: str = "fb_devices_properties"

        property_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="property_id")
        key: str = Required(str, column="property_key", unique=True, max_len=50, nullable=False)
        identifier: str = Required(str, column="property_identifier", max_len=50, nullable=False)
        name: str = Optional(str, column="property_name", nullable=True)
        settable: bool = Required(bool, column="property_settable", default=False, nullable=False)
        queryable: bool = Required(bool, column="property_queryable", default=False, nullable=False)
        data_type: DataType or None = Optional(DataType, column="property_data_type", nullable=True)
        unit: str or None = Optional(str, column="property_unit", nullable=True)
        format: str or None = Optional(str, column="property_format", nullable=True)
        created_at: datetime.datetime or None = Optional(datetime.datetime, column="created_at", nullable=True)
        updated_at: datetime.datetime or None = Optional(datetime.datetime, column="updated_at", nullable=True)

        device: DeviceEntity = Required("DeviceEntity", reverse="properties", column="device_id", nullable=False)

        def to_array(self) -> Dict[str, str or int or bool or None]:
            if isinstance(self.data_type, DataType):
                data_type = self.data_type.value

            elif self.data_type is None:
                data_type = None

            else:
                data_type = self.data_type

            return {
                "id": self.property_id.__str__(),
                "key": self.key,
                "identifier": self.identifier,
                "name": self.name,
                "settable": self.settable,
                "queryable": self.queryable,
                "data_type": data_type,
                "unit": self.unit,
                "format": self.format,
            }

        def before_insert(self) -> None:
            self.created_at = datetime.datetime.now()

        def after_insert(self) -> None:
            app_dispatcher.dispatch(
                DatabaseEntityChangedEvent.EVENT_NAME,
                DatabaseEntityChangedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                    EntityChangedType(EntityChangedType.ENTITY_CREATED),
                ),
            )

        def before_update(self) -> None:
            self.updated_at = datetime.datetime.now()

        def after_update(self) -> None:
            app_dispatcher.dispatch(
                DatabaseEntityChangedEvent.EVENT_NAME,
                DatabaseEntityChangedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                    EntityChangedType(EntityChangedType.ENTITY_UPDATED),
                ),
            )

    class DeviceConfigurationEntity(db.Entity):
        _table_: str = "fb_devices_configuration"

        configuration_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="configuration_id")
        key: str = Required(str, column="configuration_key", unique=True, max_len=50, nullable=False)
        identifier: str = Required(str, column="configuration_identifier", max_len=50, nullable=False)
        name: str or None = Optional(str, column="configuration_name", nullable=True)
        comment: str or None = Optional(str, column="configuration_comment", nullable=True)
        data_type: DataType = Required(DataType, column="configuration_data_type", nullable=False)
        default: str or None = Optional(str, column="configuration_default", nullable=True)
        value: str or None = Optional(str, column="configuration_value", nullable=True)
        params: Json or None = Optional(Json, column="params", nullable=True)
        created_at: datetime.datetime or None = Optional(datetime.datetime, column="created_at", nullable=True)
        updated_at: datetime.datetime or None = Optional(datetime.datetime, column="updated_at", nullable=True)

        device: DeviceEntity = Required("DeviceEntity", reverse="configuration", column="device_id", nullable=False)

        def has_min(self) -> bool:
            return True if self.params is not None and self.params.get("min_value") is not None else False

        def has_max(self) -> bool:
            return True if self.params is not None and self.params.get("max_value") is not None else False

        def has_step(self) -> bool:
            return True if self.params is not None and self.params.get("step_value") is not None else False

        def get_value(self) -> float or int or str or None:
            if self.value is None:
                return None

            if isinstance(self.data_type, DataType):
                if (
                    self.data_type == DataType.DATA_TYPE_CHAR
                    or self.data_type == DataType.DATA_TYPE_UCHAR
                    or self.data_type == DataType.DATA_TYPE_SHORT
                    or self.data_type == DataType.DATA_TYPE_USHORT
                    or self.data_type == DataType.DATA_TYPE_INT
                    or self.data_type == DataType.DATA_TYPE_UINT
                ):
                    return int(self.value)

                elif self.data_type == DataType.DATA_TYPE_FLOAT:
                    return float(self.value)

            return self.value

        def get_min(self) -> float or None:
            if self.params is not None and self.params.get("min_value") is not None:
                return float(self.params.get("min_value"))

            else:
                return None

        def set_min(self, min_value: float or None) -> None:
            self.params["min_value"] = min_value

        def get_max(self) -> float or None:
            if self.params is not None and self.params.get("max_value") is not None:
                return float(self.params.get("max_value"))

            else:
                return None

        def set_max(self, max_value: float or None) -> None:
            self.params["max_value"] = max_value

        def get_step(self) -> float or None:
            if self.params is not None and self.params.get("step_value") is not None:
                return float(self.params.get("step_value"))

            else:
                return None

        def set_step(self, step: float or None) -> None:
            self.params["step_value"] = step

        def get_values(self) -> List[Dict[str, str]]:
            return self.params.get("select_values", [])

        def set_values(self, select_values: List[Dict[str, str]]) -> None:
            self.params["select_values"] = select_values

        def to_array(self) -> Dict[str, str or int or bool or None]:
            if isinstance(self.data_type, DataType):
                data_type = self.data_type.value

            elif self.data_type is None:
                data_type = None

            else:
                data_type = self.data_type

            structure: dict = {
                "id": self.configuration_id.__str__(),
                "key": self.key,
                "identifier": self.identifier,
                "name": self.name,
                "comment": self.comment,
                "data_type": data_type,
                "default": self.default,
                "value": self.get_value(),
            }

            if isinstance(self.data_type, DataType):
                if (
                    self.data_type == DataType.DATA_TYPE_CHAR
                    or self.data_type == DataType.DATA_TYPE_UCHAR
                    or self.data_type == DataType.DATA_TYPE_SHORT
                    or self.data_type == DataType.DATA_TYPE_USHORT
                    or self.data_type == DataType.DATA_TYPE_INT
                    or self.data_type == DataType.DATA_TYPE_UINT
                    or self.data_type == DataType.DATA_TYPE_FLOAT
                ):
                    return {
                        **structure,
                        **{
                            "min": self.get_min(),
                            "max": self.get_max(),
                            "step": self.get_step(),
                        },
                    }

                elif self.data_type == DataType.DATA_TYPE_ENUM:
                    return {
                        **structure,
                        **{
                            "values": self.get_values(),
                        },
                    }

            return structure

        def before_insert(self) -> None:
            self.created_at = datetime.datetime.now()

        def after_insert(self) -> None:
            app_dispatcher.dispatch(
                DatabaseEntityChangedEvent.EVENT_NAME,
                DatabaseEntityChangedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                    EntityChangedType(EntityChangedType.ENTITY_CREATED),
                ),
            )

        def before_update(self) -> None:
            self.updated_at = datetime.datetime.now()

        def after_update(self) -> None:
            app_dispatcher.dispatch(
                DatabaseEntityChangedEvent.EVENT_NAME,
                DatabaseEntityChangedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                    EntityChangedType(EntityChangedType.ENTITY_UPDATED),
                ),
            )

    class DeviceControlEntity(db.Entity):
        _table_: str = "fb_devices_controls"

        control_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="control_id")
        name: str = Optional(str, column="control_name", nullable=False)
        created_at: datetime.datetime or None = Optional(datetime.datetime, column="created_at", nullable=True)
        updated_at: datetime.datetime or None = Optional(datetime.datetime, column="updated_at", nullable=True)

        device: DeviceEntity = Required("DeviceEntity", reverse="controls", column="device_id", nullable=False)

        def before_insert(self) -> None:
            self.created_at = datetime.datetime.now()

        def before_update(self) -> None:
            self.updated_at = datetime.datetime.now()

    class DeviceConnectorEntity(db.Entity):
        _table_: str = "fb_devices_connectors"

        connector_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="device_connector_id")
        params: Json or None = Optional(Json, column="params", nullable=True)
        created_at: datetime.datetime or None = Optional(datetime.datetime, column="created_at", nullable=True)
        updated_at: datetime.datetime or None = Optional(datetime.datetime, column="updated_at", nullable=True)

        device: DeviceEntity = Required("DeviceEntity", reverse="connector", column="device_id", nullable=False)
        connector: DeviceEntity = Required("ConnectorEntity", reverse="devices", column="connector_id", nullable=False)

        def before_insert(self) -> None:
            self.created_at = datetime.datetime.now()

        def before_update(self) -> None:
            self.updated_at = datetime.datetime.now()

    class ChannelEntity(db.Entity):
        _table_: str = "fb_channels"

        channel_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="channel_id")
        key: str = Required(str, column="channel_key", unique=True, max_len=50, nullable=False)
        identifier: str = Required(str, column="channel_identifier", max_len=40, nullable=False)
        name: str or None = Optional(str, column="channel_name", nullable=True)
        comment: str or None = Optional(str, column="channel_comment", nullable=True)
        params: Json or None = Optional(Json, column="params", nullable=True)
        created_at: datetime.datetime or None = Optional(datetime.datetime, column="created_at", nullable=True)
        updated_at: datetime.datetime or None = Optional(datetime.datetime, column="updated_at", nullable=True)

        device: DeviceEntity = Required("DeviceEntity", reverse="channels", column="device_id", nullable=False)
        properties: List["ChannelPropertyEntity"] = Set("ChannelPropertyEntity", reverse="channel")
        configuration: List["ChannelConfigurationEntity"] = Set("ChannelConfigurationEntity", reverse="channel")
        controls: List["ChannelControlEntity"] = Set("ChannelControlEntity", reverse="channel")

        def to_array(self) -> Dict[str, str or int or bool or None]:
            return {
                "id": self.channel_id.__str__(),
                "key": self.key,
                "identifier": self.identifier,
                "name": self.name,
                "comment": self.comment,
                "control": self.get_plain_controls(),
                "params": self.params,
            }

        def get_plain_controls(self) -> List[str]:
            controls: List[str] = []

            for control in self.controls:
                controls.append(control.name)

            return controls

        def before_insert(self) -> None:
            self.created_at = datetime.datetime.now()

        def after_insert(self) -> None:
            app_dispatcher.dispatch(
                DatabaseEntityChangedEvent.EVENT_NAME,
                DatabaseEntityChangedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                    EntityChangedType(EntityChangedType.ENTITY_CREATED),
                ),
            )

        def before_update(self) -> None:
            self.updated_at = datetime.datetime.now()

        def after_update(self) -> None:
            app_dispatcher.dispatch(
                DatabaseEntityChangedEvent.EVENT_NAME,
                DatabaseEntityChangedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                    EntityChangedType(EntityChangedType.ENTITY_UPDATED),
                ),
            )

    class ChannelPropertyEntity(db.Entity):
        _table_: str = "fb_channels_properties"

        property_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="property_id")
        key: str = Required(str, column="property_key", unique=True, max_len=50, nullable=False)
        identifier: str = Required(str, column="property_identifier", max_len=50, nullable=False)
        name: str = Optional(str, column="property_name", nullable=True)
        settable: bool = Required(bool, column="property_settable", default=False, nullable=False)
        queryable: bool = Required(bool, column="property_queryable", default=False, nullable=False)
        data_type: DataType or None = Optional(DataType, column="property_data_type", nullable=True)
        unit: str or None = Optional(str, column="property_unit", nullable=True)
        format: str or None = Optional(str, column="property_format", nullable=True)
        created_at: datetime.datetime or None = Optional(datetime.datetime, column="created_at", nullable=True)
        updated_at: datetime.datetime or None = Optional(datetime.datetime, column="updated_at", nullable=True)

        channel: ChannelEntity = Required("ChannelEntity", reverse="properties", column="channel_id", nullable=False)

        def to_array(self) -> Dict[str, str or int or bool or None]:
            if isinstance(self.data_type, DataType):
                data_type = self.data_type.value

            elif self.data_type is None:
                data_type = None

            else:
                data_type = self.data_type

            return {
                "id": self.property_id.__str__(),
                "key": self.key,
                "identifier": self.identifier,
                "name": self.name,
                "settable": self.settable,
                "queryable": self.queryable,
                "data_type": data_type,
                "unit": self.unit,
                "format": self.format,
            }

        def before_insert(self) -> None:
            self.created_at = datetime.datetime.now()

        def after_insert(self) -> None:
            app_dispatcher.dispatch(
                DatabaseEntityChangedEvent.EVENT_NAME,
                DatabaseEntityChangedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                    EntityChangedType(EntityChangedType.ENTITY_CREATED),
                ),
            )

        def before_update(self) -> None:
            self.updated_at = datetime.datetime.now()

        def after_update(self) -> None:
            app_dispatcher.dispatch(
                DatabaseEntityChangedEvent.EVENT_NAME,
                DatabaseEntityChangedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                    EntityChangedType(EntityChangedType.ENTITY_UPDATED),
                ),
            )

    class ChannelConfigurationEntity(db.Entity):
        _table_: str = "fb_channels_configuration"

        configuration_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="configuration_id")
        key: str = Required(str, column="configuration_key", unique=True, max_len=50, nullable=False)
        identifier: str = Required(str, column="configuration_identifier", max_len=50, nullable=False)
        name: str or None = Optional(str, column="configuration_name", nullable=True)
        comment: str or None = Optional(str, column="configuration_comment", nullable=True)
        data_type: DataType = Required(DataType, column="configuration_data_type", nullable=False)
        default: str or None = Optional(str, column="configuration_default", nullable=True)
        value: str or None = Optional(str, column="configuration_value", nullable=True)
        params: Json or None = Optional(Json, column="params", nullable=True)
        created_at: datetime.datetime or None = Optional(datetime.datetime, column="created_at", nullable=True)
        updated_at: datetime.datetime or None = Optional(datetime.datetime, column="updated_at", nullable=True)

        channel: ChannelEntity = Required("ChannelEntity", reverse="configuration", column="channel_id", nullable=False)

        def has_min(self) -> bool:
            return True if self.params is not None and self.params.get("min_value") is not None else False

        def has_max(self) -> bool:
            return True if self.params is not None and self.params.get("max_value") is not None else False

        def has_step(self) -> bool:
            return True if self.params is not None and self.params.get("step_value") is not None else False

        def get_value(self) -> float or int or str or None:
            if self.value is None:
                return None

            if isinstance(self.data_type, DataType):
                if (
                    self.data_type == DataType.DATA_TYPE_CHAR
                    or self.data_type == DataType.DATA_TYPE_UCHAR
                    or self.data_type == DataType.DATA_TYPE_SHORT
                    or self.data_type == DataType.DATA_TYPE_USHORT
                    or self.data_type == DataType.DATA_TYPE_INT
                    or self.data_type == DataType.DATA_TYPE_UINT
                ):
                    return int(self.value)

                elif self.data_type == DataType.DATA_TYPE_FLOAT:
                    return float(self.value)

            return self.value

        def get_min(self) -> float or None:
            if self.params is not None and self.params.get("min_value") is not None:
                return float(self.params.get("min_value"))

            else:
                return None

        def set_min(self, min_value: float or None) -> None:
            self.params["min_value"] = min_value

        def get_max(self) -> float or None:
            if self.params is not None and self.params.get("max_value") is not None:
                return float(self.params.get("max_value"))

            else:
                return None

        def set_max(self, max_value: float or None) -> None:
            self.params["max_value"] = max_value

        def get_step(self) -> float or None:
            if self.params is not None and self.params.get("step_value") is not None:
                return float(self.params.get("step_value"))

            else:
                return None

        def set_step(self, step: float or None) -> None:
            self.params["step_value"] = step

        def get_values(self) -> List[Dict[str, str]]:
            return self.params.get("select_values", [])

        def set_values(self, select_values: List[Dict[str, str]]) -> None:
            self.params["select_values"] = select_values

        def to_array(self) -> Dict[str, str or int or bool or None]:
            if isinstance(self.data_type, DataType):
                data_type = self.data_type.value

            elif self.data_type is None:
                data_type = None

            else:
                data_type = self.data_type

            structure: dict = {
                "id": self.configuration_id.__str__(),
                "key": self.key,
                "identifier": self.identifier,
                "name": self.name,
                "comment": self.comment,
                "data_type": data_type,
                "default": self.default,
                "value": self.get_value(),
            }

            if isinstance(self.data_type, DataType):
                if (
                    self.data_type == DataType.DATA_TYPE_CHAR
                    or self.data_type == DataType.DATA_TYPE_UCHAR
                    or self.data_type == DataType.DATA_TYPE_SHORT
                    or self.data_type == DataType.DATA_TYPE_USHORT
                    or self.data_type == DataType.DATA_TYPE_INT
                    or self.data_type == DataType.DATA_TYPE_UINT
                    or self.data_type == DataType.DATA_TYPE_FLOAT
                ):
                    return {
                        **structure,
                        **{
                            "min": self.get_min(),
                            "max": self.get_max(),
                            "step": self.get_step(),
                        },
                    }

                elif self.data_type == DataType.DATA_TYPE_ENUM:
                    return {
                        **structure,
                        **{
                            "values": self.get_values(),
                        },
                    }

            return structure

        def before_insert(self) -> None:
            self.created_at = datetime.datetime.now()

        def after_insert(self) -> None:
            app_dispatcher.dispatch(
                DatabaseEntityChangedEvent.EVENT_NAME,
                DatabaseEntityChangedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                    EntityChangedType(EntityChangedType.ENTITY_CREATED),
                ),
            )

        def before_update(self) -> None:
            self.updated_at = datetime.datetime.now()

        def after_update(self) -> None:
            app_dispatcher.dispatch(
                DatabaseEntityChangedEvent.EVENT_NAME,
                DatabaseEntityChangedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                    EntityChangedType(EntityChangedType.ENTITY_UPDATED),
                ),
            )

    class ChannelControlEntity(db.Entity):
        _table_: str = "fb_channels_controls"

        control_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="control_id")
        name: str = Optional(str, column="control_name", nullable=False)
        created_at: datetime.datetime or None = Optional(datetime.datetime, column="created_at", nullable=True)
        updated_at: datetime.datetime or None = Optional(datetime.datetime, column="updated_at", nullable=True)

        channel: ChannelEntity = Required("ChannelEntity", reverse="controls", column="channel_id", nullable=False)

        def before_insert(self) -> None:
            self.created_at = datetime.datetime.now()

        def before_update(self) -> None:
            self.updated_at = datetime.datetime.now()

    return ConnectorEntity, \
           DeviceEntity, DeviceConnectorEntity, DeviceControlEntity, DevicePropertyEntity, DeviceConfigurationEntity, \
           ChannelEntity, ChannelControlEntity, ChannelPropertyEntity, ChannelConfigurationEntity
