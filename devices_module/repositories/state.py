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
Devices module state repositories module
"""

# Python base dependencies
import uuid
from abc import ABC, abstractmethod
from typing import Optional

# Library libs
from devices_module.state.property import ChannelPropertyState, DevicePropertyState


class IDevicePropertyStateRepository(ABC):
    """
    State repository for device property

    @package        FastyBird:DevicesModule!
    @module         repositories/state

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    @abstractmethod
    def get_by_id(self, property_id: uuid.UUID) -> Optional[DevicePropertyState]:
        """Find device property state record by provided database identifier"""


class IChannelPropertyStateRepository(ABC):
    """
    State repository for channel property

    @package        FastyBird:DevicesModule!
    @module         repositories/state

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    @abstractmethod
    def get_by_id(self, property_id: uuid.UUID) -> Optional[ChannelPropertyState]:
        """Find channel property state record by provided database identifier"""
