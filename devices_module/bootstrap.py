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
import logging

# Library dependencies
from kink import di
from sqlalchemy.orm import Session as OrmSession

# Library libs
from devices_module.helpers import KeyHashHelpers
from devices_module.managers.channel import (
    ChannelConfigurationManager,
    ChannelControlsManager,
    ChannelPropertiesManager,
    ChannelsManager,
)
from devices_module.managers.connector import (
    ConnectorControlsManager,
    ConnectorsManager,
)
from devices_module.managers.device import (
    DeviceConfigurationManager,
    DeviceControlsManager,
    DevicePropertiesManager,
    DevicesManager,
)
from devices_module.repositories.channel import (
    ChannelsConfigurationRepository,
    ChannelsControlsRepository,
    ChannelsPropertiesRepository,
    ChannelsRepository,
)
from devices_module.repositories.connector import (
    ConnectorsControlsRepository,
    ConnectorsRepository,
)
from devices_module.repositories.device import (
    DevicesConfigurationRepository,
    DevicesControlsRepository,
    DevicesPropertiesRepository,
    DevicesRepository,
)
from devices_module.subscriber import EntitiesSubscriber, EntityCreatedSubscriber


def register_services(
    logger: logging.Logger = logging.getLogger("dummy"),
) -> None:
    """Register devices module services"""
    if OrmSession not in di:
        logger.error("SQLAlchemy database session is not registered in container!")

        return

    di[KeyHashHelpers] = KeyHashHelpers()
    di["fb-devices-module_helpers-key-hash"] = di[KeyHashHelpers]

    di[ConnectorsRepository] = ConnectorsRepository(session=di[OrmSession])
    di["fb-devices-module_connector-repository"] = di[ConnectorsRepository]
    di[ConnectorsControlsRepository] = ConnectorsControlsRepository(session=di[OrmSession])
    di["fb-devices-module_connector-control-repository"] = di[ConnectorsControlsRepository]
    di[DevicesRepository] = DevicesRepository(session=di[OrmSession])
    di["fb-devices-module_device-repository"] = di[DevicesRepository]
    di[DevicesPropertiesRepository] = DevicesPropertiesRepository(session=di[OrmSession])
    di["fb-devices-module_device-property-repository"] = di[DevicesPropertiesRepository]
    di[DevicesControlsRepository] = DevicesControlsRepository(session=di[OrmSession])
    di["fb-devices-module_device-control-repository"] = di[DevicesControlsRepository]
    di[DevicesConfigurationRepository] = DevicesConfigurationRepository(session=di[OrmSession])
    di["fb-devices-module_device-configuration-repository"] = di[DevicesConfigurationRepository]
    di[ChannelsRepository] = ChannelsRepository(session=di[OrmSession])
    di["fb-devices-module_channel-repository"] = di[ChannelsRepository]
    di[ChannelsPropertiesRepository] = ChannelsPropertiesRepository(session=di[OrmSession])
    di["fb-devices-module_channel-property-repository"] = di[ChannelsPropertiesRepository]
    di[ChannelsControlsRepository] = ChannelsControlsRepository(session=di[OrmSession])
    di["fb-devices-module_channel-control-repository"] = di[ChannelsControlsRepository]
    di[ChannelsConfigurationRepository] = ChannelsConfigurationRepository(session=di[OrmSession])
    di["fb-devices-module_channel-configuration-repository"] = di[ChannelsConfigurationRepository]

    di[ConnectorsManager] = ConnectorsManager(session=di[OrmSession])
    di["fb-devices-module_connectors-manager"] = di[ConnectorsManager]
    di[ConnectorControlsManager] = ConnectorControlsManager(session=di[OrmSession])
    di["fb-devices-module_connectors-controls-manager"] = di[ConnectorControlsManager]
    di[DevicesManager] = DevicesManager(session=di[OrmSession])
    di["fb-devices-module_devices-manager"] = di[DevicesManager]
    di[DevicePropertiesManager] = DevicePropertiesManager(session=di[OrmSession])
    di["fb-devices-module_devices-properties-manager"] = di[DevicePropertiesManager]
    di[DeviceConfigurationManager] = DeviceConfigurationManager(session=di[OrmSession])
    di["fb-devices-module_devices-configuration-manager"] = di[DeviceConfigurationManager]
    di[DeviceControlsManager] = DeviceControlsManager(session=di[OrmSession])
    di["fb-devices-module_devices-controls-manager"] = di[DeviceControlsManager]
    di[ChannelsManager] = ChannelsManager(session=di[OrmSession])
    di["fb-devices-module_channels-manager"] = di[ChannelsManager]
    di[ChannelPropertiesManager] = ChannelPropertiesManager(session=di[OrmSession])
    di["fb-devices-module_channels-properties-manager"] = di[ChannelPropertiesManager]
    di[ChannelConfigurationManager] = ChannelConfigurationManager(session=di[OrmSession])
    di["fb-devices-module_channels-configuration-manager"] = di[ChannelConfigurationManager]
    di[ChannelControlsManager] = ChannelControlsManager(session=di[OrmSession])
    di["fb-devices-module_channels-controls-manager"] = di[ChannelControlsManager]

    di[EntitiesSubscriber] = EntitiesSubscriber(key_hash_helpers=di[KeyHashHelpers])
    di["fb-devices-module_entities-subscriber"] = di[EntitiesSubscriber]
    di[EntityCreatedSubscriber] = EntityCreatedSubscriber()
    di["fb-devices-module_entity-created-subscriber"] = di[EntityCreatedSubscriber]
