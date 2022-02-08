<?php declare(strict_types = 1);

/**
 * IPropertyRepository.php
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
interface IPropertyRepository
{

	/**
	 * @param Queries\FindConnectorPropertiesQuery $queryObject
	 * @param string $type
	 *
	 * @return Entities\Connectors\Properties\IProperty|null
	 *
	 * @phpstan-param class-string $type
	 */
	public function findOneBy(
		Queries\FindConnectorPropertiesQuery $queryObject,
		string $type = Entities\Connectors\Properties\Property::class
	): ?Entities\Connectors\Properties\IProperty;

	/**
	 * @param Queries\FindConnectorPropertiesQuery $queryObject
	 * @param string $type
	 *
	 * @return DoctrineOrmQuery\ResultSet
	 *
	 * @phpstan-param class-string $type
	 *
	 * @phpstan-return DoctrineOrmQuery\ResultSet<Entities\Connectors\Properties\IProperty>
	 */
	public function getResultSet(
		Queries\FindConnectorPropertiesQuery $queryObject,
		string $type = Entities\Connectors\Properties\Property::class
	): DoctrineOrmQuery\ResultSet;

}
