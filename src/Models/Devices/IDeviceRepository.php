<?php declare(strict_types = 1);

/**
 * IDeviceRepository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           28.07.18
 */

namespace FastyBird\DevicesModule\Models\Devices;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineOrmQuery;

/**
 * Device repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IDeviceRepository
{

	/**
	 * @param Queries\FindDevicesQuery $queryObject
	 * @param string $type
	 *
	 * @return Entities\Devices\IDevice|null
	 *
	 * @phpstan-param class-string $type
	 */
	public function findOneBy(
		Queries\FindDevicesQuery $queryObject,
		string $type = Entities\Devices\Device::class
	): ?Entities\Devices\IDevice;

	/**
	 * @param Queries\FindDevicesQuery $queryObject
	 * @param string $type
	 *
	 * @return Entities\Devices\IDevice[]
	 *
	 * @phpstan-param class-string $type
	 */
	public function findAllBy(
		Queries\FindDevicesQuery $queryObject,
		string $type = Entities\Devices\Device::class
	): array;

	/**
	 * @param Queries\FindDevicesQuery $queryObject
	 * @param string $type
	 *
	 * @return DoctrineOrmQuery\ResultSet
	 *
	 * @phpstan-param class-string $type
	 *
	 * @phpstan-return DoctrineOrmQuery\ResultSet<Entities\Devices\IDevice>
	 */
	public function getResultSet(
		Queries\FindDevicesQuery $queryObject,
		string $type = Entities\Devices\Device::class
	): DoctrineOrmQuery\ResultSet;

}
