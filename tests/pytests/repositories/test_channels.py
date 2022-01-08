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
from modules_metadata.routing import RoutingKey

# Library libs
from devices_module.entities.channel import ChannelEntity
from devices_module.repositories.channel import ChannelsRepository

# Tests libs
from tests.pytests.tests import DbTestCase


class TestChannelsRepository(DbTestCase):
    @inject
    def test_repository_iterator(self, channel_repository: ChannelsRepository) -> None:
        self.assertEqual(3, len(channel_repository.get_all()))

    # -----------------------------------------------------------------------------

    @inject
    def test_get_item(self, channel_repository: ChannelsRepository) -> None:
        entity = channel_repository.get_by_id(channel_id=uuid.UUID("17c59dfa-2edd-438e-8c49-faa4e38e5a5e", version=4))

        self.assertIsInstance(entity, ChannelEntity)
        self.assertEqual("bLikxh", entity.key)

    # -----------------------------------------------------------------------------

    @inject
    def test_transform_to_dict(self, channel_repository: ChannelsRepository) -> None:
        entity = channel_repository.get_by_id(channel_id=uuid.UUID("17c59dfa-2edd-438e-8c49-faa4e38e5a5e", version=4))

        self.assertIsInstance(entity, ChannelEntity)
        self.assertEqual(
            {
                "id": "17c59dfa-2edd-438e-8c49-faa4e38e5a5e",
                "identifier": "channel-one",
                "key": "bLikxh",
                "name": "Channel one",
                "comment": None,
                "device": "69786d15-fd0c-4d9f-9378-33287c2009fa",
                "owner": "455354e8-96bd-4c29-84e7-9f10e1d4db4b",
            },
            entity.to_dict(),
        )
        self.assertIsInstance(
            self.validate_exchange_data(
                routing_key=RoutingKey.CHANNELS_ENTITY_REPORTED,
                data=entity.to_dict(),
            ),
            dict,
        )
