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

"""
Devices module exchange module
"""

# Python base dependencies
from abc import ABC, abstractmethod
from typing import Dict, Optional

# Library dependencies
from metadata.routing import RoutingKey
from metadata.types import ModuleOrigin


class IPublisher(ABC):  # pylint: disable=too-few-public-methods
    """
    Data exchange publisher interface

    @package        FastyBird:DevicesModule!
    @module         exchange

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    @abstractmethod
    def publish(
        self,
        origin: ModuleOrigin,
        routing_key: RoutingKey,
        data: Optional[Dict],
    ) -> None:
        """Publish data to exchange bus"""
