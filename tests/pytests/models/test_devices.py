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
from devices_module.items import DeviceItem
from devices_module.models import DeviceEntity
from devices_module.repositories import DevicesRepository

# Tests libs
from tests.pytests.tests import DbTestCase


class TestDeviceEntity(DbTestCase):

    @inject
    def test_create_entity(self, device_repository: DevicesRepository) -> None:
        device_item = device_repository.get_by_id(
            device_id=uuid.UUID("26d7a945-ba29-471e-9e3c-304ef0acb199", version=4),
        )

        self.assertIsNone(device_item)

        with patch.object(Publisher, "publish") as MockPublisher:
            device_entity = self.__create_entity()

        MockPublisher.assert_called_once()

        self.assertIsInstance(device_entity, DeviceEntity)
        self.assertEqual("26d7a945-ba29-471e-9e3c-304ef0acb199", device_entity.device_id.__str__())
        self.assertEqual("device-identifier", device_entity.identifier)
        self.assertEqual("New device name", device_entity.name)
        self.assertFalse(device_entity.enabled)
        self.assertIsNotNone(device_entity.key)
        self.assertIsNotNone(device_entity.created_at)

        device_item = device_repository.get_by_id(
            device_id=uuid.UUID("26d7a945-ba29-471e-9e3c-304ef0acb199", version=4),
        )

        self.assertIsInstance(device_item, DeviceItem)

    # -----------------------------------------------------------------------------

    @staticmethod
    @orm.db_session
    def __create_entity() -> DeviceEntity:
        device_entity = DeviceEntity(
            device_id=uuid.UUID("26d7a945-ba29-471e-9e3c-304ef0acb199", version=4),
            identifier="device-identifier",
            name="New device name",
        )

        return device_entity
