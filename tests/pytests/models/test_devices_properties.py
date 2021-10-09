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
from pony.orm import core as orm
from unittest.mock import patch
from exchange_plugin.publisher import Publisher

# Library libs
from devices_module.items import DevicePropertyItem
from devices_module.models import DeviceEntity, DevicePropertyEntity
from devices_module.repositories import DevicesPropertiesRepository

# Tests libs
from tests.pytests.tests import DbTestCase


class TestDevicePropertyEntity(DbTestCase):

    @inject
    def test_create_entity(self, property_repository: DevicesPropertiesRepository) -> None:
        property_item = property_repository.get_by_id(
            property_id=uuid.UUID("26d7a945-ba29-471e-9e3c-304ef0acb199", version=4),
        )

        self.assertIsNone(property_item)

        with patch.object(Publisher, "publish") as MockPublisher:
            property_entity = self.__create_entity()

        MockPublisher.assert_called_once()

        self.assertIsInstance(property_entity, DevicePropertyEntity)
        self.assertEqual("26d7a945-ba29-471e-9e3c-304ef0acb199", property_entity.property_id.__str__())
        self.assertEqual("property-identifier", property_entity.identifier)
        self.assertEqual("Property name", property_entity.name)
        self.assertIsNotNone(property_entity.key)
        self.assertIsNotNone(property_entity.created_at)

        property_item = property_repository.get_by_id(
            property_id=uuid.UUID("26d7a945-ba29-471e-9e3c-304ef0acb199", version=4),
        )

        self.assertIsInstance(property_item, DevicePropertyItem)

    # -----------------------------------------------------------------------------

    @staticmethod
    @orm.db_session
    def __create_entity() -> DevicePropertyEntity:
        device = DeviceEntity.get(device_id=uuid.UUID("69786d15-fd0c-4d9f-9378-33287c2009fa", version=4))

        property_entity = DevicePropertyEntity(
            property_id=uuid.UUID("26d7a945-ba29-471e-9e3c-304ef0acb199", version=4),
            identifier="property-identifier",
            name="Property name",
            queryable=True,
            settable=False,
            device=device,
        )

        return property_entity
