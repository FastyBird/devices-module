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
from devices_module.items import DevicePropertyItem
from devices_module.models import device_property_repository

# Tests libs
from tests.pytests.tests import DbTestCase


class TestDevicesPropertiesRepository(DbTestCase):
    def test_repository_iterator(self) -> None:
        self.assertEqual(3, len(device_property_repository))

    # -----------------------------------------------------------------------------

    def test_get_item(self) -> None:
        property_item = device_property_repository.get_property_by_id(
            uuid.UUID("28bc0d38-2f7c-4a71-aa74-27b102f8df4c", version=4)
        )

        self.assertIsInstance(property_item, DevicePropertyItem)
        self.assertEqual("bLikvh", property_item.key)
