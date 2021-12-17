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
from kink import inject

# Library libs
from devices_module.items import ChannelPropertyItem
from devices_module.repositories import ChannelsPropertiesRepository
from modules_metadata.routing import RoutingKey

# Tests libs
from tests.pytests.tests import DbTestCase


class TestChannelsPropertiesRepository(DbTestCase):
    @inject
    def test_repository_iterator(self, property_repository: ChannelsPropertiesRepository) -> None:
        property_repository.initialize()

        self.assertEqual(3, len(property_repository))

    # -----------------------------------------------------------------------------

    @inject
    def test_get_item(self, property_repository: ChannelsPropertiesRepository) -> None:
        property_repository.initialize()

        property_item = property_repository.get_by_id(
            uuid.UUID("bbcccf8c-33ab-431b-a795-d7bb38b6b6db", version=4)
        )

        self.assertIsInstance(property_item, ChannelPropertyItem)
        self.assertEqual("bLikx4", property_item.key)

    # -----------------------------------------------------------------------------

    @inject
    def test_create_from_exchange(self, property_repository: ChannelsPropertiesRepository) -> None:
        property_repository.initialize()

        result: bool = property_repository.create_from_exchange(
            RoutingKey(RoutingKey.CHANNELS_PROPERTY_ENTITY_CREATED),
            {
                "id": "bbcccf8c-33ab-431b-a795-d7bb38b6b6db",
                "key": "bLikx4",
                "identifier": "switch",
                "name": "new name",
                "settable": True,
                "queryable": True,
                "data_type": "enum",
                "unit": None,
                "format": "off,on,toggle",
                "invalid": None,
                "channel": "17c59dfa-2edd-438e-8c49-faa4e38e5a5e",
            },
        )

        self.assertTrue(result)

        property_item = property_repository.get_by_id(
            uuid.UUID("bbcccf8c-33ab-431b-a795-d7bb38b6b6db", version=4)
        )

        self.assertIsInstance(property_item, ChannelPropertyItem)
        self.assertEqual("bbcccf8c-33ab-431b-a795-d7bb38b6b6db", property_item.property_id.__str__())
        self.assertEqual({
            "id": "bbcccf8c-33ab-431b-a795-d7bb38b6b6db",
            "name": "new name",
            "identifier": "switch",
            "key": "bLikx4",
            "channel": "17c59dfa-2edd-438e-8c49-faa4e38e5a5e",
            "queryable": True,
            "settable": True,
            "data_type": "enum",
            "format": "off,on,toggle",
            "invalid": None,
            "unit": None,
        }, property_item.to_dict())

    # -----------------------------------------------------------------------------

    @inject
    def test_update_from_exchange(self, property_repository: ChannelsPropertiesRepository) -> None:
        property_repository.initialize()

        result: bool = property_repository.update_from_exchange(
            RoutingKey(RoutingKey.CHANNELS_PROPERTY_ENTITY_UPDATED),
            {
                "id": "bbcccf8c-33ab-431b-a795-d7bb38b6b6db",
                "key": "bLikx4",
                "identifier": "switch",
                "name": "Renamed",
                "settable": False,
                "queryable": True,
                "data_type": "enum",
                "unit": None,
                "format": "off,on",
                "invalid": None,
                "channel": "17c59dfa-2edd-438e-8c49-faa4e38e5a5e",
            },
        )

        self.assertTrue(result)

        property_item = property_repository.get_by_id(
            uuid.UUID("bbcccf8c-33ab-431b-a795-d7bb38b6b6db", version=4)
        )

        self.assertIsInstance(property_item, ChannelPropertyItem)
        self.assertEqual("bbcccf8c-33ab-431b-a795-d7bb38b6b6db", property_item.property_id.__str__())
        self.assertEqual({
            "id": "bbcccf8c-33ab-431b-a795-d7bb38b6b6db",
            "name": "Renamed",
            "key": "bLikx4",
            "identifier": "switch",
            "queryable": True,
            "settable": False,
            "data_type": "enum",
            "unit": None,
            "format": "off,on",
            "invalid": None,
            "channel": "17c59dfa-2edd-438e-8c49-faa4e38e5a5e",
        }, property_item.to_dict())

    # -----------------------------------------------------------------------------

    @inject
    def test_delete_from_exchange(self, property_repository: ChannelsPropertiesRepository) -> None:
        property_repository.initialize()

        property_item = property_repository.get_by_id(
            uuid.UUID("bbcccf8c-33ab-431b-a795-d7bb38b6b6db", version=4)
        )

        self.assertIsInstance(property_item, ChannelPropertyItem)
        self.assertEqual("bbcccf8c-33ab-431b-a795-d7bb38b6b6db", property_item.property_id.__str__())

        result: bool = property_repository.delete_from_exchange(
            RoutingKey(RoutingKey.CHANNELS_PROPERTY_ENTITY_DELETED),
            {
                "id": "bbcccf8c-33ab-431b-a795-d7bb38b6b6db",
                "name": "switch",
                "identifier": "switch",
                "key": "bLikx4",
                "channel": "17c59dfa-2edd-438e-8c49-faa4e38e5a5e",
                "queryable": True,
                "settable": True,
                "data_type": "enum",
                "format": "off,on,toggle",
                "invalid": None,
                "unit": None,
            },
        )

        self.assertTrue(result)

        property_item = property_repository.get_by_id(
            uuid.UUID("bbcccf8c-33ab-431b-a795-d7bb38b6b6db", version=4)
        )

        self.assertIsNone(property_item)
