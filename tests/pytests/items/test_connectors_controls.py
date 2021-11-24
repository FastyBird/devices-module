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
from devices_module.items import ConnectorControlItem
from devices_module.repositories import ConnectorsControlsRepository

# Tests libs
from tests.pytests.tests import DbTestCase


class TestConnectorControlItem(DbTestCase):
    @inject
    def test_transform_to_dict(self, control_repository: ConnectorsControlsRepository) -> None:
        control_item = control_repository.get_by_id(
            uuid.UUID("7c055b2b-60c3-4017-93db-e9478d8aa662", version=4)
        )

        self.assertIsInstance(control_item, ConnectorControlItem)

        self.assertEqual({
            "id": "7c055b2b-60c3-4017-93db-e9478d8aa662",
            "name": "search",
            "connector": "17c59dfa-2edd-438e-8c49-faa4e38e5a5e",
        }, control_item.to_dict())
