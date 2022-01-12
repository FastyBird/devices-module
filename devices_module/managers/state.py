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
Devices module device states managers module
"""

# Python base dependencies
from abc import abstractmethod
from typing import Dict, Union

# Library libs
from devices_module.entities.channel import ChannelEntity
from devices_module.entities.device import DeviceEntity
from devices_module.state.property import IChannelPropertyState, IDevicePropertyState


class IDeviceStatesManager:
    """
    Devices states manager

    @package        FastyBird:DevicesModule!
    @module         managers/state

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    @abstractmethod
    def create(
        self,
        device: DeviceEntity,
        data: Dict[str, Union[str, int, float, bool, None]],
    ) -> IDevicePropertyState:
        """Create new device property state record"""

    # -----------------------------------------------------------------------------

    @abstractmethod
    def update(
        self,
        state: IDevicePropertyState,
        data: Dict[str, Union[str, int, float, bool, None]],
    ) -> IDevicePropertyState:
        """Update existing device property state record"""

    # -----------------------------------------------------------------------------

    @abstractmethod
    def delete(self, state: IDevicePropertyState) -> bool:
        """Delete existing device property state"""


class IChannelStatesManager:
    """
    Devices states manager

    @package        FastyBird:DevicesModule!
    @module         managers/state

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    @abstractmethod
    def create(
        self,
        channel: ChannelEntity,
        data: Dict[str, Union[str, int, float, bool, None]],
    ) -> IChannelPropertyState:
        """Create new channel property state record"""

    # -----------------------------------------------------------------------------

    @abstractmethod
    def update(
        self,
        state: IChannelPropertyState,
        data: Dict[str, Union[str, int, float, bool, None]],
    ) -> IChannelPropertyState:
        """Update existing channel property state record"""

    # -----------------------------------------------------------------------------

    @abstractmethod
    def delete(self, state: IChannelPropertyState) -> bool:
        """Delete existing channel property state"""
