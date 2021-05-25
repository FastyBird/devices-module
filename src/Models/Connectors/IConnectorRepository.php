<?php declare(strict_types = 1);

/**
 * IConnectorRepository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           16.04.21
 */

namespace FastyBird\DevicesModule\Models\Connectors;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineOrmQuery;

/**
 * Connector repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IConnectorRepository
{

	/**
	 * @param Queries\FindConnectorsQuery $queryObject
	 *
	 * @return Entities\Connectors\IConnector|null
	 */
	public function findOneBy(Queries\FindConnectorsQuery $queryObject): ?Entities\Connectors\IConnector;

	/**
	 * @param Queries\FindConnectorsQuery $queryObject
	 *
	 * @return Entities\Connectors\IConnector[]
	 */
	public function findAllBy(Queries\FindConnectorsQuery $queryObject): array;

	/**
	 * @param Queries\FindConnectorsQuery $queryObject
	 *
	 * @return DoctrineOrmQuery\ResultSet
	 *
	 * @phpstan-return DoctrineOrmQuery\ResultSet<Entities\Connectors\Connector>
	 */
	public function getResultSet(
		Queries\FindConnectorsQuery $queryObject
	): DoctrineOrmQuery\ResultSet;

}
