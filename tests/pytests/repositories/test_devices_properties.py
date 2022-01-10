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
from metadata.devices_module import PropertyType
from metadata.routing import RoutingKey

# Library libs
from devices_module.entities.device import (
    DeviceDynamicPropertyEntity,
    DeviceStaticPropertyEntity,
)
from devices_module.repositories.device import DevicesPropertiesRepository

# Tests libs
from tests.pytests.tests import DbTestCase


class TestDevicesPropertiesRepository(DbTestCase):
    @inject
    def test_repository_iterator(self, property_repository: DevicesPropertiesRepository) -> None:
        self.assertEqual(5, len(property_repository.get_all()))

    # -----------------------------------------------------------------------------

    @inject
    def test_get_item(self, property_repository: DevicesPropertiesRepository) -> None:
        entity = property_repository.get_by_id(property_id=uuid.UUID("3134ba8e-f134-4bf2-9c80-c977c4deb0fb", version=4))

        self.assertIsInstance(entity, DeviceStaticPropertyEntity)
        self.assertEqual("bLykvV", entity.key)

    # -----------------------------------------------------------------------------

    @inject
    def test_transform_to_dict(self, property_repository: DevicesPropertiesRepository) -> None:
        entity = property_repository.get_by_id(property_id=uuid.UUID("28bc0d38-2f7c-4a71-aa74-27b102f8df4c", version=4))

        self.assertIsInstance(entity, DeviceDynamicPropertyEntity)
        self.assertEqual(
            {
                "id": "28bc0d38-2f7c-4a71-aa74-27b102f8df4c",
                "type": PropertyType.DYNAMIC.value,
                "name": "rssi",
                "identifier": "rssi",
                "key": "bLikvh",
                "device": "69786d15-fd0c-4d9f-9378-33287c2009fa",
                "queryable": True,
                "settable": False,
                "data_type": "int",
                "unit": None,
                "format": None,
                "invalid": None,
                "number_of_decimals": None,
                "owner": "455354e8-96bd-4c29-84e7-9f10e1d4db4b",
            },
            entity.to_dict(),
        )
        self.assertIsInstance(
            self.validate_exchange_data(
                routing_key=RoutingKey.DEVICES_PROPERTY_ENTITY_REPORTED,
                data=entity.to_dict(),
            ),
            dict,
        )
