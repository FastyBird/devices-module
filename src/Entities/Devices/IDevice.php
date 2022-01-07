<?php declare(strict_types = 1);

/**
 * IDevice.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           29.01.17
 */

namespace FastyBird\DevicesModule\Entities\Devices;

use FastyBird\DevicesModule\Entities;
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;
use FastyBird\SimpleAuth\Entities as SimpleAuthEntities;
use IPub\DoctrineTimestampable;

/**
 * Base device entity interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IDevice extends Entities\IEntity,
	Entities\IKey,
	Entities\IEntityParams,
	SimpleAuthEntities\IEntityOwner,
	DoctrineTimestampable\Entities\IEntityCreated, DoctrineTimestampable\Entities\IEntityUpdated
{

	/**
	 * @return ModulesMetadataTypes\DeviceTypeType
	 */
	public function getType(): ModulesMetadataTypes\DeviceTypeType;

	/**
	 * @return IDevice|null
	 */
	public function getParent(): ?IDevice;

	/**
	 * @param IDevice $device
	 *
	 * @return void
	 */
	public function setParent(IDevice $device): void;

	/**
	 * @return void
	 */
	public function removeParent(): void;

	/**
	 * @return IDevice[]
	 */
	public function getChildren(): array;

	/**
	 * @param IDevice[] $children
	 *
	 * @return void
	 */
	public function setChildren(array $children): void;

	/**
	 * @param IDevice $child
	 *
	 * @return void
	 */
	public function addChild(IDevice $child): void;

	/**
	 * @param IDevice $child
	 *
	 * @return void
	 */
	public function removeChild(IDevice $child): void;

	/**
	 * @return string
	 */
	public function getIdentifier(): string;

	/**
	 * @return string|null
	 */
	public function getName(): ?string;

	/**
	 * @param string|null $name
	 *
	 * @return void
	 */
	public function setName(?string $name): void;

	/**
	 * @return string|null
	 */
	public function getComment(): ?string;

	/**
	 * @param string|null $comment
	 *
	 * @return void
	 */
	public function setComment(?string $comment = null): void;

	/**
	 * @return bool
	 */
	public function isEnabled(): bool;

	/**
	 * @param bool $enabled
	 *
	 * @return void
	 */
	public function setEnabled(bool $enabled): void;

	/**
	 * @return string|ModulesMetadataTypes\HardwareManufacturerType
	 */
	public function getHardwareManufacturer();

	/**
	 * @param string|ModulesMetadataTypes\HardwareManufacturerType $manufacturer
	 *
	 * @return void
	 */
	public function setHardwareManufacturer($manufacturer): void;

	/**
	 * @return string|ModulesMetadataTypes\DeviceModelType
	 */
	public function getHardwareModel();

	/**
	 * @param string|ModulesMetadataTypes\DeviceModelType $model
	 *
	 * @return void
	 */
	public function setHardwareModel($model): void;

	/**
	 * @return string|null
	 */
	public function getHardwareVersion(): ?string;

	/**
	 * @param string|null $version
	 *
	 * @return void
	 */
	public function setHardwareVersion(?string $version): void;

	/**
	 * @param string $separator
	 *
	 * @return string|null
	 */
	public function getHardwareMacAddress(string $separator = ':'): ?string;

	/**
	 * @param string|null $macAddress
	 *
	 * @return void
	 */
	public function setHardwareMacAddress(?string $macAddress): void;

	/**
	 * @return string|ModulesMetadataTypes\FirmwareManufacturerType
	 */
	public function getFirmwareManufacturer();

	/**
	 * @param string|ModulesMetadataTypes\FirmwareManufacturerType $manufacturer
	 *
	 * @return void
	 */
	public function setFirmwareManufacturer($manufacturer): void;

	/**
	 * @return string|null
	 */
	public function getFirmwareVersion(): ?string;

	/**
	 * @param string|null $version
	 *
	 * @return void
	 */
	public function setFirmwareVersion(?string $version): void;

	/**
	 * @return Entities\Channels\IChannel[]
	 */
	public function getChannels(): array;

	/**
	 * @param Entities\Channels\IChannel[] $channels
	 *
	 * @return void
	 */
	public function setChannels(array $channels = []): void;

	/**
	 * @param Entities\Channels\IChannel $channel
	 *
	 * @return void
	 */
	public function addChannel(Entities\Channels\IChannel $channel): void;

	/**
	 * @param string $id
	 *
	 * @return Entities\Channels\IChannel|null
	 */
	public function getChannel(string $id): ?Entities\Channels\IChannel;

	/**
	 * @param Entities\Channels\IChannel $channel
	 *
	 * @return void
	 */
	public function removeChannel(Entities\Channels\IChannel $channel): void;

	/**
	 * @return Entities\Devices\Controls\IControl[]
	 */
	public function getControls(): array;

	/**
	 * @param Entities\Devices\Controls\IControl[] $control
	 *
	 * @return void
	 */
	public function setControls(array $control): void;

	/**
	 * @param Entities\Devices\Controls\IControl $control
	 *
	 * @return void
	 */
	public function addControl(Entities\Devices\Controls\IControl $control): void;

	/**
	 * @param string $name
	 *
	 * @return Entities\Devices\Controls\IControl|null
	 */
	public function getControl(string $name): ?Entities\Devices\Controls\IControl;

	/**
	 * @param Entities\Devices\Controls\IControl $control
	 *
	 * @return void
	 */
	public function removeControl(Entities\Devices\Controls\IControl $control): void;

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasControl(string $name): bool;

	/**
	 * @param string $name
	 *
	 * @return Entities\Devices\Controls\IControl|null
	 */
	public function findControl(string $name): ?Entities\Devices\Controls\IControl;

	/**
	 * @return Entities\Devices\Properties\IProperty[]
	 */
	public function getProperties(): array;

	/**
	 * @param Entities\Devices\Properties\IProperty[] $properties
	 *
	 * @return void
	 */
	public function setProperties(array $properties = []): void;

	/**
	 * @param Entities\Devices\Properties\IProperty $property
	 *
	 * @return void
	 */
	public function addProperty(Entities\Devices\Properties\IProperty $property): void;

	/**
	 * @param string $id
	 *
	 * @return Entities\Devices\Properties\IProperty|null
	 */
	public function getProperty(string $id): ?Entities\Devices\Properties\IProperty;

	/**
	 * @param Properties\IProperty $property
	 *
	 * @return void
	 */
	public function removeProperty(Entities\Devices\Properties\IProperty $property): void;

	/**
	 * @param string $property
	 *
	 * @return bool
	 */
	public function hasProperty(string $property): bool;

	/**
	 * @param string $property
	 *
	 * @return Properties\IProperty|null
	 */
	public function findProperty(string $property): ?Entities\Devices\Properties\IProperty;

	/**
	 * @return Entities\Devices\Configuration\IRow[]
	 */
	public function getConfiguration(): array;

	/**
	 * @param Entities\Devices\Configuration\IRow[] $configuration
	 */
	public function setConfiguration(array $configuration = []): void;

	/**
	 * @param Configuration\IRow $row
	 *
	 * @return void
	 */
	public function addConfigurationRow(Entities\Devices\Configuration\IRow $row): void;

	/**
	 * @param string $id
	 *
	 * @return Configuration\IRow|null
	 */
	public function getConfigurationRow(string $id): ?Entities\Devices\Configuration\IRow;

	/**
	 * @param Entities\Devices\Configuration\IRow $stat
	 *
	 * @return void
	 */
	public function removeConfigurationRow(Entities\Devices\Configuration\IRow $stat): void;

	/**
	 * @return bool
	 */
	public function hasConfigurationRow(string $configuration): bool;

	/**
	 * @param string|null $configuration
	 *
	 * @return Entities\Devices\Configuration\IRow|null
	 */
	public function findConfigurationRow(?string $configuration): ?Entities\Devices\Configuration\IRow;

	/**
	 * @return Entities\Connectors\IConnector|null
	 */
	public function getConnector(): ?Entities\Connectors\IConnector;

	/**
	 * @param Entities\Connectors\IConnector|null $connector
	 *
	 * @return void
	 */
	public function setConnector(?Entities\Connectors\IConnector $connector): void;

	/**
	 * @return mixed[]
	 */
	public function toArray(): array;

}
