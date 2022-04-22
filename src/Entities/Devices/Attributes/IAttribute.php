<?php declare(strict_types = 1);

/**
 * IAttribute.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.57.0
 *
 * @date           22.04.22
 */

namespace FastyBird\DevicesModule\Entities\Devices\Attributes;

use FastyBird\DevicesModule\Entities;
use FastyBird\Metadata\Types as MetadataTypes;
use IPub\DoctrineTimestampable;

/**
 * Attribute settings entity interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IAttribute extends Entities\IEntity,
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
	 * @return string|MetadataTypes\HardwareManufacturerType|MetadataTypes\FirmwareManufacturerType|MetadataTypes\DeviceModelType|null
	 */
	public function getContent();

	/**
	 * @param string|null $content
	 *
	 * @return void
	 */
	public function setContent(?string $content): void;

}
