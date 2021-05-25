<?php declare(strict_types = 1);

/**
 * IPropertyRepository.php
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
interface IPropertyRepository
{

	/**
	 * @param Queries\FindDevicePropertiesQuery $queryObject
	 *
	 * @return Entities\Devices\Properties\IProperty|null
	 */
	public function findOneBy(
		Queries\FindDevicePropertiesQuery $queryObject
	): ?Entities\Devices\Properties\IProperty;

	/**
	 * @param Queries\FindDevicePropertiesQuery $queryObject
	 *
	 * @return DoctrineOrmQuery\ResultSet
	 *
	 * @phpstan-return DoctrineOrmQuery\ResultSet<Entities\Devices\Properties\Property>
	 */
	public function getResultSet(
		Queries\FindDevicePropertiesQuery $queryObject
	): DoctrineOrmQuery\ResultSet;

}
