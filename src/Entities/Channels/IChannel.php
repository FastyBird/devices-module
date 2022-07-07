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
interface IChannel extends Entities\IEntity,
	Entities\IEntityParams,
	DoctrineTimestampable\Entities\IEntityCreated, DoctrineTimestampable\Entities\IEntityUpdated
{

	/**
	 * @return Entities\Devices\IDevice
	 */
	public function getDevice(): Entities\Devices\IDevice;

	/**
	 * @return string
	 */
	public function getIdentifier(): string;

	/**
	 * @param string $identifier
	 *
	 * @return void
	 */
	public function setIdentifier(string $identifier): void;

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
	 * @return Entities\Channels\Properties\IProperty[]
	 */
	public function getProperties(): array;

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
	 * @param string $id
	 *
	 * @return Entities\Channels\Properties\IProperty|null
	 */
	public function getProperty(string $id): ?Entities\Channels\Properties\IProperty;

	/**
	 * @param Properties\IProperty $property
	 *
	 * @return void
	 */
	public function removeProperty(Entities\Channels\Properties\IProperty $property): void;

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
	public function findProperty(string $property): ?Entities\Channels\Properties\IProperty;

	/**
	 * @return Entities\Channels\Controls\IControl[]
	 */
	public function getControls(): array;

	/**
	 * @param Entities\Channels\Controls\IControl[] $controls
	 *
	 * @return void
	 */
	public function setControls(array $controls): void;

	/**
	 * @param Entities\Channels\Controls\IControl $control
	 *
	 * @return void
	 */
	public function addControl(Entities\Channels\Controls\IControl $control): void;

	/**
	 * @param string $name
	 *
	 * @return Entities\Channels\Controls\IControl|null
	 */
	public function getControl(string $name): ?Entities\Channels\Controls\IControl;

	/**
	 * @param Entities\Channels\Controls\IControl $control
	 *
	 * @return void
	 */
	public function removeControl(Entities\Channels\Controls\IControl $control): void;

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasControl(string $name): bool;

	/**
	 * @param string $name
	 *
	 * @return Entities\Channels\Controls\IControl|null
	 */
	public function findControl(string $name): ?Entities\Channels\Controls\IControl;

}
