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
Devices module models events
"""

# Library dependencies
from pony.orm import core as orm
from whistle import Event

# Library libs
from devices_module.items import RepositoryItem


class ModelEntityCreatedEvent(Event):
    """
    Event fired by model when new entity is created

    @package        FastyBird:DevicesModule!
    @module         events

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    __entity: orm.Entity

    EVENT_NAME: str = "devices-module.entityCreated"

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        entity: orm.Entity,
    ) -> None:
        self.__entity = entity

    # -----------------------------------------------------------------------------

    @property
    def entity(self) -> orm.Entity:
        """Created entity instance"""
        return self.__entity


class ModelEntityUpdatedEvent(Event):
    """
    Event fired by model when existing entity is update

    @package        FastyBird:DevicesModule!
    @module         events

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    __entity: orm.Entity

    EVENT_NAME: str = "devices-module.entityUpdated"

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        entity: orm.Entity,
    ) -> None:
        self.__entity = entity

    # -----------------------------------------------------------------------------

    @property
    def entity(self) -> orm.Entity:
        """Updated entity instance"""
        return self.__entity


class ModelEntityDeletedEvent(Event):
    """
    Event fired by model when existing entity is deleted

    @package        FastyBird:DevicesModule!
    @module         events

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    __entity: orm.Entity

    EVENT_NAME: str = "devices-module.entityDeleted"

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        entity: orm.Entity,
    ) -> None:
        self.__entity = entity

    # -----------------------------------------------------------------------------

    @property
    def entity(self) -> orm.Entity:
        """Deleted entity instance"""
        return self.__entity


class ModelItemCreatedEvent(Event):
    """
    Event fired by model when new item is created

    @package        FastyBird:DevicesModule!
    @module         events

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    __item: RepositoryItem

    EVENT_NAME: str = "devices-module.itemCreated"

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        item: RepositoryItem,
    ) -> None:
        self.__item = item

    # -----------------------------------------------------------------------------

    @property
    def item(self) -> RepositoryItem:
        """Created item instance"""
        return self.__item


class ModelItemUpdatedEvent(Event):
    """
    Event fired by model when existing item is update

    @package        FastyBird:DevicesModule!
    @module         events

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    __item: RepositoryItem

    EVENT_NAME: str = "devices-module.itemUpdated"

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        item: RepositoryItem,
    ) -> None:
        self.__item = item

    # -----------------------------------------------------------------------------

    @property
    def item(self) -> RepositoryItem:
        """Updated item instance"""
        return self.__item


class ModelItemDeletedEvent(Event):
    """
    Event fired by model when existing item is deleted

    @package        FastyBird:DevicesModule!
    @module         events

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    __item: RepositoryItem

    EVENT_NAME: str = "devices-module.itemDeleted"

    # -----------------------------------------------------------------------------

    def __init__(
        self,
        item: RepositoryItem,
    ) -> None:
        self.__item = item

    # -----------------------------------------------------------------------------

    @property
    def item(self) -> RepositoryItem:
        """Deleted item instance"""
        return self.__item
