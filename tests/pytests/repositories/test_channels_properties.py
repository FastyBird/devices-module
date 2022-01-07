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

# Test dependencies
import uuid

# Library dependencies
from kink import inject
from modules_metadata.devices_module import PropertyType
from modules_metadata.routing import RoutingKey
from modules_metadata.types import ModuleOrigin

# Library libs
from devices_module.entities.channel import ChannelPropertyEntity
from devices_module.repositories.channel import ChannelsPropertiesRepository

# Tests libs
from tests.pytests.tests import DbTestCase


class TestChannelsPropertiesRepository(DbTestCase):
    @inject
    def test_repository_iterator(self, property_repository: ChannelsPropertiesRepository) -> None:
        self.assertEqual(3, len(property_repository.get_all()))

    # -----------------------------------------------------------------------------

    @inject
    def test_get_item(self, property_repository: ChannelsPropertiesRepository) -> None:
        entity = property_repository.get_by_id(property_id=uuid.UUID("bbcccf8c-33ab-431b-a795-d7bb38b6b6db", version=4))

        self.assertIsInstance(entity, ChannelPropertyEntity)
        self.assertEqual("bLikx4", entity.key)

    # -----------------------------------------------------------------------------

    @inject
    def test_transform_to_dict(self, property_repository: ChannelsPropertiesRepository) -> None:
        entity = property_repository.get_by_id(property_id=uuid.UUID("bbcccf8c-33ab-431b-a795-d7bb38b6b6db", version=4))

        self.assertIsInstance(entity, ChannelPropertyEntity)
        self.assertEqual(
            {
                "id": "bbcccf8c-33ab-431b-a795-d7bb38b6b6db",
                "type": PropertyType.DYNAMIC.value,
                "name": "switch",
                "identifier": "switch",
                "key": "bLikx4",
                "channel": "17c59dfa-2edd-438e-8c49-faa4e38e5a5e",
                "queryable": True,
                "settable": True,
                "data_type": "enum",
                "unit": None,
                "format": ["off", "on", "toggle"],
                "invalid": None,
                "number_of_decimals": None,
            },
            entity.to_dict(),
        )
        self.assertIsInstance(
            self.validate_exchange_data(
                origin=ModuleOrigin.DEVICES_MODULE,
                routing_key=RoutingKey.CHANNELS_PROPERTY_ENTITY_CREATED,
                data=entity.to_dict(),
            ),
            dict,
        )
