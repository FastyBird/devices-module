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

# App dependencies
import uuid
from abc import abstractmethod, ABC
from modules_metadata.types import DataType
from pony.orm import core as orm
from typing import Dict, List, Set, Tuple

# App libs
from devices_module.models import ConnectorEntity, DevicePropertyEntity, ChannelPropertyEntity
from devices_module.items import ConnectorItem, DevicePropertyEntity, ChannelPropertyItem


#
# Base properties repository
#
# @package        FastyBird:DevicesModule!
# @subpackage     Repositories
#
# @author         Adam Kadlec <adam.kadlec@fastybird.com>
#
class PropertiesRepository(ABC):
    _cache: List[ChannelPropertyItem or DevicePropertyItem] or None = None

    __iterator_index = 0

    # -----------------------------------------------------------------------------

    def get_property_by_id(self, property_id: uuid.UUID) -> DevicePropertyItem or ChannelPropertyItem or None:
        if self._cache is None:
            self.initialize()

        for record in self._cache:
            if record.property_id == property_id:
                return record

        return None

    # -----------------------------------------------------------------------------

    def get_property_by_key(self, property_key: str) -> DevicePropertyItem or ChannelPropertyItem or None:
        if self._cache is None:
            self.initialize()

        for record in self._cache:
            if record.key == property_key:
                return record

        return None

    # -----------------------------------------------------------------------------

    def clear_cache(self) -> None:
        self._cache = None

    # -----------------------------------------------------------------------------

    @abstractmethod
    def initialize(self) -> None:
        pass

    # -----------------------------------------------------------------------------

    def __iter__(self) -> "PropertiesRepository":
        # Reset index for nex iteration
        self.__iterator_index = 0

        return self

    # -----------------------------------------------------------------------------

    def __next__(self) -> DevicePropertyItem or ChannelPropertyItem:
        if self.__iterator_index < len(self._cache):
            result: ConnectorItem = self._cache[self.__iterator_index]

            self.__iterator_index += 1

            return result

        # Reset index for nex iteration
        self.__iterator_index = 0

        # End of iteration
        raise StopIteration


#
# Device properties repository
#
# @package        FastyBird:DevicesModule!
# @subpackage     Repositories
#
# @author         Adam Kadlec <adam.kadlec@fastybird.com>
#
class DevicesPropertiesRepository(PropertiesRepository):
    @orm.db_session
    def initialize(self) -> None:
        self._cache = []

        for entity in DevicePropertyEntity.select():
            self._cache.append(
                DevicePropertyItem(
                    property_id=entity.property_id,
                    property_identifier=entity.identifier,
                    property_key=entity.key,
                    property_settable=entity.settable,
                    property_queryable=entity.queryable,
                    property_data_type=entity.data_type,
                    property_format=entity.format,
                    property_unit=entity.unit,
                    device_id=entity.device.device_id,
                )
            )


#
# Channel properties repository
#
# @package        FastyBird:DevicesModule!
# @subpackage     Repositories
#
# @author         Adam Kadlec <adam.kadlec@fastybird.com>
#
class ChannelsPropertiesRepository(PropertiesRepository):
    @orm.db_session
    def initialize(self) -> None:
        self._cache = []

        for entity in ChannelPropertyEntity.select():
            self._cache.append(
                ChannelPropertyItem(
                    property_id=entity.property_id,
                    property_identifier=entity.identifier,
                    property_key=entity.key,
                    property_settable=entity.settable,
                    property_queryable=entity.queryable,
                    property_data_type=entity.data_type,
                    property_format=entity.format,
                    property_unit=entity.unit,
                    device_id=entity.channel.device.device_id,
                    channel_id=entity.channel.channel_id,
                )
            )


#
# Connectors repository
#
# @package        FastyBird:DevicesModule!
# @subpackage     Repositories
#
# @author         Adam Kadlec <adam.kadlec@fastybird.com>
#
class ConnectorsRepository(ABC):

    __cache: List[ConnectorItem] or None = None

    __iterator_index = 0

    # -----------------------------------------------------------------------------

    def get_connector_by_id(self, connector_id: uuid.UUID) -> ConnectorItem or None:
        if self.__cache is None:
            self.initialize()

        for record in self.__cache:
            if record.connector_id == connector_id:
                return record

        return None

    # -----------------------------------------------------------------------------

    def get_connector_by_key(self, connector_key: str) -> ConnectorItem or None:
        if self.__cache is None:
            self.initialize()

        for record in self.__cache:
            if record.key == connector_key:
                return record

        return None

    # -----------------------------------------------------------------------------

    def clear_cache(self) -> None:
        self.__cache = None

    # -----------------------------------------------------------------------------

    @orm.db_session
    def initialize(self) -> None:
        self.__cache = []

        for entity in ConnectorEntity.select():
            self.__cache.append(
                ConnectorItem(
                    connector_id=entity.connector_id,
                    connector_name=entity.name,
                    connector_key=entity.key,
                    connector_enabled=entity.enabled,
                    connector_type=entity.type,
                    connector_params=entity.params,
                )
            )

    # -----------------------------------------------------------------------------

    def __iter__(self) -> "ConnectorsRepository":
        # Reset index for nex iteration
        self.__iterator_index = 0

        return self

    # -----------------------------------------------------------------------------

    def __next__(self) -> ConnectorItem:
        if self.__iterator_index < len(self.__cache):
            result: ConnectorItem = self.__cache[self.__iterator_index]

            self.__iterator_index += 1

            return result

        # Reset index for nex iteration
        self.__iterator_index = 0

        # End of iteration
        raise StopIteration


# -----------------------------------------------------------------------------


device_property_cache = DevicesPropertiesRepository()

channel_property_cache = ChannelsPropertiesRepository()

connector_cache = ConnectorsRepository()
