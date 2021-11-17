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

# Library dependencies
import uuid
import datetime
from typing import List, Dict, Optional, Tuple, Union
from exchange_plugin.dispatcher import EventDispatcher
from kink import di
from modules_metadata.types import DataType
from pony.orm import (
    Database,
    Discriminator,
    PrimaryKey,
    Required as RequiredField,
    Optional as OptionalField,
    Set,
    Json,
)

# Library libs
from devices_module.events import ModelEntityCreatedEvent, ModelEntityUpdatedEvent, ModelEntityDeletedEvent
from devices_module.helpers import KeyHashHelpers

# Create devices module database accessor
db: Database = Database()


class ConnectorEntity(db.Entity):
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
    name: Optional[str] = RequiredField(str, column="connector_name", nullable=False)
    key: str = RequiredField(str, column="connector_key", unique=True, max_len=50, nullable=False)
    enabled: bool = OptionalField(bool, column="connector_enabled", nullable=True, default=True)
    params: Optional[Dict[str, Union[str, int, float, bool, None]]] = OptionalField(
        Json, column="params", nullable=True
    )

    created_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="created_at", nullable=True)
    updated_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="updated_at", nullable=True)

    devices: List["DeviceConnectorEntity"] = Set("DeviceConnectorEntity", reverse="connector")
    controls: List["ConnectorControlEntity"] = Set("ConnectorControlEntity", reverse="connector")

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
        only: Tuple = None,  # pylint: disable=unused-argument
        exclude: Tuple = None,  # pylint: disable=unused-argument
        with_collections: bool = False,  # pylint: disable=unused-argument
        with_lazy: bool = False,  # pylint: disable=unused-argument
        related_objects: bool = False,  # pylint: disable=unused-argument
    ) -> Dict[str, Union[str, int, bool, None]]:
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
        return int(self.params.get("address", None)) \
            if self.params is not None and self.params.get("address") is not None else None

    # -----------------------------------------------------------------------------

    @property
    def serial_interface(self) -> Optional[str]:
        """Connector serial interface"""
        return str(self.params.get("serial_interface", None)) \
            if self.params is not None and self.params.get("serial_interface") is not None else None

    # -----------------------------------------------------------------------------

    @property
    def baud_rate(self) -> Optional[int]:
        """Connector communication baud rate"""
        return int(self.params.get("baud_rate", None)) \
            if self.params is not None and self.params.get("baud_rate") is not None else None

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
        only: Tuple = None,
        exclude: Tuple = None,
        with_collections: bool = False,
        with_lazy: bool = False,
        related_objects: bool = False,
    ) -> Dict[str, Union[str, int, bool, None]]:
        """Transform entity to dictionary"""
        return {**{
            "address": self.address,
            "serial_interface": self.serial_interface,
            "baud_rate": self.baud_rate,
        }, **super().to_dict(only, exclude, with_collections, with_lazy, related_objects)}


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
        return str(self.params.get("server", None)) \
            if self.params is not None and self.params.get("server") is not None else None

    # -----------------------------------------------------------------------------

    @property
    def port(self) -> Optional[int]:
        """Connector server port"""
        return int(self.params.get("port", None)) \
            if self.params is not None and self.params.get("port") is not None else None

    # -----------------------------------------------------------------------------

    @property
    def secured_port(self) -> Optional[int]:
        """Connector server secured port"""
        return int(self.params.get("secured_port", None)) \
            if self.params is not None and self.params.get("secured_port") is not None else None

    # -----------------------------------------------------------------------------

    @property
    def username(self) -> Optional[str]:
        """Connector server username"""
        return str(self.params.get("username", None)) \
            if self.params is not None and self.params.get("username") is not None else None

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
        only: Tuple = None,
        exclude: Tuple = None,
        with_collections: bool = False,
        with_lazy: bool = False,
        related_objects: bool = False,
    ) -> Dict[str, Union[str, int, bool, None]]:
        """Transform entity to dictionary"""
        return {**{
            "server": self.server,
            "port": self.port,
            "secured_port": self.secured_port,
            "username": self.username,
        }, **super().to_dict(only, exclude, with_collections, with_lazy, related_objects)}


class ConnectorControlEntity(db.Entity):
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


class DeviceEntity(db.Entity):
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
    params: Optional[Dict[str, Union[str, int, float, bool, None]]] = OptionalField(
        Json, column="params", nullable=True
    )

    created_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="created_at", nullable=True)
    updated_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="updated_at", nullable=True)

    channels: List["ChannelEntity"] = Set("ChannelEntity", reverse="device")
    properties: List["DevicePropertyEntity"] = Set("DevicePropertyEntity", reverse="device")
    configuration: List["DeviceConfigurationEntity"] = Set("DeviceConfigurationEntity", reverse="device")
    controls: List["DeviceControlEntity"] = Set("DeviceControlEntity", reverse="device")
    connector: Optional["DeviceConnectorEntity"] = OptionalField("DeviceConnectorEntity", reverse="device")

    owner: Optional[str] = OptionalField(str, column="owner", max_len=15, nullable=True)

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
        only: Tuple = None,  # pylint: disable=unused-argument
        exclude: Tuple = None,  # pylint: disable=unused-argument
        with_collections: bool = False,  # pylint: disable=unused-argument
        with_lazy: bool = False,  # pylint: disable=unused-argument
        related_objects: bool = False,  # pylint: disable=unused-argument
    ) -> Dict[str, Union[str, int, bool, None]]:
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

        self.hardware_model = self.hardware_model.lower()
        self.hardware_manufacturer = self.hardware_manufacturer.lower()
        self.firmware_manufacturer = self.firmware_manufacturer.lower()

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


class DevicePropertyEntity(db.Entity):
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
        only: Tuple = None,  # pylint: disable=unused-argument
        exclude: Tuple = None,  # pylint: disable=unused-argument
        with_collections: bool = False,  # pylint: disable=unused-argument
        with_lazy: bool = False,  # pylint: disable=unused-argument
        related_objects: bool = False,  # pylint: disable=unused-argument
    ) -> Dict[str, Union[str, int, bool, None]]:
        """Transform entity to dictionary"""
        if isinstance(self.data_type_formatted, DataType):
            data_type = self.data_type_formatted.value

        elif self.data_type_formatted is None:
            data_type = None

        else:
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


class DeviceConfigurationEntity(db.Entity):
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
    params: Optional[Dict[str, Union[str, int, float, bool, List[Dict[str, str]], None]]] = OptionalField(
        Json, column="params", nullable=True
    )

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
        return self.params is not None and self.params.get("min_value") is not None

    # -----------------------------------------------------------------------------

    def has_max(self) -> bool:
        """Has max value flag"""
        return self.params is not None and self.params.get("max_value") is not None

    # -----------------------------------------------------------------------------

    def has_step(self) -> bool:
        """Has step value flag"""
        return self.params is not None and self.params.get("step_value") is not None

    # -----------------------------------------------------------------------------

    def get_value(self) -> Union[float, int, str, None]:
        """Get configuration value"""
        if self.value is None:
            return None

        if isinstance(self.data_type_formatted, DataType):
            if (
                self.data_type_formatted in [
                    DataType.CHAR,
                    DataType.UCHAR,
                    DataType.SHORT,
                    DataType.USHORT,
                    DataType.INT,
                    DataType.UINT,
                ]
            ):
                return int(self.value)

            if self.data_type_formatted == DataType.FLOAT:
                return float(self.value)

        return self.value

    # -----------------------------------------------------------------------------

    def get_min(self) -> Optional[float]:
        """Get min value"""
        if self.params is not None and self.params.get("min_value") is not None:
            return float(self.params.get("min_value"))

        return None

    # -----------------------------------------------------------------------------

    def set_min(self, min_value: Optional[float]) -> None:
        """Set min value"""
        self.params["min_value"] = min_value

    # -----------------------------------------------------------------------------

    def get_max(self) -> Optional[float]:
        """Get max value"""
        if self.params is not None and self.params.get("max_value") is not None:
            return float(self.params.get("max_value"))

        return None

    # -----------------------------------------------------------------------------

    def set_max(self, max_value: Optional[float]) -> None:
        """Set max value"""
        self.params["max_value"] = max_value

    # -----------------------------------------------------------------------------

    def get_step(self) -> Optional[float]:
        """Get step value"""
        if self.params is not None and self.params.get("step_value") is not None:
            return float(self.params.get("step_value"))

        return None

    # -----------------------------------------------------------------------------

    def set_step(self, step: Optional[float]) -> None:
        """Set step value"""
        self.params["step_value"] = step

    # -----------------------------------------------------------------------------

    def get_values(self) -> List[Dict[str, str]]:
        """Get values for options"""
        return self.params.get("select_values", [])

    # -----------------------------------------------------------------------------

    def set_values(self, select_values: List[Dict[str, str]]) -> None:
        """Set values for options"""
        self.params["select_values"] = select_values

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
        only: Tuple = None,  # pylint: disable=unused-argument
        exclude: Tuple = None,  # pylint: disable=unused-argument
        with_collections: bool = False,  # pylint: disable=unused-argument
        with_lazy: bool = False,  # pylint: disable=unused-argument
        related_objects: bool = False,  # pylint: disable=unused-argument
    ) -> Dict[str, Union[str, int, bool, None]]:
        """Transform entity to dictionary"""
        if isinstance(self.data_type_formatted, DataType):
            data_type = self.data_type_formatted.value

        elif self.data_type_formatted is None:
            data_type = None

        else:
            data_type = self.data_type_formatted

        structure: Dict[str, Optional[str]] = {
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
            if (
                self.data_type_formatted in [
                    DataType.CHAR,
                    DataType.UCHAR,
                    DataType.SHORT,
                    DataType.USHORT,
                    DataType.INT,
                    DataType.UINT,
                    DataType.FLOAT,
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

            if self.data_type_formatted == DataType.ENUM:
                return {
                    **structure,
                    **{
                        "values": self.get_values(),
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


class DeviceControlEntity(db.Entity):
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


class DeviceConnectorEntity(db.Entity):
    """
    Device connector entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    _table_: str = "fb_devices_connectors"

    connector_id: uuid.UUID = PrimaryKey(uuid.UUID, default=uuid.uuid4, column="device_connector_id")
    params: Optional[Dict[str, Union[str, int, float, bool, None]]] = OptionalField(
        Json, column="params", nullable=True
    )

    created_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="created_at", nullable=True)
    updated_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="updated_at", nullable=True)

    device: DeviceEntity = RequiredField("DeviceEntity", reverse="connector", column="device_id", nullable=False)
    connector: ConnectorEntity = RequiredField(
        "ConnectorEntity", reverse="devices", column="connector_id", nullable=False
    )

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
        only: Tuple = None,  # pylint: disable=unused-argument
        exclude: Tuple = None,  # pylint: disable=unused-argument
        with_collections: bool = False,  # pylint: disable=unused-argument
        with_lazy: bool = False,  # pylint: disable=unused-argument
        related_objects: bool = False,  # pylint: disable=unused-argument
    ) -> Dict[str, Union[str, int, bool, None]]:
        """Transform entity to dictionary"""
        structure: Dict[str, Union[str, int, bool, None]] = {
            "id": self.connector_id.__str__(),
            "type": self.connector.type,
            "connector": self.connector.connector_id.__str__(),
            "device": self.device.device_id.__str__(),
        }

        if isinstance(self.connector, FbBusConnectorEntity):
            return {**structure, **{
                "address": self.params.get("address"),
                "max_packet_length": self.params.get("max_packet_length"),
                "description_support": bool(self.params.get("description_support", False)),
                "settings_support": bool(self.params.get("settings_support", False)),
                "configured_key_length": self.params.get("configured_key_length"),
            }}

        if isinstance(self.connector, FbMqttConnectorEntity):
            return {**structure, **{
                "username": self.params.get("username"),
            }}

        return structure

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


class ChannelEntity(db.Entity):
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
    params: Optional[Dict[str, Union[str, int, float, bool, None]]] = OptionalField(
        Json, column="params", nullable=True
    )

    created_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="created_at", nullable=True)
    updated_at: Optional[datetime.datetime] = OptionalField(datetime.datetime, column="updated_at", nullable=True)

    device: DeviceEntity = RequiredField("DeviceEntity", reverse="channels", column="device_id", nullable=False)
    properties: List["ChannelPropertyEntity"] = Set("ChannelPropertyEntity", reverse="channel")
    configuration: List["ChannelConfigurationEntity"] = Set("ChannelConfigurationEntity", reverse="channel")
    controls: List["ChannelControlEntity"] = Set("ChannelControlEntity", reverse="channel")

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
        only: Tuple = None,  # pylint: disable=unused-argument
        exclude: Tuple = None,  # pylint: disable=unused-argument
        with_collections: bool = False,  # pylint: disable=unused-argument
        with_lazy: bool = False,  # pylint: disable=unused-argument
        related_objects: bool = False,  # pylint: disable=unused-argument
    ) -> Dict[str, Union[str, int, bool, None]]:
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


class ChannelPropertyEntity(db.Entity):
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
        only: Tuple = None,  # pylint: disable=unused-argument
        exclude: Tuple = None,  # pylint: disable=unused-argument
        with_collections: bool = False,  # pylint: disable=unused-argument
        with_lazy: bool = False,  # pylint: disable=unused-argument
        related_objects: bool = False,  # pylint: disable=unused-argument
    ) -> Dict[str, Union[str, int, bool, None]]:
        """Transform entity to dictionary"""
        if isinstance(self.data_type_formatted, DataType):
            data_type = self.data_type_formatted.value

        elif self.data_type_formatted is None:
            data_type = None

        else:
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


class ChannelConfigurationEntity(db.Entity):
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
    params: Optional[Dict[str, Union[str, int, float, bool, List[Dict[str, str]], None]]] = OptionalField(
        Json, column="params", nullable=True
    )

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
        return self.params is not None and self.params.get("min_value") is not None

    # -----------------------------------------------------------------------------

    def has_max(self) -> bool:
        """Has max value flag"""
        return self.params is not None and self.params.get("max_value") is not None

    # -----------------------------------------------------------------------------

    def has_step(self) -> bool:
        """Has step value flag"""
        return self.params is not None and self.params.get("step_value") is not None

    # -----------------------------------------------------------------------------

    def get_value(self) -> Union[float, int, str, None]:
        """Get configuration value"""
        if self.value is None:
            return None

        if isinstance(self.data_type_formatted, DataType):
            if (
                self.data_type_formatted in [
                    DataType.CHAR,
                    DataType.UCHAR,
                    DataType.SHORT,
                    DataType.USHORT,
                    DataType.INT,
                    DataType.UINT,
                ]
            ):
                return int(self.value)

            if self.data_type_formatted == DataType.FLOAT:
                return float(self.value)

        return self.value

    # -----------------------------------------------------------------------------

    def get_min(self) -> Optional[float]:
        """Get min value"""
        if self.params is not None and self.params.get("min_value") is not None:
            return float(self.params.get("min_value"))

        return None

    # -----------------------------------------------------------------------------

    def set_min(self, min_value: Optional[float]) -> None:
        """Set min value"""
        self.params["min_value"] = min_value

    # -----------------------------------------------------------------------------

    def get_max(self) -> Optional[float]:
        """Get max value"""
        if self.params is not None and self.params.get("max_value") is not None:
            return float(self.params.get("max_value"))

        return None

    # -----------------------------------------------------------------------------

    def set_max(self, max_value: Optional[float]) -> None:
        """Set max value"""
        self.params["max_value"] = max_value

    # -----------------------------------------------------------------------------

    def get_step(self) -> Optional[float]:
        """Get step value"""
        if self.params is not None and self.params.get("step_value") is not None:
            return float(self.params.get("step_value"))

        return None

    # -----------------------------------------------------------------------------

    def set_step(self, step: Optional[float]) -> None:
        """Set step value"""
        self.params["step_value"] = step

    # -----------------------------------------------------------------------------

    def get_values(self) -> List[Dict[str, str]]:
        """Get values for options"""
        return self.params.get("select_values", [])

    # -----------------------------------------------------------------------------

    def set_values(self, select_values: List[Dict[str, str]]) -> None:
        """Set values for options"""
        self.params["select_values"] = select_values

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
        only: Tuple = None,  # pylint: disable=unused-argument
        exclude: Tuple = None,  # pylint: disable=unused-argument
        with_collections: bool = False,  # pylint: disable=unused-argument
        with_lazy: bool = False,  # pylint: disable=unused-argument
        related_objects: bool = False,  # pylint: disable=unused-argument
    ) -> Dict[str, Union[str, int, bool, None]]:
        """Transform entity to dictionary"""
        if isinstance(self.data_type_formatted, DataType):
            data_type = self.data_type_formatted.value

        elif self.data_type_formatted is None:
            data_type = None

        else:
            data_type = self.data_type_formatted

        structure: Dict[str, Optional[str]] = {
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
            if (
                self.data_type_formatted in [
                    DataType.CHAR,
                    DataType.UCHAR,
                    DataType.SHORT,
                    DataType.USHORT,
                    DataType.INT,
                    DataType.UINT,
                    DataType.FLOAT,
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

            if self.data_type_formatted == DataType.ENUM:
                return {
                    **structure,
                    **{
                        "values": self.get_values(),
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


class ChannelControlEntity(db.Entity):
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
