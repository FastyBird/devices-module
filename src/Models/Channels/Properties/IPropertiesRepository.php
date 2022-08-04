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

namespace FastyBird\DevicesModule\Models\Channels\Properties;

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
	 * @param Queries\FindChannelPropertiesQuery $queryObject
	 * @param class-string $type
	 *
	 * @return Entities\Channels\Properties\IProperty|null
	 */
	public function findOneBy(
		Queries\FindChannelPropertiesQuery $queryObject,
		string $type = Entities\Channels\Properties\Property::class
	): ?Entities\Channels\Properties\IProperty;

	/**
	 * @param Queries\FindChannelPropertiesQuery $queryObject
	 * @param class-string $type
	 *
	 * @return Entities\Channels\Properties\IProperty[]
	 */
	public function findAllBy(
		Queries\FindChannelPropertiesQuery $queryObject,
		string $type = Entities\Channels\Properties\Property::class
	): array;

	/**
	 * @param Queries\FindChannelPropertiesQuery $queryObject
	 * @param class-string $type
	 *
	 * @return DoctrineOrmQuery\ResultSet<Entities\Channels\Properties\IProperty>
	 */
	public function getResultSet(
		Queries\FindChannelPropertiesQuery $queryObject,
		string $type = Entities\Channels\Properties\Property::class
	): DoctrineOrmQuery\ResultSet;

}
