<?php declare(strict_types = 1);

/**
 * PropertiesRepository.php
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

use Doctrine\ORM;
use Doctrine\Persistence;
use Exception;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineOrmQuery;
use Nette;
use Throwable;

/**
 * Connector channel property structure repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class PropertiesRepository implements IPropertiesRepository
{

	use Nette\SmartObject;

	/**
	 * @var ORM\EntityRepository<Entities\Connectors\Properties\IProperty>[]
	 */
	private array $repository = [];

	/** @var Persistence\ManagerRegistry */
	private Persistence\ManagerRegistry $managerRegistry;

	/**
	 * @param Persistence\ManagerRegistry $managerRegistry
	 */
	public function __construct(Persistence\ManagerRegistry $managerRegistry)
	{
		$this->managerRegistry = $managerRegistry;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findOneBy(
		Queries\FindConnectorPropertiesQuery $queryObject,
		string $type = Entities\Connectors\Properties\Property::class
	): ?Entities\Connectors\Properties\IProperty {
		/** @var Entities\Connectors\Properties\IProperty|null $property */
		$property = $queryObject->fetchOne($this->getRepository($type));

		return $property;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Throwable
	 */
	public function findAllBy(
		Queries\FindConnectorPropertiesQuery $queryObject,
		string $type = Entities\Connectors\Properties\Property::class
	): array {
		/** @var Array<Entities\Connectors\Properties\IProperty>|DoctrineOrmQuery\ResultSet<Entities\Connectors\Properties\IProperty> $result */
		$result = $queryObject->fetch($this->getRepository($type));

		if (is_array($result)) {
			return $result;
		}

		/** @var Entities\Connectors\Properties\IProperty[] $data */
		$data = $result->toArray();

		return $data;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Throwable
	 */
	public function getResultSet(
		Queries\FindConnectorPropertiesQuery $queryObject,
		string $type = Entities\Connectors\Properties\Property::class
	): DoctrineOrmQuery\ResultSet {
		$result = $queryObject->fetch($this->getRepository($type));

		if (!$result instanceof DoctrineOrmQuery\ResultSet) {
			throw new Exceptions\InvalidStateException('Result set for given query could not be loaded.');
		}

		return $result;
	}

	/**
	 * @param class-string $type
	 *
	 * @return ORM\EntityRepository<Entities\Connectors\Properties\IProperty>
	 */
	private function getRepository(string $type): ORM\EntityRepository
	{
		if (!isset($this->repository[$type])) {
			/** @var ORM\EntityRepository<Entities\Connectors\Properties\IProperty> $repository */
			$repository = $this->managerRegistry->getRepository($type);

			if (!$repository instanceof ORM\EntityRepository) {
				throw new Exceptions\InvalidStateException('Entity repository could not be loaded');
			}

			$this->repository[$type] = $repository;
		}

		return $this->repository[$type];
	}

}
