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
from devices_module.models import connector_repository

# Tests libs
from tests.pytests.tests import DbTestCase


class TestConnectorsRepository(DbTestCase):
    def test_repository_iterator(self) -> None:
        self.assertEqual(1, len(connector_repository))

    # -----------------------------------------------------------------------------

    def test_get_item(self) -> None:
        connector_item = connector_repository.get_connector_by_id(
            uuid.UUID("17c59dfa-2edd-438e-8c49-faa4e38e5a5e", version=4)
        )

        self.assertIsInstance(connector_item, ConnectorItem)
        self.assertEqual("bLikvZ", connector_item.key)
