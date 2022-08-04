<?php declare(strict_types = 1);

/**
 * IPropertiesRepository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.31.0
 *
 * @date           08.02.22
 */

namespace FastyBird\DevicesModule\Models\Connectors\Properties;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineOrmQuery;

/**
 * Connector property repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IPropertiesRepository
{

	/**
	 * @param Queries\FindConnectorPropertiesQuery $queryObject
	 * @param class-string $type
	 *
	 * @return Entities\Connectors\Properties\IProperty|null
	 */
	public function findOneBy(
		Queries\FindConnectorPropertiesQuery $queryObject,
		string $type = Entities\Connectors\Properties\Property::class
	): ?Entities\Connectors\Properties\IProperty;

	/**
	 * @param Queries\FindConnectorPropertiesQuery $queryObject
	 * @param class-string $type
	 *
	 * @return Entities\Connectors\Properties\IProperty[]
	 */
	public function findAllBy(
		Queries\FindConnectorPropertiesQuery $queryObject,
		string $type = Entities\Connectors\Properties\Property::class
	): array;

	/**
	 * @param Queries\FindConnectorPropertiesQuery $queryObject
	 * @param class-string $type
	 *
	 * @return DoctrineOrmQuery\ResultSet<Entities\Connectors\Properties\IProperty>
	 */
	public function getResultSet(
		Queries\FindConnectorPropertiesQuery $queryObject,
		string $type = Entities\Connectors\Properties\Property::class
	): DoctrineOrmQuery\ResultSet;

}
