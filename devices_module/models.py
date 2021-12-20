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
Devices module models
"""

# Python base dependencies
import datetime
import uuid
from typing import Dict, List, Optional, Union

# Library dependencies
from exchange_plugin.dispatcher import EventDispatcher
from kink import di
from modules_metadata.devices_module import (
    ConfigurationNumberFieldAttribute,
    ConfigurationSelectFieldAttribute,
)
from modules_metadata.types import DataType
from pony.orm import PrimaryKey  # type: ignore[attr-defined]
from pony.orm import Set  # type: ignore[attr-defined]
from pony.orm import Database, Discriminator, Json  # type: ignore[attr-defined]
from pony.orm import Optional as OptionalField  # type: ignore[attr-defined]
from pony.orm import Required as RequiredField  # type: ignore[attr-defined]

# Library libs
from devices_module.events import (
    ModelEntityCreatedEvent,
    ModelEntityDeletedEvent,
    ModelEntityUpdatedEvent,
)
from devices_module.helpers import KeyHashHelpers

# Create devices module database accessor
db: Database = Database()  # type: ignore[no-any-unimported]


class ConnectorEntity(db.Entity):  # type: ignore[no-any-unimported]
    """
    Connector entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    _table_: str = "fb_connectors"

    type = Discriminator(str, column="connector_type")
    _discriminator_: str = "connector"

    connector_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="connector_id")
    name: str = RequiredField(str, column="connector_name", nullable=False)
    key: str = RequiredField(str, column="connector_key", unique=True, max_len=50, nullable=False)
    enabled: bool = OptionalField(bool, column="connector_enabled", nullable=True, default=True)
    params: Optional[Dict] = OptionalField(Json, column="params", nullable=True)

    created_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="created_at", nullable=True)
    updated_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="updated_at", nullable=True)

    devices: List["DeviceEntity"] = Set("DeviceEntity", reverse="connector")
    controls: List["ConnectorControlEntity"] = Set("ConnectorControlEntity", reverse="connector")

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
        only: Union[List[str], str, None] = None,  # pylint: disable=unused-argument
        exclude: Union[List[str], str, None] = None,  # pylint: disable=unused-argument
        with_collections: bool = False,  # pylint: disable=unused-argument
        with_lazy: bool = False,  # pylint: disable=unused-argument
        related_objects: bool = False,  # pylint: disable=unused-argument
    ) -> Dict[str, Union[str, int, bool, List[str], None]]:
        """Transform entity to dictionary"""
        return {
            "id": self.connector_id.__str__(),
            "key": self.key,
            "name": self.name,
            "type": self.type,
            "enabled": self.enabled,
            "control": self.get_plain_controls(),
        }

    # -----------------------------------------------------------------------------

    def get_plain_controls(self) -> List[str]:
        """Get list of controls strings"""
        controls: List[str] = []

        for control in self.controls:
            controls.append(control.name)

        return controls

    # -----------------------------------------------------------------------------

    def before_insert(self) -> None:
        """Before insert entity hook"""
        self.created_at = datetime.datetime.now()

        if self.key is None:
            self.key = di[KeyHashHelpers].generate_key(self)

    # -----------------------------------------------------------------------------

    def after_insert(self) -> None:
        """After insert entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityCreatedEvent.EVENT_NAME,
            ModelEntityCreatedEvent(self),
        )

    # -----------------------------------------------------------------------------

    def before_update(self) -> None:
        """Before update entity hook"""
        self.updated_at = datetime.datetime.now()

    # -----------------------------------------------------------------------------

    def after_update(self) -> None:
        """After update entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityUpdatedEvent.EVENT_NAME,
            ModelEntityUpdatedEvent(self),
        )

    # -----------------------------------------------------------------------------

    def after_delete(self) -> None:
        """After delete entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityDeletedEvent.EVENT_NAME,
            ModelEntityDeletedEvent(self),
        )


class FbBusConnectorEntity(ConnectorEntity):
    """
    FastyBird BUS connector entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    _discriminator_: str = "fb-bus"

    # -----------------------------------------------------------------------------

    @property
    def address(self) -> Optional[int]:
        """Connector address"""
        return (
            int(str(self.params.get("address", None)))
            if self.params is not None and self.params.get("address") is not None
            else None
        )

    # -----------------------------------------------------------------------------

    @property
    def serial_interface(self) -> Optional[str]:
        """Connector serial interface"""
        return (
            str(self.params.get("serial_interface", None))
            if self.params is not None and self.params.get("serial_interface") is not None
            else None
        )

    # -----------------------------------------------------------------------------

    @property
    def baud_rate(self) -> Optional[int]:
        """Connector communication baud rate"""
        return (
            int(str(self.params.get("baud_rate", None)))
            if self.params is not None and self.params.get("baud_rate") is not None
            else None
        )

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
        only: Union[List[str], str, None] = None,
        exclude: Union[List[str], str, None] = None,
        with_collections: bool = False,
        with_lazy: bool = False,
        related_objects: bool = False,
    ) -> Dict[str, Union[str, int, bool, List[str], None]]:
        """Transform entity to dictionary"""
        return {
            **{
                "address": self.address,
                "serial_interface": self.serial_interface,
                "baud_rate": self.baud_rate,
            },
            **super().to_dict(only, exclude, with_collections, with_lazy, related_objects),
        }


class FbMqttConnectorEntity(ConnectorEntity):
    """
    FastyBird MQTT v1 connector entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    _discriminator_: str = "fb-mqtt"

    # -----------------------------------------------------------------------------

    @property
    def server(self) -> Optional[str]:
        """Connector server address"""
        return (
            str(self.params.get("server", None))
            if self.params is not None and self.params.get("server") is not None
            else None
        )

    # -----------------------------------------------------------------------------

    @property
    def port(self) -> Optional[int]:
        """Connector server port"""
        return (
            int(str(self.params.get("port", None)))
            if self.params is not None and self.params.get("port") is not None
            else None
        )

    # -----------------------------------------------------------------------------

    @property
    def secured_port(self) -> Optional[int]:
        """Connector server secured port"""
        return (
            int(str(self.params.get("secured_port", None)))
            if self.params is not None and self.params.get("secured_port") is not None
            else None
        )

    # -----------------------------------------------------------------------------

    @property
    def username(self) -> Optional[str]:
        """Connector server username"""
        return (
            str(self.params.get("username", None))
            if self.params is not None and self.params.get("username") is not None
            else None
        )

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
        only: Union[List[str], str, None] = None,
        exclude: Union[List[str], str, None] = None,
        with_collections: bool = False,
        with_lazy: bool = False,
        related_objects: bool = False,
    ) -> Dict[str, Union[str, int, bool, List[str], None]]:
        """Transform entity to dictionary"""
        return {
            **{
                "server": self.server,
                "port": self.port,
                "secured_port": self.secured_port,
                "username": self.username,
            },
            **super().to_dict(only, exclude, with_collections, with_lazy, related_objects),
        }


class ShellyConnectorEntity(ConnectorEntity):
    """
    Shelly connector entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    _discriminator_: str = "shelly"


class TuyaConnectorEntity(ConnectorEntity):
    """
    Tuya connector entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    _discriminator_: str = "tuya"


class SonoffConnectorEntity(ConnectorEntity):
    """
    Sonoff connector entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    _discriminator_: str = "sonoff"


class ModbusConnectorEntity(ConnectorEntity):
    """
    Modbus connector entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    _discriminator_: str = "modbus"


class ConnectorControlEntity(db.Entity):  # type: ignore[no-any-unimported]
    """
    Connector control entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    _table_: str = "fb_connectors_controls"

    control_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="control_id")
    name: str = OptionalField(str, column="control_name", nullable=False)

    created_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="created_at", nullable=True)
    updated_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="updated_at", nullable=True)

    connector: ConnectorEntity = RequiredField(
        "ConnectorEntity", reverse="controls", column="connector_id", nullable=False
    )

    # -----------------------------------------------------------------------------

    def before_insert(self) -> None:
        """Before insert entity hook"""
        self.created_at = datetime.datetime.now()

    # -----------------------------------------------------------------------------

    def after_insert(self) -> None:
        """After insert entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityCreatedEvent.EVENT_NAME,
            ModelEntityCreatedEvent(self),
        )

    # -----------------------------------------------------------------------------

    def before_update(self) -> None:
        """Before update entity hook"""
        self.updated_at = datetime.datetime.now()

    # -----------------------------------------------------------------------------

    def after_update(self) -> None:
        """After update entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityUpdatedEvent.EVENT_NAME,
            ModelEntityUpdatedEvent(self),
        )

    # -----------------------------------------------------------------------------

    def after_delete(self) -> None:
        """After delete entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityDeletedEvent.EVENT_NAME,
            ModelEntityDeletedEvent(self),
        )


class DeviceEntity(db.Entity):  # type: ignore[no-any-unimported]
    """
    Device entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    _table_: str = "fb_devices"

    device_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="device_id")
    identifier: str = RequiredField(str, column="device_identifier", unique=True, max_len=50, nullable=False)
    key: str = OptionalField(str, column="device_key", unique=True, max_len=50, nullable=True)
    parent: Optional["DeviceEntity"] = OptionalField(
        "DeviceEntity", reverse="children", column="parent_id", nullable=True
    )
    children: List["DeviceEntity"] = Set("DeviceEntity", reverse="parent")
    name: Optional[str] = OptionalField(str, column="device_name", nullable=True)
    comment: Optional[str] = OptionalField(str, column="device_comment", nullable=True)
    enabled: bool = OptionalField(bool, column="device_enabled", default=False, nullable=True)
    hardware_manufacturer: Optional[str] = OptionalField(
        str,
        column="device_hardware_manufacturer",
        max_len=150,
        default="generic",
        nullable=False,
    )
    hardware_model: Optional[str] = OptionalField(
        str,
        column="device_hardware_model",
        max_len=150,
        default="custom",
        nullable=False,
    )
    hardware_version: Optional[str] = OptionalField(str, column="device_hardware_version", max_len=150, nullable=True)
    hardware_mac_address: Optional[str] = OptionalField(
        str, column="device_hardware_mac_address", max_len=15, nullable=True
    )
    firmware_manufacturer: Optional[str] = OptionalField(
        str,
        column="device_firmware_manufacturer",
        max_len=150,
        default="generic",
        nullable=False,
    )
    firmware_version: Optional[str] = OptionalField(str, column="device_firmware_version", max_len=150, nullable=True)
    params: Optional[Dict] = OptionalField(Json, column="params", nullable=True)

    created_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="created_at", nullable=True)
    updated_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="updated_at", nullable=True)

    channels: List["ChannelEntity"] = Set("ChannelEntity", reverse="device")
    properties: List["DevicePropertyEntity"] = Set("DevicePropertyEntity", reverse="device")
    configuration: List["DeviceConfigurationEntity"] = Set("DeviceConfigurationEntity", reverse="device")
    controls: List["DeviceControlEntity"] = Set("DeviceControlEntity", reverse="device")

    owner: Optional[str] = OptionalField(str, column="owner", max_len=15, nullable=True)

    connector: ConnectorEntity = OptionalField(
        "ConnectorEntity", reverse="devices", column="connector_id", nullable=True
    )

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
        only: Union[List[str], str, None] = None,  # pylint: disable=unused-argument
        exclude: Union[List[str], str, None] = None,  # pylint: disable=unused-argument
        with_collections: bool = False,  # pylint: disable=unused-argument
        with_lazy: bool = False,  # pylint: disable=unused-argument
        related_objects: bool = False,  # pylint: disable=unused-argument
    ) -> Dict[str, Union[str, int, bool, List[str], Dict, None]]:
        """Transform entity to dictionary"""
        parent_id: Optional[str] = self.parent.device_id.__str__() if self.parent is not None else None

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
            "hardware_mac_address": self.hardware_mac_address,
            "firmware_manufacturer": self.firmware_manufacturer,
            "firmware_version": self.firmware_version,
            "control": self.get_plain_controls(),
            "params": self.params,
        }

    # -----------------------------------------------------------------------------

    def get_plain_controls(self) -> List[str]:
        """Get list of controls strings"""
        controls: List[str] = []

        for control in self.controls:
            controls.append(control.name)

        return controls

    # -----------------------------------------------------------------------------

    def before_insert(self) -> None:
        """Before insert entity hook"""
        self.created_at = datetime.datetime.now()

        if self.hardware_model is not None:
            self.hardware_model = self.hardware_model.lower()

        if self.hardware_manufacturer is not None:
            self.hardware_manufacturer = self.hardware_manufacturer.lower()

        if self.firmware_manufacturer is not None:
            self.firmware_manufacturer = self.firmware_manufacturer.lower()

        if self.key is None:
            self.key = di[KeyHashHelpers].generate_key(self)

    # -----------------------------------------------------------------------------

    def after_insert(self) -> None:
        """After insert entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityCreatedEvent.EVENT_NAME,
            ModelEntityCreatedEvent(self),
        )

    # -----------------------------------------------------------------------------

    def before_update(self) -> None:
        """Before update entity hook"""
        self.updated_at = datetime.datetime.now()

        self.hardware_model = self.hardware_model.lower() if self.hardware_model else None
        self.hardware_manufacturer = self.hardware_manufacturer.lower() if self.hardware_manufacturer else None
        self.firmware_manufacturer = self.firmware_manufacturer.lower() if self.firmware_manufacturer else None

    # -----------------------------------------------------------------------------

    def after_update(self) -> None:
        """After update entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityUpdatedEvent.EVENT_NAME,
            ModelEntityUpdatedEvent(self),
        )

    # -----------------------------------------------------------------------------

    def after_delete(self) -> None:
        """After delete entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityDeletedEvent.EVENT_NAME,
            ModelEntityDeletedEvent(self),
        )


class DevicePropertyEntity(db.Entity):  # type: ignore[no-any-unimported]
    """
    Device property entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    _table_: str = "fb_devices_properties"

    property_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="property_id")
    key: str = OptionalField(str, column="property_key", unique=True, max_len=50, nullable=True)
    identifier: str = RequiredField(str, column="property_identifier", max_len=50, nullable=False)
    name: str = OptionalField(str, column="property_name", nullable=True)
    settable: bool = OptionalField(bool, column="property_settable", default=False, nullable=True)
    queryable: bool = OptionalField(bool, column="property_queryable", default=False, nullable=True)
    data_type: Optional[str] = OptionalField(str, column="property_data_type", nullable=True)
    unit: Optional[str] = OptionalField(str, column="property_unit", nullable=True)
    format: Optional[str] = OptionalField(str, column="property_format", nullable=True)
    invalid: Optional[str] = OptionalField(str, column="property_invalid", nullable=True)

    created_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="created_at", nullable=True)
    updated_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="updated_at", nullable=True)

    device: DeviceEntity = RequiredField("DeviceEntity", reverse="properties", column="device_id", nullable=False)

    # -----------------------------------------------------------------------------

    @property
    def data_type_formatted(self) -> Optional[DataType]:
        """Transform data type to enum value"""
        return DataType(self.data_type) if self.data_type is not None else None

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
        only: Union[List[str], str, None] = None,  # pylint: disable=unused-argument
        exclude: Union[List[str], str, None] = None,  # pylint: disable=unused-argument
        with_collections: bool = False,  # pylint: disable=unused-argument
        with_lazy: bool = False,  # pylint: disable=unused-argument
        related_objects: bool = False,  # pylint: disable=unused-argument
    ) -> Dict[str, Union[str, int, bool, None]]:
        """Transform entity to dictionary"""
        data_type: Optional[str] = None

        if isinstance(self.data_type_formatted, DataType):
            data_type = self.data_type_formatted.value

        elif self.data_type_formatted is not None:
            data_type = self.data_type_formatted

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
            "invalid": self.invalid,
            "device": self.device.device_id.__str__(),
        }

    # -----------------------------------------------------------------------------

    def before_insert(self) -> None:
        """Before insert entity hook"""
        self.created_at = datetime.datetime.now()

        if self.key is None:
            self.key = di[KeyHashHelpers].generate_key(self)

    # -----------------------------------------------------------------------------

    def after_insert(self) -> None:
        """After insert entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityCreatedEvent.EVENT_NAME,
            ModelEntityCreatedEvent(self),
        )

    # -----------------------------------------------------------------------------

    def before_update(self) -> None:
        """Before update entity hook"""
        self.updated_at = datetime.datetime.now()

    # -----------------------------------------------------------------------------

    def after_update(self) -> None:
        """After update entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityUpdatedEvent.EVENT_NAME,
            ModelEntityUpdatedEvent(self),
        )

    # -----------------------------------------------------------------------------

    def after_delete(self) -> None:
        """After delete entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityDeletedEvent.EVENT_NAME,
            ModelEntityDeletedEvent(self),
        )


class DeviceConfigurationEntity(db.Entity):  # type: ignore[no-any-unimported]
    """
    Device configuration entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    _table_: str = "fb_devices_configuration"

    configuration_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="configuration_id")
    key: str = OptionalField(str, column="configuration_key", unique=True, max_len=50, nullable=True)
    identifier: str = RequiredField(str, column="configuration_identifier", max_len=50, nullable=False)
    name: Optional[str] = OptionalField(str, column="configuration_name", nullable=True)
    comment: Optional[str] = OptionalField(str, column="configuration_comment", nullable=True)
    data_type: str = RequiredField(str, column="configuration_data_type", nullable=False)
    default: Optional[str] = OptionalField(str, column="configuration_default", nullable=True)
    value: Optional[str] = OptionalField(str, column="configuration_value", nullable=True)
    params: Optional[Dict] = OptionalField(Json, column="params", nullable=True)

    created_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="created_at", nullable=True)
    updated_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="updated_at", nullable=True)

    device: DeviceEntity = RequiredField("DeviceEntity", reverse="configuration", column="device_id", nullable=False)

    # -----------------------------------------------------------------------------

    @property
    def data_type_formatted(self) -> DataType:
        """Transform data type to enum value"""
        return DataType(self.data_type)

    # -----------------------------------------------------------------------------

    def has_min(self) -> bool:
        """Has min value flag"""
        return self.params is not None and self.params.get(ConfigurationNumberFieldAttribute.MIN.value) is not None

    # -----------------------------------------------------------------------------

    def has_max(self) -> bool:
        """Has max value flag"""
        return self.params is not None and self.params.get(ConfigurationNumberFieldAttribute.MAX.value) is not None

    # -----------------------------------------------------------------------------

    def has_step(self) -> bool:
        """Has step value flag"""
        return self.params is not None and self.params.get(ConfigurationNumberFieldAttribute.STEP.value) is not None

    # -----------------------------------------------------------------------------

    def get_value(self) -> Union[str, float, int, bool, None]:
        """Get configuration value"""
        if self.value is None:
            return None

        if isinstance(self.data_type_formatted, DataType):
            if self.data_type_formatted in [
                DataType.CHAR,
                DataType.UCHAR,
                DataType.SHORT,
                DataType.USHORT,
                DataType.INT,
                DataType.UINT,
            ]:
                return int(self.value)

            if self.data_type_formatted == DataType.FLOAT:
                return float(self.value)

            if self.data_type_formatted == DataType.BOOLEAN:
                value = str(self.value)

                return value.lower() in ["true", "t", "yes", "y", "1", "on"]

        return str(self.value) if self.value else None

    # -----------------------------------------------------------------------------

    def get_min(self) -> Optional[float]:
        """Get min value"""
        if self.params is not None and self.params.get(ConfigurationNumberFieldAttribute.MIN.value) is not None:
            return float(str(self.params.get(ConfigurationNumberFieldAttribute.MIN.value)))

        return None

    # -----------------------------------------------------------------------------

    def set_min(self, min_value: Optional[float]) -> None:
        """Set min value"""
        if self.params is not None:
            self.params[ConfigurationNumberFieldAttribute.MIN.value] = min_value

        else:
            self.params = {ConfigurationNumberFieldAttribute.MIN.value: min_value}

    # -----------------------------------------------------------------------------

    def get_max(self) -> Optional[float]:
        """Get max value"""
        if self.params is not None and self.params.get(ConfigurationNumberFieldAttribute.MAX.value) is not None:
            return float(str(self.params.get(ConfigurationNumberFieldAttribute.MAX.value)))

        return None

    # -----------------------------------------------------------------------------

    def set_max(self, max_value: Optional[float]) -> None:
        """Set max value"""
        if self.params is not None:
            self.params[ConfigurationNumberFieldAttribute.MAX.value] = max_value

        else:
            self.params = {ConfigurationNumberFieldAttribute.MAX.value: max_value}

    # -----------------------------------------------------------------------------

    def get_step(self) -> Optional[float]:
        """Get step value"""
        if self.params is not None and self.params.get(ConfigurationNumberFieldAttribute.STEP.value) is not None:
            return float(str(self.params.get(ConfigurationNumberFieldAttribute.STEP.value)))

        return None

    # -----------------------------------------------------------------------------

    def set_step(self, step: Optional[float]) -> None:
        """Set step value"""
        if self.params is not None:
            self.params[ConfigurationNumberFieldAttribute.STEP.value] = step

        else:
            self.params = {ConfigurationNumberFieldAttribute.STEP.value: step}

    # -----------------------------------------------------------------------------

    def get_values(self) -> List[Dict[str, str]]:
        """Get values for options"""
        values = self.params.get(ConfigurationSelectFieldAttribute.VALUES.value, []) if self.params is not None else []

        if isinstance(values, List):
            mapped_values: List[Dict[str, str]] = []

            for value in values:
                if isinstance(value, Dict) and value.get("name") is not None and value.get("value") is not None:
                    mapped_values.append({"name": str(value.get("name")), "value": str(value.get("value"))})

            return mapped_values

        return []

    # -----------------------------------------------------------------------------

    def set_values(self, select_values: List[Dict[str, str]]) -> None:
        """Set values for options"""
        if self.params is not None:
            self.params[ConfigurationSelectFieldAttribute.VALUES.value] = select_values

        else:
            self.params = {ConfigurationSelectFieldAttribute.VALUES.value: select_values}

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
        only: Union[List[str], str, None] = None,  # pylint: disable=unused-argument
        exclude: Union[List[str], str, None] = None,  # pylint: disable=unused-argument
        with_collections: bool = False,  # pylint: disable=unused-argument
        with_lazy: bool = False,  # pylint: disable=unused-argument
        related_objects: bool = False,  # pylint: disable=unused-argument
    ) -> Dict[str, Union[str, int, float, bool, List[Dict[str, str]], None]]:
        """Transform entity to dictionary"""
        if isinstance(self.data_type_formatted, DataType):
            data_type = self.data_type_formatted.value

        elif self.data_type_formatted is None:
            data_type = None

        else:
            data_type = self.data_type_formatted

        structure: Dict[str, Union[str, int, float, bool, List[Dict[str, str]], None]] = {
            "id": self.configuration_id.__str__(),
            "key": self.key,
            "identifier": self.identifier,
            "name": self.name,
            "comment": self.comment,
            "data_type": data_type,
            "default": self.default,
            "value": self.get_value(),
            "device": self.device.device_id.__str__(),
        }

        if isinstance(self.data_type_formatted, DataType):
            if self.data_type_formatted in [
                DataType.CHAR,
                DataType.UCHAR,
                DataType.SHORT,
                DataType.USHORT,
                DataType.INT,
                DataType.UINT,
                DataType.FLOAT,
            ]:
                return {
                    **structure,
                    **{
                        ConfigurationNumberFieldAttribute.MIN.value: self.get_min(),
                        ConfigurationNumberFieldAttribute.MAX.value: self.get_max(),
                        ConfigurationNumberFieldAttribute.STEP.value: self.get_step(),
                    },
                }

            if self.data_type_formatted == DataType.ENUM:
                return {
                    **structure,
                    **{
                        ConfigurationSelectFieldAttribute.VALUES.value: self.get_values(),
                    },
                }

        return structure

    # -----------------------------------------------------------------------------

    def before_insert(self) -> None:
        """Before insert entity hook"""
        self.created_at = datetime.datetime.now()

        if self.key is None:
            self.key = di[KeyHashHelpers].generate_key(self)

    # -----------------------------------------------------------------------------

    def after_insert(self) -> None:
        """After insert entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityCreatedEvent.EVENT_NAME,
            ModelEntityCreatedEvent(self),
        )

    # -----------------------------------------------------------------------------

    def before_update(self) -> None:
        """Before update entity hook"""
        self.updated_at = datetime.datetime.now()

    # -----------------------------------------------------------------------------

    def after_update(self) -> None:
        """After update entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityUpdatedEvent.EVENT_NAME,
            ModelEntityUpdatedEvent(self),
        )

    # -----------------------------------------------------------------------------

    def after_delete(self) -> None:
        """After delete entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityDeletedEvent.EVENT_NAME,
            ModelEntityDeletedEvent(self),
        )


class DeviceControlEntity(db.Entity):  # type: ignore[no-any-unimported]
    """
    Device control entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    _table_: str = "fb_devices_controls"

    control_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="control_id")
    name: str = OptionalField(str, column="control_name", nullable=False)

    created_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="created_at", nullable=True)
    updated_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="updated_at", nullable=True)

    device: DeviceEntity = RequiredField("DeviceEntity", reverse="controls", column="device_id", nullable=False)

    # -----------------------------------------------------------------------------

    def before_insert(self) -> None:
        """Before insert entity hook"""
        self.created_at = datetime.datetime.now()

    # -----------------------------------------------------------------------------

    def after_insert(self) -> None:
        """After insert entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityCreatedEvent.EVENT_NAME,
            ModelEntityCreatedEvent(self),
        )

    # -----------------------------------------------------------------------------

    def before_update(self) -> None:
        """Before update entity hook"""
        self.updated_at = datetime.datetime.now()

    # -----------------------------------------------------------------------------

    def after_update(self) -> None:
        """After update entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityUpdatedEvent.EVENT_NAME,
            ModelEntityUpdatedEvent(self),
        )

    # -----------------------------------------------------------------------------

    def after_delete(self) -> None:
        """After delete entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityDeletedEvent.EVENT_NAME,
            ModelEntityDeletedEvent(self),
        )


class ChannelEntity(db.Entity):  # type: ignore[no-any-unimported]
    """
    Channel entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    _table_: str = "fb_channels"

    channel_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="channel_id")
    key: str = OptionalField(str, column="channel_key", unique=True, max_len=50, nullable=True)
    identifier: str = RequiredField(str, column="channel_identifier", max_len=40, nullable=False)
    name: Optional[str] = OptionalField(str, column="channel_name", nullable=True)
    comment: Optional[str] = OptionalField(str, column="channel_comment", nullable=True)
    params: Optional[Dict] = OptionalField(Json, column="params", nullable=True)

    created_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="created_at", nullable=True)
    updated_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="updated_at", nullable=True)

    device: DeviceEntity = RequiredField("DeviceEntity", reverse="channels", column="device_id", nullable=False)
    properties: List["ChannelPropertyEntity"] = Set("ChannelPropertyEntity", reverse="channel")
    configuration: List["ChannelConfigurationEntity"] = Set("ChannelConfigurationEntity", reverse="channel")
    controls: List["ChannelControlEntity"] = Set("ChannelControlEntity", reverse="channel")

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
        only: Union[List[str], str, None] = None,  # pylint: disable=unused-argument
        exclude: Union[List[str], str, None] = None,  # pylint: disable=unused-argument
        with_collections: bool = False,  # pylint: disable=unused-argument
        with_lazy: bool = False,  # pylint: disable=unused-argument
        related_objects: bool = False,  # pylint: disable=unused-argument
    ) -> Dict[str, Union[str, int, float, bool, List[str], Dict, None]]:
        """Transform entity to dictionary"""
        return {
            "id": self.channel_id.__str__(),
            "key": self.key,
            "identifier": self.identifier,
            "name": self.name,
            "comment": self.comment,
            "control": self.get_plain_controls(),
            "params": self.params,
            "device": self.device.device_id.__str__(),
        }

    # -----------------------------------------------------------------------------

    def get_plain_controls(self) -> List[str]:
        """Get list of controls strings"""
        controls: List[str] = []

        for control in self.controls:
            controls.append(control.name)

        return controls

    # -----------------------------------------------------------------------------

    def before_insert(self) -> None:
        """Before insert entity hook"""
        self.created_at = datetime.datetime.now()

        if self.key is None:
            self.key = di[KeyHashHelpers].generate_key(self)

    # -----------------------------------------------------------------------------

    def after_insert(self) -> None:
        """After insert entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityCreatedEvent.EVENT_NAME,
            ModelEntityCreatedEvent(self),
        )

    # -----------------------------------------------------------------------------

    def before_update(self) -> None:
        """Before update entity hook"""
        self.updated_at = datetime.datetime.now()

    # -----------------------------------------------------------------------------

    def after_update(self) -> None:
        """After update entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityUpdatedEvent.EVENT_NAME,
            ModelEntityUpdatedEvent(self),
        )

    # -----------------------------------------------------------------------------

    def after_delete(self) -> None:
        """After delete entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityDeletedEvent.EVENT_NAME,
            ModelEntityDeletedEvent(self),
        )


class ChannelPropertyEntity(db.Entity):  # type: ignore[no-any-unimported]
    """
    Channel property entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    _table_: str = "fb_channels_properties"

    property_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="property_id")
    key: str = OptionalField(str, column="property_key", unique=True, max_len=50, nullable=True)
    identifier: str = RequiredField(str, column="property_identifier", max_len=50, nullable=False)
    name: str = OptionalField(str, column="property_name", nullable=True)
    settable: bool = OptionalField(bool, column="property_settable", default=False, nullable=True)
    queryable: bool = OptionalField(bool, column="property_queryable", default=False, nullable=True)
    data_type: Optional[str] = OptionalField(str, column="property_data_type", nullable=True)
    unit: Optional[str] = OptionalField(str, column="property_unit", nullable=True)
    format: Optional[str] = OptionalField(str, column="property_format", nullable=True)
    invalid: Optional[str] = OptionalField(str, column="property_invalid", nullable=True)

    created_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="created_at", nullable=True)
    updated_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="updated_at", nullable=True)

    channel: ChannelEntity = RequiredField("ChannelEntity", reverse="properties", column="channel_id", nullable=False)

    # -----------------------------------------------------------------------------

    @property
    def data_type_formatted(self) -> Optional[DataType]:
        """Transform data type to enum value"""
        return DataType(self.data_type) if self.data_type is not None else None

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
        only: Union[List[str], str, None] = None,  # pylint: disable=unused-argument
        exclude: Union[List[str], str, None] = None,  # pylint: disable=unused-argument
        with_collections: bool = False,  # pylint: disable=unused-argument
        with_lazy: bool = False,  # pylint: disable=unused-argument
        related_objects: bool = False,  # pylint: disable=unused-argument
    ) -> Dict[str, Union[str, int, bool, None]]:
        """Transform entity to dictionary"""
        data_type: Optional[str] = None

        if isinstance(self.data_type_formatted, DataType):
            data_type = self.data_type_formatted.value

        elif self.data_type_formatted is not None:
            data_type = self.data_type_formatted

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
            "invalid": self.invalid,
            "channel": self.channel.channel_id.__str__(),
        }

    # -----------------------------------------------------------------------------

    def before_insert(self) -> None:
        """Before insert entity hook"""
        self.created_at = datetime.datetime.now()

        if self.key is None:
            self.key = di[KeyHashHelpers].generate_key(self)

    # -----------------------------------------------------------------------------

    def after_insert(self) -> None:
        """After insert entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityCreatedEvent.EVENT_NAME,
            ModelEntityCreatedEvent(self),
        )

    # -----------------------------------------------------------------------------

    def before_update(self) -> None:
        """Before update entity hook"""
        self.updated_at = datetime.datetime.now()

    # -----------------------------------------------------------------------------

    def after_update(self) -> None:
        """After update entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityUpdatedEvent.EVENT_NAME,
            ModelEntityUpdatedEvent(self),
        )

    # -----------------------------------------------------------------------------

    def after_delete(self) -> None:
        """After delete entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityDeletedEvent.EVENT_NAME,
            ModelEntityDeletedEvent(self),
        )


class ChannelConfigurationEntity(db.Entity):  # type: ignore[no-any-unimported]
    """
    Channel configuration entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    _table_: str = "fb_channels_configuration"

    configuration_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="configuration_id")
    key: str = OptionalField(str, column="configuration_key", unique=True, max_len=50, nullable=True)
    identifier: str = RequiredField(str, column="configuration_identifier", max_len=50, nullable=False)
    name: Optional[str] = OptionalField(str, column="configuration_name", nullable=True)
    comment: Optional[str] = OptionalField(str, column="configuration_comment", nullable=True)
    data_type: str = RequiredField(str, column="configuration_data_type", nullable=False)
    default: Optional[str] = OptionalField(str, column="configuration_default", nullable=True)
    value: Optional[str] = OptionalField(str, column="configuration_value", nullable=True)
    params: Optional[Dict] = OptionalField(Json, column="params", nullable=True)

    created_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="created_at", nullable=True)
    updated_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="updated_at", nullable=True)

    channel: ChannelEntity = RequiredField(
        "ChannelEntity", reverse="configuration", column="channel_id", nullable=False
    )

    # -----------------------------------------------------------------------------

    @property
    def data_type_formatted(self) -> DataType:
        """Transform data type to enum value"""
        return DataType(self.data_type)

    # -----------------------------------------------------------------------------

    def has_min(self) -> bool:
        """Has min value flag"""
        return self.params is not None and self.params.get(ConfigurationNumberFieldAttribute.MIN.value) is not None

    # -----------------------------------------------------------------------------

    def has_max(self) -> bool:
        """Has max value flag"""
        return self.params is not None and self.params.get(ConfigurationNumberFieldAttribute.MAX.value) is not None

    # -----------------------------------------------------------------------------

    def has_step(self) -> bool:
        """Has step value flag"""
        return self.params is not None and self.params.get(ConfigurationNumberFieldAttribute.STEP.value) is not None

    # -----------------------------------------------------------------------------

    def get_value(self) -> Union[str, float, int, bool, None]:
        """Get configuration value"""
        if self.value is None:
            return None

        if isinstance(self.data_type_formatted, DataType):
            if self.data_type_formatted in [
                DataType.CHAR,
                DataType.UCHAR,
                DataType.SHORT,
                DataType.USHORT,
                DataType.INT,
                DataType.UINT,
            ]:
                return int(self.value)

            if self.data_type_formatted == DataType.FLOAT:
                return float(self.value)

            if self.data_type_formatted == DataType.BOOLEAN:
                value = str(self.value)

                return value.lower() in ["true", "t", "yes", "y", "1", "on"]

        return str(self.value) if self.value else None

    # -----------------------------------------------------------------------------

    def get_min(self) -> Optional[float]:
        """Get min value"""
        if self.params is not None and self.params.get(ConfigurationNumberFieldAttribute.MIN.value) is not None:
            return float(str(self.params.get(ConfigurationNumberFieldAttribute.MIN.value)))

        return None

    # -----------------------------------------------------------------------------

    def set_min(self, min_value: Optional[float]) -> None:
        """Set min value"""
        if self.params is not None:
            self.params[ConfigurationNumberFieldAttribute.MIN.value] = min_value

        else:
            self.params = {ConfigurationNumberFieldAttribute.MIN.value: min_value}

    # -----------------------------------------------------------------------------

    def get_max(self) -> Optional[float]:
        """Get max value"""
        if self.params is not None and self.params.get(ConfigurationNumberFieldAttribute.MAX.value) is not None:
            return float(str(self.params.get(ConfigurationNumberFieldAttribute.MAX.value)))

        return None

    # -----------------------------------------------------------------------------

    def set_max(self, max_value: Optional[float]) -> None:
        """Set max value"""
        if self.params is not None:
            self.params[ConfigurationNumberFieldAttribute.MAX.value] = max_value

        else:
            self.params = {ConfigurationNumberFieldAttribute.MAX.value: max_value}

    # -----------------------------------------------------------------------------

    def get_step(self) -> Optional[float]:
        """Get step value"""
        if self.params is not None and self.params.get(ConfigurationNumberFieldAttribute.STEP.value) is not None:
            return float(str(self.params.get(ConfigurationNumberFieldAttribute.STEP.value)))

        return None

    # -----------------------------------------------------------------------------

    def set_step(self, step: Optional[float]) -> None:
        """Set step value"""
        if self.params is not None:
            self.params[ConfigurationNumberFieldAttribute.STEP.value] = step

        else:
            self.params = {ConfigurationNumberFieldAttribute.STEP.value: step}

    # -----------------------------------------------------------------------------

    def get_values(self) -> List[Dict[str, str]]:
        """Get values for options"""
        values = self.params.get(ConfigurationSelectFieldAttribute.VALUES.value, []) if self.params is not None else []

        if isinstance(values, List):
            mapped_values: List[Dict[str, str]] = []

            for value in values:
                if isinstance(value, Dict) and value.get("name") is not None and value.get("value") is not None:
                    mapped_values.append({"name": str(value.get("name")), "value": str(value.get("value"))})

            return mapped_values

        return []

    # -----------------------------------------------------------------------------

    def set_values(self, select_values: List[Dict[str, str]]) -> None:
        """Set values for options"""
        if self.params is not None:
            self.params[ConfigurationSelectFieldAttribute.VALUES.value] = select_values

        else:
            self.params = {ConfigurationSelectFieldAttribute.VALUES.value: select_values}

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
        only: Union[List[str], str, None] = None,  # pylint: disable=unused-argument
        exclude: Union[List[str], str, None] = None,  # pylint: disable=unused-argument
        with_collections: bool = False,  # pylint: disable=unused-argument
        with_lazy: bool = False,  # pylint: disable=unused-argument
        related_objects: bool = False,  # pylint: disable=unused-argument
    ) -> Dict[str, Union[str, int, float, bool, List[Dict[str, str]], None]]:
        """Transform entity to dictionary"""
        if isinstance(self.data_type_formatted, DataType):
            data_type = self.data_type_formatted.value

        elif self.data_type_formatted is None:
            data_type = None

        else:
            data_type = self.data_type_formatted

        structure: Dict[str, Union[str, int, float, bool, List[Dict[str, str]], None]] = {
            "id": self.configuration_id.__str__(),
            "key": self.key,
            "identifier": self.identifier,
            "name": self.name,
            "comment": self.comment,
            "data_type": data_type,
            "default": self.default,
            "value": self.get_value(),
            "channel": self.channel.channel_id.__str__(),
        }

        if isinstance(self.data_type_formatted, DataType):
            if self.data_type_formatted in [
                DataType.CHAR,
                DataType.UCHAR,
                DataType.SHORT,
                DataType.USHORT,
                DataType.INT,
                DataType.UINT,
                DataType.FLOAT,
            ]:
                return {
                    **structure,
                    **{
                        ConfigurationNumberFieldAttribute.MIN.value: self.get_min(),
                        ConfigurationNumberFieldAttribute.MAX.value: self.get_max(),
                        ConfigurationNumberFieldAttribute.STEP.value: self.get_step(),
                    },
                }

            if self.data_type_formatted == DataType.ENUM:
                return {
                    **structure,
                    **{
                        ConfigurationSelectFieldAttribute.VALUES.value: self.get_values(),
                    },
                }

        return structure

    # -----------------------------------------------------------------------------

    def before_insert(self) -> None:
        """Before insert entity hook"""
        self.created_at = datetime.datetime.now()

        if self.key is None:
            self.key = di[KeyHashHelpers].generate_key(self)

    # -----------------------------------------------------------------------------

    def after_insert(self) -> None:
        """After insert entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityCreatedEvent.EVENT_NAME,
            ModelEntityCreatedEvent(self),
        )

    # -----------------------------------------------------------------------------

    def before_update(self) -> None:
        """Before update entity hook"""
        self.updated_at = datetime.datetime.now()

    # -----------------------------------------------------------------------------

    def after_update(self) -> None:
        """After update entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityUpdatedEvent.EVENT_NAME,
            ModelEntityUpdatedEvent(self),
        )

    # -----------------------------------------------------------------------------

    def after_delete(self) -> None:
        """After delete entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityDeletedEvent.EVENT_NAME,
            ModelEntityDeletedEvent(self),
        )


class ChannelControlEntity(db.Entity):  # type: ignore[no-any-unimported]
    """
    Channel control entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    _table_: str = "fb_channels_controls"

    control_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="control_id")
    name: str = OptionalField(str, column="control_name", nullable=False)

    created_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="created_at", nullable=True)
    updated_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="updated_at", nullable=True)

    channel: ChannelEntity = RequiredField("ChannelEntity", reverse="controls", column="channel_id", nullable=False)

    # -----------------------------------------------------------------------------

    def before_insert(self) -> None:
        """Before insert entity hook"""
        self.created_at = datetime.datetime.now()

    # -----------------------------------------------------------------------------

    def after_insert(self) -> None:
        """After insert entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityCreatedEvent.EVENT_NAME,
            ModelEntityCreatedEvent(self),
        )

    # -----------------------------------------------------------------------------

    def before_update(self) -> None:
        """Before update entity hook"""
        self.updated_at = datetime.datetime.now()

    # -----------------------------------------------------------------------------

    def after_update(self) -> None:
        """After update entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityUpdatedEvent.EVENT_NAME,
            ModelEntityUpdatedEvent(self),
        )

    # -----------------------------------------------------------------------------

    def after_delete(self) -> None:
        """After delete entity hook"""
        di[EventDispatcher].dispatch(
            ModelEntityDeletedEvent.EVENT_NAME,
            ModelEntityDeletedEvent(self),
        )
