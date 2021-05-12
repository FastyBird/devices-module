<?php declare(strict_types = 1);

/**
 * IChannel.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           28.07.18
 */

namespace FastyBird\DevicesModule\Entities\Channels;

use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\DevicesModule\Entities;
use IPub\DoctrineTimestampable;

/**
 * Device communication channel entity interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IChannel extends DatabaseEntities\IEntity,
	Entities\IKey,
	DatabaseEntities\IEntityParams,
	DoctrineTimestampable\Entities\IEntityCreated, DoctrineTimestampable\Entities\IEntityUpdated
{

	/**
	 * @return Entities\Devices\IDevice
	 */
	public function getDevice(): Entities\Devices\IDevice;

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
	 * @param string $identifier
	 *
	 * @return void
	 */
	public function setIdentifier(string $identifier): void;

	/**
	 * @return string
	 */
	public function getIdentifier(): string;

	/**
	 * @param Entities\Channels\Properties\IProperty[] $properties
	 *
	 * @return void
	 */
	public function setProperties(array $properties = []): void;

	/**
	 * @param Entities\Channels\Properties\IProperty $property
	 *
	 * @return void
	 */
	public function addProperty(Entities\Channels\Properties\IProperty $property): void;

	/**
	 * @return Entities\Channels\Properties\IProperty[]
	 */
	public function getProperties(): array;

	/**
	 * @param string $id
	 *
	 * @return Entities\Channels\Properties\IProperty|null
	 */
	public function getProperty(string $id): ?Entities\Channels\Properties\IProperty;

	/**
	 * @param string $property
	 *
	 * @return Properties\IProperty|null
	 */
	public function findProperty(string $property): ?Entities\Channels\Properties\IProperty;

	/**
	 * @param string $property
	 *
	 * @return bool
	 */
	public function hasProperty(string $property): bool;

	/**
	 * @return bool
	 */
	public function hasSettableProperty(): bool;

	/**
	 * @param Properties\IProperty $property
	 *
	 * @return void
	 */
	public function removeProperty(Entities\Channels\Properties\IProperty $property): void;

	/**
	 * @param Entities\Channels\Configuration\IRow[] $configuration
	 *
	 * @return void
	 */
	public function setConfiguration(array $configuration = []): void;

	/**
	 * @param Configuration\IRow $row
	 *
	 * @return void
	 */
	public function addConfiguration(Entities\Channels\Configuration\IRow $row): void;

	/**
	 * @return Entities\Channels\Configuration\IRow[]
	 */
	public function getConfiguration(): array;

	/**
	 * @param string $id
	 *
	 * @return Configuration\IRow|null
	 */
	public function getConfigurationRow(string $id): ?Entities\Channels\Configuration\IRow;

	/**
	 * @param string|null $configuration
	 *
	 * @return Entities\Channels\Configuration\IRow|null
	 */
	public function findConfiguration(?string $configuration): ?Entities\Channels\Configuration\IRow;

	/**
	 * @return bool
	 */
	public function hasConfiguration(): bool;

	/**
	 * @param Configuration\IRow $property
	 *
	 * @return void
	 */
	public function removeConfiguration(Entities\Channels\Configuration\IRow $property): void;

	/**
	 * @param Entities\Channels\Controls\IControl[] $control
	 *
	 * @return void
	 */
	public function setControls(array $control): void;

	/**
	 * @param Entities\Channels\Controls\IControl $control
	 *
	 * @return void
	 */
	public function addControl(Entities\Channels\Controls\IControl $control): void;

	/**
	 * @return Entities\Channels\Controls\IControl[]
	 */
	public function getControls(): array;

	/**
	 * @param string $name
	 *
	 * @return Entities\Channels\Controls\IControl|null
	 */
	public function getControl(string $name): ?Entities\Channels\Controls\IControl;

	/**
	 * @param string $name
	 *
	 * @return Entities\Channels\Controls\IControl|null
	 */
	public function findControl(string $name): ?Entities\Channels\Controls\IControl;

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasControl(string $name): bool;

	/**
	 * @param Entities\Channels\Controls\IControl $control
	 *
	 * @return void
	 */
	public function removeControl(Entities\Channels\Controls\IControl $control): void;

	/**
	 * @return mixed[]
	 */
	public function toArray(): array;

}
