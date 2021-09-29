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
from modules_metadata.routing import RoutingKey

# Tests libs
from tests.pytests.tests import DbTestCase


class TestDevicesControlsRepository(DbTestCase):
    def test_repository_iterator(self) -> None:
        device_control_repository.initialize()

        self.assertEqual(1, len(device_control_repository))

    # -----------------------------------------------------------------------------

    def test_get_item(self) -> None:
        device_control_repository.initialize()

        control_item = device_control_repository.get_by_id(
            uuid.UUID("7c055b2b-60c3-4017-93db-e9478d8aa662", version=4)
        )

        self.assertIsInstance(control_item, DeviceControlItem)
        self.assertEqual("configure", control_item.name)

    # -----------------------------------------------------------------------------

    def test_create_from_exchange(self) -> None:
        device_control_repository.initialize()

        result: bool = device_control_repository.create_from_exchange(
            RoutingKey(RoutingKey.DEVICES_CONTROL_ENTITY_CREATED),
            {
                "id": "7c055b2b-60c3-4017-93db-e9478d8aa662",
                "name": "configure",
                "device": "69786d15-fd0c-4d9f-9378-33287c2009fa",
            },
        )

        self.assertTrue(result)

        control_item = device_control_repository.get_by_id(
            uuid.UUID("7c055b2b-60c3-4017-93db-e9478d8aa662", version=4)
        )

        self.assertIsInstance(control_item, DeviceControlItem)
        self.assertEqual("7c055b2b-60c3-4017-93db-e9478d8aa662", control_item.control_id.__str__())
        self.assertEqual({
            "id": "7c055b2b-60c3-4017-93db-e9478d8aa662",
            "name": "configure",
            "device": "69786d15-fd0c-4d9f-9378-33287c2009fa",
        }, control_item.to_dict())

    # -----------------------------------------------------------------------------

    def test_update_from_exchange(self) -> None:
        device_control_repository.initialize()

        result: bool = device_control_repository.update_from_exchange(
            RoutingKey(RoutingKey.DEVICES_CONTROL_ENTITY_UPDATED),
            {
                "id": "7c055b2b-60c3-4017-93db-e9478d8aa662",
                "name": "not edited name",
                "device": "69786d15-fd0c-4d9f-9378-33287c2009fa",
            },
        )

        self.assertTrue(result)

        control_item = device_control_repository.get_by_id(
            uuid.UUID("7c055b2b-60c3-4017-93db-e9478d8aa662", version=4)
        )

        self.assertIsInstance(control_item, DeviceControlItem)
        self.assertEqual("7c055b2b-60c3-4017-93db-e9478d8aa662", control_item.control_id.__str__())
        self.assertEqual({
            "id": "7c055b2b-60c3-4017-93db-e9478d8aa662",
            "name": "configure",
            "device": "69786d15-fd0c-4d9f-9378-33287c2009fa",
        }, control_item.to_dict())

    # -----------------------------------------------------------------------------

    def test_delete_from_exchange(self) -> None:
        device_control_repository.initialize()

        control_item = device_control_repository.get_by_id(
            uuid.UUID("7c055b2b-60c3-4017-93db-e9478d8aa662", version=4)
        )

        self.assertIsInstance(control_item, DeviceControlItem)
        self.assertEqual("7c055b2b-60c3-4017-93db-e9478d8aa662", control_item.control_id.__str__())

        result: bool = device_control_repository.delete_from_exchange(
            RoutingKey(RoutingKey.DEVICES_CONTROL_ENTITY_DELETED),
            {
                "id": "7c055b2b-60c3-4017-93db-e9478d8aa662",
                "name": "configure",
                "device": "69786d15-fd0c-4d9f-9378-33287c2009fa",
            },
        )

        self.assertTrue(result)

        control_item = device_control_repository.get_by_id(
            uuid.UUID("7c055b2b-60c3-4017-93db-e9478d8aa662", version=4)
        )

        self.assertIsNone(control_item)
