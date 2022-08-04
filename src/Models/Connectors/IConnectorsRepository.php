<?php declare(strict_types = 1);

/**
 * IConnectorsRepository.php
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
interface IConnectorsRepository
{

	/**
	 * @param Queries\FindConnectorsQuery $queryObject
	 * @param class-string $type
	 *
	 * @return Entities\Connectors\IConnector|null
	 */
	public function findOneBy(
		Queries\FindConnectorsQuery $queryObject,
		string $type = Entities\Connectors\Connector::class
	): ?Entities\Connectors\IConnector;

	/**
	 * @param Queries\FindConnectorsQuery $queryObject
	 * @param class-string $type
	 *
	 * @return Entities\Connectors\IConnector[]
	 */
	public function findAllBy(
		Queries\FindConnectorsQuery $queryObject,
		string $type = Entities\Connectors\Connector::class
	): array;

	/**
	 * @param Queries\FindConnectorsQuery $queryObject
	 * @param class-string $type
	 *
	 * @return DoctrineOrmQuery\ResultSet<Entities\Connectors\IConnector>
	 */
	public function getResultSet(
		Queries\FindConnectorsQuery $queryObject,
		string $type = Entities\Connectors\Connector::class
	): DoctrineOrmQuery\ResultSet;

}
