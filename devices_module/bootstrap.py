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
    DevicesConfigurationRepository,
    ChannelsRepository,
    ChannelsPropertiesRepository,
    ChannelsControlsRepository,
    ChannelsConfigurationRepository,
)

default_settings: Dict[str, Dict[str, str or int or bool or None]] = {
    "database": {
        "provider": "mysql",
        "host": "127.0.0.1",
        "port": 3306,
        "username": None,
        "password": None,
        "database": "fb_devices_module",
        "create_tables": False,
    },
}


def create_container(settings: Dict[str, Dict[str, str or int or bool or None]]) -> None:
    """Register devices module services"""
    module_settings: Dict[str, Dict[str, str or int or bool or None]] = {**default_settings, **settings}

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
    di[DevicesConfigurationRepository] = DevicesConfigurationRepository()
    di["fb-devices-module_device-configuration-repository"] = di[DevicesConfigurationRepository]
    di[ChannelsRepository] = ChannelsRepository()
    di["fb-devices-module_channel-repository"] = di[ChannelsRepository]
    di[ChannelsPropertiesRepository] = ChannelsPropertiesRepository()
    di["fb-devices-module_channel-property-repository"] = di[ChannelsPropertiesRepository]
    di[ChannelsControlsRepository] = ChannelsControlsRepository()
    di["fb-devices-module_channel-control-repository"] = di[ChannelsControlsRepository]
    di[ChannelsConfigurationRepository] = ChannelsConfigurationRepository()
    di["fb-devices-module_channel-configuration-repository"] = di[ChannelsConfigurationRepository]

    di[ModuleExchange] = ModuleExchange()
    di["fb-devices-module_exchange"] = di[ModuleExchange]

    di[EntityKey] = EntityKey()
    di["fb-devices-module_entity-key-generator"] = di[EntityKey]

    db.bind(
        provider="mysql",
        host=module_settings.get("database", {}).get("host", "127.0.0.1"),
        user=module_settings.get("database", {}).get("username", None),
        passwd=module_settings.get("database", {}).get("password", None),
        db=module_settings.get("database", {}).get("database", None),
        port=int(module_settings.get("database", {}).get("port", 3306)),
    )
    db.generate_mapping(create_tables=settings.get("database", {}).get("create_tables", False))
