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

# Python base dependencies
from typing import Dict, Union

# Library dependencies
from kink import di

# Library libs
from devices_module.exchange import ModuleExchange
from devices_module.helpers import ItemValueHelpers, KeyHashHelpers
from devices_module.models import db
from devices_module.repositories import (
    ChannelsConfigurationRepository,
    ChannelsControlsRepository,
    ChannelsPropertiesRepository,
    ChannelsRepository,
    ConnectorsControlsRepository,
    ConnectorsRepository,
    DevicesConfigurationRepository,
    DevicesControlsRepository,
    DevicesPropertiesRepository,
    DevicesRepository,
)

default_settings: Dict[str, Dict[str, Union[str, int, bool, None]]] = {
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


def create_container(settings: Dict[str, Dict[str, Union[str, int, bool, None]]]) -> None:
    """Register devices module services"""
    module_settings: Dict[str, Dict[str, Union[str, int, bool, None]]] = {**default_settings, **settings}

    di["fb-devices-module_database"] = db

    di[ConnectorsRepository] = ConnectorsRepository()  # type: ignore[call-arg]
    di["fb-devices-module_connector-repository"] = di[ConnectorsRepository]
    di[ConnectorsControlsRepository] = ConnectorsControlsRepository()  # type: ignore[call-arg]
    di["fb-devices-module_connector-control-repository"] = di[ConnectorsControlsRepository]
    di[DevicesRepository] = DevicesRepository()  # type: ignore[call-arg]
    di["fb-devices-module_device-repository"] = di[DevicesRepository]
    di[DevicesPropertiesRepository] = DevicesPropertiesRepository()  # type: ignore[call-arg]
    di["fb-devices-module_device-property-repository"] = di[DevicesPropertiesRepository]
    di[DevicesControlsRepository] = DevicesControlsRepository()  # type: ignore[call-arg]
    di["fb-devices-module_device-control-repository"] = di[DevicesControlsRepository]
    di[DevicesConfigurationRepository] = DevicesConfigurationRepository()  # type: ignore[call-arg]
    di["fb-devices-module_device-configuration-repository"] = di[DevicesConfigurationRepository]
    di[ChannelsRepository] = ChannelsRepository()  # type: ignore[call-arg]
    di["fb-devices-module_channel-repository"] = di[ChannelsRepository]
    di[ChannelsPropertiesRepository] = ChannelsPropertiesRepository()  # type: ignore[call-arg]
    di["fb-devices-module_channel-property-repository"] = di[ChannelsPropertiesRepository]
    di[ChannelsControlsRepository] = ChannelsControlsRepository()  # type: ignore[call-arg]
    di["fb-devices-module_channel-control-repository"] = di[ChannelsControlsRepository]
    di[ChannelsConfigurationRepository] = ChannelsConfigurationRepository()  # type: ignore[call-arg]
    di["fb-devices-module_channel-configuration-repository"] = di[ChannelsConfigurationRepository]

    di[ModuleExchange] = ModuleExchange()  # type: ignore[call-arg]
    di["fb-devices-module_exchange"] = di[ModuleExchange]

    di[ItemValueHelpers] = ItemValueHelpers()
    di["fb-devices-module_helpers-properties"] = di[ItemValueHelpers]

    di[KeyHashHelpers] = KeyHashHelpers()
    di["fb-devices-module_helpers-key-hash"] = di[KeyHashHelpers]

    db.bind(
        provider="mysql",
        host=module_settings.get("database", {}).get("host", "127.0.0.1"),
        user=module_settings.get("database", {}).get("username", None),
        passwd=module_settings.get("database", {}).get("password", None),
        db=module_settings.get("database", {}).get("database", None),
        port=int(str(module_settings.get("database", {}).get("port", 3306))),
    )
    db.generate_mapping(create_tables=settings.get("database", {}).get("create_tables", False))
