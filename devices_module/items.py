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


class PropertyItem(ABC):
    """
    Property entity base item

    @package        FastyBird:DevicesModule!
    @module         items

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    __id: uuid.UUID
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
        property_identifier: str,
        property_settable: bool,
        property_queryable: bool,
        property_data_type: DataType or None,
        property_unit: str or None,
        property_format: str or None,
        device_id: uuid.UUID,
    ) -> None:
        self.__id = property_id
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
    def device(self) -> uuid.UUID:
        """Property device identifier"""
        return self.__device_id

    # -----------------------------------------------------------------------------

    @property
    def property_id(self) -> uuid.UUID:
        """Property identifier"""
        return self.__id

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
            if self.__data_type == DataType.DATA_TYPE_INT:
                min_value, max_value, *rest = self.__format.split(":") + [None, None]  # pylint: disable=unused-variable

                if min_value is not None and max_value is not None and int(min_value) <= int(max_value):
                    return int(min_value), int(max_value)

            elif self.__data_type == DataType.DATA_TYPE_FLOAT:
                min_value, max_value, *rest = self.__format.split(":") + [None, None]

                if min_value is not None and max_value is not None and float(min_value) <= float(max_value):
                    return float(min_value), float(max_value)

            elif self.__data_type == DataType.DATA_TYPE_ENUM:
                return {x.strip() for x in self.__format.split(",")}

        return None

    # -----------------------------------------------------------------------------

    def to_array(self) -> Dict[str, str or int or bool or None]:
        """Convert property item to dictionary"""
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
            property_id,
            property_key,
            property_identifier,
            property_settable,
            property_queryable,
            property_data_type,
            property_unit,
            property_format,
            device_id,
        )

        self.__channel_id = channel_id

    # -----------------------------------------------------------------------------

    def channel(self) -> uuid.UUID:
        """Property channel identifier"""
        return self.__channel_id


class ConnectorItem:
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
        connector_params: dict,
    ) -> None:
        self.__id = connector_id
        self.__key = connector_key
        self.__name = connector_name
        self.__enabled = connector_enabled
        self.__type = connector_type
        self.__params = connector_params

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
