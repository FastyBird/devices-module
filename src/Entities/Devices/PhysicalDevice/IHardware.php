<?php declare(strict_types = 1);

/**
 * IHardware.php
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
 * Hardware info entity interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IHardware extends DatabaseEntities\IEntity,
	DoctrineTimestampable\Entities\IEntityCreated, DoctrineTimestampable\Entities\IEntityUpdated
{

	/**
	 * @return Uuid\UuidInterface
	 */
	public function getDevice(): Uuid\UuidInterface;

	/**
	 * @param string|null $manufacturer
	 *
	 * @return void
	 */
	public function setManufacturer(?string $manufacturer): void;

	/**
	 * @return Types\HardwareManufacturerType
	 */
	public function getManufacturer(): Types\HardwareManufacturerType;

	/**
	 * @param string|null $model
	 *
	 * @return void
	 */
	public function setModel(?string $model): void;

	/**
	 * @return Types\ModelType
	 */
	public function getModel(): Types\ModelType;

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
	 * @param string|null $macAddress
	 *
	 * @return void
	 */
	public function setMacAddress(?string $macAddress): void;

	/**
	 * @param string $separator
	 *
	 * @return string|null
	 */
	public function getMacAddress(string $separator = ':'): ?string;

	/**
	 * @return mixed[]
	 */
	public function toArray(): array;

}
