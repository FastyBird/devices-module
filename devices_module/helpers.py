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
Devices module helpers
"""

# Python base dependencies
import math
import time
from typing import Callable, Optional, Set, Union

# Library dependencies
from fastnumbers import fast_float, fast_int
from kink import inject
from modules_metadata.types import ButtonPayload, DataType, SwitchPayload
from pony.orm import core as orm

# Library libs
from devices_module.items import ChannelPropertyItem, DevicePropertyItem


@inject
class PropertiesHelpers:  # pylint: disable=too-few-public-methods
    """
    Properties helpers

    @package        FastyBird:DevicesModule!
    @module         helpers

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    @staticmethod
    def normalize_value(  # pylint: disable=too-many-return-statements
        item: Union[DevicePropertyItem, ChannelPropertyItem],
        value: Union[int, float, str, bool, None],
    ) -> Union[int, float, str, bool, ButtonPayload, SwitchPayload, None]:
        """Normalize property value based od property data type"""
        if value is None or item.data_type is None:
            return value

        if item.data_type == DataType.INT:
            return fast_int(value)

        if item.data_type == DataType.FLOAT:
            return fast_float(value)

        if item.data_type == DataType.STRING:
            return str(value)

        if item.data_type == DataType.BOOLEAN:
            value = str(value)

            return value.lower() in ["true", "t", "yes", "y", "1", "on"]

        if item.data_type == DataType.ENUM:
            data_format = item.get_format()

            if data_format is not None and isinstance(data_format, Set) and str(value) in data_format:
                return str(value)

            return None

        if item.data_type == DataType.BUTTON:
            if ButtonPayload.has_value(str(value)):
                return ButtonPayload(str(value))

            return None

        if item.data_type == DataType.SWITCH:
            if SwitchPayload.has_value(str(value)):
                return SwitchPayload(str(value))

            return None

        return value


@inject
class KeyHashHelpers:
    """
    Key hash generator & parser

    @package        FastyBird:DevicesModule!
    @module         helpers

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    __max_len: int = 6
    __custom_generator: Optional[Callable[[orm.Entity], str]] = None

    __ALPHABET: str = "bcdfghjklmnpqrstvwxyz0123456789BCDFGHJKLMNPQRSTVWXYZ"

    __BASE: int = len(__ALPHABET)

    # -----------------------------------------------------------------------------

    def __init__(self, max_len: int = 6):
        self.__max_len = max_len

    # -----------------------------------------------------------------------------

    def set_generator(self, callback: Callable[[orm.Entity], str]) -> None:
        """Set custom key generator"""
        self.__custom_generator = callback

    # -----------------------------------------------------------------------------

    def generate_key(self, entity: orm.Entity) -> str:
        """Generate key for given entity"""
        if self.__custom_generator is not None:
            return self.__custom_generator(entity)

        return self.encode(int(time.time_ns() / 1000))

    # -----------------------------------------------------------------------------

    def encode(self, number: int, max_length: Optional[int] = None) -> str:
        """Convert number to key hash"""
        pad = (self.__max_len if max_length is None else max_length) - 1
        number = int(number + pow(self.__BASE, pad))

        result = []
        pointer = int(math.log(number, self.__BASE))

        while True:
            bcp = int(pow(self.__BASE, pointer))
            position = int(number / bcp) % self.__BASE
            result.append(self.__ALPHABET[position : position + 1])
            number = number - (position * bcp)
            pointer -= 1

            if pointer < 0:
                break

        return "".join(reversed(result))

    # -----------------------------------------------------------------------------

    def decode(self, string: str, max_length: Optional[int] = None) -> int:
        """Convert key hash to number"""
        string = "".join(reversed(string))
        result = 0
        length = len(string) - 1
        pointer = 0

        while True:
            bcpow = int(pow(self.__BASE, length - pointer))
            result = result + self.__ALPHABET.index(string[pointer : pointer + 1]) * bcpow
            pointer += 1
            if pointer > length:
                break

        pad = (self.__max_len if max_length is None else max_length) - 1
        result = int(result - pow(self.__BASE, pad))

        return int(result)
