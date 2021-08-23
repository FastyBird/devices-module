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

# pylint: disable=too-many-lines

"""
Module models definitions
"""

# Library dependencies
import uuid
import datetime
from abc import abstractmethod, ABC
from typing import List, Dict
from application_events.database import (
    DatabaseEntityCreatedEvent,
    DatabaseEntityUpdatedEvent,
)
from application_events.dispatcher import app_dispatcher
from modules_metadata.types import DataType, ModuleOrigin
from pony.orm import core as orm, Database, PrimaryKey, Required, Optional, Set, Json

# Library libs
from devices_module.items import ConnectorItem, DevicePropertyItem, ChannelPropertyItem


def define_entities(db: Database):  # pylint: disable=invalid-name
    """Register all module entities and initialize repositories cache"""

    class ConnectorEntity(db.Entity):
        """
        Connector entity

        @package        FastyBird:DevicesModule!
        @module         models

        @author         Adam Kadlec <adam.kadlec@fastybird.com>
        """
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
            """Before insert entity hook"""
            self.created_at = datetime.datetime.now()

        def before_update(self) -> None:
            """Before update entity hook"""
            self.updated_at = datetime.datetime.now()

    class DeviceEntity(db.Entity):
        """
        Device entity

        @package        FastyBird:DevicesModule!
        @module         models

        @author         Adam Kadlec <adam.kadlec@fastybird.com>
        """
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
            """Transform entity to dictionary"""
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
            """Get list of controls strings"""
            controls: List[str] = []

            for control in self.controls:
                controls.append(control.name)

            return controls

        def before_insert(self) -> None:
            """Before insert entity hook"""
            self.hardware_model = self.hardware_model.lower()
            self.hardware_manufacturer = self.hardware_manufacturer.lower()
            self.firmware_manufacturer = self.firmware_manufacturer.lower()

            self.created_at = datetime.datetime.now()

        def after_insert(self) -> None:
            """After insert entity hook"""
            app_dispatcher.dispatch(
                DatabaseEntityCreatedEvent.EVENT_NAME,
                DatabaseEntityCreatedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                ),
            )

        def before_update(self) -> None:
            """Before update entity hook"""
            self.hardware_model = self.hardware_model.lower()
            self.hardware_manufacturer = self.hardware_manufacturer.lower()
            self.firmware_manufacturer = self.firmware_manufacturer.lower()

            self.updated_at = datetime.datetime.now()

        def after_update(self) -> None:
            """After update entity hook"""
            app_dispatcher.dispatch(
                DatabaseEntityUpdatedEvent.EVENT_NAME,
                DatabaseEntityUpdatedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                ),
            )

    class DevicePropertyEntity(db.Entity):
        """
        Device property entity

        @package        FastyBird:DevicesModule!
        @module         models

        @author         Adam Kadlec <adam.kadlec@fastybird.com>
        """
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
            """Transform entity to dictionary"""
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
            """Before insert entity hook"""
            self.created_at = datetime.datetime.now()

        def after_insert(self) -> None:
            """After insert entity hook"""
            app_dispatcher.dispatch(
                DatabaseEntityCreatedEvent.EVENT_NAME,
                DatabaseEntityCreatedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                ),
            )

        def before_update(self) -> None:
            """Before update entity hook"""
            self.updated_at = datetime.datetime.now()

        def after_update(self) -> None:
            """After update entity hook"""
            app_dispatcher.dispatch(
                DatabaseEntityUpdatedEvent.EVENT_NAME,
                DatabaseEntityUpdatedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                ),
            )

    class DeviceConfigurationEntity(db.Entity):
        """
        Device configuration entity

        @package        FastyBird:DevicesModule!
        @module         models

        @author         Adam Kadlec <adam.kadlec@fastybird.com>
        """
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
            """Has min value flag"""
            return self.params is not None and self.params.get("min_value") is not None

        def has_max(self) -> bool:
            """Has max value flag"""
            return self.params is not None and self.params.get("max_value") is not None

        def has_step(self) -> bool:
            """Has step value flag"""
            return self.params is not None and self.params.get("step_value") is not None

        def get_value(self) -> float or int or str or None:
            """Get configuration value"""
            if self.value is None:
                return None

            if isinstance(self.data_type, DataType):
                if (
                    self.data_type in [
                        DataType.DATA_TYPE_CHAR,
                        DataType.DATA_TYPE_UCHAR,
                        DataType.DATA_TYPE_SHORT,
                        DataType.DATA_TYPE_USHORT,
                        DataType.DATA_TYPE_INT,
                        DataType.DATA_TYPE_UINT,
                    ]
                ):
                    return int(self.value)

                if self.data_type == DataType.DATA_TYPE_FLOAT:
                    return float(self.value)

            return self.value

        def get_min(self) -> float or None:
            """Get min value"""
            if self.params is not None and self.params.get("min_value") is not None:
                return float(self.params.get("min_value"))

            return None

        def set_min(self, min_value: float or None) -> None:
            """Set min value"""
            self.params["min_value"] = min_value

        def get_max(self) -> float or None:
            """Get max value"""
            if self.params is not None and self.params.get("max_value") is not None:
                return float(self.params.get("max_value"))

            return None

        def set_max(self, max_value: float or None) -> None:
            """Set max value"""
            self.params["max_value"] = max_value

        def get_step(self) -> float or None:
            """Get step value"""
            if self.params is not None and self.params.get("step_value") is not None:
                return float(self.params.get("step_value"))

            return None

        def set_step(self, step: float or None) -> None:
            """Set step value"""
            self.params["step_value"] = step

        def get_values(self) -> List[Dict[str, str]]:
            """Get values for options"""
            return self.params.get("select_values", [])

        def set_values(self, select_values: List[Dict[str, str]]) -> None:
            """Set values for options"""
            self.params["select_values"] = select_values
        def to_array(self) -> Dict[str, str or int or bool or None]:
            """Transform entity to dictionary"""
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
                    self.data_type in [
                        DataType.DATA_TYPE_CHAR,
                        DataType.DATA_TYPE_UCHAR,
                        DataType.DATA_TYPE_SHORT,
                        DataType.DATA_TYPE_USHORT,
                        DataType.DATA_TYPE_INT,
                        DataType.DATA_TYPE_UINT,
                        DataType.DATA_TYPE_FLOAT,
                    ]
                ):
                    return {
                        **structure,
                        **{
                            "min": self.get_min(),
                            "max": self.get_max(),
                            "step": self.get_step(),
                        },
                    }

                if self.data_type == DataType.DATA_TYPE_ENUM:
                    return {
                        **structure,
                        **{
                            "values": self.get_values(),
                        },
                    }

            return structure

        def before_insert(self) -> None:
            """Before insert entity hook"""
            self.created_at = datetime.datetime.now()

        def after_insert(self) -> None:
            """After insert entity hook"""
            app_dispatcher.dispatch(
                DatabaseEntityCreatedEvent.EVENT_NAME,
                DatabaseEntityCreatedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                ),
            )

        def before_update(self) -> None:
            """Before update entity hook"""
            self.updated_at = datetime.datetime.now()

        def after_update(self) -> None:
            """After update entity hook"""
            app_dispatcher.dispatch(
                DatabaseEntityUpdatedEvent.EVENT_NAME,
                DatabaseEntityUpdatedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                ),
            )

    class DeviceControlEntity(db.Entity):
        """
        Device control entity

        @package        FastyBird:DevicesModule!
        @module         models

        @author         Adam Kadlec <adam.kadlec@fastybird.com>
        """
        _table_: str = "fb_devices_controls"

        control_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="control_id")
        name: str = Optional(str, column="control_name", nullable=False)
        created_at: datetime.datetime or None = Optional(datetime.datetime, column="created_at", nullable=True)
        updated_at: datetime.datetime or None = Optional(datetime.datetime, column="updated_at", nullable=True)

        device: DeviceEntity = Required("DeviceEntity", reverse="controls", column="device_id", nullable=False)

        def before_insert(self) -> None:
            """Before insert entity hook"""
            self.created_at = datetime.datetime.now()

        def before_update(self) -> None:
            """Before update entity hook"""
            self.updated_at = datetime.datetime.now()

    class DeviceConnectorEntity(db.Entity):
        """
        Device connector entity

        @package        FastyBird:DevicesModule!
        @module         models

        @author         Adam Kadlec <adam.kadlec@fastybird.com>
        """
        _table_: str = "fb_devices_connectors"

        connector_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="device_connector_id")
        params: Json or None = Optional(Json, column="params", nullable=True)
        created_at: datetime.datetime or None = Optional(datetime.datetime, column="created_at", nullable=True)
        updated_at: datetime.datetime or None = Optional(datetime.datetime, column="updated_at", nullable=True)

        device: DeviceEntity = Required("DeviceEntity", reverse="connector", column="device_id", nullable=False)
        connector: DeviceEntity = Required("ConnectorEntity", reverse="devices", column="connector_id", nullable=False)

        def before_insert(self) -> None:
            """Before insert entity hook"""
            self.created_at = datetime.datetime.now()

        def before_update(self) -> None:
            """Before update entity hook"""
            self.updated_at = datetime.datetime.now()

    class ChannelEntity(db.Entity):
        """
        Channel entity

        @package        FastyBird:DevicesModule!
        @module         models

        @author         Adam Kadlec <adam.kadlec@fastybird.com>
        """
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
            """Transform entity to dictionary"""
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
            """Get list of controls strings"""
            controls: List[str] = []

            for control in self.controls:
                controls.append(control.name)

            return controls

        def before_insert(self) -> None:
            """Before insert entity hook"""
            self.created_at = datetime.datetime.now()

        def after_insert(self) -> None:
            """After insert entity hook"""
            app_dispatcher.dispatch(
                DatabaseEntityCreatedEvent.EVENT_NAME,
                DatabaseEntityCreatedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                ),
            )

        def before_update(self) -> None:
            """Before update entity hook"""
            self.updated_at = datetime.datetime.now()

        def after_update(self) -> None:
            """After update entity hook"""
            app_dispatcher.dispatch(
                DatabaseEntityUpdatedEvent.EVENT_NAME,
                DatabaseEntityUpdatedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                ),
            )

    class ChannelPropertyEntity(db.Entity):
        """
        Channel property entity

        @package        FastyBird:DevicesModule!
        @module         models

        @author         Adam Kadlec <adam.kadlec@fastybird.com>
        """
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
            """Transform entity to dictionary"""
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
            """Before insert entity hook"""
            self.created_at = datetime.datetime.now()

        def after_insert(self) -> None:
            """After insert entity hook"""
            app_dispatcher.dispatch(
                DatabaseEntityCreatedEvent.EVENT_NAME,
                DatabaseEntityCreatedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                ),
            )

        def before_update(self) -> None:
            """Before update entity hook"""
            self.updated_at = datetime.datetime.now()

        def after_update(self) -> None:
            """After update entity hook"""
            app_dispatcher.dispatch(
                DatabaseEntityUpdatedEvent.EVENT_NAME,
                DatabaseEntityUpdatedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                ),
            )

    class ChannelConfigurationEntity(db.Entity):
        """
        Channel configuration entity

        @package        FastyBird:DevicesModule!
        @module         models

        @author         Adam Kadlec <adam.kadlec@fastybird.com>
        """
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
            """Has min value flag"""
            return self.params is not None and self.params.get("min_value") is not None

        def has_max(self) -> bool:
            """Has max value flag"""
            return self.params is not None and self.params.get("max_value") is not None

        def has_step(self) -> bool:
            """Has step value flag"""
            return self.params is not None and self.params.get("step_value") is not None

        def get_value(self) -> float or int or str or None:
            """Get configuration value"""
            if self.value is None:
                return None

            if isinstance(self.data_type, DataType):
                if (
                    self.data_type in [
                        DataType.DATA_TYPE_CHAR,
                        DataType.DATA_TYPE_UCHAR,
                        DataType.DATA_TYPE_SHORT,
                        DataType.DATA_TYPE_USHORT,
                        DataType.DATA_TYPE_INT,
                        DataType.DATA_TYPE_UINT,
                    ]
                ):
                    return int(self.value)

                if self.data_type == DataType.DATA_TYPE_FLOAT:
                    return float(self.value)

            return self.value

        def get_min(self) -> float or None:
            """Get min value"""
            if self.params is not None and self.params.get("min_value") is not None:
                return float(self.params.get("min_value"))

            return None

        def set_min(self, min_value: float or None) -> None:
            """Set min value"""
            self.params["min_value"] = min_value

        def get_max(self) -> float or None:
            """Get max value"""
            if self.params is not None and self.params.get("max_value") is not None:
                return float(self.params.get("max_value"))

            return None

        def set_max(self, max_value: float or None) -> None:
            """Set max value"""
            self.params["max_value"] = max_value

        def get_step(self) -> float or None:
            """Get step value"""
            if self.params is not None and self.params.get("step_value") is not None:
                return float(self.params.get("step_value"))

            return None

        def set_step(self, step: float or None) -> None:
            """Set step value"""
            self.params["step_value"] = step

        def get_values(self) -> List[Dict[str, str]]:
            """Get values for options"""
            return self.params.get("select_values", [])

        def set_values(self, select_values: List[Dict[str, str]]) -> None:
            """Set values for options"""
            self.params["select_values"] = select_values

        def to_array(self) -> Dict[str, str or int or bool or None]:
            """Transform entity to dictionary"""
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
                    self.data_type in [
                        DataType.DATA_TYPE_CHAR,
                        DataType.DATA_TYPE_UCHAR,
                        DataType.DATA_TYPE_SHORT,
                        DataType.DATA_TYPE_USHORT,
                        DataType.DATA_TYPE_INT,
                        DataType.DATA_TYPE_UINT,
                        DataType.DATA_TYPE_FLOAT,
                    ]
                ):
                    return {
                        **structure,
                        **{
                            "min": self.get_min(),
                            "max": self.get_max(),
                            "step": self.get_step(),
                        },
                    }

                if self.data_type == DataType.DATA_TYPE_ENUM:
                    return {
                        **structure,
                        **{
                            "values": self.get_values(),
                        },
                    }

            return structure

        def before_insert(self) -> None:
            """Before insert entity hook"""
            self.created_at = datetime.datetime.now()

        def after_insert(self) -> None:
            """After insert entity hook"""
            app_dispatcher.dispatch(
                DatabaseEntityCreatedEvent.EVENT_NAME,
                DatabaseEntityCreatedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                ),
            )

        def before_update(self) -> None:
            """Before update entity hook"""
            self.updated_at = datetime.datetime.now()

        def after_update(self) -> None:
            """After update entity hook"""
            app_dispatcher.dispatch(
                DatabaseEntityUpdatedEvent.EVENT_NAME,
                DatabaseEntityUpdatedEvent(
                    ModuleOrigin(ModuleOrigin.DEVICES_MODULE),
                    self,
                ),
            )

    class ChannelControlEntity(db.Entity):
        """
        Channel control entity

        @package        FastyBird:DevicesModule!
        @module         models

        @author         Adam Kadlec <adam.kadlec@fastybird.com>
        """
        _table_: str = "fb_channels_controls"

        control_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="control_id")
        name: str = Optional(str, column="control_name", nullable=False)
        created_at: datetime.datetime or None = Optional(datetime.datetime, column="created_at", nullable=True)
        updated_at: datetime.datetime or None = Optional(datetime.datetime, column="updated_at", nullable=True)

        channel: ChannelEntity = Required("ChannelEntity", reverse="controls", column="channel_id", nullable=False)

        def before_insert(self) -> None:
            """Before insert entity hook"""
            self.created_at = datetime.datetime.now()

        def before_update(self) -> None:
            """Before update entity hook"""
            self.updated_at = datetime.datetime.now()

    class PropertiesRepository(ABC):
        """
        Base properties repository

        @package        FastyBird:DevicesModule!
        @module         models

        @author         Adam Kadlec <adam.kadlec@fastybird.com>
        """
        _items: Dict[str, ChannelPropertyItem or DevicePropertyItem] or None = None

        __iterator_index = 0

        # -----------------------------------------------------------------------------

        def get_property_by_id(self, property_id: uuid.UUID) -> DevicePropertyItem or ChannelPropertyItem or None:
            """Find property in cache by provided identifier"""
            if self._items is None:
                self.initialize()

            if property_id.__str__() in self._items:
                return self._items[property_id.__str__()]

            return None

        # -----------------------------------------------------------------------------

        def get_property_by_key(self, property_key: str) -> DevicePropertyItem or ChannelPropertyItem or None:
            """Find property in cache by provided key"""
            if self._items is None:
                self.initialize()

            for record in self._items.values():
                if record.key == property_key:
                    return record

            return None

        # -----------------------------------------------------------------------------

        def clear(self) -> None:
            """Clear items cache"""
            self._items = None

        # -----------------------------------------------------------------------------

        @abstractmethod
        def initialize(self) -> None:
            """Initialize repository by fetching entities from database"""

        # -----------------------------------------------------------------------------

        def __iter__(self) -> "PropertiesRepository":
            # Reset index for nex iteration
            self.__iterator_index = 0

            return self

        # -----------------------------------------------------------------------------

        def __len__(self):
            if self._items is None:
                self.initialize()

            return len(self._items.values())

        # -----------------------------------------------------------------------------

        def __next__(self) -> DevicePropertyItem or ChannelPropertyItem:
            if self._items is None:
                self.initialize()

            if self.__iterator_index < len(self._items.values()):
                items: List[DevicePropertyItem or ChannelPropertyItem] = list(self._items.values())

                result: DevicePropertyItem or ChannelPropertyItem = items[self.__iterator_index]

                self.__iterator_index += 1

                return result

            # Reset index for nex iteration
            self.__iterator_index = 0

            # End of iteration
            raise StopIteration

    class DevicesPropertiesRepository(PropertiesRepository):
        """
        Devices properties repository

        @package        FastyBird:DevicesModule!
        @module         models

        @author         Adam Kadlec <adam.kadlec@fastybird.com>
        """
        @orm.db_session
        def initialize(self) -> None:
            """Initialize repository by fetching entities from database"""
            items: Dict[str, DevicePropertyItem] = {}

            for entity in DevicePropertyEntity.select():
                items[entity.property_id.__str__()] = DevicePropertyItem(
                    property_id=entity.property_id,
                    property_identifier=entity.identifier,
                    property_key=entity.key,
                    property_settable=entity.settable,
                    property_queryable=entity.queryable,
                    property_data_type=entity.data_type,
                    property_format=entity.format,
                    property_unit=entity.unit,
                    device_id=entity.device.device_id,
                )

            self._items = items

    class ChannelsPropertiesRepository(PropertiesRepository):
        """
        Channels properties repository

        @package        FastyBird:DevicesModule!
        @module         models

        @author         Adam Kadlec <adam.kadlec@fastybird.com>
        """
        @orm.db_session
        def initialize(self) -> None:
            """Initialize repository by fetching entities from database"""
            items: Dict[str, ChannelPropertyItem] = {}

            for entity in ChannelPropertyEntity.select():
                items[entity.property_id.__str__()] = ChannelPropertyItem(
                    property_id=entity.property_id,
                    property_identifier=entity.identifier,
                    property_key=entity.key,
                    property_settable=entity.settable,
                    property_queryable=entity.queryable,
                    property_data_type=entity.data_type,
                    property_format=entity.format,
                    property_unit=entity.unit,
                    device_id=entity.channel.device.device_id,
                    channel_id=entity.channel.channel_id,
                )

            self._items = items

    class ConnectorsRepository(ABC):
        """
        Connectors repository

        @package        FastyBird:DevicesModule!
        @module         models

        @author         Adam Kadlec <adam.kadlec@fastybird.com>
        """
        __items: Dict[str, ConnectorItem] or None = None

        __iterator_index = 0

        # -----------------------------------------------------------------------------

        def get_connector_by_id(self, connector_id: uuid.UUID) -> ConnectorItem or None:
            """Find connector in cache by provided identifier"""
            if self.__items is None:
                self.initialize()

            if connector_id.__str__() in self.__items:
                return self.__items[connector_id.__str__()]

            return None

        # -----------------------------------------------------------------------------

        def get_connector_by_key(self, connector_key: str) -> ConnectorItem or None:
            """Find connector in cache by provided key"""
            if self.__items is None:
                self.initialize()

            for record in self.__items.values():
                if record.key == connector_key:
                    return record

            return None

        # -----------------------------------------------------------------------------

        def clear(self) -> None:
            """Clear items cache"""
            self.__items = None

        # -----------------------------------------------------------------------------

        @orm.db_session
        def initialize(self) -> None:
            """Initialize repository by fetching entities from database"""
            items: Dict[str, ConnectorItem] = {}

            for entity in ConnectorEntity.select():
                items[entity.connector_id.__str__()] = ConnectorItem(
                    connector_id=entity.connector_id,
                    connector_name=entity.name,
                    connector_key=entity.key,
                    connector_enabled=entity.enabled,
                    connector_type=entity.type,
                    connector_params=entity.params,
                )

            self.__items = items

        # -----------------------------------------------------------------------------

        def __iter__(self) -> "ConnectorsRepository":
            # Reset index for nex iteration
            self.__iterator_index = 0

            return self

        # -----------------------------------------------------------------------------

        def __len__(self):
            if self.__items is None:
                self.initialize()

            return len(self.__items.values())

        # -----------------------------------------------------------------------------

        def __next__(self) -> ConnectorItem:
            if self.__items is None:
                self.initialize()

            if self.__iterator_index < len(self.__items.values()):
                items: List[ConnectorItem] = list(self.__items.values())

                result: ConnectorItem = items[self.__iterator_index]

                self.__iterator_index += 1

                return result

            # Reset index for nex iteration
            self.__iterator_index = 0

            # End of iteration
            raise StopIteration

    connector_repository = ConnectorsRepository()
    device_property_repository = DevicesPropertiesRepository()
    channel_property_repository = ChannelsPropertiesRepository()

    return ConnectorEntity, \
           DeviceEntity, DeviceConnectorEntity, DeviceControlEntity, DevicePropertyEntity, DeviceConfigurationEntity, \
           ChannelEntity, ChannelControlEntity, ChannelPropertyEntity, ChannelConfigurationEntity, \
           connector_repository, device_property_repository, channel_property_repository
