<?php declare(strict_types = 1);

/**
 * ConnectorRepository.php
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

use Doctrine\Common;
use Doctrine\Persistence;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineOrmQuery;
use Nette;
use Throwable;

/**
 * Connector repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ConnectorRepository implements IConnectorRepository
{

	use Nette\SmartObject;

	/** @var Persistence\ObjectRepository<Entities\Connectors\Connector>|null */
	public ?Persistence\ObjectRepository $repository = null;

	/** @var Common\Persistence\ManagerRegistry */
	private Common\Persistence\ManagerRegistry $managerRegistry;

	public function __construct(Common\Persistence\ManagerRegistry $managerRegistry)
	{
		$this->managerRegistry = $managerRegistry;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findOneBy(Queries\FindConnectorsQuery $queryObject): ?Entities\Connectors\IConnector
	{
		/** @var Entities\Connectors\IConnector|null $connector */
		$connector = $queryObject->fetchOne($this->getRepository());

		return $connector;
	}

	/**
	 * @return Persistence\ObjectRepository<Entities\Connectors\Connector>
	 */
	private function getRepository(): Persistence\ObjectRepository
	{
		if ($this->repository === null) {
			$this->repository = $this->managerRegistry->getRepository(Entities\Connectors\Connector::class);
		}

		return $this->repository;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Throwable
	 */
	public function findAllBy(Queries\FindConnectorsQuery $queryObject): array
	{
		$result = $queryObject->fetch($this->getRepository());

		return is_array($result) ? $result : $result->toArray();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Throwable
	 */
	public function getResultSet(
		Queries\FindConnectorsQuery $queryObject
	): DoctrineOrmQuery\ResultSet {
		$result = $queryObject->fetch($this->getRepository());

		if (!$result instanceof DoctrineOrmQuery\ResultSet) {
			throw new Exceptions\InvalidStateException('Result set for given query could not be loaded.');
		}

		return $result;
	}

}
