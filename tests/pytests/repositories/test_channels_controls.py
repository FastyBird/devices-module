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
from devices_module.items import ChannelControlItem
from devices_module.repositories import ChannelsControlsRepository
from modules_metadata.routing import RoutingKey

# Tests libs
from tests.pytests.tests import DbTestCase


class TestChannelsControlsRepository(DbTestCase):
    @inject
    def test_repository_iterator(self, control_repository: ChannelsControlsRepository) -> None:
        control_repository.initialize()

        self.assertEqual(2, len(control_repository))

    # -----------------------------------------------------------------------------

    @inject
    def test_get_item(self, control_repository: ChannelsControlsRepository) -> None:
        control_repository.initialize()

        control_item = control_repository.get_by_id(
            uuid.UUID("15db9bef-3b57-4a87-bf67-e3c19fc3ba34", version=4)
        )

        self.assertIsInstance(control_item, ChannelControlItem)
        self.assertEqual("configure", control_item.name)

    # -----------------------------------------------------------------------------

    @inject
    def test_create_from_exchange(self, control_repository: ChannelsControlsRepository) -> None:
        control_repository.initialize()

        result: bool = control_repository.create_from_exchange(
            RoutingKey(RoutingKey.CHANNELS_CONTROL_ENTITY_CREATED),
            {
                "id": "15db9bef-3b57-4a87-bf67-e3c19fc3ba34",
                "name": "configure",
                "channel": "17c59dfa-2edd-438e-8c49-faa4e38e5a5e",
            },
        )

        self.assertTrue(result)

        control_item = control_repository.get_by_id(
            uuid.UUID("15db9bef-3b57-4a87-bf67-e3c19fc3ba34", version=4)
        )

        self.assertIsInstance(control_item, ChannelControlItem)
        self.assertEqual("15db9bef-3b57-4a87-bf67-e3c19fc3ba34", control_item.control_id.__str__())
        self.assertEqual({
            "id": "15db9bef-3b57-4a87-bf67-e3c19fc3ba34",
            "name": "configure",
            "channel": "17c59dfa-2edd-438e-8c49-faa4e38e5a5e",
        }, control_item.to_dict())

    # -----------------------------------------------------------------------------

    @inject
    def test_update_from_exchange(self, control_repository: ChannelsControlsRepository) -> None:
        control_repository.initialize()

        result: bool = control_repository.update_from_exchange(
            RoutingKey(RoutingKey.CHANNELS_CONTROL_ENTITY_UPDATED),
            {
                "id": "15db9bef-3b57-4a87-bf67-e3c19fc3ba34",
                "name": "not edited name",
                "channel": "17c59dfa-2edd-438e-8c49-faa4e38e5a5e",
            },
        )

        self.assertTrue(result)

        control_item = control_repository.get_by_id(
            uuid.UUID("15db9bef-3b57-4a87-bf67-e3c19fc3ba34", version=4)
        )

        self.assertIsInstance(control_item, ChannelControlItem)
        self.assertEqual("15db9bef-3b57-4a87-bf67-e3c19fc3ba34", control_item.control_id.__str__())
        self.assertEqual({
            "id": "15db9bef-3b57-4a87-bf67-e3c19fc3ba34",
            "name": "configure",
            "channel": "17c59dfa-2edd-438e-8c49-faa4e38e5a5e",
        }, control_item.to_dict())

    # -----------------------------------------------------------------------------

    @inject
    def test_delete_from_exchange(self, control_repository: ChannelsControlsRepository) -> None:
        control_repository.initialize()

        control_item = control_repository.get_by_id(
            uuid.UUID("15db9bef-3b57-4a87-bf67-e3c19fc3ba34", version=4)
        )

        self.assertIsInstance(control_item, ChannelControlItem)
        self.assertEqual("15db9bef-3b57-4a87-bf67-e3c19fc3ba34", control_item.control_id.__str__())

        result: bool = control_repository.delete_from_exchange(
            RoutingKey(RoutingKey.CHANNELS_CONTROL_ENTITY_DELETED),
            {
                "id": "15db9bef-3b57-4a87-bf67-e3c19fc3ba34",
                "name": "configure",
                "channel": "17c59dfa-2edd-438e-8c49-faa4e38e5a5e",
            },
        )

        self.assertTrue(result)

        control_item = control_repository.get_by_id(
            uuid.UUID("15db9bef-3b57-4a87-bf67-e3c19fc3ba34", version=4)
        )

        self.assertIsNone(control_item)
