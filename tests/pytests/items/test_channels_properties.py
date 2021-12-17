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

# Tests libs
from tests.pytests.tests import DbTestCase


class TestChannelPropertyItem(DbTestCase):
    @inject
    def test_transform_to_dict(self, property_repository: ChannelsPropertiesRepository) -> None:
        property_item = property_repository.get_by_id(
            uuid.UUID("bbcccf8c-33ab-431b-a795-d7bb38b6b6db", version=4)
        )

        self.assertIsInstance(property_item, ChannelPropertyItem)

        self.assertEqual({
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
        }, property_item.to_dict())
