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
Devices module DI container
"""

# pylint: disable=no-value-for-parameter

# Library dependencies
from enum import Enum
from typing import Dict
from kink import di
from pony.orm.dbproviders.mysql import MySQLProvider
from pony.orm.dbproviders.sqlite import SQLiteProvider

# Library libs
from devices_module.converters import EnumConverter
from devices_module.exchange import ModuleExchange
from devices_module.key import EntityKey
from devices_module.models import db
from devices_module.repositories import (
    ConnectorsRepository,
    ConnectorsControlsRepository,
    DevicesRepository,
    DevicesPropertiesRepository,
    DevicesControlsRepository,
    ChannelsRepository,
    ChannelsPropertiesRepository,
    ChannelsControlsRepository,
)


def create_container(settings: Dict) -> None:
    """Register devices module services"""
    # Add ENUM converter
    MySQLProvider.converter_classes.append((Enum, EnumConverter))
    SQLiteProvider.converter_classes.append((Enum, EnumConverter))

    di["fb-devices-module_database"] = db

    di[ConnectorsRepository] = ConnectorsRepository()
    di["fb-devices-module_connector-repository"] = di[ConnectorsRepository]
    di[ConnectorsControlsRepository] = ConnectorsControlsRepository()
    di["fb-devices-module_connector-control-repository"] = di[ConnectorsControlsRepository]
    di[DevicesRepository] = DevicesRepository()
    di["fb-devices-module_device-repository"] = di[DevicesRepository]
    di[DevicesPropertiesRepository] = DevicesPropertiesRepository()
    di["fb-devices-module_device-property-repository"] = di[DevicesPropertiesRepository]
    di[DevicesControlsRepository] = DevicesControlsRepository()
    di["fb-devices-module_device-control-repository"] = di[DevicesControlsRepository]
    di[ChannelsRepository] = ChannelsRepository()
    di["fb-devices-module_channel-repository"] = di[ChannelsRepository]
    di[ChannelsPropertiesRepository] = ChannelsPropertiesRepository()
    di["fb-devices-module_channel-property-repository"] = di[ChannelsPropertiesRepository]
    di[ChannelsControlsRepository] = ChannelsControlsRepository()
    di["fb-devices-module_channel-control-repository"] = di[ChannelsControlsRepository]

    di[ModuleExchange] = ModuleExchange()
    di["fb-devices-module_exchange"] = di[ModuleExchange]

    di[EntityKey] = EntityKey()
    di["fb-devices-module_entity-key-generator"] = di[EntityKey]

    db.bind(
        provider="mysql",
        host=settings.get("host", "127.0.0.1"),
        user=settings.get("user", None),
        passwd=settings.get("passwd", None),
        db=settings.get("db", None),
    )
    db.generate_mapping(create_tables=settings.get("create_tables", False))
