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
from devices_module.items import ConnectorItem
from devices_module.repositories import connector_repository
from modules_metadata.routing import RoutingKey

# Tests libs
from tests.pytests.tests import DbTestCase


class TestConnectorsRepository(DbTestCase):
    def test_repository_iterator(self) -> None:
        connector_repository.initialize()

        self.assertEqual(1, len(connector_repository))

    # -----------------------------------------------------------------------------

    def test_get_item(self) -> None:
        connector_repository.initialize()

        connector_item = connector_repository.get_by_id(
            uuid.UUID("17c59dfa-2edd-438e-8c49-faa4e38e5a5e", version=4)
        )

        self.assertIsInstance(connector_item, ConnectorItem)
        self.assertEqual("bLikvZ", connector_item.key)

    # -----------------------------------------------------------------------------

    def test_create_from_exchange(self) -> None:
        connector_repository.initialize()

        result: bool = connector_repository.create_from_exchange(
            RoutingKey(RoutingKey.CONNECTOR_ENTITY_CREATED),
            {
                "id": "17c59dfa-2edd-438e-8c49-faa4e38e5a5e",
                "type": "fb-mqtt-v1",
                "key": "bLikvZ",
                "name": "FB MQTT v1",
                "enabled": True,
                "control": [],
                "server": None,
                "port": None,
                "secured_port": None,
                "username": None,
            },
        )

        self.assertTrue(result)

        property_item = connector_repository.get_by_id(
            uuid.UUID("17c59dfa-2edd-438e-8c49-faa4e38e5a5e", version=4)
        )

        self.assertIsInstance(property_item, ConnectorItem)
        self.assertEqual("17c59dfa-2edd-438e-8c49-faa4e38e5a5e", property_item.connector_id.__str__())
        self.assertEqual({
            "id": "17c59dfa-2edd-438e-8c49-faa4e38e5a5e",
            "type": "fb-mqtt-v1",
            "key": "bLikvZ",
            "name": "FB MQTT v1",
            "enabled": True,
            "control": [],
            "server": None,
            "port": None,
            "secured_port": None,
            "username": None,
        }, property_item.to_dict())

    # -----------------------------------------------------------------------------

    def test_update_from_exchange(self) -> None:
        connector_repository.initialize()

        result: bool = connector_repository.update_from_exchange(
            RoutingKey(RoutingKey.CONNECTOR_ENTITY_UPDATED),
            {
                "id": "17c59dfa-2edd-438e-8c49-faa4e38e5a5e",
                "type": "fb-mqtt-v1",
                "key": "bLikvZ",
                "name": "Renamed",
                "enabled": False,
                "control": ["reset"],
                "server": "127.0.0.1",
                "port": 1883,
                "secured_port": None,
                "username": "username",
            },
        )

        self.assertTrue(result)

        property_item = connector_repository.get_by_id(
            uuid.UUID("17c59dfa-2edd-438e-8c49-faa4e38e5a5e", version=4)
        )

        self.assertIsInstance(property_item, ConnectorItem)
        self.assertEqual("17c59dfa-2edd-438e-8c49-faa4e38e5a5e", property_item.connector_id.__str__())
        self.assertEqual({
            "id": "17c59dfa-2edd-438e-8c49-faa4e38e5a5e",
            "type": "fb-mqtt-v1",
            "key": "bLikvZ",
            "name": "Renamed",
            "enabled": False,
            "control": ["reset"],
            "server": "127.0.0.1",
            "port": 1883,
            "secured_port": None,
            "username": "username",
        }, property_item.to_dict())

    # -----------------------------------------------------------------------------

    def test_delete_from_exchange(self) -> None:
        connector_repository.initialize()

        property_item = connector_repository.get_by_id(
            uuid.UUID("17c59dfa-2edd-438e-8c49-faa4e38e5a5e", version=4)
        )

        self.assertIsInstance(property_item, ConnectorItem)
        self.assertEqual("17c59dfa-2edd-438e-8c49-faa4e38e5a5e", property_item.connector_id.__str__())

        result: bool = connector_repository.delete_from_exchange(
            RoutingKey(RoutingKey.CONNECTOR_ENTITY_DELETED),
            {
                "id": "17c59dfa-2edd-438e-8c49-faa4e38e5a5e",
                "type": "fb-mqtt-v1",
                "key": "bLikvZ",
                "name": "FB MQTT v1",
                "enabled": True,
                "control": [],
                "server": None,
                "port": None,
                "secured_port": None,
                "username": None,
            },
        )

        self.assertTrue(result)

        property_item = connector_repository.get_by_id(
            uuid.UUID("17c59dfa-2edd-438e-8c49-faa4e38e5a5e", version=4)
        )

        self.assertIsNone(property_item)
