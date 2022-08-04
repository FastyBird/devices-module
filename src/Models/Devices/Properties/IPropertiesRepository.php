<?php declare(strict_types = 1);

/**
 * IPropertiesRepository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           21.11.18
 */

namespace FastyBird\DevicesModule\Models\Devices\Properties;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineOrmQuery;

/**
 * Device property repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IPropertiesRepository
{

	/**
	 * @param Queries\FindDevicePropertiesQuery $queryObject
	 * @param class-string $type
	 *
	 * @return Entities\Devices\Properties\IProperty|null
	 */
	public function findOneBy(
		Queries\FindDevicePropertiesQuery $queryObject,
		string $type = Entities\Devices\Properties\Property::class
	): ?Entities\Devices\Properties\IProperty;

	/**
	 * @param Queries\FindDevicePropertiesQuery $queryObject
	 * @param class-string $type
	 *
	 * @return Entities\Devices\Properties\IProperty[]
	 */
	public function findAllBy(
		Queries\FindDevicePropertiesQuery $queryObject,
		string $type = Entities\Devices\Properties\Property::class
	): array;

	/**
	 * @param Queries\FindDevicePropertiesQuery $queryObject
	 * @param class-string $type
	 *
	 * @return DoctrineOrmQuery\ResultSet<Entities\Devices\Properties\IProperty>
	 */
	public function getResultSet(
		Queries\FindDevicePropertiesQuery $queryObject,
		string $type = Entities\Devices\Properties\Property::class
	): DoctrineOrmQuery\ResultSet;

}
