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
from devices_module.items import DeviceItem
from devices_module.repositories import DevicesRepository
from modules_metadata.routing import RoutingKey

# Tests libs
from tests.pytests.tests import DbTestCase


class TestDevicesRepository(DbTestCase):
    @inject
    def test_repository_iterator(self, device_repository: DevicesRepository) -> None:
        device_repository.initialize()

        self.assertEqual(4, len(device_repository))

    # -----------------------------------------------------------------------------

    @inject
    def test_get_item(self, device_repository: DevicesRepository) -> None:
        device_repository.initialize()

        device_item = device_repository.get_by_id(
            uuid.UUID("69786d15-fd0c-4d9f-9378-33287c2009fa", version=4)
        )

        self.assertIsInstance(device_item, DeviceItem)
        self.assertEqual("bLikkz", device_item.key)

    # -----------------------------------------------------------------------------

    @inject
    def test_create_from_exchange(self, device_repository: DevicesRepository) -> None:
        device_repository.initialize()

        result: bool = device_repository.create_from_exchange(
            RoutingKey(RoutingKey.DEVICES_ENTITY_CREATED),
            {
                "id": "69786d15-fd0c-4d9f-9378-33287c2009fa",
                "identifier": "first-device",
                "key": "bLikkz",
                "name": "First device",
                "comment": None,
                "enabled": True,
                "hardware_manufacturer": "itead",
                "hardware_model": "sonoff_basic",
                "hardware_version": "rev1",
                "hardware_mac_address": "807d3a3dbe6d",
                "firmware_manufacturer": "fastybird",
                "firmware_version": None,
                "parent": None,
            },
        )

        self.assertTrue(result)

        device_item = device_repository.get_by_id(
            uuid.UUID("69786d15-fd0c-4d9f-9378-33287c2009fa", version=4)
        )

        self.assertIsInstance(device_item, DeviceItem)
        self.assertEqual("69786d15-fd0c-4d9f-9378-33287c2009fa", device_item.device_id.__str__())
        self.assertEqual({
            "id": "69786d15-fd0c-4d9f-9378-33287c2009fa",
            "identifier": "first-device",
            "key": "bLikkz",
            "name": "First device",
            "comment": None,
            "enabled": True,
            "hardware_manufacturer": "itead",
            "hardware_model": "sonoff_basic",
            "hardware_version": "rev1",
            "hardware_mac_address": "807d3a3dbe6d",
            "firmware_manufacturer": "fastybird",
            "firmware_version": None,
            "parent": None,
        }, device_item.to_dict())

    # -----------------------------------------------------------------------------

    @inject
    def test_update_from_exchange(self, device_repository: DevicesRepository) -> None:
        device_repository.initialize()

        result: bool = device_repository.update_from_exchange(
            RoutingKey(RoutingKey.DEVICES_ENTITY_UPDATED),
            {
                "id": "69786d15-fd0c-4d9f-9378-33287c2009fa",
                "identifier": "first-device",
                "key": "bLikkz",
                "name": "Renamed device",
                "comment": "With custom comment",
                "enabled": False,
                "hardware_manufacturer": "itead",
                "hardware_model": "sonoff_basic",
                "hardware_version": "rev2",
                "hardware_mac_address": "807d3a3dbe6d",
                "firmware_manufacturer": "fastybird",
                "firmware_version": "1.0",
                "parent": None,
            },
        )

        self.assertTrue(result)

        device_item = device_repository.get_by_id(
            uuid.UUID("69786d15-fd0c-4d9f-9378-33287c2009fa", version=4)
        )

        self.assertIsInstance(device_item, DeviceItem)
        self.assertEqual("69786d15-fd0c-4d9f-9378-33287c2009fa", device_item.device_id.__str__())
        self.assertEqual({
            "id": "69786d15-fd0c-4d9f-9378-33287c2009fa",
            "identifier": "first-device",
            "key": "bLikkz",
            "name": "Renamed device",
            "comment": "With custom comment",
            "enabled": False,
            "hardware_manufacturer": "itead",
            "hardware_model": "sonoff_basic",
            "hardware_version": "rev2",
            "hardware_mac_address": "807d3a3dbe6d",
            "firmware_manufacturer": "fastybird",
            "firmware_version": "1.0",
            "parent": None,
        }, device_item.to_dict())

    # -----------------------------------------------------------------------------

    @inject
    def test_delete_from_exchange(self, device_repository: DevicesRepository) -> None:
        device_repository.initialize()

        device_item = device_repository.get_by_id(
            uuid.UUID("69786d15-fd0c-4d9f-9378-33287c2009fa", version=4)
        )

        self.assertIsInstance(device_item, DeviceItem)
        self.assertEqual("69786d15-fd0c-4d9f-9378-33287c2009fa", device_item.device_id.__str__())

        result: bool = device_repository.delete_from_exchange(
            RoutingKey(RoutingKey.DEVICES_ENTITY_DELETED),
            {
                "id": "69786d15-fd0c-4d9f-9378-33287c2009fa",
                "identifier": "first-device",
                "key": "bLikkz",
                "name": "First device",
                "comment": None,
                "enabled": True,
                "hardware_manufacturer": "itead",
                "hardware_model": "sonoff_basic",
                "hardware_version": "rev1",
                "hardware_mac_address": "807d3a3dbe6d",
                "firmware_manufacturer": "fastybird",
                "firmware_version": None,
                "parent": None,
            },
        )

        self.assertTrue(result)

        device_item = device_repository.get_by_id(
            uuid.UUID("69786d15-fd0c-4d9f-9378-33287c2009fa", version=4)
        )

        self.assertIsNone(device_item)
