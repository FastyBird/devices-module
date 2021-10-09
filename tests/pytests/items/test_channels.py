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
from devices_module.repositories import ChannelsRepository
from devices_module.items import ChannelItem

# Tests libs
from tests.pytests.tests import DbTestCase


class TestChannelItem(DbTestCase):
    @inject
    def test_transform_to_dict(self, channel_repository: ChannelsRepository) -> None:
        channel_repository.initialize()

        channel_item = channel_repository.get_by_id(
            uuid.UUID("17c59dfa-2edd-438e-8c49-faa4e38e5a5e", version=4)
        )

        self.assertIsInstance(channel_item, ChannelItem)

        self.assertEqual({
            "id": "17c59dfa-2edd-438e-8c49-faa4e38e5a5e",
            "identifier": "channel-one",
            "key": "bLikxh",
            "name": "Channel one",
            "comment": None,
            "device": "69786d15-fd0c-4d9f-9378-33287c2009fa",
        }, channel_item.to_dict())
