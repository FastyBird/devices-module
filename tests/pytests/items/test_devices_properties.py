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
from devices_module.repositories import device_property_repository

# Tests libs
from tests.pytests.tests import DbTestCase


class TestDevicePropertyItem(DbTestCase):
    def test_transform_to_dict(self) -> None:
        device_property_repository.initialize()

        property_item = device_property_repository.get_by_id(
            uuid.UUID("28bc0d38-2f7c-4a71-aa74-27b102f8df4c", version=4)
        )

        self.assertIsInstance(property_item, DevicePropertyItem)

        self.assertEqual({
            "id": "28bc0d38-2f7c-4a71-aa74-27b102f8df4c",
            "name": "rssi",
            "identifier": "rssi",
            "key": "bLikvh",
            "device": "69786d15-fd0c-4d9f-9378-33287c2009fa",
            "queryable": True,
            "settable": False,
            "data_type": "int",
            "format": None,
            "unit": None,
        }, property_item.to_dict())
