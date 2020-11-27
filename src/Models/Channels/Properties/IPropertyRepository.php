<?php declare(strict_types = 1);

/**
 * IPropertyRepository.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           21.11.18
 */

namespace FastyBird\DevicesModule\Models\Channels\Properties;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineOrmQuery;

/**
 * Device channel property repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IPropertyRepository
{

	/**
	 * @param Queries\FindChannelPropertiesQuery $queryObject
	 *
	 * @return Entities\Channels\Properties\IProperty|null
	 *
	 * @phpstan-template T of Entities\Channels\Properties\Property
	 * @phpstan-param    Queries\FindChannelPropertiesQuery<T> $queryObject
	 */
	public function findOneBy(Queries\FindChannelPropertiesQuery $queryObject): ?Entities\Channels\Properties\IProperty;

	/**
	 * @param Queries\FindChannelPropertiesQuery $queryObject
	 *
	 * @return DoctrineOrmQuery\ResultSet
	 *
	 * @phpstan-template T of Entities\Channels\Properties\Property
	 * @phpstan-param    Queries\FindChannelPropertiesQuery<T> $queryObject
	 * @phpstan-return   DoctrineOrmQuery\ResultSet<T>
	 */
	public function getResultSet(
		Queries\FindChannelPropertiesQuery $queryObject
	): DoctrineOrmQuery\ResultSet;

}
