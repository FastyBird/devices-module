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

"""
Entities cache to prevent database overloading
"""

# Library dependencies
import uuid
from abc import ABC
from typing import Dict, Set, Tuple
from modules_metadata.types import DataType


class DeviceItem:
    """
    Device entity base item

    @package        FastyBird:DevicesModule!
    @module         items

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    __id: uuid.UUID
    __identifier: str
    __key: str
    __name: str or None
    __comment: str or None
    __enabled: bool
    __hardware_manufacturer: str or None
    __hardware_model: str or None
    __hardware_version: str or None
    __hardware_mac_address: str or None
    __firmware_manufacturer: str or None
    __firmware_version: str or None

    __parent: uuid.UUID or None = None

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        device_id: uuid.UUID,
        device_identifier: str,
        device_key: str,
        device_name: str or None,
        device_comment: str or None,
        device_enabled: bool,
        hardware_manufacturer: str or None,
        hardware_model: str or None,
        hardware_version: str or None,
        hardware_mac_address: str or None,
        firmware_manufacturer: str or None,
        firmware_version: str or None,
        parent_device: uuid.UUID or None = None,
    ) -> None:
        self.__id = device_id
        self.__identifier = device_identifier
        self.__key = device_key

        self.__name = device_name
        self.__comment = device_comment
        self.__enabled = device_enabled

        self.__hardware_manufacturer = hardware_manufacturer
        self.__hardware_model = hardware_model
        self.__hardware_version = hardware_version
        self.__hardware_mac_address = hardware_mac_address

        self.__firmware_manufacturer = firmware_manufacturer
        self.__firmware_version = firmware_version

        self.__parent = parent_device

    # -----------------------------------------------------------------------------

    @property
    def device_id(self) -> uuid.UUID:
        """Device identifier"""
        return self.__id

    # -----------------------------------------------------------------------------

    @property
    def identifier(self) -> str:
        """Device human readable identifier"""
        return self.__identifier

    # -----------------------------------------------------------------------------

    @property
    def key(self) -> str:
        """Device unique human readable key"""
        return self.__key

    # -----------------------------------------------------------------------------

    @property
    def name(self) -> str or None:
        """Device user defined name"""
        return self.__name

    # -----------------------------------------------------------------------------

    @property
    def comment(self) -> str or None:
        """Device user defined description"""
        return self.__comment

    # -----------------------------------------------------------------------------

    @property
    def enabled(self) -> bool:
        """Device is enabled flag"""
        return self.__enabled

    # -----------------------------------------------------------------------------

    @property
    def hardware_manufacturer(self) -> str or None:
        """Device hardware manufacturer name"""
        return self.__hardware_manufacturer

    # -----------------------------------------------------------------------------

    @property
    def hardware_model(self) -> str or None:
        """Device hardware model name"""
        return self.__hardware_model

    # -----------------------------------------------------------------------------

    @property
    def hardware_version(self) -> str or None:
        """Device hardware version"""
        return self.__hardware_version

    # -----------------------------------------------------------------------------

    @property
    def hardware_mac_address(self) -> str or None:
        """Device hardware MAC address"""
        return self.__hardware_mac_address

    # -----------------------------------------------------------------------------

    @property
    def firmware_manufacturer(self) -> str or None:
        """Device firmware manufacturer name"""
        return self.__firmware_manufacturer

    # -----------------------------------------------------------------------------

    @property
    def firmware_version(self) -> str or None:
        """Device firmware version"""
        return self.__firmware_version

    # -----------------------------------------------------------------------------

    @property
    def parent(self) -> uuid.UUID or None:
        """Device parent device identifier"""
        return self.__parent

    # -----------------------------------------------------------------------------

    def to_dict(self) -> Dict[str, str or int or bool or None]:
        """Convert property item to dictionary"""
        return {
            "id": self.device_id.__str__(),
            "identifier": self.identifier,
            "key": self.key,
            "name": self.name,
            "comment": self.comment,
            "enabled": self.enabled,
            "hardware_manufacturer": self.hardware_manufacturer,
            "hardware_model": self.hardware_model,
            "hardware_version": self.hardware_version,
            "hardware_mac_address": self.hardware_mac_address,
            "firmware_version": self.firmware_version,
            "firmware_manufacturer": self.firmware_manufacturer,
            "parent": self.parent.__str__() if self.parent is not None else None,
        }


class ChannelItem:
    """
    Device channel entity base item

    @package        FastyBird:DevicesModule!
    @module         items

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    __id: uuid.UUID
    __identifier: str
    __key: str
    __name: str or None
    __comment: str or None

    __device_id: uuid.UUID

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        channel_id: uuid.UUID,
        channel_identifier: str,
        channel_key: str,
        channel_name: str or None,
        channel_comment: str or None,
        device_id: uuid.UUID,
    ) -> None:
        self.__id = channel_id
        self.__identifier = channel_identifier
        self.__key = channel_key

        self.__name = channel_name
        self.__comment = channel_comment

        self.__device_id = device_id

    # -----------------------------------------------------------------------------

    @property
    def channel_id(self) -> uuid.UUID:
        """Channel identifier"""
        return self.__id

    # -----------------------------------------------------------------------------

    @property
    def device_id(self) -> uuid.UUID:
        """Device identifier"""
        return self.__device_id

    # -----------------------------------------------------------------------------

    @property
    def identifier(self) -> str:
        """Device human readable identifier"""
        return self.__identifier

    # -----------------------------------------------------------------------------

    @property
    def key(self) -> str:
        """Device unique human readable key"""
        return self.__key

    # -----------------------------------------------------------------------------

    @property
    def name(self) -> str or None:
        """Device user defined name"""
        return self.__name

    # -----------------------------------------------------------------------------

    @property
    def comment(self) -> str or None:
        """Device user defined description"""
        return self.__comment

    # -----------------------------------------------------------------------------

    def to_dict(self) -> Dict[str, str or int or bool or None]:
        """Convert property item to dictionary"""
        return {
            "id": self.channel_id.__str__(),
            "identifier": self.identifier,
            "key": self.key,
            "name": self.name,
            "comment": self.comment,
            "device": self.device_id.__str__(),
        }


class PropertyItem(ABC):
    """
    Property entity base item

    @package        FastyBird:DevicesModule!
    @module         items

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    __id: uuid.UUID
    __name: str or None
    __key: str
    __identifier: str
    __settable: bool
    __queryable: bool
    __data_type: DataType or None
    __unit: str or None
    __format: str or None

    __device_id: uuid.UUID

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        property_id: uuid.UUID,
        property_key: str,
        property_name: str or None,
        property_identifier: str,
        property_settable: bool,
        property_queryable: bool,
        property_data_type: DataType or None,
        property_unit: str or None,
        property_format: str or None,
        device_id: uuid.UUID,
    ) -> None:
        self.__id = property_id
        self.__name = property_name
        self.__key = property_key
        self.__identifier = property_identifier
        self.__settable = property_settable
        self.__queryable = property_queryable
        self.__data_type = property_data_type
        self.__unit = property_unit
        self.__format = property_format

        self.__device_id = device_id

    # -----------------------------------------------------------------------------

    @property
    def device_id(self) -> uuid.UUID:
        """Property device identifier"""
        return self.__device_id

    # -----------------------------------------------------------------------------

    @property
    def property_id(self) -> uuid.UUID:
        """Property identifier"""
        return self.__id

    # -----------------------------------------------------------------------------

    @property
    def name(self) -> str or None:
        """Property name"""
        return self.__name

    # -----------------------------------------------------------------------------

    @property
    def key(self) -> str:
        """Property unique human readable key"""
        return self.__key

    # -----------------------------------------------------------------------------

    @property
    def identifier(self) -> str:
        """Property human readable identifier"""
        return self.__identifier

    # -----------------------------------------------------------------------------

    @property
    def settable(self) -> bool:
        """Property is settable flag"""
        return self.__settable

    # -----------------------------------------------------------------------------

    @property
    def queryable(self) -> bool:
        """Property is queryable flag"""
        return self.__queryable

    # -----------------------------------------------------------------------------

    @property
    def data_type(self) -> DataType or None:
        """Property data type"""
        return self.__data_type

    # -----------------------------------------------------------------------------

    @property
    def unit(self) -> str or None:
        """Property unit"""
        return self.__unit

    # -----------------------------------------------------------------------------

    @property
    def format(self) -> str or None:
        """Property value format"""
        return self.__format

    # -----------------------------------------------------------------------------

    def get_format(self) -> Tuple[int, int] or Tuple[float, float] or Set[str]:
        """Property formatted value format"""
        if self.__format is None:
            return None

        if self.__data_type is not None:
            if self.__data_type == DataType.INT:
                min_value, max_value, *rest = self.__format.split(":") + [None, None]  # pylint: disable=unused-variable

                if min_value is not None and max_value is not None and int(min_value) <= int(max_value):
                    return int(min_value), int(max_value)

            elif self.__data_type == DataType.FLOAT:
                min_value, max_value, *rest = self.__format.split(":") + [None, None]

                if min_value is not None and max_value is not None and float(min_value) <= float(max_value):
                    return float(min_value), float(max_value)

            elif self.__data_type == DataType.ENUM:
                return {x.strip() for x in self.__format.split(",")}

        return None

    # -----------------------------------------------------------------------------

    def to_dict(self) -> Dict[str, str or int or bool or None]:
        """Convert property item to dictionary"""
        if isinstance(self.data_type, DataType):
            data_type = self.data_type.value

        elif self.data_type is None:
            data_type = None

        else:
            data_type = self.data_type

        return {
            "id": self.property_id.__str__(),
            "name": self.name,
            "key": self.key,
            "identifier": self.identifier,
            "settable": self.settable,
            "queryable": self.queryable,
            "data_type": data_type,
            "unit": self.unit,
            "format": self.format,
        }


class DevicePropertyItem(PropertyItem):
    """
    Device property entity item

    @package        FastyBird:DevicesModule!
    @module         items

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    def to_dict(self) -> Dict[str, str or int or bool or None]:
        return {**{
            "device": self.device_id.__str__(),
        }, **super().to_dict()}


class ChannelPropertyItem(PropertyItem):
    """
    Channel property entity item

    @package        FastyBird:DevicesModule!
    @module         items

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    __channel_id: uuid.UUID

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        property_id: uuid.UUID,
        property_name: str or None,
        property_key: str,
        property_identifier: str,
        property_settable: bool,
        property_queryable: bool,
        property_data_type: DataType or None,
        property_unit: str or None,
        property_format: str or None,
        device_id: uuid.UUID,
        channel_id: uuid.UUID,
    ) -> None:
        super().__init__(
            property_id=property_id,
            property_name=property_name,
            property_key=property_key,
            property_identifier=property_identifier,
            property_settable=property_settable,
            property_queryable=property_queryable,
            property_data_type=property_data_type,
            property_unit=property_unit,
            property_format=property_format,
            device_id=device_id,
        )

        self.__channel_id = channel_id

    # -----------------------------------------------------------------------------

    @property
    def channel_id(self) -> uuid.UUID:
        """Property channel identifier"""
        return self.__channel_id

    # -----------------------------------------------------------------------------

    def to_dict(self) -> Dict[str, str or int or bool or None]:
        return {**{
            "channel": self.channel_id.__str__(),
        }, **super().to_dict()}


class ConnectorItem(ABC):
    """
    Connector entity item

    @package        FastyBird:DevicesModule!
    @module         items

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    __id: uuid.UUID
    __key: str
    __name: str
    __enabled: bool
    __type: str
    __params: dict

    def __init__(
        self,
        connector_id: uuid.UUID,
        connector_name: str,
        connector_key: str,
        connector_enabled: bool,
        connector_type: str,
        connector_params: dict or None,
    ) -> None:
        self.__id = connector_id
        self.__key = connector_key
        self.__name = connector_name
        self.__enabled = connector_enabled
        self.__type = connector_type
        self.__params = connector_params if connector_params is not None else {}

    # -----------------------------------------------------------------------------

    @property
    def connector_id(self) -> uuid.UUID:
        """Connector identifier"""
        return self.__id

    # -----------------------------------------------------------------------------

    @property
    def key(self) -> str:
        """Connector unique human readable key"""
        return self.__key

    # -----------------------------------------------------------------------------

    @property
    def name(self) -> str:
        """Connector name"""
        return self.__name

    # -----------------------------------------------------------------------------

    @property
    def enabled(self) -> bool:
        """Connector enabled or disabled flag"""
        return self.__enabled

    # -----------------------------------------------------------------------------

    @property
    def type(self) -> str:
        """Connector type"""
        return self.__type

    # -----------------------------------------------------------------------------

    @property
    def params(self) -> dict:
        """Connector configuration params"""
        return self.__params

    # -----------------------------------------------------------------------------

    def to_dict(self) -> Dict[str, str or int or bool or None]:
        """Convert connector item to dictionary"""
        return {
            "id": self.connector_id.__str__(),
            "key": self.key,
            "name": self.name,
            "enabled": self.enabled,
            "type": self.type,
        }


class FbBusConnectorItem(ConnectorItem):
    """
    FastyBird BUS connector entity item

    @package        FastyBird:DevicesModule!
    @module         items

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    @property
    def address(self) -> int or None:
        """Connector address"""
        return int(self.params.get("address", None)) \
            if self.params is not None and self.params.get("address") is not None else None

    # -----------------------------------------------------------------------------

    @property
    def serial_interface(self) -> str or None:
        """Connector serial interface"""
        return str(self.params.get("serial_interface", None)) \
            if self.params is not None and self.params.get("serial_interface") is not None else None

    # -----------------------------------------------------------------------------

    @property
    def baud_rate(self) -> int or None:
        """Connector communication baud rate"""
        return int(self.params.get("baud_rate", None)) \
            if self.params is not None and self.params.get("baud_rate") is not None else None

    # -----------------------------------------------------------------------------

    def to_dict(self) -> Dict[str, str or int or bool or None]:
        """Convert connector item to dictionary"""
        return {**{
            "address": self.address,
            "serial_interface": self.serial_interface,
            "baud_rate": self.baud_rate,
        }, **super().to_dict()}


class FbMqttV1ConnectorItem(ConnectorItem):
    """
    FastyBird MQTT v1 connector entity item

    @package        FastyBird:DevicesModule!
    @module         items

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    @property
    def server(self) -> str or None:
        """Connector server address"""
        return str(self.params.get("server", None)) \
            if self.params is not None and self.params.get("server") is not None else None

    # -----------------------------------------------------------------------------

    @property
    def port(self) -> int or None:
        """Connector server port"""
        return int(self.params.get("port", None)) \
            if self.params is not None and self.params.get("port") is not None else None

    # -----------------------------------------------------------------------------

    @property
    def secured_port(self) -> int or None:
        """Connector server secured port"""
        return int(self.params.get("secured_port", None)) \
            if self.params is not None and self.params.get("secured_port") is not None else None

    # -----------------------------------------------------------------------------

    @property
    def username(self) -> str or None:
        """Connector server username"""
        return str(self.params.get("username", None)) \
            if self.params is not None and self.params.get("username") is not None else None

    # -----------------------------------------------------------------------------

    def to_dict(self) -> Dict[str, str or int or bool or None]:
        """Convert connector item to dictionary"""
        return {**{
            "server": self.server,
            "port": self.port,
            "secured_port": self.secured_port,
            "username": self.username,
        }, **super().to_dict()}


class ControlItem(ABC):
    """
    Control entity base item

    @package        FastyBird:DevicesModule!
    @module         items

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    __id: uuid.UUID
    __name: str

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        control_id: uuid.UUID,
        control_name: str,
    ) -> None:
        self.__id = control_id
        self.__name = control_name

    # -----------------------------------------------------------------------------

    @property
    def control_id(self) -> uuid.UUID:
        """Control identifier"""
        return self.__id

    # -----------------------------------------------------------------------------

    @property
    def name(self) -> str:
        """Control name"""
        return self.__name

    # -----------------------------------------------------------------------------

    def to_dict(self) -> Dict[str, str]:
        """Convert control item to dictionary"""
        return {
            "id": self.control_id.__str__(),
            "name": self.name,
        }


class DeviceControlItem(ControlItem):
    """
    Device control entity item

    @package        FastyBird:DevicesModule!
    @module         items

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    __device_id: uuid.UUID

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        device_id: uuid.UUID,
        control_id: uuid.UUID,
        control_name: str,
    ) -> None:
        super().__init__(
            control_id=control_id,
            control_name=control_name,
        )

        self.__device_id = device_id

    # -----------------------------------------------------------------------------

    @property
    def device_id(self) -> uuid.UUID:
        """Control device identifier"""
        return self.__device_id

    # -----------------------------------------------------------------------------

    def to_dict(self) -> Dict[str, str]:
        """Convert device control item to dictionary"""
        return {**{
            "device": self.device_id.__str__(),
        }, **super().to_dict()}


class ChannelControlItem(ControlItem):
    """
    Channel control entity item

    @package        FastyBird:DevicesModule!
    @module         items

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    __channel_id: uuid.UUID
    __device_id: uuid.UUID

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        device_id: uuid.UUID,
        channel_id: uuid.UUID,
        control_id: uuid.UUID,
        control_name: str,
    ) -> None:
        super().__init__(
            control_id=control_id,
            control_name=control_name,
        )

        self.__device_id = device_id
        self.__channel_id = channel_id

    # -----------------------------------------------------------------------------

    @property
    def device_id(self) -> uuid.UUID:
        """Control device identifier"""
        return self.__device_id

    # -----------------------------------------------------------------------------

    @property
    def channel_id(self) -> uuid.UUID:
        """Control channel identifier"""
        return self.__channel_id

    # -----------------------------------------------------------------------------

    def to_dict(self) -> Dict[str, str]:
        """Convert channel control item to dictionary"""
        return {**{
            "channel": self.channel_id.__str__(),
        }, **super().to_dict()}


class ConnectorControlItem(ControlItem):
    """
    Connector control entity item

    @package        FastyBird:DevicesModule!
    @module         items

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    __connector_id: uuid.UUID

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        connector_id: uuid.UUID,
        control_id: uuid.UUID,
        control_name: str,
    ) -> None:
        super().__init__(
            control_id=control_id,
            control_name=control_name,
        )

        self.__connector_id = connector_id

    # -----------------------------------------------------------------------------

    @property
    def connector_id(self) -> uuid.UUID:
        """Control connector identifier"""
        return self.__connector_id

    # -----------------------------------------------------------------------------

    def to_dict(self) -> Dict[str, str]:
        """Convert connector control item to dictionary"""
        return {**{
            "connector": self.connector_id.__str__(),
        }, **super().to_dict()}
