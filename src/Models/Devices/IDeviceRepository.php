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
	 *
	 * @return Entities\Devices\IDevice|null
	 */
	public function findOneBy(
		Queries\FindDevicesQuery $queryObject
	): ?Entities\Devices\IDevice;

	/**
	 * @param Queries\FindDevicesQuery $queryObject
	 *
	 * @return Entities\Devices\IDevice[]
	 */
	public function findAllBy(
		Queries\FindDevicesQuery $queryObject
	): array;

	/**
	 * @param Queries\FindDevicesQuery $queryObject
	 *
	 * @return DoctrineOrmQuery\ResultSet
	 *
	 * @phpstan-return DoctrineOrmQuery\ResultSet<Entities\Devices\Device>
	 */
	public function getResultSet(
		Queries\FindDevicesQuery $queryObject
	): DoctrineOrmQuery\ResultSet;

}
