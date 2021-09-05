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
Database converters
"""

# App dependencies
from typing import Type
from enum import Enum
from pony.orm.dbapiprovider import IntConverter, StrConverter, Converter


class EnumConverter(Converter):
    """
    Database enum data type converter

    @package        FastyBird:DevicesModule!
    @module         converters

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    __converter: Converter
    __converter_class: Type[Converter]

    @staticmethod
    def _get_real_converter(py_type) -> Type[Converter]:
        """
        Gets a converter for the underlying type
        """
        if issubclass(py_type, Enum):
            for member in py_type.__members__.values():
                if issubclass(type(member.value), int):
                    return IntConverter

                if issubclass(type(member.value), str):
                    return StrConverter

        elif issubclass(py_type, int):
            return IntConverter

        elif issubclass(py_type, str):
            return StrConverter

        raise TypeError("only str and int based enums supported")

    # -----------------------------------------------------------------------------

    def __init__(self, provider, py_type, attr=None) -> None:
        Converter.__init__(self, provider, py_type, attr)

        self.__converter_class = self._get_real_converter(self.py_type)
        self.__converter = self.__converter_class(provider=self.provider, py_type=self.py_type, attr=self.attr)

    # -----------------------------------------------------------------------------

    def init(self, kwargs) -> None:
        """
        Initialize ENUM database converter
        """
        if hasattr(self, "__converter"):
            self.__converter.init(kwargs=kwargs)

    # -----------------------------------------------------------------------------

    def validate(self, val, obj=None):
        """
        Validate attribute data type
        """
        if val is not None and isinstance(val, Enum):
            return self.__converter.validate(val=val.value, obj=obj)

        if val is None or (isinstance(val, (str, int))):
            return self.__converter.validate(val=val, obj=obj)

        return False

    # -----------------------------------------------------------------------------

    def py2sql(self, val):
        """
        Convert Python value data type to SQL data type
        """
        return self.__converter.py2sql(val=val)

    # -----------------------------------------------------------------------------

    def sql2py(self, val):
        """
        Convert SQL value data type to Python data type
        """
        return self.__converter.sql2py(val=val)

    # -----------------------------------------------------------------------------

    def val2dbval(self, val, obj=None):
        """
        Passes on the value to the right converter
        """
        return self.__converter.val2dbval(val=val, obj=obj)

    # -----------------------------------------------------------------------------

    def dbval2val(self, dbval, obj=None):
        """
        Passes on the value to the right converter
        """
        py_val = self.__converter.dbval2val(dbval=dbval, obj=obj)

        if py_val is None:
            return None

        return self.py_type(py_val)  # SomeEnum(123) => SomeEnum.SOMETHING

    # -----------------------------------------------------------------------------

    def dbvals_equal(self, x, y) -> bool:  # pylint: disable=invalid-name
        """
        Compare values
        """
        return self.__converter.dbvals_equal(x=x, y=y)

    # -----------------------------------------------------------------------------

    def get_sql_type(self, attr=None):
        """
        Get SQL data type
        """
        return self.__converter.get_sql_type(attr=attr)

    # -----------------------------------------------------------------------------

    def get_fk_type(self, sql_type):
        """
        Get foreign key data type
        """
        return self.__converter.get_fk_type(sql_type=sql_type)
