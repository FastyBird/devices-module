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
from unittest.mock import patch

# Library dependencies
from exchange_plugin.publisher import Publisher
from kink import inject

# Library libs
from devices_module.entities.connector import ConnectorEntity, FbBusConnectorEntity, FbMqttConnectorEntity
from devices_module.managers.connector import ConnectorsManager
from devices_module.repositories.connector import ConnectorsRepository

# Tests libs
from tests.pytests.tests import DbTestCase


class TestDevicesManager(DbTestCase):
    @inject
    def test_create_entity(
        self,
        connector_repository: ConnectorsRepository,
        connectors_manager: ConnectorsManager,
    ) -> None:
        with patch.object(Publisher, "publish") as MockPublisher:
            connector_entity = connectors_manager.create(
                data={
                    "name": "FB Bus connector",
                    "enabled": False,
                    "id": uuid.UUID("26d7a945-ba29-471e-9e3c-304ef0acb199", version=4),
                },
                connector_type=FbBusConnectorEntity,
            )

        MockPublisher.assert_called_once()

        self.assertIsInstance(connector_entity, FbBusConnectorEntity)
        self.assertEqual("26d7a945-ba29-471e-9e3c-304ef0acb199", connector_entity.id.__str__())
        self.assertEqual("FB Bus connector", connector_entity.name)
        self.assertFalse(connector_entity.enabled)
        self.assertIsNotNone(connector_entity.key)
        self.assertIsNotNone(connector_entity.created_at)

        entity = connector_repository.get_by_id(
            connector_id=uuid.UUID("26d7a945-ba29-471e-9e3c-304ef0acb199", version=4),
        )

        self.assertIsInstance(entity, ConnectorEntity)

    # -----------------------------------------------------------------------------

    @inject
    def test_update_entity(
        self,
        connector_repository: ConnectorsRepository,
        connectors_manager: ConnectorsManager,
    ) -> None:
        connector = connector_repository.get_by_id(
            connector_id=uuid.UUID("17c59dfa-2edd-438e-8c49-faa4e38e5a5e", version=4),
        )

        with patch.object(Publisher, "publish") as MockPublisher:
            connector_entity = connectors_manager.update(
                connector=connector,
                data={
                    "enabled": False,
                    "server": "mqtt.server.com",
                },
            )

        MockPublisher.assert_called()

        self.assertIsInstance(connector_entity, FbMqttConnectorEntity)
        self.assertEqual("17c59dfa-2edd-438e-8c49-faa4e38e5a5e", connector_entity.id.__str__())
        self.assertEqual("FB MQTT", connector_entity.name)
        self.assertEqual("mqtt.server.com", connector_entity.server)
        self.assertFalse(connector_entity.enabled)

        entity = connector_repository.get_by_id(
            connector_id=uuid.UUID("17c59dfa-2edd-438e-8c49-faa4e38e5a5e", version=4),
        )

        self.assertIsInstance(entity, FbMqttConnectorEntity)