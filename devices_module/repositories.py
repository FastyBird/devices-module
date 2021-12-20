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
Devices module repositories
"""

# Python base dependencies
import json
import uuid
from abc import abstractmethod
from typing import Dict, Generic, List, Optional, Set, Tuple, TypeVar, Union

# Library dependencies
import modules_metadata.exceptions as metadata_exceptions
from exchange_plugin.dispatcher import EventDispatcher
from fastnumbers import fast_float, fast_int
from kink import inject
from modules_metadata.loader import load_schema
from modules_metadata.routing import RoutingKey
from modules_metadata.types import DataType, ModuleOrigin
from modules_metadata.validator import validate
from pony.orm import core as orm
from whistle import Event

# Library libs
from devices_module.events import (
    ModelEntityCreatedEvent,
    ModelEntityDeletedEvent,
    ModelEntityUpdatedEvent,
    ModelItemCreatedEvent,
    ModelItemDeletedEvent,
    ModelItemUpdatedEvent,
)
from devices_module.exceptions import HandleExchangeDataException, InvalidStateException
from devices_module.items import (
    ChannelConfigurationItem,
    ChannelControlItem,
    ChannelItem,
    ChannelPropertyItem,
    ConnectorControlItem,
    ConnectorItem,
    DeviceConfigurationItem,
    DeviceControlItem,
    DeviceItem,
    DevicePropertyItem,
    FbBusConnectorItem,
    FbMqttV1ConnectorItem,
    ModbusConnectorItem,
    ShellyConnectorItem,
    SonoffConnectorItem,
    TuyaConnectorItem,
)
from devices_module.models import (
    ChannelConfigurationEntity,
    ChannelControlEntity,
    ChannelEntity,
    ChannelPropertyEntity,
    ConnectorControlEntity,
    ConnectorEntity,
    DeviceConfigurationEntity,
    DeviceControlEntity,
    DeviceEntity,
    DevicePropertyEntity,
    FbBusConnectorEntity,
    FbMqttConnectorEntity,
    ModbusConnectorEntity,
    ShellyConnectorEntity,
    SonoffConnectorEntity,
    TuyaConnectorEntity,
)

T = TypeVar("T")  # pylint: disable=invalid-name


def build_property_invalid_value(
    data_type: Optional[DataType],
    invalid_value: Optional[str],
) -> Union[str, int, float, bool, None]:
    """Transform serialized property invalid value into value representation used for data transforming"""
    if invalid_value is None:
        return None

    if data_type is not None:
        if data_type in (
            DataType.CHAR,
            DataType.UCHAR,
            DataType.SHORT,
            DataType.USHORT,
            DataType.INT,
            DataType.UINT,
        ):
            try:
                return fast_int(invalid_value, raise_on_invalid=True)

            except ValueError:
                return str(invalid_value)

        if data_type == DataType.FLOAT:
            try:
                return fast_float(invalid_value, raise_on_invalid=True)

            except ValueError:
                return str(invalid_value)

    return str(invalid_value)


def build_property_value_format(  # pylint: disable=too-many-branches,too-many-return-statements
    data_type: Optional[DataType],
    value_format: Optional[str],
) -> Union[Tuple[Optional[int], Optional[int]], Tuple[Optional[float], Optional[float]], Set[str], None]:
    """Transform serialized property value format into value representation used for data transforming"""
    if value_format is None:
        return None

    if data_type is not None:
        if data_type in (
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

        elif data_type == DataType.FLOAT:
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

        elif data_type == DataType.ENUM:
            return {x.strip() for x in value_format.split(",")}

    return None


@inject
class DevicesRepository:
    """
    Devices repository

    @package        FastyBird:DevicesModule!
    @module         repositories

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    __items: Optional[Dict[str, DeviceItem]] = None

    __iterator_index = 0

    __event_dispatcher: EventDispatcher

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        event_dispatcher: EventDispatcher,
    ) -> None:
        self.__event_dispatcher = event_dispatcher

        self.__event_dispatcher.add_listener(
            event_id=ModelEntityCreatedEvent.EVENT_NAME,
            listener=self.__entity_created,
        )

        self.__event_dispatcher.add_listener(
            event_id=ModelEntityUpdatedEvent.EVENT_NAME,
            listener=self.__entity_updated,
        )

        self.__event_dispatcher.add_listener(
            event_id=ModelEntityDeletedEvent.EVENT_NAME,
            listener=self.__entity_deleted,
        )

    # -----------------------------------------------------------------------------

    def get_by_id(self, device_id: uuid.UUID) -> Optional[DeviceItem]:
        """Find device in cache by provided identifier"""
        for record in self:
            if device_id.__eq__(record.device_id):
                return record

        return None

    # -----------------------------------------------------------------------------

    def get_by_key(self, device_key: str) -> Optional[DeviceItem]:
        """Find device in cache by provided key"""
        for record in self:
            if record.key == device_key:
                return record

        return None

    # -----------------------------------------------------------------------------

    def get_by_identifier(self, device_identifier: str) -> Optional[DeviceItem]:
        """Find device in cache by provided identifier"""
        for record in self:
            if record.identifier == device_identifier:
                return record

        return None

    # -----------------------------------------------------------------------------

    def get_all_by_parent(self, device_id: uuid.UUID) -> List[DeviceItem]:
        """Find all devices in cache for parent device identifier"""
        items: List[DeviceItem] = []

        for record in self:
            if record.parent is not None and record.parent.__eq__(device_id):
                items.append(record)

        return items

    # -----------------------------------------------------------------------------

    def get_all_by_connector(self, connector_id: uuid.UUID) -> List[DeviceItem]:
        """Find all devices in cache for connector identifier"""
        items: List[DeviceItem] = []

        for record in self:
            if record.connector_id is not None and connector_id.__eq__(record.connector_id):
                items.append(record)

        return items

    # -----------------------------------------------------------------------------

    def clear(self) -> None:
        """Clear items cache"""
        self.__items = None

    # -----------------------------------------------------------------------------

    def create_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received device message from exchange when entity was created"""
        if routing_key != RoutingKey.DEVICES_ENTITY_CREATED:
            return False

        result: bool = self.__handle_data_from_exchange(routing_key=routing_key, data=data)

        return result

    # -----------------------------------------------------------------------------

    def update_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received device message from exchange when entity was updated"""
        if routing_key != RoutingKey.DEVICES_ENTITY_UPDATED:
            return False

        result: bool = self.__handle_data_from_exchange(routing_key=routing_key, data=data)

        return result

    # -----------------------------------------------------------------------------

    @orm.db_session
    def delete_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received device message from exchange when entity was updated"""
        if routing_key != RoutingKey.DEVICES_ENTITY_DELETED:
            return False

        validated_data = validate_exchange_data(ModuleOrigin.DEVICES_MODULE, routing_key, data)

        if self.get_by_id(device_id=uuid.UUID(validated_data.get("id"), version=4)) is not None:
            del self[str(data.get("id"))]

            return True

        return False

    # -----------------------------------------------------------------------------

    @orm.db_session
    def initialize(self) -> None:
        """Initialize devices properties repository by fetching entities from database"""
        items: Dict[str, DeviceItem] = {}

        for entity in DeviceEntity.select():
            items[entity.device_id.__str__()] = self.__create_item(entity=entity)

        self.__items = items

    # -----------------------------------------------------------------------------

    @orm.db_session
    def __handle_data_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        validated_data = validate_exchange_data(ModuleOrigin.DEVICES_MODULE, routing_key, data)

        device_item = self.get_by_id(device_id=uuid.UUID(validated_data.get("id"), version=4))

        if device_item is None:
            entity: Optional[DeviceEntity] = DeviceEntity.get(device_id=uuid.UUID(validated_data.get("id"), version=4))

            if entity is not None:
                self[entity.device_id.__str__()] = self.__create_item(entity=entity)

                return True

            return False

        item = self.__update_item(item=device_item, data=validated_data)

        if item is not None:
            self[str(validated_data.get("id"))] = item

            return True

        return False

    # -----------------------------------------------------------------------------

    @staticmethod
    def __create_item(entity: DeviceEntity) -> DeviceItem:
        return DeviceItem(
            device_id=entity.device_id,
            device_identifier=entity.identifier,
            device_key=entity.key,
            device_name=entity.name,
            device_comment=entity.comment,
            device_enabled=entity.enabled,
            hardware_manufacturer=entity.hardware_manufacturer,
            hardware_model=entity.hardware_model,
            hardware_version=entity.hardware_version,
            hardware_mac_address=entity.hardware_mac_address,
            firmware_manufacturer=entity.firmware_manufacturer,
            firmware_version=entity.firmware_version,
            connector_id=entity.connector.connector_id if entity.connector is not None else None,
            parent_device=entity.parent.device_id if entity.parent is not None else None,
        )

    # -----------------------------------------------------------------------------

    @staticmethod
    def __update_item(item: DeviceItem, data: Dict) -> DeviceItem:
        return DeviceItem(
            device_id=item.device_id,
            device_identifier=item.identifier,
            device_key=item.key,
            device_name=data.get("name", item.name),
            device_comment=data.get("comment", item.comment),
            device_enabled=data.get("enabled", item.enabled),
            hardware_manufacturer=data.get("hardware_manufacturer", item.hardware_manufacturer),
            hardware_model=data.get("hardware_model", item.hardware_model),
            hardware_version=data.get("hardware_version", item.hardware_version),
            hardware_mac_address=data.get("hardware_mac_address", item.hardware_mac_address),
            firmware_manufacturer=data.get("firmware_manufacturer", item.firmware_manufacturer),
            firmware_version=data.get("firmware_version", item.firmware_version),
            connector_id=item.connector_id,
            parent_device=item.parent,
        )

    # -----------------------------------------------------------------------------

    def __entity_created(self, event: Event) -> None:
        if not isinstance(event, ModelEntityCreatedEvent) or not isinstance(event.entity, DeviceEntity):
            return

        self.initialize()

        device_item = self.get_by_id(device_id=event.entity.device_id)

        if device_item is not None:
            self.__event_dispatcher.dispatch(
                event_id=ModelItemCreatedEvent.EVENT_NAME, event=ModelItemCreatedEvent[DeviceItem](item=device_item)
            )

    # -----------------------------------------------------------------------------

    def __entity_updated(self, event: Event) -> None:
        if not isinstance(event, ModelEntityUpdatedEvent) or not isinstance(event.entity, DeviceEntity):
            return

        self.initialize()

        device_item = self.get_by_id(device_id=event.entity.device_id)

        if device_item is not None:
            self.__event_dispatcher.dispatch(
                event_id=ModelItemUpdatedEvent.EVENT_NAME, event=ModelItemUpdatedEvent[DeviceItem](item=device_item)
            )

    # -----------------------------------------------------------------------------

    def __entity_deleted(self, event: Event) -> None:
        if not isinstance(event, ModelEntityDeletedEvent) or not isinstance(event.entity, DeviceEntity):
            return

        device_item = self.get_by_id(device_id=event.entity.device_id)

        self.initialize()

        if device_item is not None:
            self.__event_dispatcher.dispatch(
                event_id=ModelItemDeletedEvent.EVENT_NAME,
                event=ModelItemDeletedEvent[DeviceItem](
                    item=device_item,
                ),
            )

    # -----------------------------------------------------------------------------

    def __setitem__(self, key: str, value: DeviceItem) -> None:
        if self.__items is None:
            self.initialize()

        if self.__items:
            self.__items[key] = value

    # -----------------------------------------------------------------------------

    def __getitem__(self, key: str) -> DeviceItem:
        if self.__items is None:
            self.initialize()

        if self.__items and key in self.__items:
            return self.__items[key]

        raise IndexError

    # -----------------------------------------------------------------------------

    def __delitem__(self, key: str) -> None:
        if self.__items and key in self.__items:
            del self.__items[key]

    # -----------------------------------------------------------------------------

    def __iter__(self) -> "DevicesRepository":
        # Reset index for nex iteration
        self.__iterator_index = 0

        return self

    # -----------------------------------------------------------------------------

    def __len__(self) -> int:
        if self.__items is None:
            self.initialize()

        return len(self.__items.values()) if isinstance(self.__items, dict) else 0

    # -----------------------------------------------------------------------------

    def __next__(self) -> DeviceItem:
        if self.__items is None:
            self.initialize()

        if self.__items and self.__iterator_index < len(self.__items.values()):
            items = list(self.__items.values()) if self.__items else []

            result = items[self.__iterator_index]

            self.__iterator_index += 1

            return result

        # Reset index for nex iteration
        self.__iterator_index = 0

        # End of iteration
        raise StopIteration


@inject
class ChannelsRepository:
    """
    Channels repository

    @package        FastyBird:DevicesModule!
    @module         repositories

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    __items: Optional[Dict[str, ChannelItem]] = None

    __iterator_index = 0

    __event_dispatcher: EventDispatcher

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        event_dispatcher: EventDispatcher,
    ) -> None:
        self.__event_dispatcher = event_dispatcher

        self.__event_dispatcher.add_listener(
            event_id=ModelEntityCreatedEvent.EVENT_NAME,
            listener=self.__entity_created,
        )

        self.__event_dispatcher.add_listener(
            event_id=ModelEntityUpdatedEvent.EVENT_NAME,
            listener=self.__entity_updated,
        )

        self.__event_dispatcher.add_listener(
            event_id=ModelEntityDeletedEvent.EVENT_NAME,
            listener=self.__entity_deleted,
        )

    # -----------------------------------------------------------------------------

    def get_by_id(self, channel_id: uuid.UUID) -> Optional[ChannelItem]:
        """Find channel in cache by provided identifier"""
        for record in self:
            if channel_id.__eq__(record.channel_id):
                return record

        return None

    # -----------------------------------------------------------------------------

    def get_by_key(self, channel_key: str) -> Optional[ChannelItem]:
        """Find channel in cache by provided key"""
        for record in self:
            if record.key == channel_key:
                return record

        return None

    # -----------------------------------------------------------------------------

    def get_by_identifier(self, device_id: uuid.UUID, channel_identifier: str) -> Optional[ChannelItem]:
        """Find channel in cache by provided identifier"""
        for record in self:
            if device_id.__eq__(record.device_id) and record.identifier == channel_identifier:
                return record

        return None

    # -----------------------------------------------------------------------------

    def get_all_by_device(self, device_id: uuid.UUID) -> List[ChannelItem]:
        """Find all channels in cache for device identifier"""
        items: List[ChannelItem] = []

        for record in self:
            if device_id.__eq__(record.device_id):
                items.append(record)

        return items

    # -----------------------------------------------------------------------------

    def clear(self) -> None:
        """Clear items cache"""
        self.__items = None

    # -----------------------------------------------------------------------------

    def create_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received channel message from exchange when entity was created"""
        if routing_key != RoutingKey.CHANNELS_ENTITY_CREATED:
            return False

        result: bool = self.__handle_data_from_exchange(routing_key=routing_key, data=data)

        return result

    # -----------------------------------------------------------------------------

    def update_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received channel message from exchange when entity was updated"""
        if routing_key != RoutingKey.CHANNELS_ENTITY_UPDATED:
            return False

        result: bool = self.__handle_data_from_exchange(routing_key=routing_key, data=data)

        return result

    # -----------------------------------------------------------------------------

    @orm.db_session
    def delete_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received channel message from exchange when entity was updated"""
        if routing_key != RoutingKey.CHANNELS_ENTITY_DELETED:
            return False

        validated_data = validate_exchange_data(ModuleOrigin.DEVICES_MODULE, routing_key, data)

        if self.get_by_id(channel_id=uuid.UUID(validated_data.get("id"), version=4)) is not None:
            del self[str(data.get("id"))]

            return True

        return False

    # -----------------------------------------------------------------------------

    @orm.db_session
    def initialize(self) -> None:
        """Initialize channels properties repository by fetching entities from database"""
        items: Dict[str, ChannelItem] = {}

        for entity in ChannelEntity.select():
            items[entity.channel_id.__str__()] = self.__create_item(entity=entity)

        self.__items = items

    # -----------------------------------------------------------------------------

    @orm.db_session
    def __handle_data_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received channel message from exchange when entity was updated"""
        validated_data = validate_exchange_data(ModuleOrigin.DEVICES_MODULE, routing_key, data)

        channel_item = self.get_by_id(channel_id=uuid.UUID(validated_data.get("id"), version=4))

        if channel_item is None:
            entity: Optional[ChannelEntity] = ChannelEntity.get(
                channel_id=uuid.UUID(validated_data.get("id"), version=4)
            )

            if entity is not None:
                self[entity.channel_id.__str__()] = self.__create_item(entity=entity)

                return True

            return False

        item = self.__update_item(item=channel_item, data=validated_data)

        if item is not None:
            self[str(validated_data.get("id"))] = item

            return True

        return False

    # -----------------------------------------------------------------------------

    @staticmethod
    def __create_item(entity: ChannelEntity) -> ChannelItem:
        return ChannelItem(
            channel_id=entity.channel_id,
            channel_identifier=entity.identifier,
            channel_key=entity.key,
            channel_name=entity.name,
            channel_comment=entity.comment,
            device_id=entity.device.device_id,
        )

    # -----------------------------------------------------------------------------

    @staticmethod
    def __update_item(item: ChannelItem, data: Dict) -> ChannelItem:
        return ChannelItem(
            channel_id=item.channel_id,
            channel_identifier=item.identifier,
            channel_key=item.key,
            channel_name=data.get("name", item.name),
            channel_comment=data.get("comment", item.comment),
            device_id=item.device_id,
        )

    # -----------------------------------------------------------------------------

    def __entity_created(self, event: Event) -> None:
        if not isinstance(event, ModelEntityCreatedEvent) or not isinstance(event.entity, ChannelEntity):
            return

        self.initialize()

        channel_item = self.get_by_id(channel_id=event.entity.channel_id)

        if channel_item is not None:
            self.__event_dispatcher.dispatch(
                event_id=ModelItemCreatedEvent.EVENT_NAME,
                event=ModelItemCreatedEvent[ChannelItem](
                    item=channel_item,
                ),
            )

    # -----------------------------------------------------------------------------

    def __entity_updated(self, event: Event) -> None:
        if not isinstance(event, ModelEntityUpdatedEvent) or not isinstance(event.entity, ChannelEntity):
            return

        self.initialize()

        channel_item = self.get_by_id(channel_id=event.entity.channel_id)

        if channel_item is not None:
            self.__event_dispatcher.dispatch(
                event_id=ModelItemUpdatedEvent.EVENT_NAME,
                event=ModelItemUpdatedEvent[ChannelItem](
                    item=channel_item,
                ),
            )

    # -----------------------------------------------------------------------------

    def __entity_deleted(self, event: Event) -> None:
        if not isinstance(event, ModelEntityDeletedEvent) or not isinstance(event.entity, ChannelEntity):
            return

        channel_item = self.get_by_id(channel_id=event.entity.channel_id)

        self.initialize()

        if channel_item is not None:
            self.__event_dispatcher.dispatch(
                event_id=ModelItemDeletedEvent.EVENT_NAME,
                event=ModelItemDeletedEvent[ChannelItem](
                    item=channel_item,
                ),
            )

    # -----------------------------------------------------------------------------

    def __setitem__(self, key: str, value: ChannelItem) -> None:
        if self.__items is None:
            self.initialize()

        if self.__items:
            self.__items[key] = value

    # -----------------------------------------------------------------------------

    def __getitem__(self, key: str) -> ChannelItem:
        if self.__items is None:
            self.initialize()

        if self.__items and key in self.__items:
            return self.__items[key]

        raise IndexError

    # -----------------------------------------------------------------------------

    def __delitem__(self, key: str) -> None:
        if self.__items and key in self.__items:
            del self.__items[key]

    # -----------------------------------------------------------------------------

    def __iter__(self) -> "ChannelsRepository":
        # Reset index for nex iteration
        self.__iterator_index = 0

        return self

    # -----------------------------------------------------------------------------

    def __len__(self) -> int:
        if self.__items is None:
            self.initialize()

        return len(self.__items.values()) if isinstance(self.__items, dict) else 0

    # -----------------------------------------------------------------------------

    def __next__(self) -> ChannelItem:
        if self.__items is None:
            self.initialize()

        if self.__items and self.__iterator_index < len(self.__items.values()):
            items = list(self.__items.values()) if self.__items else []

            result = items[self.__iterator_index]

            self.__iterator_index += 1

            return result

        # Reset index for nex iteration
        self.__iterator_index = 0

        # End of iteration
        raise StopIteration


@inject
class PropertiesRepository(Generic[T]):
    """
    Base properties repository

    @package        FastyBird:DevicesModule!
    @module         repositories

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    _items: Optional[Dict[str, T]] = None

    __iterator_index = 0

    _event_dispatcher: EventDispatcher

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        event_dispatcher: EventDispatcher,
    ) -> None:
        self.__event_dispatcher = event_dispatcher

        self.__event_dispatcher.add_listener(
            event_id=ModelEntityCreatedEvent.EVENT_NAME,
            listener=self._entity_created,
        )

        self.__event_dispatcher.add_listener(
            event_id=ModelEntityUpdatedEvent.EVENT_NAME,
            listener=self._entity_updated,
        )

        self.__event_dispatcher.add_listener(
            event_id=ModelEntityDeletedEvent.EVENT_NAME,
            listener=self._entity_deleted,
        )

    # -----------------------------------------------------------------------------

    def get_by_id(self, property_id: uuid.UUID) -> Union[T, None]:
        """Find property in cache by provided identifier"""
        for record in self:
            if property_id.__eq__(record.property_id):
                return record  # type: ignore[no-any-return]

        return None

    # -----------------------------------------------------------------------------

    def get_by_key(self, property_key: str) -> Union[T, None]:
        """Find property in cache by provided key"""
        for record in self:
            if record.key == property_key:
                return record  # type: ignore[no-any-return]

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

    def _entity_created(self, event: Event) -> None:
        if not isinstance(event, ModelEntityCreatedEvent) or not isinstance(
            event.entity, (DevicePropertyEntity, ChannelPropertyEntity)
        ):
            return

        self.initialize()

        property_item = self.get_by_id(property_id=event.entity.property_id)

        if property_item is not None:
            self.__event_dispatcher.dispatch(
                event_id=ModelItemCreatedEvent.EVENT_NAME,
                event=ModelItemCreatedEvent[T](
                    item=property_item,
                ),
            )

    # -----------------------------------------------------------------------------

    def _entity_updated(self, event: Event) -> None:
        if not isinstance(event, ModelEntityUpdatedEvent) or not isinstance(
            event.entity, (DevicePropertyEntity, ChannelPropertyEntity)
        ):
            return

        self.initialize()

        property_item = self.get_by_id(property_id=event.entity.property_id)

        if property_item is not None:
            self.__event_dispatcher.dispatch(
                event_id=ModelItemUpdatedEvent.EVENT_NAME,
                event=ModelItemUpdatedEvent[T](
                    item=property_item,
                ),
            )

    # -----------------------------------------------------------------------------

    def _entity_deleted(self, event: Event) -> None:
        if not isinstance(event, ModelEntityDeletedEvent) or not isinstance(
            event.entity, (DevicePropertyEntity, ChannelPropertyEntity)
        ):
            return

        property_item = self.get_by_id(property_id=event.entity.property_id)

        self.initialize()

        if property_item is not None:
            self.__event_dispatcher.dispatch(
                event_id=ModelItemDeletedEvent.EVENT_NAME,
                event=ModelItemDeletedEvent[T](
                    item=property_item,
                ),
            )

    # -----------------------------------------------------------------------------

    def __setitem__(self, key: str, value: T) -> None:
        if self._items is None:
            self.initialize()

        if self._items:
            self._items[key] = value

    # -----------------------------------------------------------------------------

    def __getitem__(self, key: str) -> T:
        if self._items is None:
            self.initialize()

        if self._items and key in self._items:
            return self._items[key]

        raise IndexError

    # -----------------------------------------------------------------------------

    def __delitem__(self, key: str) -> None:
        if self._items and key in self._items:
            del self._items[key]

    # -----------------------------------------------------------------------------

    def __iter__(self) -> "PropertiesRepository":
        # Reset index for nex iteration
        self.__iterator_index = 0

        return self

    # -----------------------------------------------------------------------------

    def __len__(self) -> int:
        if self._items is None:
            self.initialize()

        return len(self._items.values()) if isinstance(self._items, dict) else 0

    # -----------------------------------------------------------------------------

    def __next__(self) -> T:
        if self._items is None:
            self.initialize()

        if self._items and self.__iterator_index < len(self._items.values()):
            items = list(self._items.values()) if self._items else []

            result = items[self.__iterator_index]

            self.__iterator_index += 1

            return result

        # Reset index for nex iteration
        self.__iterator_index = 0

        # End of iteration
        raise StopIteration


class DevicesPropertiesRepository(PropertiesRepository[DevicePropertyItem]):
    """
    Devices properties repository

    @package        FastyBird:DevicesModule!
    @module         repositories

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    def get_by_identifier(self, device_id: uuid.UUID, property_identifier: str) -> Optional[DevicePropertyItem]:
        """Find property in cache by provided identifier"""
        for record in self:
            if (
                isinstance(record, DevicePropertyItem)
                and device_id.__eq__(record.device_id)
                and record.identifier == property_identifier
            ):
                return record

        return None

    # -----------------------------------------------------------------------------

    def get_all_by_device(self, device_id: uuid.UUID) -> List[DevicePropertyItem]:
        """Find all devices properties in cache for device identifier"""
        items: List[DevicePropertyItem] = []

        for record in self:
            if isinstance(record, DevicePropertyItem) and device_id.__eq__(record.device_id):
                items.append(record)

        return items

    # -----------------------------------------------------------------------------

    def create_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received device property message from exchange when entity was created"""
        if routing_key != RoutingKey.DEVICES_PROPERTY_ENTITY_CREATED:
            return False

        result: bool = self.__handle_data_from_exchange(routing_key=routing_key, data=data)

        return result

    # -----------------------------------------------------------------------------

    def update_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received device property message from exchange when entity was updated"""
        if routing_key != RoutingKey.DEVICES_PROPERTY_ENTITY_UPDATED:
            return False

        result: bool = self.__handle_data_from_exchange(routing_key=routing_key, data=data)

        return result

    # -----------------------------------------------------------------------------

    @orm.db_session
    def delete_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received device property message from exchange when entity was updated"""
        if routing_key != RoutingKey.DEVICES_PROPERTY_ENTITY_DELETED:
            return False

        validated_data = validate_exchange_data(ModuleOrigin.DEVICES_MODULE, routing_key, data)

        if self.get_by_id(property_id=uuid.UUID(validated_data.get("id"), version=4)) is not None:
            del self[str(data.get("id"))]

            return True

        return False

    # -----------------------------------------------------------------------------

    @orm.db_session
    def initialize(self) -> None:
        """Initialize devices properties repository by fetching entities from database"""
        items: Dict[str, DevicePropertyItem] = {}

        for entity in DevicePropertyEntity.select():
            items[entity.property_id.__str__()] = self.__create_item(entity=entity)

        self._items = items

    # -----------------------------------------------------------------------------

    @orm.db_session
    def __handle_data_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        validated_data = validate_exchange_data(ModuleOrigin.DEVICES_MODULE, routing_key, data)

        property_item = self.get_by_id(property_id=uuid.UUID(validated_data.get("id"), version=4))

        if property_item is None:
            entity: Optional[DevicePropertyEntity] = DevicePropertyEntity.get(
                property_id=uuid.UUID(validated_data.get("id"), version=4)
            )

            if entity is not None:
                self[entity.property_id.__str__()] = self.__create_item(entity=entity)

                return True

            return False

        item = self.__update_item(item=property_item, data=validated_data)

        if item is not None:
            self[str(validated_data.get("id"))] = item

            return True

        return False

    # -----------------------------------------------------------------------------

    @staticmethod
    def __create_item(
        entity: DevicePropertyEntity,
    ) -> DevicePropertyItem:
        return DevicePropertyItem(
            property_id=entity.property_id,
            property_name=entity.name,
            property_identifier=entity.identifier,
            property_key=entity.key,
            property_settable=entity.settable,
            property_queryable=entity.queryable,
            property_data_type=entity.data_type_formatted,
            property_format=build_property_value_format(
                data_type=entity.data_type_formatted, value_format=entity.format
            ),
            property_invalid=build_property_invalid_value(
                data_type=entity.data_type_formatted, invalid_value=entity.invalid
            ),
            property_unit=entity.unit,
            device_id=entity.device.device_id,
        )

    # -----------------------------------------------------------------------------

    @staticmethod
    def __update_item(
        item: DevicePropertyItem,
        data: Dict,
    ) -> DevicePropertyItem:
        data_type = data.get("data_type", None)
        data_type = DataType(data_type) if data_type is not None and DataType.has_value(data_type) else None

        return DevicePropertyItem(
            property_id=item.property_id,
            property_name=data.get("name", item.name),
            property_identifier=item.identifier,
            property_key=item.key,
            property_settable=data.get("settable", item.settable),
            property_queryable=data.get("queryable", item.queryable),
            property_data_type=data_type,
            property_format=build_property_value_format(data_type=data_type, value_format=data.get("format", None)),
            property_invalid=build_property_invalid_value(data_type=data_type, invalid_value=data.get("invalid", None)),
            property_unit=data.get("unit", item.unit),
            device_id=item.device_id,
        )


class ChannelsPropertiesRepository(PropertiesRepository[ChannelPropertyItem]):
    """
    Channel property repository

    @package        FastyBird:DevicesModule!
    @module         repositories

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    def get_by_identifier(self, channel_id: uuid.UUID, property_identifier: str) -> Optional[ChannelPropertyItem]:
        """Find property in cache by provided identifier"""
        for record in self:
            if (
                isinstance(record, ChannelPropertyItem)
                and channel_id.__eq__(record.channel_id)
                and record.identifier == property_identifier
            ):
                return record

        return None

    # -----------------------------------------------------------------------------

    def get_all_by_channel(self, channel_id: uuid.UUID) -> List[ChannelPropertyItem]:
        """Find all channels properties in cache for channel identifier"""
        items: List[ChannelPropertyItem] = []

        for record in self:
            if isinstance(record, ChannelPropertyItem) and channel_id.__eq__(record.channel_id):
                items.append(record)

        return items

    # -----------------------------------------------------------------------------

    def create_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received channel property message from exchange when entity was created"""
        if routing_key != RoutingKey.CHANNELS_PROPERTY_ENTITY_CREATED:
            return False

        result: bool = self.__handle_data_from_exchange(routing_key=routing_key, data=data)

        return result

    # -----------------------------------------------------------------------------

    def update_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received channel property message from exchange when entity was updated"""
        if routing_key != RoutingKey.CHANNELS_PROPERTY_ENTITY_UPDATED:
            return False

        result: bool = self.__handle_data_from_exchange(routing_key=routing_key, data=data)

        return result

    # -----------------------------------------------------------------------------

    @orm.db_session
    def delete_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received channel property message from exchange when entity was updated"""
        if routing_key != RoutingKey.CHANNELS_PROPERTY_ENTITY_DELETED:
            return False

        validated_data = validate_exchange_data(ModuleOrigin.DEVICES_MODULE, routing_key, data)

        if self.get_by_id(property_id=uuid.UUID(validated_data.get("id"), version=4)) is not None:
            del self[str(data.get("id"))]

            return True

        return False

    # -----------------------------------------------------------------------------

    @orm.db_session
    def initialize(self) -> None:
        """Initialize channel properties repository by fetching entities from database"""
        items: Dict[str, ChannelPropertyItem] = {}

        for entity in ChannelPropertyEntity.select():
            items[entity.property_id.__str__()] = self.__create_item(entity=entity)

        self._items = items

    # -----------------------------------------------------------------------------

    @orm.db_session
    def __handle_data_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        validated_data = validate_exchange_data(ModuleOrigin.DEVICES_MODULE, routing_key, data)

        property_item = self.get_by_id(property_id=uuid.UUID(validated_data.get("id"), version=4))

        if property_item is None:
            entity: Optional[ChannelPropertyEntity] = DevicePropertyEntity.get(
                property_id=uuid.UUID(validated_data.get("id"), version=4)
            )

            if entity is not None:
                self[entity.property_id.__str__()] = self.__create_item(entity=entity)

                return True

            return False

        item = self.__update_item(item=property_item, data=validated_data)

        if item is not None:
            self[str(validated_data.get("id"))] = item

            return True

        return False

    # -----------------------------------------------------------------------------

    @staticmethod
    def __create_item(
        entity: ChannelPropertyEntity,
    ) -> ChannelPropertyItem:
        return ChannelPropertyItem(
            property_id=entity.property_id,
            property_name=entity.name,
            property_identifier=entity.identifier,
            property_key=entity.key,
            property_settable=entity.settable,
            property_queryable=entity.queryable,
            property_data_type=entity.data_type_formatted,
            property_format=build_property_value_format(
                data_type=entity.data_type_formatted, value_format=entity.format
            ),
            property_invalid=build_property_invalid_value(
                data_type=entity.data_type_formatted, invalid_value=entity.invalid
            ),
            property_unit=entity.unit,
            device_id=entity.channel.device.device_id,
            channel_id=entity.channel.channel_id,
        )

    # -----------------------------------------------------------------------------

    @staticmethod
    def __update_item(
        item: ChannelPropertyItem,
        data: Dict,
    ) -> ChannelPropertyItem:
        data_type = data.get("data_type", None)
        data_type = DataType(data_type) if data_type is not None and DataType.has_value(data_type) else None

        return ChannelPropertyItem(
            property_id=item.property_id,
            property_name=data.get("name", item.name),
            property_identifier=item.identifier,
            property_key=item.key,
            property_settable=data.get("settable", item.settable),
            property_queryable=data.get("queryable", item.queryable),
            property_data_type=data_type,
            property_format=build_property_value_format(data_type=data_type, value_format=data.get("format", None)),
            property_invalid=build_property_invalid_value(data_type=data_type, invalid_value=data.get("invalid", None)),
            property_unit=data.get("unit", item.unit),
            device_id=item.device_id,
            channel_id=item.channel_id,
        )


@inject
class ConnectorsRepository:
    """
    Connectors repository

    @package        FastyBird:DevicesModule!
    @module         repositories

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    __items: Optional[
        Dict[
            str,
            Union[
                FbBusConnectorItem,
                FbMqttV1ConnectorItem,
                ShellyConnectorItem,
                TuyaConnectorItem,
                SonoffConnectorItem,
                ModbusConnectorItem,
            ],
        ]
    ] = None

    __iterator_index = 0

    __event_dispatcher: EventDispatcher

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        event_dispatcher: EventDispatcher,
    ) -> None:
        self.__event_dispatcher = event_dispatcher

        self.__event_dispatcher.add_listener(
            event_id=ModelEntityCreatedEvent.EVENT_NAME,
            listener=self.__entity_created,
        )

        self.__event_dispatcher.add_listener(
            event_id=ModelEntityUpdatedEvent.EVENT_NAME,
            listener=self.__entity_updated,
        )

        self.__event_dispatcher.add_listener(
            event_id=ModelEntityDeletedEvent.EVENT_NAME,
            listener=self.__entity_deleted,
        )

    # -----------------------------------------------------------------------------

    def get_by_id(
        self, connector_id: uuid.UUID
    ) -> Union[
        FbBusConnectorItem,
        FbMqttV1ConnectorItem,
        ShellyConnectorItem,
        TuyaConnectorItem,
        SonoffConnectorItem,
        ModbusConnectorItem,
        None,
    ]:
        """Find connector in cache by provided identifier"""
        for record in self:
            if connector_id.__eq__(record.connector_id):
                return record

        return None

    # -----------------------------------------------------------------------------

    def get_by_key(
        self, connector_key: str
    ) -> Union[
        FbBusConnectorItem,
        FbMqttV1ConnectorItem,
        ShellyConnectorItem,
        TuyaConnectorItem,
        SonoffConnectorItem,
        ModbusConnectorItem,
        None,
    ]:
        """Find connector in cache by provided key"""
        for record in self:
            if record.key == connector_key:
                return record

        return None

    # -----------------------------------------------------------------------------

    def create_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received connector message from exchange when entity was created"""
        if routing_key != RoutingKey.CONNECTORS_ENTITY_CREATED:
            return False

        result: bool = self.__handle_data_from_exchange(routing_key=routing_key, data=data)

        return result

    # -----------------------------------------------------------------------------

    def update_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received connector message from exchange when entity was updated"""
        if routing_key != RoutingKey.CONNECTORS_ENTITY_UPDATED:
            return False

        result: bool = self.__handle_data_from_exchange(routing_key=routing_key, data=data)

        return result

    # -----------------------------------------------------------------------------

    @orm.db_session
    def delete_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received connector message from exchange when entity was updated"""
        if routing_key != RoutingKey.CONNECTORS_ENTITY_DELETED:
            return False

        validated_data = validate_exchange_data(ModuleOrigin.DEVICES_MODULE, routing_key, data)

        if self.get_by_id(connector_id=uuid.UUID(validated_data.get("id"), version=4)) is not None:
            del self[str(data.get("id"))]

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
        items: Dict[
            str,
            Union[
                FbBusConnectorItem,
                FbMqttV1ConnectorItem,
                ShellyConnectorItem,
                TuyaConnectorItem,
                SonoffConnectorItem,
                ModbusConnectorItem,
            ],
        ] = {}

        for entity in ConnectorEntity.select():
            items[entity.connector_id.__str__()] = self.__create_item(entity=entity)

        self.__items = items

    # -----------------------------------------------------------------------------

    @orm.db_session
    def __handle_data_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        validated_data = validate_exchange_data(ModuleOrigin.DEVICES_MODULE, routing_key, data)

        connector_item = self.get_by_id(connector_id=uuid.UUID(validated_data.get("id"), version=4))

        if connector_item is None:
            entity: Optional[ConnectorEntity] = ConnectorEntity.get(
                connector_id=uuid.UUID(validated_data.get("id"), version=4)
            )

            if entity is not None:
                self[entity.connector_id.__str__()] = self.__create_item(entity=entity)

                return True

            return False

        item = self.__update_item(item=connector_item, data=validated_data)

        if item is not None:
            self[str(validated_data.get("id"))] = item

            return True

        return False

    # -----------------------------------------------------------------------------

    def __entity_created(self, event: Event) -> None:
        if not isinstance(event, ModelEntityCreatedEvent) or not isinstance(event.entity, ConnectorEntity):
            return

        self.initialize()

        connector_item = self.get_by_id(connector_id=event.entity.connector_id)

        if connector_item is not None:
            self.__event_dispatcher.dispatch(
                event_id=ModelItemCreatedEvent.EVENT_NAME,
                event=ModelItemCreatedEvent[ConnectorItem](
                    item=connector_item,
                ),
            )

    # -----------------------------------------------------------------------------

    def __entity_updated(self, event: Event) -> None:
        if not isinstance(event, ModelEntityUpdatedEvent) or not isinstance(event.entity, ConnectorEntity):
            return

        self.initialize()

        connector_item = self.get_by_id(connector_id=event.entity.connector_id)

        if connector_item is not None:
            self.__event_dispatcher.dispatch(
                event_id=ModelItemUpdatedEvent.EVENT_NAME,
                event=ModelItemUpdatedEvent[ConnectorItem](
                    item=connector_item,
                ),
            )

    # -----------------------------------------------------------------------------

    def __entity_deleted(self, event: Event) -> None:
        if not isinstance(event, ModelEntityDeletedEvent) or not isinstance(event.entity, ConnectorEntity):
            return

        connector_item = self.get_by_id(connector_id=event.entity.connector_id)

        self.initialize()

        if connector_item is not None:
            self.__event_dispatcher.dispatch(
                event_id=ModelItemDeletedEvent.EVENT_NAME,
                event=ModelItemDeletedEvent[ConnectorItem](
                    item=connector_item,
                ),
            )

    # -----------------------------------------------------------------------------

    @staticmethod
    def __create_item(
        entity: ConnectorEntity,
    ) -> Union[
        FbBusConnectorItem,
        FbMqttV1ConnectorItem,
        ShellyConnectorItem,
        TuyaConnectorItem,
        SonoffConnectorItem,
        ModbusConnectorItem,
    ]:
        if isinstance(entity, FbBusConnectorEntity):
            return FbBusConnectorItem(
                connector_id=entity.connector_id,
                connector_name=entity.name,
                connector_key=entity.key,
                connector_enabled=entity.enabled,
                connector_type=entity.type,
                connector_params=entity.params,
            )

        if isinstance(entity, FbMqttConnectorEntity):
            return FbMqttV1ConnectorItem(
                connector_id=entity.connector_id,
                connector_name=entity.name,
                connector_key=entity.key,
                connector_enabled=entity.enabled,
                connector_type=entity.type,
                connector_params=entity.params,
            )

        if isinstance(entity, ShellyConnectorEntity):
            return ShellyConnectorItem(
                connector_id=entity.connector_id,
                connector_name=entity.name,
                connector_key=entity.key,
                connector_enabled=entity.enabled,
                connector_type=entity.type,
                connector_params=entity.params,
            )

        if isinstance(entity, TuyaConnectorEntity):
            return TuyaConnectorItem(
                connector_id=entity.connector_id,
                connector_name=entity.name,
                connector_key=entity.key,
                connector_enabled=entity.enabled,
                connector_type=entity.type,
                connector_params=entity.params,
            )

        if isinstance(entity, SonoffConnectorEntity):
            return SonoffConnectorItem(
                connector_id=entity.connector_id,
                connector_name=entity.name,
                connector_key=entity.key,
                connector_enabled=entity.enabled,
                connector_type=entity.type,
                connector_params=entity.params,
            )

        if isinstance(entity, ModbusConnectorEntity):
            return ModbusConnectorItem(
                connector_id=entity.connector_id,
                connector_name=entity.name,
                connector_key=entity.key,
                connector_enabled=entity.enabled,
                connector_type=entity.type,
                connector_params=entity.params,
            )

        raise InvalidStateException("Unsupported entity type provided")

    # -----------------------------------------------------------------------------

    @staticmethod
    def __update_item(  # pylint: disable=too-many-return-statements
        item: ConnectorItem, data: Dict
    ) -> Union[
        FbBusConnectorItem,
        FbMqttV1ConnectorItem,
        ShellyConnectorItem,
        TuyaConnectorItem,
        SonoffConnectorItem,
        ModbusConnectorItem,
        None,
    ]:
        if isinstance(item, FbBusConnectorItem):
            params = item.params
            params["address"] = str(data.get("address", item.address))
            params["serial_interface"] = str(data.get("serial_interface", item.serial_interface))
            params["baud_rate"] = int(str(data.get("baud_rate", item.baud_rate)))

            return FbBusConnectorItem(
                connector_id=item.connector_id,
                connector_name=data.get("name", item.name),
                connector_key=item.key,
                connector_enabled=bool(data.get("enabled", item.enabled)),
                connector_type=item.type,
                connector_params=params,
            )

        if isinstance(item, FbMqttV1ConnectorItem):
            params = item.params
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
                connector_params=params,
            )

        if isinstance(item, ShellyConnectorItem):
            params = item.params

            return ShellyConnectorItem(
                connector_id=item.connector_id,
                connector_name=data.get("name", item.name),
                connector_key=item.key,
                connector_enabled=bool(data.get("enabled", item.enabled)),
                connector_type=item.type,
                connector_params=params,
            )

        if isinstance(item, TuyaConnectorItem):
            params = item.params

            return TuyaConnectorItem(
                connector_id=item.connector_id,
                connector_name=data.get("name", item.name),
                connector_key=item.key,
                connector_enabled=bool(data.get("enabled", item.enabled)),
                connector_type=item.type,
                connector_params=params,
            )

        if isinstance(item, SonoffConnectorItem):
            params = item.params

            return SonoffConnectorItem(
                connector_id=item.connector_id,
                connector_name=data.get("name", item.name),
                connector_key=item.key,
                connector_enabled=bool(data.get("enabled", item.enabled)),
                connector_type=item.type,
                connector_params=params,
            )

        if isinstance(item, ModbusConnectorItem):
            params = item.params

            return ModbusConnectorItem(
                connector_id=item.connector_id,
                connector_name=data.get("name", item.name),
                connector_key=item.key,
                connector_enabled=bool(data.get("enabled", item.enabled)),
                connector_type=item.type,
                connector_params=params,
            )

        return None

    # -----------------------------------------------------------------------------

    def __setitem__(
        self,
        key: str,
        value: Union[
            FbBusConnectorItem,
            FbMqttV1ConnectorItem,
            ShellyConnectorItem,
            TuyaConnectorItem,
            SonoffConnectorItem,
            ModbusConnectorItem,
        ],
    ) -> None:
        if self.__items is None:
            self.initialize()

        if self.__items:
            self.__items[key] = value

    # -----------------------------------------------------------------------------

    def __getitem__(
        self, key: str
    ) -> Union[
        FbBusConnectorItem,
        FbMqttV1ConnectorItem,
        ShellyConnectorItem,
        TuyaConnectorItem,
        SonoffConnectorItem,
        ModbusConnectorItem,
    ]:
        if self.__items is None:
            self.initialize()

        if self.__items and key in self.__items:
            return self.__items[key]

        raise IndexError

    # -----------------------------------------------------------------------------

    def __delitem__(self, key: str) -> None:
        if self.__items and key in self.__items:
            del self.__items[key]

    # -----------------------------------------------------------------------------

    def __iter__(self) -> "ConnectorsRepository":
        # Reset index for nex iteration
        self.__iterator_index = 0

        return self

    # -----------------------------------------------------------------------------

    def __len__(self) -> int:
        if self.__items is None:
            self.initialize()

        return len(self.__items.values()) if isinstance(self.__items, dict) else 0

    # -----------------------------------------------------------------------------

    def __next__(
        self,
    ) -> Union[
        FbBusConnectorItem,
        FbMqttV1ConnectorItem,
        ShellyConnectorItem,
        TuyaConnectorItem,
        SonoffConnectorItem,
        ModbusConnectorItem,
    ]:
        if self.__items is None:
            self.initialize()

        if self.__items and self.__iterator_index < len(self.__items.values()):
            items = list(self.__items.values()) if self.__items else []

            result = items[self.__iterator_index]

            self.__iterator_index += 1

            return result

        # Reset index for nex iteration
        self.__iterator_index = 0

        # End of iteration
        raise StopIteration


@inject
class ControlsRepository(Generic[T]):
    """
    Base controls repository

    @package        FastyBird:DevicesModule!
    @module         repositories

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    _items: Optional[Dict[str, T]] = None

    __iterator_index = 0

    _event_dispatcher: EventDispatcher

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        event_dispatcher: EventDispatcher,
    ) -> None:
        self._event_dispatcher = event_dispatcher

        self._event_dispatcher.add_listener(
            event_id=ModelEntityCreatedEvent.EVENT_NAME,
            listener=self._entity_created,
        )

        self._event_dispatcher.add_listener(
            event_id=ModelEntityUpdatedEvent.EVENT_NAME,
            listener=self._entity_updated,
        )

        self._event_dispatcher.add_listener(
            event_id=ModelEntityDeletedEvent.EVENT_NAME,
            listener=self._entity_deleted,
        )

    # -----------------------------------------------------------------------------

    def get_by_id(
        self,
        control_id: uuid.UUID,
    ) -> Union[T, None]:
        """Find control in cache by provided identifier"""
        for record in self:
            if control_id.__eq__(record.control_id):
                return record  # type: ignore[no-any-return]

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

    def _entity_created(self, event: Event) -> None:
        if not isinstance(event, ModelEntityCreatedEvent) or not isinstance(
            event.entity, (DevicesControlsRepository, ChannelControlEntity, ConnectorControlEntity)
        ):
            return

        self.initialize()

        control_item = self.get_by_id(control_id=event.entity.control_id)

        if control_item is not None:
            self._event_dispatcher.dispatch(
                event_id=ModelItemCreatedEvent.EVENT_NAME,
                event=ModelItemCreatedEvent[T](
                    item=control_item,
                ),
            )

    # -----------------------------------------------------------------------------

    def _entity_updated(self, event: Event) -> None:
        if not isinstance(event, ModelEntityUpdatedEvent) or not isinstance(
            event.entity, (DevicesControlsRepository, ChannelControlEntity, ConnectorControlEntity)
        ):
            return

        self.initialize()

        control_item = self.get_by_id(control_id=event.entity.control_id)

        if control_item is not None:
            self._event_dispatcher.dispatch(
                event_id=ModelItemUpdatedEvent.EVENT_NAME,
                event=ModelItemUpdatedEvent[T](
                    item=control_item,
                ),
            )

    # -----------------------------------------------------------------------------

    def _entity_deleted(self, event: Event) -> None:
        if not isinstance(event, ModelEntityDeletedEvent) or not isinstance(
            event.entity, (DevicesControlsRepository, ChannelControlEntity, ConnectorControlEntity)
        ):
            return

        control_item = self.get_by_id(control_id=event.entity.control_id)

        self.initialize()

        if control_item is not None:
            self._event_dispatcher.dispatch(
                event_id=ModelItemDeletedEvent.EVENT_NAME,
                event=ModelItemDeletedEvent[T](
                    item=control_item,
                ),
            )

    # -----------------------------------------------------------------------------

    def __setitem__(self, key: str, value: T) -> None:
        if self._items is None:
            self.initialize()

        if self._items:
            self._items[key] = value

    # -----------------------------------------------------------------------------

    def __getitem__(self, key: str) -> T:
        if self._items is None:
            self.initialize()

        if self._items and key in self._items:
            return self._items[key]

        raise IndexError

    # -----------------------------------------------------------------------------

    def __delitem__(self, key: str) -> None:
        if self._items and key in self._items:
            del self._items[key]

    # -----------------------------------------------------------------------------

    def __iter__(self) -> "ControlsRepository":
        # Reset index for nex iteration
        self.__iterator_index = 0

        return self

    # -----------------------------------------------------------------------------

    def __len__(self) -> int:
        if self._items is None:
            self.initialize()

        return len(self._items.values()) if isinstance(self._items, dict) else 0

    # -----------------------------------------------------------------------------

    def __next__(self) -> T:
        if self._items is None:
            self.initialize()

        if self._items and self.__iterator_index < len(self._items.values()):
            items = list(self._items.values()) if self._items else []

            result = items[self.__iterator_index]

            self.__iterator_index += 1

            return result

        # Reset index for nex iteration
        self.__iterator_index = 0

        # End of iteration
        raise StopIteration


class DevicesControlsRepository(ControlsRepository[DeviceControlItem]):
    """
    Devices controls repository

    @package        FastyBird:DevicesModule!
    @module         repositories

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    def get_by_name(self, device_id: uuid.UUID, control_name: str) -> Optional[DeviceControlItem]:
        """Find control in cache by provided name"""
        for record in self:
            if (
                isinstance(record, DeviceControlItem)
                and device_id.__eq__(record.device_id)
                and record.name == control_name
            ):
                return record

        return None

    # -----------------------------------------------------------------------------

    def get_all_by_device(self, device_id: uuid.UUID) -> List[DeviceControlItem]:
        """Find all devices controls in cache for device identifier"""
        items: List[DeviceControlItem] = []

        for record in self:
            if isinstance(record, DeviceControlItem) and device_id.__eq__(record.device_id):
                items.append(record)

        return items

    # -----------------------------------------------------------------------------

    def create_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received device control message from exchange when entity was created"""
        if routing_key != RoutingKey.DEVICES_CONTROL_ENTITY_CREATED:
            return False

        result: bool = self.__handle_data_from_exchange(routing_key=routing_key, data=data)

        return result

    # -----------------------------------------------------------------------------

    def update_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received device control message from exchange when entity was updated"""
        if routing_key != RoutingKey.DEVICES_CONTROL_ENTITY_UPDATED:
            return False

        result: bool = self.__handle_data_from_exchange(routing_key=routing_key, data=data)

        return result

    # -----------------------------------------------------------------------------

    @orm.db_session
    def delete_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received device control message from exchange when entity was updated"""
        if routing_key != RoutingKey.DEVICES_CONTROL_ENTITY_DELETED:
            return False

        validated_data = validate_exchange_data(ModuleOrigin.DEVICES_MODULE, routing_key, data)

        if self.get_by_id(control_id=uuid.UUID(validated_data.get("id"), version=4)) is not None:
            del self[str(data.get("id"))]

            return True

        return False

    # -----------------------------------------------------------------------------

    @orm.db_session
    def initialize(self) -> None:
        """Initialize devices controls repository by fetching entities from database"""
        items: Dict[str, DeviceControlItem] = {}

        for entity in DeviceControlEntity.select():
            items[entity.control_id.__str__()] = self.__create_item(entity=entity)

        self._items = items

    # -----------------------------------------------------------------------------

    @orm.db_session
    def __handle_data_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        validated_data = validate_exchange_data(ModuleOrigin.DEVICES_MODULE, routing_key, data)

        control_item = self.get_by_id(control_id=uuid.UUID(validated_data.get("id"), version=4))

        if control_item is None:
            entity: Optional[DeviceControlEntity] = DeviceControlEntity.get(
                control_id=uuid.UUID(validated_data.get("id"), version=4)
            )

            if entity is not None:
                self[entity.control_id.__str__()] = self.__create_item(entity=entity)

                return True

            return False

        item = self.__update_item(item=control_item)

        if item is not None:
            self[str(validated_data.get("id"))] = item

            return True

        return False

    # -----------------------------------------------------------------------------

    @staticmethod
    def __create_item(entity: DeviceControlEntity) -> DeviceControlItem:
        return DeviceControlItem(
            control_id=entity.control_id,
            control_name=entity.name,
            device_id=entity.device.device_id,
        )

    # -----------------------------------------------------------------------------

    @staticmethod
    def __update_item(
        item: DeviceControlItem,
    ) -> DeviceControlItem:
        return DeviceControlItem(
            control_id=item.control_id,
            control_name=item.name,
            device_id=item.device_id,
        )


class ChannelsControlsRepository(ControlsRepository[ChannelControlItem]):
    """
    Channels controls repository

    @package        FastyBird:DevicesModule!
    @module         repositories

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    def get_by_name(self, channel_id: uuid.UUID, control_name: str) -> Optional[ChannelControlItem]:
        """Find control in cache by provided name"""
        for record in self:
            if (
                isinstance(record, ChannelControlItem)
                and channel_id.__eq__(record.channel_id)
                and record.name == control_name
            ):
                return record

        return None

    # -----------------------------------------------------------------------------

    def get_all_by_channel(self, channel_id: uuid.UUID) -> List[ChannelControlItem]:
        """Find all channels controls in cache for channel identifier"""
        items: List[ChannelControlItem] = []

        for record in self:
            if isinstance(record, ChannelControlItem) and channel_id.__eq__(record.channel_id):
                items.append(record)

        return items

    # -----------------------------------------------------------------------------

    def create_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received channel control message from exchange when entity was created"""
        if routing_key != RoutingKey.CHANNELS_CONTROL_ENTITY_CREATED:
            return False

        result: bool = self.__handle_data_from_exchange(routing_key=routing_key, data=data)

        return result

    # -----------------------------------------------------------------------------

    def update_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received channel control message from exchange when entity was updated"""
        if routing_key != RoutingKey.CHANNELS_CONTROL_ENTITY_UPDATED:
            return False

        result: bool = self.__handle_data_from_exchange(routing_key=routing_key, data=data)

        return result

    # -----------------------------------------------------------------------------

    @orm.db_session
    def delete_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received channel control message from exchange when entity was updated"""
        if routing_key != RoutingKey.CHANNELS_CONTROL_ENTITY_DELETED:
            return False

        validated_data = validate_exchange_data(ModuleOrigin.DEVICES_MODULE, routing_key, data)

        if self.get_by_id(control_id=uuid.UUID(validated_data.get("id"), version=4)) is not None:
            del self[str(data.get("id"))]

            return True

        return False

    # -----------------------------------------------------------------------------

    @orm.db_session
    def initialize(self) -> None:
        """Initialize channel controls repository by fetching entities from database"""
        items: Dict[str, ChannelControlItem] = {}

        for entity in ChannelControlEntity.select():
            items[entity.control_id.__str__()] = self.__create_item(entity=entity)

        self._items = items

    # -----------------------------------------------------------------------------

    @orm.db_session
    def __handle_data_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        validated_data = validate_exchange_data(ModuleOrigin.DEVICES_MODULE, routing_key, data)

        control_item = self.get_by_id(control_id=uuid.UUID(validated_data.get("id"), version=4))

        if control_item is None:
            entity: Optional[ChannelControlEntity] = ChannelControlEntity.get(
                control_id=uuid.UUID(validated_data.get("id"), version=4)
            )

            if entity is not None:
                self[entity.control_id.__str__()] = self.__create_item(entity=entity)

                return True

            return False

        item = self.__update_item(item=control_item)

        if item is not None:
            self[str(validated_data.get("id"))] = item

            return True

        return False

    # -----------------------------------------------------------------------------

    @staticmethod
    def __create_item(entity: ChannelControlEntity) -> ChannelControlItem:
        return ChannelControlItem(
            control_id=entity.control_id,
            control_name=entity.name,
            device_id=entity.channel.device.device_id,
            channel_id=entity.channel.channel_id,
        )

    # -----------------------------------------------------------------------------

    @staticmethod
    def __update_item(
        item: ChannelControlItem,
    ) -> ChannelControlItem:
        return ChannelControlItem(
            control_id=item.control_id,
            control_name=item.name,
            device_id=item.device_id,
            channel_id=item.channel_id,
        )


class ConnectorsControlsRepository(ControlsRepository[ConnectorControlItem]):
    """
    Connectors controls repository

    @package        FastyBird:DevicesModule!
    @module         repositories

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    def get_by_name(self, connector_id: uuid.UUID, control_name: str) -> Optional[ConnectorControlItem]:
        """Find control in cache by provided name"""
        for record in self:
            if (
                isinstance(record, ConnectorControlItem)
                and connector_id.__eq__(record.connector_id)
                and record.name == control_name
            ):
                return record

        return None

    # -----------------------------------------------------------------------------

    def get_all_by_channel(self, connector_id: uuid.UUID) -> List[ConnectorControlItem]:
        """Find all connectors controls in cache for connector identifier"""
        items: List[ConnectorControlItem] = []

        for record in self:
            if isinstance(record, ConnectorControlItem) and connector_id.__eq__(record.connector_id):
                items.append(record)

        return items

    # -----------------------------------------------------------------------------

    def create_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received connector control message from exchange when entity was created"""
        if routing_key != RoutingKey.CONNECTORS_CONTROL_ENTITY_CREATED:
            return False

        result: bool = self.__handle_data_from_exchange(routing_key=routing_key, data=data)

        return result

    # -----------------------------------------------------------------------------

    def update_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received connector control message from exchange when entity was updated"""
        if routing_key != RoutingKey.CONNECTORS_CONTROL_ENTITY_UPDATED:
            return False

        result: bool = self.__handle_data_from_exchange(routing_key=routing_key, data=data)

        return result

    # -----------------------------------------------------------------------------

    @orm.db_session
    def delete_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received connector control message from exchange when entity was updated"""
        if routing_key != RoutingKey.CONNECTORS_CONTROL_ENTITY_DELETED:
            return False

        validated_data = validate_exchange_data(ModuleOrigin.DEVICES_MODULE, routing_key, data)

        if self.get_by_id(control_id=uuid.UUID(validated_data.get("id"), version=4)) is not None:
            del self[str(data.get("id"))]

            return True

        return False

    # -----------------------------------------------------------------------------

    @orm.db_session
    def initialize(self) -> None:
        """Initialize connector controls repository by fetching entities from database"""
        items: Dict[str, ConnectorControlItem] = {}

        for entity in ConnectorControlEntity.select():
            items[entity.control_id.__str__()] = self.__create_item(entity=entity)

        self._items = items

    # -----------------------------------------------------------------------------

    @orm.db_session
    def __handle_data_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        validated_data = validate_exchange_data(ModuleOrigin.DEVICES_MODULE, routing_key, data)

        control_item = self.get_by_id(control_id=uuid.UUID(validated_data.get("id"), version=4))

        if control_item is None:
            entity: Optional[ConnectorControlEntity] = ConnectorControlEntity.get(
                control_id=uuid.UUID(validated_data.get("id"), version=4)
            )

            if entity is not None:
                self[entity.control_id.__str__()] = self.__create_item(entity=entity)

                return True

            return False

        item = self.__update_item(item=control_item)

        if item is not None:
            self[str(validated_data.get("id"))] = item

            return True

        return False

    # -----------------------------------------------------------------------------

    @staticmethod
    def __create_item(entity: ConnectorControlEntity) -> ConnectorControlItem:
        return ConnectorControlItem(
            control_id=entity.control_id,
            control_name=entity.name,
            connector_id=entity.connector.connector_id,
        )

    # -----------------------------------------------------------------------------

    @staticmethod
    def __update_item(
        item: ConnectorControlItem,
    ) -> ConnectorControlItem:
        return ConnectorControlItem(
            control_id=item.control_id,
            control_name=item.name,
            connector_id=item.connector_id,
        )


@inject
class ConfigurationRepository(Generic[T]):
    """
    Base configuration repository

    @package        FastyBird:DevicesModule!
    @module         repositories

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    _items: Optional[Dict[str, T]] = None

    __iterator_index = 0

    _event_dispatcher: EventDispatcher

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        event_dispatcher: EventDispatcher,
    ) -> None:
        self.__event_dispatcher = event_dispatcher

        self.__event_dispatcher.add_listener(
            event_id=ModelEntityCreatedEvent.EVENT_NAME,
            listener=self._entity_created,
        )

        self.__event_dispatcher.add_listener(
            event_id=ModelEntityUpdatedEvent.EVENT_NAME,
            listener=self._entity_updated,
        )

        self.__event_dispatcher.add_listener(
            event_id=ModelEntityDeletedEvent.EVENT_NAME,
            listener=self._entity_deleted,
        )

    # -----------------------------------------------------------------------------

    def get_by_id(self, configuration_id: uuid.UUID) -> Union[T, None]:
        """Find configuration in cache by provided identifier"""
        for record in self:
            if configuration_id.__eq__(record.configuration_id):
                return record  # type: ignore[no-any-return]

        return None

    # -----------------------------------------------------------------------------

    def get_by_key(self, configuration_key: str) -> Union[T, None]:
        """Find configuration in cache by provided key"""
        for record in self:
            if record.key == configuration_key:
                return record  # type: ignore[no-any-return]

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

    def _entity_created(self, event: Event) -> None:
        if not isinstance(event, ModelEntityCreatedEvent) or not isinstance(
            event.entity, (DeviceConfigurationEntity, ChannelConfigurationEntity)
        ):
            return

        self.initialize()

        configuration_item = self.get_by_id(configuration_id=event.entity.configuration_id)

        if configuration_item is not None:
            self.__event_dispatcher.dispatch(
                event_id=ModelItemCreatedEvent.EVENT_NAME,
                event=ModelItemCreatedEvent[T](
                    item=configuration_item,
                ),
            )

    # -----------------------------------------------------------------------------

    def _entity_updated(self, event: Event) -> None:
        if not isinstance(event, ModelEntityUpdatedEvent) or not isinstance(
            event.entity, (DeviceConfigurationEntity, ChannelConfigurationEntity)
        ):
            return

        self.initialize()

        configuration_item = self.get_by_id(configuration_id=event.entity.configuration_id)

        if configuration_item is not None:
            self.__event_dispatcher.dispatch(
                event_id=ModelItemUpdatedEvent.EVENT_NAME,
                event=ModelItemUpdatedEvent[T](
                    item=configuration_item,
                ),
            )

    # -----------------------------------------------------------------------------

    def _entity_deleted(self, event: Event) -> None:
        if not isinstance(event, ModelEntityDeletedEvent) or not isinstance(
            event.entity, (DeviceConfigurationEntity, ChannelConfigurationEntity)
        ):
            return

        configuration_item = self.get_by_id(configuration_id=event.entity.configuration_id)

        self.initialize()

        if configuration_item is not None:
            self.__event_dispatcher.dispatch(
                event_id=ModelItemDeletedEvent.EVENT_NAME,
                event=ModelItemDeletedEvent[T](
                    item=configuration_item,
                ),
            )

    # -----------------------------------------------------------------------------

    def __setitem__(self, key: str, value: T) -> None:
        if self._items is None:
            self.initialize()

        if self._items:
            self._items[key] = value

    # -----------------------------------------------------------------------------

    def __getitem__(self, key: str) -> T:
        if self._items is None:
            self.initialize()

        if self._items and key in self._items:
            return self._items[key]

        raise IndexError

    # -----------------------------------------------------------------------------

    def __delitem__(self, key: str) -> None:
        if self._items and key in self._items:
            del self._items[key]

    # -----------------------------------------------------------------------------

    def __iter__(self) -> "ConfigurationRepository":
        # Reset index for nex iteration
        self.__iterator_index = 0

        return self

    # -----------------------------------------------------------------------------

    def __len__(self) -> int:
        if self._items is None:
            self.initialize()

        return len(self._items.values()) if isinstance(self._items, dict) else 0

    # -----------------------------------------------------------------------------

    def __next__(self) -> T:
        if self._items is None:
            self.initialize()

        if self._items and self.__iterator_index < len(self._items.values()):
            items = list(self._items.values()) if self._items else []

            result = items[self.__iterator_index]

            self.__iterator_index += 1

            return result

        # Reset index for nex iteration
        self.__iterator_index = 0

        # End of iteration
        raise StopIteration


class DevicesConfigurationRepository(ConfigurationRepository[DeviceConfigurationItem]):
    """
    Devices configuration repository

    @package        FastyBird:DevicesModule!
    @module         repositories

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    def get_by_identifier(
        self,
        device_id: uuid.UUID,
        configuration_identifier: str,
    ) -> Optional[DeviceConfigurationItem]:
        """Find configuration in cache by provided identifier"""
        for record in self:
            if (
                isinstance(record, DeviceConfigurationItem)
                and device_id.__eq__(record.device_id)
                and record.identifier == configuration_identifier
            ):
                return record

        return None

    # -----------------------------------------------------------------------------

    def get_all_by_device(self, device_id: uuid.UUID) -> List[DeviceConfigurationItem]:
        """Find all devices properties in cache for device identifier"""
        items: List[DeviceConfigurationItem] = []

        for record in self:
            if isinstance(record, DeviceConfigurationItem) and device_id.__eq__(record.device_id):
                items.append(record)

        return items

    # -----------------------------------------------------------------------------

    def create_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received device configuration message from exchange when entity was created"""
        if routing_key != RoutingKey.DEVICES_CONFIGURATION_ENTITY_CREATED:
            return False

        result: bool = self.__handle_data_from_exchange(routing_key=routing_key, data=data)

        return result

    # -----------------------------------------------------------------------------

    def update_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received device configuration message from exchange when entity was updated"""
        if routing_key != RoutingKey.DEVICES_CONFIGURATION_ENTITY_UPDATED:
            return False

        result: bool = self.__handle_data_from_exchange(routing_key=routing_key, data=data)

        return result

    # -----------------------------------------------------------------------------

    @orm.db_session
    def delete_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received device configuration message from exchange when entity was updated"""
        if routing_key != RoutingKey.DEVICES_CONFIGURATION_ENTITY_DELETED:
            return False

        validated_data = validate_exchange_data(ModuleOrigin.DEVICES_MODULE, routing_key, data)

        if self.get_by_id(configuration_id=uuid.UUID(validated_data.get("id"), version=4)) is not None:
            del self[str(data.get("id"))]

            return True

        return False

    # -----------------------------------------------------------------------------

    @orm.db_session
    def initialize(self) -> None:
        """Initialize devices properties repository by fetching entities from database"""
        items: Dict[str, DeviceConfigurationItem] = {}

        for entity in DeviceConfigurationEntity.select():
            items[entity.configuration_id.__str__()] = self._create_item(entity=entity)

        self._items = items

    # -----------------------------------------------------------------------------

    @staticmethod
    def _create_item(
        entity: DeviceConfigurationEntity,
    ) -> DeviceConfigurationItem:
        return DeviceConfigurationItem(
            configuration_id=entity.configuration_id,
            configuration_key=entity.key,
            configuration_identifier=entity.identifier,
            configuration_name=entity.name,
            configuration_comment=entity.comment,
            configuration_data_type=entity.data_type_formatted,
            configuration_value=entity.value,
            configuration_default=entity.default,
            configuration_params=entity.params if entity.params is not None else {},
            device_id=entity.device.device_id,
        )

    # -----------------------------------------------------------------------------

    @staticmethod
    def _update_item(
        item: DeviceConfigurationItem,
        data: Dict,
    ) -> DeviceConfigurationItem:
        data_type = data.get("data_type", item.data_type.value if item.data_type is not None else None)
        data_type = DataType(data_type) if data_type is not None else None

        params: Dict[str, Union[str, int, float, bool, List, None]] = {}

        if "min" in data.keys():
            params["min"] = data.get("min", item.min_value)

        if "max" in data.keys():
            params["max"] = data.get("max", item.max_value)

        if "step" in data.keys():
            params["step"] = data.get("step", item.step_value)

        if "values" in data.keys():
            params["values"] = data.get("values", item.values)

        return DeviceConfigurationItem(
            configuration_id=item.configuration_id,
            configuration_key=item.key,
            configuration_identifier=item.identifier,
            configuration_name=data.get("name", item.name),
            configuration_comment=data.get("comment", item.comment),
            configuration_data_type=data_type,
            configuration_value=data.get("value", item.value),
            configuration_default=data.get("default", item.default),
            configuration_params={**item.params, **params},
            device_id=item.device_id,
        )

    # -----------------------------------------------------------------------------

    @orm.db_session
    def __handle_data_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        validated_data = validate_exchange_data(ModuleOrigin.DEVICES_MODULE, routing_key, data)

        configuration_item = self.get_by_id(configuration_id=uuid.UUID(validated_data.get("id"), version=4))

        if configuration_item is None:
            entity: Optional[DeviceConfigurationEntity] = DeviceConfigurationEntity.get(
                configuration_id=uuid.UUID(validated_data.get("id"), version=4)
            )

            if entity is not None:
                self[entity.configuration_id.__str__()] = self._create_item(entity=entity)

                return True

            return False

        item = self._update_item(item=configuration_item, data=validated_data)

        if item is not None:
            self[str(validated_data.get("id"))] = item

            return True

        return False


class ChannelsConfigurationRepository(ConfigurationRepository[ChannelConfigurationItem]):
    """
    Channel configuration repository

    @package        FastyBird:DevicesModule!
    @module         repositories

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    def get_by_identifier(
        self,
        channel_id: uuid.UUID,
        configuration_identifier: str,
    ) -> Optional[ChannelConfigurationItem]:
        """Find configuration in cache by provided identifier"""
        for record in self:
            if (
                isinstance(record, ChannelConfigurationItem)
                and channel_id.__eq__(record.channel_id)
                and record.identifier == configuration_identifier
            ):
                return record

        return None

    # -----------------------------------------------------------------------------

    def get_all_by_channel(self, channel_id: uuid.UUID) -> List[ChannelConfigurationItem]:
        """Find all channels properties in cache for channel identifier"""
        items: List[ChannelConfigurationItem] = []

        for record in self:
            if isinstance(record, ChannelConfigurationItem) and channel_id.__eq__(record.channel_id):
                items.append(record)

        return items

    # -----------------------------------------------------------------------------

    def create_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received channel configuration message from exchange when entity was created"""
        if routing_key != RoutingKey.CHANNELS_CONFIGURATION_ENTITY_CREATED:
            return False

        result: bool = self.__handle_data_from_exchange(routing_key=routing_key, data=data)

        return result

    # -----------------------------------------------------------------------------

    def update_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received channel configuration message from exchange when entity was updated"""
        if routing_key != RoutingKey.CHANNELS_CONFIGURATION_ENTITY_UPDATED:
            return False

        result: bool = self.__handle_data_from_exchange(routing_key=routing_key, data=data)

        return result

    # -----------------------------------------------------------------------------

    @orm.db_session
    def delete_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        """Process received channel configuration message from exchange when entity was updated"""
        if routing_key != RoutingKey.CHANNELS_CONFIGURATION_ENTITY_DELETED:
            return False

        validated_data = validate_exchange_data(ModuleOrigin.DEVICES_MODULE, routing_key, data)

        if self.get_by_id(configuration_id=uuid.UUID(validated_data.get("id"), version=4)) is not None:
            del self[str(data.get("id"))]

            return True

        return False

    # -----------------------------------------------------------------------------

    @orm.db_session
    def initialize(self) -> None:
        """Initialize repository by fetching entities from database"""
        items: Dict[str, ChannelConfigurationItem] = {}

        for entity in ChannelConfigurationEntity.select():
            items[entity.configuration_id.__str__()] = self.__create_item(entity=entity)

        self._items = items

    # -----------------------------------------------------------------------------

    @orm.db_session
    def __handle_data_from_exchange(self, routing_key: RoutingKey, data: Dict) -> bool:
        validated_data = validate_exchange_data(ModuleOrigin.DEVICES_MODULE, routing_key, data)

        configuration_item = self.get_by_id(configuration_id=uuid.UUID(validated_data.get("id"), version=4))

        if configuration_item is None:
            entity: Optional[ChannelConfigurationEntity] = ChannelConfigurationEntity.get(
                configuration_id=uuid.UUID(validated_data.get("id"), version=4)
            )

            if entity is not None:
                self[entity.configuration_id.__str__()] = self.__create_item(entity=entity)

                return True

            return False

        item = self.__update_item(item=configuration_item, data=validated_data)

        if item is not None:
            self[str(validated_data.get("id"))] = item

            return True

        return False

    # -----------------------------------------------------------------------------

    @staticmethod
    def __create_item(
        entity: ChannelConfigurationEntity,
    ) -> ChannelConfigurationItem:
        return ChannelConfigurationItem(
            configuration_id=entity.configuration_id,
            configuration_key=entity.key,
            configuration_identifier=entity.identifier,
            configuration_name=entity.name,
            configuration_comment=entity.comment,
            configuration_data_type=entity.data_type_formatted,
            configuration_value=entity.value,
            configuration_default=entity.default,
            configuration_params=entity.params if entity.params is not None else {},
            device_id=entity.channel.device.device_id,
            channel_id=entity.channel.channel_id,
        )

    # -----------------------------------------------------------------------------

    @staticmethod
    def __update_item(
        item: ChannelConfigurationItem,
        data: Dict,
    ) -> ChannelConfigurationItem:
        data_type = data.get("data_type", item.data_type.value if item.data_type is not None else None)
        data_type = DataType(data_type) if data_type is not None else None

        params: Dict[str, Union[str, int, float, bool, List, None]] = {}

        if "min" in data.keys():
            params["min"] = data.get("min", item.min_value)

        if "max" in data.keys():
            params["max"] = data.get("max", item.max_value)

        if "step" in data.keys():
            params["step"] = data.get("step", item.step_value)

        if "values" in data.keys():
            params["values"] = data.get("values", item.values)

        return ChannelConfigurationItem(
            configuration_id=item.configuration_id,
            configuration_key=item.key,
            configuration_identifier=item.identifier,
            configuration_name=data.get("name", item.name),
            configuration_comment=data.get("comment", item.comment),
            configuration_data_type=data_type,
            configuration_value=data.get("value", item.value),
            configuration_default=data.get("default", item.default),
            configuration_params={**item.params, **params},
            device_id=item.device_id,
            channel_id=item.channel_id,
        )


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
