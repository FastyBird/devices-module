<?php declare(strict_types = 1);

/**
 * IFirmware.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           28.07.18
 */

namespace FastyBird\DevicesModule\Entities\Devices\PhysicalDevice;

use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Types;
use IPub\DoctrineTimestampable;
use Ramsey\Uuid;

/**
 * Firmware info entity interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IFirmware extends DatabaseEntities\IEntity,
	DoctrineTimestampable\Entities\IEntityCreated, DoctrineTimestampable\Entities\IEntityUpdated
{

	/**
	 * @return Uuid\UuidInterface
	 */
	public function getDevice(): Uuid\UuidInterface;

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
	 * @param string|null $manufacturer
	 *
	 * @return void
	 */
	public function setManufacturer(?string $manufacturer): void;

	/**
	 * @return Types\FirmwareManufacturerType
	 */
	public function getManufacturer(): Types\FirmwareManufacturerType;

	/**
	 * @param string|null $version
	 *
	 * @return void
	 */
	public function setVersion(?string $version): void;

	/**
	 * @return string|null
	 */
	public function getVersion(): ?string;

	/**
	 * @return mixed[]
	 */
	public function toArray(): array;

}
