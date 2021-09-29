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
from devices_module.items import DeviceControlItem
from devices_module.repositories import device_control_repository

# Tests libs
from tests.pytests.tests import DbTestCase


class TestDeviceControlItem(DbTestCase):
    def test_transform_to_dict(self) -> None:
        device_control_repository.initialize()

        control_item = device_control_repository.get_by_id(
            uuid.UUID("7c055b2b-60c3-4017-93db-e9478d8aa662", version=4)
        )

        self.assertIsInstance(control_item, DeviceControlItem)

        self.assertEqual({
            "id": "7c055b2b-60c3-4017-93db-e9478d8aa662",
            "name": "configure",
            "device": "69786d15-fd0c-4d9f-9378-33287c2009fa",
        }, control_item.to_dict())
