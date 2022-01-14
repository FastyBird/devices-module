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
Devices module property entities module
"""

# Python base dependencies
import uuid
from abc import abstractmethod
from datetime import datetime
from typing import Dict, List, Optional, Tuple, Union

# Library dependencies
from fastnumbers import fast_float, fast_int
from metadata.devices_module import PropertyType
from metadata.helpers import normalize_value
from metadata.types import ButtonPayload, DataType, SwitchPayload
from sqlalchemy import BINARY, BOOLEAN, VARCHAR, Column, Integer

# Library libs
from devices_module.exceptions import InvalidArgumentException, InvalidStateException


class PropertyMixin:
    """
    Property entity

    @package        FastyBird:DevicesModule!
    @module         entities/property

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    __property_id: bytes = Column(  # type: ignore[assignment]
        BINARY(16), primary_key=True, name="property_id", default=uuid.uuid4
    )
    __identifier: str = Column(VARCHAR(50), name="property_identifier", nullable=False)  # type: ignore[assignment]
    __key: str = Column(VARCHAR(50), name="property_key", unique=True, nullable=False)  # type: ignore[assignment]
    __name: Optional[str] = Column(  # type: ignore[assignment]
        VARCHAR(255), name="property_name", nullable=True, default=None
    )
    __settable: bool = Column(  # type: ignore[assignment]
        BOOLEAN, name="property_settable", nullable=False, default=False
    )
    __queryable: bool = Column(  # type: ignore[assignment]
        BOOLEAN, name="property_queryable", nullable=False, default=False
    )
    __data_type: Optional[str] = Column(  # type: ignore[assignment]
        VARCHAR(100), name="property_data_type", nullable=True, default=None
    )
    __unit: Optional[str] = Column(  # type: ignore[assignment]
        VARCHAR(20), name="property_unit", nullable=True, default=None
    )
    __format: Optional[str] = Column(  # type: ignore[assignment]
        VARCHAR(255), name="property_format", nullable=True, default=None
    )
    __invalid: Optional[str] = Column(  # type: ignore[assignment]
        VARCHAR(255), name="property_invalid", nullable=True, default=None
    )
    __number_of_decimals: Optional[int] = Column(  # type: ignore[assignment]
        Integer, name="property_number_of_decimals", nullable=True, default=None
    )
    __value: Optional[str] = Column(  # type: ignore[assignment]
        VARCHAR(255), name="property_value", nullable=True, default=None
    )
    __default: Optional[str] = Column(  # type: ignore[assignment]
        VARCHAR(255), name="property_default", nullable=True, default=None
    )

    # -----------------------------------------------------------------------------

    def __init__(self, identifier: str, property_id: Optional[uuid.UUID] = None) -> None:
        self.__property_id = property_id.bytes if property_id is not None else uuid.uuid4().bytes

        self.__identifier = identifier

        if self.type == PropertyType.STATIC:
            self.__settable = False
            self.__queryable = False

    # -----------------------------------------------------------------------------

    @property
    @abstractmethod
    def type(self) -> PropertyType:
        """Property type"""

    # -----------------------------------------------------------------------------

    @property
    def id(self) -> uuid.UUID:  # pylint: disable=invalid-name
        """Property unique identifier"""
        return uuid.UUID(bytes=self.__property_id)

    # -----------------------------------------------------------------------------

    @property
    def identifier(self) -> str:
        """Property unique identifier"""
        return self.__identifier

    # -----------------------------------------------------------------------------

    @property
    def key(self) -> str:
        """Property unique key"""
        return self.__key

    # -----------------------------------------------------------------------------

    @key.setter
    def key(self, key: str) -> None:
        """Property unique key setter"""
        self.__key = key

    # -----------------------------------------------------------------------------

    @property
    def name(self) -> Optional[str]:
        """Property name"""
        return self.__name

    # -----------------------------------------------------------------------------

    @name.setter
    def name(self, name: Optional[str]) -> None:
        """Property name setter"""
        self.__name = name

    # -----------------------------------------------------------------------------

    @property
    def settable(self) -> bool:
        """Property settable status"""
        return self.__settable

    # -----------------------------------------------------------------------------

    @settable.setter
    def settable(self, settable: bool) -> None:
        """Property settable setter"""
        if settable and self.type == PropertyType.STATIC:
            raise InvalidArgumentException("Static type property can not be settable")

        self.__settable = settable

    # -----------------------------------------------------------------------------

    @property
    def queryable(self) -> bool:
        """Property queryable status"""
        return self.__queryable

    # -----------------------------------------------------------------------------

    @queryable.setter
    def queryable(self, queryable: bool) -> None:
        """Property queryable setter"""
        if queryable and self.type == PropertyType.STATIC:
            raise InvalidArgumentException("Static type property can not be queryable")

        self.__queryable = queryable

    # -----------------------------------------------------------------------------

    @property
    def data_type(self) -> Optional[DataType]:
        """Transform data type to enum value"""
        return DataType(self.__data_type) if self.__data_type is not None else None

    # -----------------------------------------------------------------------------

    @data_type.setter
    def data_type(self, data_type: Optional[DataType]) -> None:
        self.__data_type = data_type.value if isinstance(data_type, DataType) else None

    # -----------------------------------------------------------------------------

    @property
    def unit(self) -> Optional[str]:
        """Property unit"""
        return self.__unit

    # -----------------------------------------------------------------------------

    @unit.setter
    def unit(self, unit: Optional[str]) -> None:
        """Property unit setter"""
        self.__unit = unit

    # -----------------------------------------------------------------------------

    @property
    def format(
        self,
    ) -> Union[
        Tuple[Optional[int], Optional[int]],
        Tuple[Optional[float], Optional[float]],
        List[Union[str, Tuple[str, Optional[str], Optional[str]]]],
        None,
    ]:
        """Property format"""
        return self.__build_format(self.__format)

    # -----------------------------------------------------------------------------

    @format.setter
    def format(self, value_format: Optional[str]) -> None:
        """Property format setter"""
        if value_format is not None and self.__build_format(value_format=value_format) is None:
            raise InvalidArgumentException("Provided property format is not valid")

        self.__format = value_format

    # -----------------------------------------------------------------------------

    @property
    def invalid(self) -> Union[str, int, float, None]:
        """Property invalid value"""
        if self.__invalid is None:
            return None

        if self.data_type is not None:
            if self.data_type in (
                DataType.CHAR,
                DataType.UCHAR,
                DataType.SHORT,
                DataType.USHORT,
                DataType.INT,
                DataType.UINT,
            ):
                return fast_int(self.__invalid)

            if self.data_type == DataType.FLOAT:
                return fast_float(self.__invalid)

        return self.__invalid

    # -----------------------------------------------------------------------------

    @invalid.setter
    def invalid(self, invalid: str) -> None:
        """Property invalid value setter"""
        self.__invalid = invalid

    # -----------------------------------------------------------------------------

    @property
    def number_of_decimals(self) -> Optional[int]:
        """Property value number of decimals"""
        return self.__number_of_decimals

    # -----------------------------------------------------------------------------

    @number_of_decimals.setter
    def number_of_decimals(self, number_of_decimals: Optional[int]) -> None:
        """Property value number of decimals setter"""
        self.__number_of_decimals = number_of_decimals

    # -----------------------------------------------------------------------------

    @property
    def value(self) -> Union[int, float, str, bool, datetime, ButtonPayload, SwitchPayload, None]:
        """Property value"""
        if not self.type == PropertyType.STATIC:
            raise InvalidStateException(f"Value is not allowed for property type: {self.type.value}")

        if self.__value is None:
            return None

        if self.data_type is None:
            return None

        return normalize_value(data_type=self.data_type, value=self.__value, value_format=self.format)

    # -----------------------------------------------------------------------------

    @value.setter
    def value(self, value: Optional[str]) -> None:
        """Property value number of decimals setter"""
        if not self.type == PropertyType.STATIC:
            raise InvalidStateException(f"Value is not allowed for property type: {self.type.value}")

        self.__value = value

    # -----------------------------------------------------------------------------

    @property
    def default(self) -> Union[int, float, str, bool, datetime, ButtonPayload, SwitchPayload, None]:
        """Property default"""
        if not self.type == PropertyType.STATIC:
            raise InvalidStateException(f"Default value is not allowed for property type: {self.type.value}")

        if self.__default is None:
            return None

        if self.data_type is None:
            return None

        return normalize_value(data_type=self.data_type, value=self.__default, value_format=self.format)

    # -----------------------------------------------------------------------------

    @default.setter
    def default(self, default: Optional[str]) -> None:
        """Property default number of decimals setter"""
        if not self.type == PropertyType.STATIC:
            raise InvalidStateException(f"Default value is not allowed for property type: {self.type.value}")

        self.__default = default

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
    ) -> Dict[
        str,
        Union[
            int,
            float,
            str,
            bool,
            datetime,
            ButtonPayload,
            SwitchPayload,
            List[Union[str, Tuple[str, Optional[str], Optional[str]]]],
            Tuple[Optional[int], Optional[int]],
            Tuple[Optional[float], Optional[float]],
            None,
        ],
    ]:
        """Transform entity to dictionary"""
        data: Dict[
            str,
            Union[
                int,
                float,
                str,
                bool,
                datetime,
                ButtonPayload,
                SwitchPayload,
                List[Union[str, Tuple[str, Optional[str], Optional[str]]]],
                Tuple[Optional[int], Optional[int]],
                Tuple[Optional[float], Optional[float]],
                None,
            ],
        ] = {
            "id": self.id.__str__(),
            "type": self.type.value,
            "key": self.key,
            "identifier": self.identifier,
            "name": self.name,
            "settable": self.settable,
            "queryable": self.queryable,
            "data_type": self.data_type.value if isinstance(self.data_type, DataType) else None,
            "unit": self.unit,
            "format": self.format,
            "invalid": self.invalid,
            "number_of_decimals": self.number_of_decimals,
        }

        if self.type == PropertyType.STATIC:
            return {
                **{
                    "value": self.value,
                    "default": self.default,
                },
                **data,
            }

        return data

    # -----------------------------------------------------------------------------

    def __build_format(  # pylint: disable=too-many-branches,too-many-return-statements
        self,
        value_format: Optional[str],
    ) -> Union[
        Tuple[Optional[int], Optional[int]],
        Tuple[Optional[float], Optional[float]],
        List[Union[str, Tuple[str, Optional[str], Optional[str]]]],
        None,
    ]:
        if value_format is None:
            return None

        if self.data_type is not None:
            if self.data_type in (
                DataType.CHAR,
                DataType.UCHAR,
                DataType.SHORT,
                DataType.USHORT,
                DataType.INT,
                DataType.UINT,
            ):
                format_parts = value_format.split(":")  # pylint: disable=unused-variable

                int_min_value: Optional[int] = None
                int_max_value: Optional[int] = None

                try:
                    int_min_value = int(fast_int(format_parts[0], raise_on_invalid=True))

                except (IndexError, ValueError):
                    int_min_value = None

                try:
                    int_max_value = int(fast_int(format_parts[1], raise_on_invalid=True))

                except (IndexError, ValueError):
                    int_max_value = None

                if int_min_value is not None and int_max_value is not None and int_min_value <= int_max_value:
                    return int_min_value, int_max_value

                if int_min_value is not None and int_max_value is None:
                    return int_min_value, None

                if int_min_value is None and int_max_value is not None:
                    return None, int_max_value

            elif self.data_type == DataType.FLOAT:
                format_parts = value_format.split(":")  # pylint: disable=unused-variable

                float_min_value: Optional[float] = None
                float_max_value: Optional[float] = None

                try:
                    float_min_value = float(fast_float(format_parts[0], raise_on_invalid=True))

                except (IndexError, ValueError):
                    float_min_value = None

                try:
                    float_max_value = float(fast_float(format_parts[1], raise_on_invalid=True))

                except (IndexError, ValueError):
                    float_max_value = None

                if float_min_value is not None and float_max_value is not None and float_min_value <= float_max_value:
                    return float_min_value, float_max_value

                if float_min_value is not None and float_max_value is None:
                    return float_min_value, None

                if float_min_value is None and float_max_value is not None:
                    return None, float_max_value

            elif self.data_type in (DataType.ENUM, DataType.BUTTON, DataType.SWITCH):
                enums = list({x.strip() for x in value_format.split(",")})
                enums.sort()

                enum_values: List[Union[str, Tuple[str, Optional[str], Optional[str]]]] = []

                for enum_element in enums:
                    if ":" not in enum_element:
                        enum_values.append(enum_element)

                        continue

                    parts = enum_element.split(":")

                    enum_item: List[Union[str, None]] = []

                    for i in range(0, 3):
                        try:
                            enum_item.append(str(parts[i]) if parts[i] is not None and parts[i] != "" else None)
                        except ValueError:
                            enum_item.append(None)

                    enum_values.append((str(enum_item[0]), enum_item[1], enum_item[2]))

                return enum_values

        return None
