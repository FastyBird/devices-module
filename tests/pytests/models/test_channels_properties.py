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

# Library libs
from devices_module.items import ChannelPropertyItem
from devices_module.models import channel_property_repository

# Tests libs
from tests.pytests.tests import DbTestCase


class TestChannelsPropertiesRepository(DbTestCase):
    def test_repository_iterator(self) -> None:
        self.assertEqual(3, len(channel_property_repository))

    # -----------------------------------------------------------------------------

    def test_get_item(self) -> None:
        property_item = channel_property_repository.get_property_by_id(
            uuid.UUID("bbcccf8c-33ab-431b-a795-d7bb38b6b6db", version=4)
        )

        self.assertIsInstance(property_item, ChannelPropertyItem)
        self.assertEqual("bLikx4", property_item.key)
