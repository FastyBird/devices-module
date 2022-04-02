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
Devices module device entities module
"""

# Python base dependencies
import re
import uuid
from abc import abstractmethod
from datetime import datetime
from typing import Dict, List, Optional, Tuple, Union

# Library dependencies
from fastybird_metadata.devices_module import (
    DeviceModel,
    FirmwareManufacturer,
    HardwareManufacturer,
    PropertyType,
)
from fastybird_metadata.types import ButtonPayload, DataType, SwitchPayload
from sqlalchemy import (
    BINARY,
    BOOLEAN,
    JSON,
    TEXT,
    VARCHAR,
    Column,
    ForeignKey,
    Index,
    UniqueConstraint,
)
from sqlalchemy.orm import backref, relationship

# Library libs
import fastybird_devices_module.entities  # pylint: disable=unused-import
from fastybird_devices_module.entities.base import (
    Base,
    EntityCreatedMixin,
    EntityUpdatedMixin,
)
from fastybird_devices_module.entities.property import PropertyMixin
from fastybird_devices_module.exceptions import InvalidArgumentException


class DevicesChildrenEntity(Base):
    """
    Device parent child relation entity

    @package        FastyBird:DevicesModule!
    @module         entities/device

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    __tablename__: str = "fb_devices_module_devices_children"

    col_parent_device: bytes = Column(  # type: ignore[assignment]
        BINARY(16),
        ForeignKey("fb_devices_module_devices.device_id"),
        name="parent_device",
        primary_key=True,
    )
    col_child_device: bytes = Column(  # type: ignore[assignment]
        BINARY(16),
        ForeignKey("fb_devices_module_devices.device_id"),
        name="child_device",
        primary_key=True,
    )


class DeviceEntity(EntityCreatedMixin, EntityUpdatedMixin, Base):  # pylint: disable=too-many-instance-attributes
    """
    Device entity

    @package        FastyBird:DevicesModule!
    @module         entities/device

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    __tablename__: str = "fb_devices_module_devices"

    __table_args__ = (
        Index("device_identifier_idx", "device_identifier"),
        Index("device_name_idx", "device_name"),
        Index("device_enabled_idx", "device_enabled"),
        UniqueConstraint("device_identifier", "connector_id", name="device_identifier_connector_unique"),
        {
            "mysql_engine": "InnoDB",
            "mysql_collate": "utf8mb4_general_ci",
            "mysql_charset": "utf8mb4",
            "mysql_comment": "Devices",
        },
    )

    col_type: str = Column(VARCHAR(40), name="device_type", nullable=False)  # type: ignore[assignment]

    col_device_id: bytes = Column(  # type: ignore[assignment]
        BINARY(16), primary_key=True, name="device_id", default=uuid.uuid4
    )
    col_identifier: str = Column(VARCHAR(50), name="device_identifier", nullable=False)  # type: ignore[assignment]
    col_name: Optional[str] = Column(  # type: ignore[assignment]
        VARCHAR(255), name="device_name", nullable=True, default=None
    )
    col_comment: Optional[str] = Column(  # type: ignore[assignment]
        TEXT, name="device_comment", nullable=True, default=None
    )
    col_enabled: bool = Column(BOOLEAN, name="device_enabled", nullable=False, default=True)  # type: ignore[assignment]

    col_hardware_manufacturer: str = Column(  # type: ignore[assignment]
        VARCHAR(150), name="device_hardware_manufacturer", nullable=False, default="generic"
    )
    col_hardware_model: str = Column(  # type: ignore[assignment]
        VARCHAR(150), name="device_hardware_model", nullable=False, default="custom"
    )
    col_hardware_version: Optional[str] = Column(  # type: ignore[assignment]
        VARCHAR(150), name="device_hardware_version", nullable=True, default=None
    )
    col_hardware_mac_address: Optional[str] = Column(  # type: ignore[assignment]
        VARCHAR(50), name="device_hardware_mac_address", nullable=True, default=None
    )

    col_firmware_manufacturer: str = Column(  # type: ignore[assignment]
        VARCHAR(150), name="device_firmware_manufacturer", nullable=False, default="generic"
    )
    col_firmware_version: Optional[str] = Column(  # type: ignore[assignment]
        VARCHAR(150), name="device_firmware_version", nullable=True, default=None
    )

    col_owner: Optional[str] = Column(  # type: ignore[assignment]
        VARCHAR(50), name="owner", nullable=True, default=None
    )

    col_params: Optional[Dict] = Column(JSON, name="params", nullable=True)  # type: ignore[assignment]

    connector_id: Optional[bytes] = Column(  # type: ignore[assignment]  # pylint: disable=unused-private-member
        BINARY(16),
        ForeignKey("fb_devices_module_connectors.connector_id", ondelete="CASCADE"),
        name="connector_id",
        nullable=False,
    )

    parents: List["DeviceEntity"] = relationship(  # type: ignore[assignment]
        "DeviceEntity",
        secondary="fb_devices_module_devices_children",
        primaryjoin=col_device_id == DevicesChildrenEntity.col_child_device,
        secondaryjoin=col_device_id == DevicesChildrenEntity.col_parent_device,
        back_populates="children",
    )
    children: List["DeviceEntity"] = relationship(  # type: ignore[assignment]
        "DeviceEntity",
        secondary="fb_devices_module_devices_children",
        primaryjoin=col_device_id == DevicesChildrenEntity.col_parent_device,
        secondaryjoin=col_device_id == DevicesChildrenEntity.col_child_device,
        back_populates="parents",
    )

    properties: List["DevicePropertyEntity"] = relationship(  # type: ignore[assignment]
        "DevicePropertyEntity",
        back_populates="device",
        cascade="delete, delete-orphan",
    )
    controls: List["DeviceControlEntity"] = relationship(  # type: ignore[assignment]
        "DeviceControlEntity",
        back_populates="device",
        cascade="delete, delete-orphan",
    )

    channels: List["entities.channel.ChannelEntity"] = relationship(  # type: ignore[assignment,name-defined]
        "entities.channel.ChannelEntity", back_populates="device", cascade="all, delete-orphan"
    )

    connector: "entities.connector.ConnectorEntity" = relationship(  # type: ignore[name-defined]
        "entities.connector.ConnectorEntity",
        back_populates="devices",
    )

    __mapper_args__ = {
        "polymorphic_identity": "device",
        "polymorphic_on": col_type,
    }

    # -----------------------------------------------------------------------------

    def __init__(self, identifier: str, name: Optional[str] = None, device_id: Optional[uuid.UUID] = None) -> None:
        super().__init__()

        self.col_device_id = device_id.bytes if device_id is not None else uuid.uuid4().bytes

        self.col_identifier = identifier
        self.col_name = name

        self.col_hardware_manufacturer = HardwareManufacturer.GENERIC.value
        self.col_hardware_model = DeviceModel.CUSTOM.value

        self.col_firmware_manufacturer = FirmwareManufacturer.GENERIC.value

    # -----------------------------------------------------------------------------

    @property
    @abstractmethod
    def type(self) -> str:
        """Device type"""

    # -----------------------------------------------------------------------------

    @property
    def id(self) -> uuid.UUID:  # pylint: disable=invalid-name
        """Device unique identifier"""
        return uuid.UUID(bytes=self.col_device_id)

    # -----------------------------------------------------------------------------

    @property
    def identifier(self) -> str:
        """Device unique key"""
        return self.col_identifier

    # -----------------------------------------------------------------------------

    @property
    def name(self) -> Optional[str]:
        """Device name"""
        return self.col_name

    # -----------------------------------------------------------------------------

    @name.setter
    def name(self, name: Optional[str]) -> None:
        """Device name setter"""
        self.col_name = name

    # -----------------------------------------------------------------------------

    @property
    def comment(self) -> Optional[str]:
        """Device comment"""
        return self.col_comment

    # -----------------------------------------------------------------------------

    @comment.setter
    def comment(self, comment: Optional[str]) -> None:
        """Device comment setter"""
        self.col_comment = comment

    # -----------------------------------------------------------------------------

    @property
    def enabled(self) -> bool:
        """Device enabled status"""
        return self.col_enabled

    # -----------------------------------------------------------------------------

    @enabled.setter
    def enabled(self, enabled: bool) -> None:
        """Device enabled setter"""
        self.col_enabled = enabled

    # -----------------------------------------------------------------------------

    @property
    def hardware_manufacturer(self) -> Union[str, HardwareManufacturer]:
        """Device hardware manufacturer"""
        if HardwareManufacturer.has_value(self.col_hardware_manufacturer):
            return HardwareManufacturer(self.col_hardware_manufacturer)

        return self.col_hardware_manufacturer

    # -----------------------------------------------------------------------------

    @hardware_manufacturer.setter
    def hardware_manufacturer(self, hardware_manufacturer: Union[str, HardwareManufacturer]) -> None:
        """Device hardware manufacturer setter"""
        if isinstance(hardware_manufacturer, HardwareManufacturer):
            self.col_hardware_manufacturer = hardware_manufacturer.value

        else:
            self.col_hardware_manufacturer = hardware_manufacturer.lower()

    # -----------------------------------------------------------------------------

    @property
    def hardware_model(self) -> Union[str, DeviceModel]:
        """Device hardware model"""
        if HardwareManufacturer.has_value(self.col_hardware_model):
            return DeviceModel(self.col_hardware_model)

        return self.col_hardware_model

    # -----------------------------------------------------------------------------

    @hardware_model.setter
    def hardware_model(self, hardware_model: Union[str, DeviceModel]) -> None:
        """Device hardware model setter"""
        if isinstance(hardware_model, DeviceModel):
            self.col_hardware_model = hardware_model.value

        else:
            self.col_hardware_model = hardware_model.lower()

    # -----------------------------------------------------------------------------

    @property
    def hardware_version(self) -> Optional[str]:
        """Device hardware version"""
        return self.col_hardware_version

    # -----------------------------------------------------------------------------

    @hardware_version.setter
    def hardware_version(self, hardware_version: Optional[str]) -> None:
        """Device hardware version setter"""
        self.col_hardware_version = hardware_version.lower() if hardware_version is not None else None

    # -----------------------------------------------------------------------------

    @property
    def hardware_mac_address(self) -> Optional[str]:
        """Device hardware MAC address"""
        if self.col_hardware_mac_address is None:
            return None

        return ":".join(
            [
                self.col_hardware_mac_address[index : (index + 2)]
                for index in range(0, len(self.col_hardware_mac_address), 2)
            ]
        )

    # -----------------------------------------------------------------------------

    @hardware_mac_address.setter
    def hardware_mac_address(self, hardware_mac_address: Optional[str]) -> None:
        """Device hardware MAC address setter"""
        if (
            hardware_mac_address is not None
            and len(re.findall("^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$", hardware_mac_address)) == 0
            and len(re.findall("^([0-9A-Fa-f]{12})$", hardware_mac_address)) == 0
        ):
            raise InvalidArgumentException("Provided mac address is not in valid format")

        if hardware_mac_address is not None:
            self.col_hardware_mac_address = hardware_mac_address.replace(":", "").replace("-", "").lower()

        else:
            self.col_hardware_mac_address = None

    # -----------------------------------------------------------------------------

    @property
    def firmware_manufacturer(self) -> Union[str, FirmwareManufacturer]:
        """Device firmware manufacturer"""
        if FirmwareManufacturer.has_value(self.col_firmware_manufacturer):
            return FirmwareManufacturer(self.col_firmware_manufacturer)

        return self.col_firmware_manufacturer

    # -----------------------------------------------------------------------------

    @firmware_manufacturer.setter
    def firmware_manufacturer(self, firmware_manufacturer: Union[str, FirmwareManufacturer]) -> None:
        """Device firmware manufacturer setter"""
        if isinstance(firmware_manufacturer, FirmwareManufacturer):
            self.col_firmware_manufacturer = firmware_manufacturer.value

        else:
            self.col_firmware_manufacturer = firmware_manufacturer.lower()

    # -----------------------------------------------------------------------------

    @property
    def firmware_version(self) -> Optional[str]:
        """Device firmware version"""
        return self.col_firmware_version

    # -----------------------------------------------------------------------------

    @firmware_version.setter
    def firmware_version(self, firmware_version: Optional[str]) -> None:
        """Device firmware version setter"""
        self.col_firmware_version = firmware_version.lower() if firmware_version is not None else None

    # -----------------------------------------------------------------------------

    @property
    def owner(self) -> Optional[str]:
        """Device owner identifier"""
        return self.col_owner

    # -----------------------------------------------------------------------------

    @owner.setter
    def owner(self, owner: Optional[str]) -> None:
        """Device owner identifier setter"""
        self.col_owner = owner

    # -----------------------------------------------------------------------------

    @property
    def params(self) -> Dict:
        """Device params"""
        return self.col_params if self.col_params is not None else {}

    # -----------------------------------------------------------------------------

    @params.setter
    def params(self, params: Optional[Dict]) -> None:
        """Device params"""
        self.col_params = params

    # -----------------------------------------------------------------------------

    def to_dict(self) -> Dict[str, Union[str, int, bool, List[str], Dict, None]]:
        """Transform entity to dictionary"""
        parents: List[str] = []

        for parent in self.parents:
            parents.append(parent.id.__str__())

        children: List[str] = []

        for child in self.children:
            children.append(child.id.__str__())

        return {
            "id": self.id.__str__(),
            "type": self.type,
            "identifier": self.identifier,
            "name": self.name,
            "comment": self.comment,
            "enabled": self.enabled,
            "hardware_manufacturer": self.hardware_manufacturer.value
            if isinstance(self.hardware_manufacturer, HardwareManufacturer)
            else self.hardware_manufacturer,
            "hardware_model": self.hardware_model.value
            if isinstance(self.hardware_model, DeviceModel)
            else self.hardware_model,
            "hardware_version": self.hardware_version,
            "hardware_mac_address": self.hardware_mac_address,
            "firmware_manufacturer": self.firmware_manufacturer.value
            if isinstance(self.firmware_manufacturer, FirmwareManufacturer)
            else self.firmware_manufacturer,
            "firmware_version": self.firmware_version,
            "connector": uuid.UUID(bytes=self.connector_id).__str__(),
            "parents": parents,
            "children": children,
            "owner": self.owner,
        }


class BlankDeviceEntity(DeviceEntity):
    """
    Blank device entity

    @package        FastyBird:DevicesModule!
    @module         entities/device

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    __mapper_args__ = {"polymorphic_identity": "blank"}

    # -----------------------------------------------------------------------------

    @property
    def type(self) -> str:
        """Device type"""
        return "blank"


class DevicePropertyEntity(EntityCreatedMixin, EntityUpdatedMixin, PropertyMixin, Base):
    """
    Device property entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    __tablename__: str = "fb_devices_module_devices_properties"

    __table_args__ = (
        Index("property_identifier_idx", "property_identifier"),
        Index("property_settable_idx", "property_settable"),
        Index("property_queryable_idx", "property_queryable"),
        UniqueConstraint("property_identifier", "device_id", name="property_identifier_unique"),
        {
            "mysql_engine": "InnoDB",
            "mysql_collate": "utf8mb4_general_ci",
            "mysql_charset": "utf8mb4",
            "mysql_comment": "Devices properties",
        },
    )

    col_type: str = Column(VARCHAR(20), name="property_type", nullable=False)  # type: ignore[assignment]

    col_property_id: bytes = Column(  # type: ignore[assignment]
        BINARY(16), primary_key=True, name="property_id", default=uuid.uuid4
    )

    parent_id: Optional[bytes] = Column(  # type: ignore[assignment]  # pylint: disable=unused-private-member
        BINARY(16),
        ForeignKey("fb_devices_module_devices_properties.property_id", ondelete="SET NULL"),
        name="parent_id",
        nullable=True,
    )

    device_id: bytes = Column(  # type: ignore[assignment]  # pylint: disable=unused-private-member
        BINARY(16), ForeignKey("fb_devices_module_devices.device_id"), name="device_id", nullable=False
    )

    device: DeviceEntity = relationship(DeviceEntity, back_populates="properties")  # type: ignore[assignment]

    children: List["DevicePropertyEntity"] = relationship(  # type: ignore[assignment]
        "DevicePropertyEntity", backref=backref("parent", remote_side=[col_property_id])
    )

    __mapper_args__ = {
        "polymorphic_identity": "device_property",
        "polymorphic_on": col_type,
    }

    # -----------------------------------------------------------------------------

    def __init__(self, device: DeviceEntity, identifier: str, property_id: Optional[uuid.UUID] = None) -> None:
        super().__init__(identifier, property_id)

        self.device = device

    # -----------------------------------------------------------------------------

    @property
    @abstractmethod
    def type(self) -> PropertyType:
        """Property type"""

    # -----------------------------------------------------------------------------

    @property
    def settable(self) -> bool:
        """Property settable status"""
        if self.parent is not None and not self.type.__eq__(PropertyType.MAPPED):
            return self.parent.settable  # type: ignore[no-any-return]

        return super().settable

    # -----------------------------------------------------------------------------

    @settable.setter
    def settable(self, settable: bool) -> None:
        """Property settable setter"""
        if self.parent is not None and not self.type.__eq__(PropertyType.MAPPED):
            raise AttributeError("Settable setter is allowed only for parent")

        super(DevicePropertyEntity, type(self)).settable.fset(self, settable)  # type: ignore[attr-defined]

    # -----------------------------------------------------------------------------

    @property
    def queryable(self) -> bool:
        """Property queryable status"""
        if self.parent is not None and not self.type.__eq__(PropertyType.MAPPED):
            return self.parent.queryable  # type: ignore[no-any-return]

        return super().queryable

    # -----------------------------------------------------------------------------

    @queryable.setter
    def queryable(self, queryable: bool) -> None:
        """Property queryable setter"""
        if self.parent is not None and not self.type.__eq__(PropertyType.MAPPED):
            raise AttributeError("Queryable setter is allowed only for parent")

        super(DevicePropertyEntity, type(self)).queryable.fset(self, queryable)  # type: ignore[attr-defined]

    # -----------------------------------------------------------------------------

    @property
    def data_type(self) -> DataType:
        """Transform data type to enum value"""
        if self.parent is not None and not self.type.__eq__(PropertyType.MAPPED):
            return self.parent.data_type  # type: ignore[no-any-return]

        return super().data_type

    # -----------------------------------------------------------------------------

    @data_type.setter
    def data_type(self, data_type: DataType) -> None:
        """Data type setter"""
        if self.parent is not None and not self.type.__eq__(PropertyType.MAPPED):
            raise AttributeError("Data type setter is allowed only for parent")

        super(DevicePropertyEntity, type(self)).data_type.fset(self, data_type)  # type: ignore[attr-defined]

    # -----------------------------------------------------------------------------

    @property
    def unit(self) -> Optional[str]:
        """Property unit"""
        if self.parent is not None and not self.type.__eq__(PropertyType.MAPPED):
            return self.parent.unit  # type: ignore[no-any-return]

        return super().unit

    # -----------------------------------------------------------------------------

    @unit.setter
    def unit(self, unit: Optional[str]) -> None:
        """Property unit setter"""
        if self.parent is not None and not self.type.__eq__(PropertyType.MAPPED):
            raise AttributeError("Unit setter is allowed only for parent")

        super(DevicePropertyEntity, type(self)).unit.fset(self, unit)  # type: ignore[attr-defined]

    # -----------------------------------------------------------------------------

    @property
    def format(
        self,
    ) -> Union[
        Tuple[Optional[int], Optional[int]],
        Tuple[Optional[float], Optional[float]],
        List[Union[str, Tuple[str, Optional[str], Optional[str]]]],
        None,
    ]:
        """Property format"""
        if self.parent is not None and not self.type.__eq__(PropertyType.MAPPED):
            return self.parent.format  # type: ignore[no-any-return]

        return super().format

    # -----------------------------------------------------------------------------

    @format.setter
    def format(
        self,
        value_format: Union[
            str,
            Tuple[Optional[int], Optional[int]],
            Tuple[Optional[float], Optional[float]],
            List[Union[str, Tuple[str, Optional[str], Optional[str]]]],
            None,
        ],
    ) -> None:
        """Property format setter"""
        if self.parent is not None and not self.type.__eq__(PropertyType.MAPPED):
            raise AttributeError("Value format setter is allowed only for parent")

        super(DevicePropertyEntity, type(self)).format.fset(self, value_format)  # type: ignore[attr-defined]

    # -----------------------------------------------------------------------------

    @property
    def invalid(self) -> Union[str, int, float, None]:
        """Property invalid value"""
        if self.parent is not None and not self.type.__eq__(PropertyType.MAPPED):
            return self.parent.invalid  # type: ignore[no-any-return]

        return super().invalid

    # -----------------------------------------------------------------------------

    @invalid.setter
    def invalid(self, invalid: str) -> None:
        """Property invalid value setter"""
        if self.parent is not None and not self.type.__eq__(PropertyType.MAPPED):
            raise AttributeError("Invalid value setter is allowed only for parent")

        super(DevicePropertyEntity, type(self)).invalid.fset(self, invalid)  # type: ignore[attr-defined]

    # -----------------------------------------------------------------------------

    @property
    def number_of_decimals(self) -> Optional[int]:
        """Property value number of decimals"""
        if self.parent is not None and not self.type.__eq__(PropertyType.MAPPED):
            return self.parent.number_of_decimals  # type: ignore[no-any-return]

        return super().number_of_decimals

    # -----------------------------------------------------------------------------

    @number_of_decimals.setter
    def number_of_decimals(self, number_of_decimals: Optional[int]) -> None:
        """Property value number of decimals setter"""
        if self.parent is not None and not self.type.__eq__(PropertyType.MAPPED):
            raise AttributeError("Number of decimals setter is allowed only for parent")

        super(DevicePropertyEntity, type(self)).number_of_decimals.fset(  # type: ignore[attr-defined]
            self, number_of_decimals
        )

    # -----------------------------------------------------------------------------

    @property
    def default(self) -> Union[int, float, str, bool, datetime, ButtonPayload, SwitchPayload, None]:
        """Property default"""
        if self.parent is not None and not self.type.__eq__(PropertyType.MAPPED):
            return self.parent.default  # type: ignore[no-any-return]

        return super().default

    # -----------------------------------------------------------------------------

    @default.setter
    def default(self, default: Optional[str]) -> None:
        """Property default value setter"""
        if self.parent is not None and not self.type.__eq__(PropertyType.MAPPED):
            raise AttributeError("Default value setter is allowed only for parent")

        super(DevicePropertyEntity, type(self)).default.fset(self, default)  # type: ignore[attr-defined]

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
    ) -> Dict[
        str,
        Union[
            int,
            float,
            str,
            bool,
            datetime,
            ButtonPayload,
            SwitchPayload,
            List[Union[str, Tuple[str, Optional[str], Optional[str]]]],
            List[str],
            Tuple[Optional[int], Optional[int]],
            Tuple[Optional[float], Optional[float]],
            None,
        ],
    ]:
        """Transform entity to dictionary"""
        children: List[str] = []

        for child in self.children:
            children.append(child.id.__str__())

        return {
            **super().to_dict(),
            **{
                "device": uuid.UUID(bytes=self.device_id).__str__(),
                "parent": uuid.UUID(bytes=self.parent_id).__str__() if self.parent_id is not None else None,
                "children": children,
                "owner": self.device.owner,
            },
        }


class DeviceDynamicPropertyEntity(DevicePropertyEntity):
    """
    Device property entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    __mapper_args__ = {"polymorphic_identity": "dynamic"}

    # -----------------------------------------------------------------------------

    @property
    def type(self) -> PropertyType:
        """Property type"""
        return PropertyType.DYNAMIC


class DeviceStaticPropertyEntity(DevicePropertyEntity):
    """
    Device property entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    __mapper_args__ = {"polymorphic_identity": "static"}

    # -----------------------------------------------------------------------------

    @property
    def type(self) -> PropertyType:
        """Property type"""
        return PropertyType.STATIC

    # -----------------------------------------------------------------------------

    @property
    def value(self) -> Union[int, float, str, bool, datetime, ButtonPayload, SwitchPayload, None]:
        """Property value"""
        if self.parent is not None:
            return self.parent.value  # type: ignore[no-any-return]

        return super().value

    # -----------------------------------------------------------------------------

    @value.setter
    def value(self, value: Optional[str]) -> None:
        """Property value number of decimals setter"""
        if self.parent is not None:
            raise AttributeError("Value setter is allowed only for parent")

        super(DeviceStaticPropertyEntity, type(self)).value.fset(self, value)  # type: ignore[attr-defined]

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
    ) -> Dict[
        str,
        Union[
            int,
            float,
            str,
            bool,
            datetime,
            ButtonPayload,
            SwitchPayload,
            List[Union[str, Tuple[str, Optional[str], Optional[str]]]],
            List[str],
            Tuple[Optional[int], Optional[int]],
            Tuple[Optional[float], Optional[float]],
            None,
        ],
    ]:
        """Transform entity to dictionary"""
        return {
            **super().to_dict(),
            **{
                "value": self.value,
                "default": self.default,
            },
        }


class DeviceMappedPropertyEntity(DevicePropertyEntity):
    """
    Device property entity

    @package        FastyBird:DevicesModule!
    @module         models

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    __mapper_args__ = {"polymorphic_identity": "mapped"}

    # -----------------------------------------------------------------------------

    @property
    def type(self) -> PropertyType:
        """Property type"""
        return PropertyType.MAPPED

    # -----------------------------------------------------------------------------

    def to_dict(
        self,
    ) -> Dict[
        str,
        Union[
            int,
            float,
            str,
            bool,
            datetime,
            ButtonPayload,
            SwitchPayload,
            List[Union[str, Tuple[str, Optional[str], Optional[str]]]],
            List[str],
            Tuple[Optional[int], Optional[int]],
            Tuple[Optional[float], Optional[float]],
            None,
        ],
    ]:
        """Transform entity to dictionary"""
        if isinstance(self.parent, DeviceStaticPropertyEntity):
            return {
                **super().to_dict(),
                **{
                    "value": self.value,
                    "default": self.default,
                },
            }

        return super().to_dict()


class DeviceControlEntity(EntityCreatedMixin, EntityUpdatedMixin, Base):
    """
    Device control entity

    @package        FastyBird:DevicesModule!
    @module         entities/device

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    __tablename__: str = "fb_devices_module_devices_controls"

    __table_args__ = (
        Index("control_name_idx", "control_name"),
        UniqueConstraint("control_name", "device_id", name="control_name_unique"),
        {
            "mysql_engine": "InnoDB",
            "mysql_collate": "utf8mb4_general_ci",
            "mysql_charset": "utf8mb4",
            "mysql_comment": "Devices controls",
        },
    )

    col_control_id: bytes = Column(  # type: ignore[assignment]
        BINARY(16), primary_key=True, name="control_id", default=uuid.uuid4
    )
    col_name: str = Column(VARCHAR(100), name="control_name", nullable=False)  # type: ignore[assignment]

    device_id: bytes = Column(  # type: ignore[assignment]  # pylint: disable=unused-private-member
        BINARY(16),
        ForeignKey("fb_devices_module_devices.device_id", ondelete="CASCADE"),
        name="device_id",
        nullable=False,
    )

    device: DeviceEntity = relationship(DeviceEntity, back_populates="controls")  # type: ignore[assignment]

    # -----------------------------------------------------------------------------

    def __init__(self, name: str, device: DeviceEntity, control_id: Optional[uuid.UUID] = None) -> None:
        super().__init__()

        self.col_control_id = control_id.bytes if control_id is not None else uuid.uuid4().bytes

        self.col_name = name

        self.device = device

    # -----------------------------------------------------------------------------

    @property
    def id(self) -> uuid.UUID:  # pylint: disable=invalid-name
        """Control unique identifier"""
        return uuid.UUID(bytes=self.col_control_id)

    # -----------------------------------------------------------------------------

    @property
    def name(self) -> str:
        """Control name"""
        return self.col_name

    # -----------------------------------------------------------------------------

    def to_dict(self) -> Dict[str, Union[str, None]]:
        """Transform entity to dictionary"""
        return {
            **super().to_dict(),
            **{
                "id": self.id.__str__(),
                "name": self.name,
                "device": uuid.UUID(bytes=self.device_id).__str__(),
                "owner": self.device.owner,
            },
        }
