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
from devices_module.items import DevicePropertyItem
from devices_module.repositories import DevicesPropertiesRepository
from modules_metadata.routing import RoutingKey

# Tests libs
from tests.pytests.tests import DbTestCase


class TestDevicesPropertiesRepository(DbTestCase):
    @inject
    def test_repository_iterator(self, property_repository: DevicesPropertiesRepository) -> None:
        property_repository.initialize()

        self.assertEqual(5, len(property_repository))

    # -----------------------------------------------------------------------------

    @inject
    def test_get_item(self, property_repository: DevicesPropertiesRepository) -> None:
        property_repository.initialize()

        property_item = property_repository.get_by_id(
            uuid.UUID("28bc0d38-2f7c-4a71-aa74-27b102f8df4c", version=4)
        )

        self.assertIsInstance(property_item, DevicePropertyItem)
        self.assertEqual("bLikvh", property_item.key)

    # -----------------------------------------------------------------------------

    @inject
    def test_create_from_exchange(self, property_repository: DevicesPropertiesRepository) -> None:
        property_repository.initialize()

        result: bool = property_repository.create_from_exchange(
            RoutingKey(RoutingKey.DEVICES_PROPERTY_ENTITY_CREATED),
            {
                "id": "28bc0d38-2f7c-4a71-aa74-27b102f8df4c",
                "name": "rssi",
                "identifier": "rssi",
                "key": "bLikvh",
                "queryable": True,
                "settable": False,
                "data_type": "int",
                "format": None,
                "invalid": None,
                "unit": None,
                "device": "69786d15-fd0c-4d9f-9378-33287c2009fa",
            },
        )

        self.assertTrue(result)

        property_item = property_repository.get_by_id(
            uuid.UUID("28bc0d38-2f7c-4a71-aa74-27b102f8df4c", version=4)
        )

        self.assertIsInstance(property_item, DevicePropertyItem)
        self.assertEqual("28bc0d38-2f7c-4a71-aa74-27b102f8df4c", property_item.property_id.__str__())
        self.assertEqual({
            "id": "28bc0d38-2f7c-4a71-aa74-27b102f8df4c",
            "name": "rssi",
            "identifier": "rssi",
            "key": "bLikvh",
            "queryable": True,
            "settable": False,
            "data_type": "int",
            "format": None,
            "invalid": None,
            "unit": None,
            "device": "69786d15-fd0c-4d9f-9378-33287c2009fa",
        }, property_item.to_dict())

    # -----------------------------------------------------------------------------

    @inject
    def test_update_from_exchange(self, property_repository: DevicesPropertiesRepository) -> None:
        property_repository.initialize()

        result: bool = property_repository.update_from_exchange(
            RoutingKey(RoutingKey.DEVICES_PROPERTY_ENTITY_UPDATED),
            {
                "id": "28bc0d38-2f7c-4a71-aa74-27b102f8df4c",
                "name": "Renamed",
                "identifier": "rssi",
                "key": "bLikvh",
                "queryable": False,
                "settable": False,
                "data_type": "enum",
                "format": "good,poor",
                "invalid": None,
                "unit": None,
                "device": "69786d15-fd0c-4d9f-9378-33287c2009fa",
            },
        )

        self.assertTrue(result)

        property_item = property_repository.get_by_id(
            uuid.UUID("28bc0d38-2f7c-4a71-aa74-27b102f8df4c", version=4)
        )

        self.assertIsInstance(property_item, DevicePropertyItem)
        self.assertEqual("28bc0d38-2f7c-4a71-aa74-27b102f8df4c", property_item.property_id.__str__())
        self.assertEqual({
            "id": "28bc0d38-2f7c-4a71-aa74-27b102f8df4c",
            "name": "Renamed",
            "identifier": "rssi",
            "key": "bLikvh",
            "queryable": False,
            "settable": False,
            "data_type": "enum",
            "format": "good,poor",
            "invalid": None,
            "unit": None,
            "device": "69786d15-fd0c-4d9f-9378-33287c2009fa",
        }, property_item.to_dict())

    # -----------------------------------------------------------------------------

    @inject
    def test_delete_from_exchange(self, property_repository: DevicesPropertiesRepository) -> None:
        property_repository.initialize()

        property_item = property_repository.get_by_id(
            uuid.UUID("28bc0d38-2f7c-4a71-aa74-27b102f8df4c", version=4)
        )

        self.assertIsInstance(property_item, DevicePropertyItem)
        self.assertEqual("28bc0d38-2f7c-4a71-aa74-27b102f8df4c", property_item.property_id.__str__())

        result: bool = property_repository.delete_from_exchange(
            RoutingKey(RoutingKey.DEVICES_PROPERTY_ENTITY_DELETED),
            {
                "id": "28bc0d38-2f7c-4a71-aa74-27b102f8df4c",
                "name": "rssi",
                "identifier": "rssi",
                "key": "bLikvh",
                "queryable": True,
                "settable": False,
                "data_type": "int",
                "format": None,
                "invalid": None,
                "unit": None,
                "device": "69786d15-fd0c-4d9f-9378-33287c2009fa",
            },
        )

        self.assertTrue(result)

        property_item = property_repository.get_by_id(
            uuid.UUID("28bc0d38-2f7c-4a71-aa74-27b102f8df4c", version=4)
        )

        self.assertIsNone(property_item)
