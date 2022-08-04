<?php declare(strict_types = 1);

/**
 * IDevicesRepository.php
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
interface IDevicesRepository
{

	/**
	 * @param Queries\FindDevicesQuery $queryObject
	 * @param class-string $type
	 *
	 * @return Entities\Devices\IDevice|null
	 */
	public function findOneBy(
		Queries\FindDevicesQuery $queryObject,
		string $type = Entities\Devices\Device::class
	): ?Entities\Devices\IDevice;

	/**
	 * @param Queries\FindDevicesQuery $queryObject
	 * @param class-string $type
	 *
	 * @return Entities\Devices\IDevice[]
	 */
	public function findAllBy(
		Queries\FindDevicesQuery $queryObject,
		string $type = Entities\Devices\Device::class
	): array;

	/**
	 * @param Queries\FindDevicesQuery $queryObject
	 * @param class-string $type
	 *
	 * @return DoctrineOrmQuery\ResultSet<Entities\Devices\IDevice>
	 */
	public function getResultSet(
		Queries\FindDevicesQuery $queryObject,
		string $type = Entities\Devices\Device::class
	): DoctrineOrmQuery\ResultSet;

}
