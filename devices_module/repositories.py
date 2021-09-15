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

# pylint: disable=too-many-lines

"""
Module repositories definitions
"""

# Library dependencies
import json
import uuid
from abc import abstractmethod, ABC
from typing import List, Dict
import modules_metadata.exceptions as metadata_exceptions
from modules_metadata.loader import load_schema
from modules_metadata.routing import RoutingKey
from modules_metadata.validator import validate
from modules_metadata.types import ModuleOrigin, DataType
from pony.orm import core as orm

# Library libs
from devices_module.exceptions import HandleExchangeDataException
from devices_module.items import (
    ConnectorItem,
    FbBusConnectorItem,
    FbMqttV1ConnectorItem,
    PropertyItem,
    DevicePropertyItem,
    ChannelPropertyItem,
)
from devices_module.models import (
    DevicePropertyEntity,
    ChannelPropertyEntity,
    ConnectorEntity,
    FbBusConnectorEntity,
    FbMqttV1ConnectorEntity,
)


class PropertiesRepository(ABC):
    """
    Base properties repository

    @package        FastyBird:DevicesModule!
    @module         repositories

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    _items: Dict[str, ChannelPropertyItem or DevicePropertyItem] or None = None

    __iterator_index = 0

    # -----------------------------------------------------------------------------

    def get_by_id(self, property_id: uuid.UUID) -> DevicePropertyItem or ChannelPropertyItem or None:
        """Find property in cache by provided identifier"""
        if self._items is None:
            self.initialize()

        if property_id.__str__() in self._items:
            return self._items[property_id.__str__()]

        return None

    # -----------------------------------------------------------------------------

    def get_by_key(self, property_key: str) -> DevicePropertyItem or ChannelPropertyItem or None:
        """Find property in cache by provided key"""
        if self._items is None:
            self.initialize()

        for record in self._items.values():
            if record.key == property_key:
                return record

        return None

    # -----------------------------------------------------------------------------

    def clear(self) -> None:
        """Clear items cache"""
        self._items = None

    # -----------------------------------------------------------------------------

    @abstractmethod
    def initialize(self) -> None:
        """Initialize repository by fetching entities from database"""

    # -----------------------------------------------------------------------------

    @staticmethod
    def _create_item(entity: DevicePropertyEntity or ChannelPropertyEntity) -> PropertyItem or None:
        if isinstance(entity, DevicePropertyEntity):
            return DevicePropertyItem(
                property_id=entity.property_id,
                property_name=entity.name,
                property_identifier=entity.identifier,
                property_key=entity.key,
                property_settable=entity.settable,
                property_queryable=entity.queryable,
                property_data_type=entity.data_type,
                property_format=entity.format,
                property_unit=entity.unit,
                device_id=entity.device.device_id,
            )

        if isinstance(entity, ChannelPropertyEntity):
            return ChannelPropertyItem(
                property_id=entity.property_id,
                property_name=entity.name,
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

        return None

    # -----------------------------------------------------------------------------

    @staticmethod
    def _update_item(item: PropertyItem, data: Dict) -> PropertyItem or None:
        data_type = data.get("data_type", item.data_type.value if item.data_type is not None else None)
        data_type = DataType(data_type) if data_type is not None else None

        if isinstance(item, DevicePropertyItem):
            return DevicePropertyItem(
                property_id=item.property_id,
                property_name=data.get("name", item.name),
                property_identifier=item.identifier,
                property_key=item.key,
                property_settable=data.get("settable", item.settable),
                property_queryable=data.get("queryable", item.queryable),
                property_data_type=data_type,
                property_format=data.get("format", item.format),
                property_unit=data.get("unit", item.unit),
                device_id=item.device_id,
            )

        if isinstance(item, ChannelPropertyItem):
            return ChannelPropertyItem(
                property_id=item.property_id,
                property_name=data.get("name", item.name),
                property_identifier=item.identifier,
                property_key=item.key,
                property_settable=data.get("settable", item.settable),
                property_queryable=data.get("queryable", item.queryable),
                property_data_type=data_type,
                property_format=data.get("format", item.format),
                property_unit=data.get("unit", item.unit),
                device_id=item.device_id,
                channel_id=item.channel_id,
            )

        return None

    # -----------------------------------------------------------------------------

    def __iter__(self) -> "PropertiesRepository":
        # Reset index for nex iteration
        self.__iterator_index = 0

        return self

    # -----------------------------------------------------------------------------

    def __len__(self):
        if self._items is None:
            self.initialize()

        return len(self._items.values())

    # -----------------------------------------------------------------------------

    def __next__(self) -> DevicePropertyItem or ChannelPropertyItem:
        if self._items is None:
            self.initialize()

        if self.__iterator_index < len(self._items.values()):
            items: List[DevicePropertyItem or ChannelPropertyItem] = list(self._items.values())

            result: DevicePropertyItem or ChannelPropertyItem = items[self.__iterator_index]

            self.__iterator_index += 1

            return result

        # Reset index for nex iteration
        self.__iterator_index = 0

        # End of iteration
        raise StopIteration


class DevicesPropertiesRepository(PropertiesRepository):
    """
    Devices properties repository

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    @orm.db_session
    def create_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received device property message from exchange when entity was created"""
        if routing_key != RoutingKey.DEVICES_PROPERTY_ENTITY_CREATED:
            return False

        if self._items is None:
            self.initialize()

            return True

        data: Dict = validate_exchange_data(ModuleOrigin(ModuleOrigin.DEVICES_MODULE), routing_key, data)

        entity: DevicePropertyEntity or None = DevicePropertyEntity.get(
            property_id=uuid.UUID(data.get("id"), version=4),
        )

        if entity is not None:
            self._items[entity.property_id.__str__()] = self._create_item(entity)

            return True

        return False

    # -----------------------------------------------------------------------------

    @orm.db_session
    def update_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received device property message from exchange when entity was updated"""
        if routing_key != RoutingKey.DEVICES_PROPERTY_ENTITY_UPDATED:
            return False

        if self._items is None:
            self.initialize()

            return True

        validated_data: Dict = validate_exchange_data(ModuleOrigin(ModuleOrigin.DEVICES_MODULE), routing_key, data)

        if validated_data.get("id") not in self._items:
            entity: DevicePropertyEntity or None = DevicePropertyEntity.get(
                property_id=uuid.UUID(validated_data.get("id"), version=4)
            )

            if entity is not None:
                self._items[entity.property_id.__str__()] = self._create_item(entity)

                return True

            return False

        item = self._update_item(
            self.get_by_id(uuid.UUID(validated_data.get("id"), version=4)),
            validated_data,
        )

        if item is not None:
            self._items[validated_data.get("id")] = item

            return True

        return False

    # -----------------------------------------------------------------------------

    @orm.db_session
    def delete_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received device property message from exchange when entity was updated"""
        if routing_key != RoutingKey.DEVICES_PROPERTY_ENTITY_DELETED:
            return False

        if data.get("id") in self._items:
            del self._items[data.get("id")]

            return True

        return False

    # -----------------------------------------------------------------------------

    @orm.db_session
    def initialize(self) -> None:
        """Initialize devices properties repository by fetching entities from database"""
        items: Dict[str, DevicePropertyItem] = {}

        for entity in DevicePropertyEntity.select():
            if self._items is None or entity.property_id.__str__() not in self._items:
                item = self._create_item(entity)

            else:
                item = self._update_item(self.get_by_id(entity.property_id), entity.to_dict())

            if item is not None:
                items[entity.property_id.__str__()] = item

        self._items = items


class ChannelsPropertiesRepository(PropertiesRepository):
    """
    Channels properties repository

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    @orm.db_session
    def create_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received channel property message from exchange when entity was created"""
        if routing_key != RoutingKey.CHANNELS_PROPERTY_ENTITY_CREATED:
            return False

        if self._items is None:
            self.initialize()

            return True

        data: Dict = validate_exchange_data(ModuleOrigin(ModuleOrigin.DEVICES_MODULE), routing_key, data)

        entity: ChannelPropertyEntity or None = ChannelPropertyEntity.get(
            property_id=uuid.UUID(data.get("id"), version=4),
        )

        if entity is not None:
            self._items[entity.property_id.__str__()] = self._create_item(entity)

            return True

        return False

    # -----------------------------------------------------------------------------

    @orm.db_session
    def update_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received channel property message from exchange when entity was updated"""
        if routing_key != RoutingKey.CHANNELS_PROPERTY_ENTITY_UPDATED:
            return False

        if self._items is None:
            self.initialize()

            return True

        validated_data: Dict = validate_exchange_data(ModuleOrigin(ModuleOrigin.DEVICES_MODULE), routing_key, data)

        if validated_data.get("id") not in self._items:
            entity: ChannelPropertyEntity or None = ChannelPropertyEntity.get(
                property_id=uuid.UUID(validated_data.get("id"), version=4)
            )

            if entity is not None:
                self._items[entity.property_id.__str__()] = self._create_item(entity)

                return True

            return False

        item = self._update_item(
            self.get_by_id(uuid.UUID(validated_data.get("id"), version=4)),
            validated_data,
        )

        if item is not None:
            self._items[validated_data.get("id")] = item

            return True

        return False

    # -----------------------------------------------------------------------------

    @orm.db_session
    def delete_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received channel property message from exchange when entity was updated"""
        if routing_key != RoutingKey.CHANNELS_PROPERTY_ENTITY_DELETED:
            return False

        if data.get("id") in self._items:
            del self._items[data.get("id")]

            return True

        return False

    # -----------------------------------------------------------------------------

    @orm.db_session
    def initialize(self) -> None:
        """Initialize channel properties repository by fetching entities from database"""
        items: Dict[str, ChannelPropertyItem] = {}

        for entity in ChannelPropertyEntity.select():
            if self._items is None or entity.property_id.__str__() not in self._items:
                item = self._create_item(entity)

            else:
                item = self._update_item(self.get_by_id(entity.property_id), entity.to_dict())

            if item is not None:
                items[entity.property_id.__str__()] = item

        self._items = items


class ConnectorsRepository(ABC):
    """
    Connectors repository

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """
    __items: Dict[str, ConnectorItem] or None = None

    __iterator_index = 0

    # -----------------------------------------------------------------------------

    def get_by_id(self, connector_id: uuid.UUID) -> ConnectorItem or None:
        """Find connector in cache by provided identifier"""
        if self.__items is None:
            self.initialize()

        if connector_id.__str__() in self.__items:
            return self.__items[connector_id.__str__()]

        return None

    # -----------------------------------------------------------------------------

    def get_by_key(self, connector_key: str) -> ConnectorItem or None:
        """Find connector in cache by provided key"""
        if self.__items is None:
            self.initialize()

        for record in self.__items.values():
            if record.key == connector_key:
                return record

        return None

    # -----------------------------------------------------------------------------

    @orm.db_session
    def create_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received connector message from exchange when entity was created"""
        if routing_key != RoutingKey.CONNECTOR_ENTITY_CREATED:
            return False

        if self.__items is None:
            self.initialize()

            return True

        data: Dict = validate_exchange_data(ModuleOrigin(ModuleOrigin.DEVICES_MODULE), routing_key, data)

        entity: ConnectorEntity or None = ConnectorEntity.get(connector_id=uuid.UUID(data.get("id"), version=4))

        if entity is not None:
            self.__items[entity.connector_id.__str__()] = self.__create_item(entity)

            return True

        return False

    # -----------------------------------------------------------------------------

    @orm.db_session
    def update_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received connector message from exchange when entity was updated"""
        if routing_key != RoutingKey.CONNECTOR_ENTITY_UPDATED:
            return False

        if self.__items is None:
            self.initialize()

            return True

        validated_data: Dict = validate_exchange_data(ModuleOrigin(ModuleOrigin.DEVICES_MODULE), routing_key, data)

        if validated_data.get("id") not in self.__items:
            entity: ConnectorEntity or None = ConnectorEntity.get(
                connector_id=uuid.UUID(validated_data.get("id"), version=4)
            )

            if entity is not None:
                self.__items[entity.connector_id.__str__()] = self.__create_item(entity)

                return True

            return False

        item = self.__update_item(
            self.get_by_id(uuid.UUID(validated_data.get("id"), version=4)),
            validated_data,
        )

        if item is not None:
            self.__items[validated_data.get("id")] = item

            return True

        return False

    # -----------------------------------------------------------------------------

    @orm.db_session
    def delete_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received connector message from exchange when entity was updated"""
        if routing_key != RoutingKey.CONNECTOR_ENTITY_DELETED:
            return False

        if data.get("id") in self.__items:
            del self.__items[data.get("id")]

            return True

        return False

    # -----------------------------------------------------------------------------

    def clear(self) -> None:
        """Clear items cache"""
        self.__items = None

    # -----------------------------------------------------------------------------

    @orm.db_session
    def initialize(self) -> None:
        """Initialize repository by fetching entities from database"""
        items: Dict[str, ConnectorItem] = {}

        for entity in ConnectorEntity.select():
            if self.__items is None or entity.connector_id.__str__() not in self.__items:
                item = self.__create_item(entity)

            else:
                item = self.__update_item(self.get_by_id(entity.connector_id), entity.to_dict())

            if item is not None:
                items[entity.connector_id.__str__()] = item

        self.__items = items

    # -----------------------------------------------------------------------------

    @staticmethod
    def __create_item(entity: ConnectorEntity) -> ConnectorItem or None:
        if isinstance(entity, FbBusConnectorEntity):
            return FbBusConnectorItem(
                connector_id=entity.connector_id,
                connector_name=entity.name,
                connector_key=entity.key,
                connector_enabled=entity.enabled,
                connector_type=entity.type,
                connector_control=entity.get_plain_controls(),
                connector_params=entity.params,
            )

        if isinstance(entity, FbMqttV1ConnectorEntity):
            return FbMqttV1ConnectorItem(
                connector_id=entity.connector_id,
                connector_name=entity.name,
                connector_key=entity.key,
                connector_enabled=entity.enabled,
                connector_type=entity.type,
                connector_control=entity.get_plain_controls(),
                connector_params=entity.params,
            )

        return None

    # -----------------------------------------------------------------------------

    @staticmethod
    def __update_item(item: ConnectorItem, data: Dict) -> ConnectorItem or None:
        if isinstance(item, FbBusConnectorItem):
            params: Dict = item.params
            params["address"] = data.get("address", item.address)
            params["serial_interface"] = data.get("serial_interface", item.serial_interface)
            params["baud_rate"] = data.get("baud_rate", item.baud_rate)

            return FbBusConnectorItem(
                connector_id=item.connector_id,
                connector_name=data.get("name", item.name),
                connector_key=item.key,
                connector_enabled=bool(data.get("enabled", item.enabled)),
                connector_type=item.type,
                connector_control=data.get("control", item.control),
                connector_params=params,
            )

        if isinstance(item, FbMqttV1ConnectorItem):
            params: Dict = item.params
            params["server"] = data.get("server", item.server)
            params["port"] = data.get("port", item.port)
            params["secured_port"] = data.get("secured_port", item.secured_port)
            params["username"] = data.get("username", item.username)

            return FbMqttV1ConnectorItem(
                connector_id=item.connector_id,
                connector_name=data.get("name", item.name),
                connector_key=item.key,
                connector_enabled=bool(data.get("enabled", item.enabled)),
                connector_type=item.type,
                connector_control=data.get("control", item.control),
                connector_params=params,
            )

        return None

    # -----------------------------------------------------------------------------

    def __iter__(self) -> "ConnectorsRepository":
        # Reset index for nex iteration
        self.__iterator_index = 0

        return self

    # -----------------------------------------------------------------------------

    def __len__(self):
        if self.__items is None:
            self.initialize()

        return len(self.__items.values())

    # -----------------------------------------------------------------------------

    def __next__(self) -> ConnectorItem:
        if self.__items is None:
            self.initialize()

        if self.__iterator_index < len(self.__items.values()):
            items: List[ConnectorItem] = list(self.__items.values())

            result: ConnectorItem = items[self.__iterator_index]

            self.__iterator_index += 1

            return result

        # Reset index for nex iteration
        self.__iterator_index = 0

        # End of iteration
        raise StopIteration


def validate_exchange_data(origin: ModuleOrigin, routing_key: RoutingKey, data: Dict) -> Dict:
    """
    Validate received RPC message against defined schema
    """
    try:
        schema: str = load_schema(origin, routing_key)

    except metadata_exceptions.FileNotFoundException as ex:
        raise HandleExchangeDataException("Provided data could not be validated") from ex

    except metadata_exceptions.InvalidArgumentException as ex:
        raise HandleExchangeDataException("Provided data could not be validated") from ex

    try:
        return validate(json.dumps(data), schema)

    except metadata_exceptions.MalformedInputException as ex:
        raise HandleExchangeDataException("Provided data are not in valid json format") from ex

    except metadata_exceptions.LogicException as ex:
        raise HandleExchangeDataException("Provided data could not be validated") from ex

    except metadata_exceptions.InvalidDataException as ex:
        raise HandleExchangeDataException("Provided data are not valid") from ex


connector_repository = ConnectorsRepository()
device_property_repository = DevicesPropertiesRepository()
channel_property_repository = ChannelsPropertiesRepository()
