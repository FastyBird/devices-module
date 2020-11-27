<?php declare(strict_types = 1);

/**
 * IDevice.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           29.01.17
 */

namespace FastyBird\DevicesModule\Entities\Devices;

use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Types;
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
interface IDevice extends DatabaseEntities\IEntity,
	DatabaseEntities\IEntityParams,
	SimpleAuthEntities\IEntityOwner,
	DoctrineTimestampable\Entities\IEntityCreated, DoctrineTimestampable\Entities\IEntityUpdated
{

	/**
	 * @return string
	 */
	public function getIdentifier(): string;

	/**
	 * @param IDevice $device
	 *
	 * @return void
	 */
	public function setParent(IDevice $device): void;

	/**
	 * @return IDevice|null
	 */
	public function getParent(): ?IDevice;

	/**
	 * @return void
	 */
	public function removeParent(): void;

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
	 * @return IDevice[]
	 */
	public function getChildren(): array;

	/**
	 * @param IDevice $child
	 *
	 * @return void
	 */
	public function removeChild(IDevice $child): void;

	/**
	 * @param string|null $name
	 *
	 * @return void
	 */
	public function setName(?string $name): void;

	/**
	 * @return string|null
	 */
	public function getName(): ?string;

	/**
	 * @param string|null $comment
	 *
	 * @return void
	 */
	public function setComment(?string $comment = null): void;

	/**
	 * @return string|null
	 */
	public function getComment(): ?string;

	/**
	 * @param string $state
	 *
	 * @return void
	 */
	public function setState(string $state): void;

	/**
	 * @return Types\DeviceConnectionState
	 */
	public function getState(): Types\DeviceConnectionState;

	/**
	 * @param bool $enabled
	 *
	 * @return void
	 */
	public function setEnabled(bool $enabled): void;

	/**
	 * @return bool
	 */
	public function isEnabled(): bool;

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
	 * @return Entities\Channels\IChannel[]
	 */
	public function getChannels(): array;

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
	 * @return Entities\Devices\Controls\IControl[]
	 */
	public function getControls(): array;

	/**
	 * @param string $name
	 *
	 * @return Entities\Devices\Controls\IControl|null
	 */
	public function getControl(string $name): ?Entities\Devices\Controls\IControl;

	/**
	 * @param string $name
	 *
	 * @return Entities\Devices\Controls\IControl|null
	 */
	public function findControl(string $name): ?Entities\Devices\Controls\IControl;

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasControl(string $name): bool;

	/**
	 * @param Entities\Devices\Controls\IControl $control
	 *
	 * @return void
	 */
	public function removeControl(Entities\Devices\Controls\IControl $control): void;

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
	 * @return Entities\Devices\Properties\IProperty[]
	 */
	public function getProperties(): array;

	/**
	 * @param string $id
	 *
	 * @return Entities\Devices\Properties\IProperty|null
	 */
	public function getProperty(string $id): ?Entities\Devices\Properties\IProperty;

	/**
	 * @param string $property
	 *
	 * @return Properties\IProperty|null
	 */
	public function findProperty(string $property): ?Entities\Devices\Properties\IProperty;

	/**
	 * @param string $property
	 *
	 * @return bool
	 */
	public function hasProperty(string $property): bool;

	/**
	 * @param Properties\IProperty $property
	 *
	 * @return void
	 */
	public function removeProperty(Entities\Devices\Properties\IProperty $property): void;

	/**
	 * @param Entities\Devices\Configuration\IRow[] $configuration
	 */
	public function setConfiguration(array $configuration = []): void;

	/**
	 * @param Configuration\IRow $row
	 *
	 * @return void
	 */
	public function addConfiguration(Entities\Devices\Configuration\IRow $row): void;

	/**
	 * @return Entities\Devices\Configuration\IRow[]
	 */
	public function getConfiguration(): array;

	/**
	 * @param string $id
	 *
	 * @return Configuration\IRow|null
	 */
	public function getConfigurationRow(string $id): ?Entities\Devices\Configuration\IRow;

	/**
	 * @param string|null $configuration
	 *
	 * @return Entities\Devices\Configuration\IRow|null
	 */
	public function findConfiguration(?string $configuration): ?Entities\Devices\Configuration\IRow;

	/**
	 * @return bool
	 */
	public function hasConfiguration(): bool;

	/**
	 * @param Entities\Devices\Configuration\IRow $stat
	 *
	 * @return void
	 */
	public function removeConfiguration(Entities\Devices\Configuration\IRow $stat): void;

	/**
	 * @return mixed[]
	 */
	public function toArray(): array;

}
