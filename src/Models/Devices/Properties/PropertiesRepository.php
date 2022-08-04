<?php declare(strict_types = 1);

/**
 * PropertiesRepository.php
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

use Doctrine\ORM;
use Doctrine\Persistence;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineOrmQuery;
use Nette;
use Throwable;

/**
 * Device channel property structure repository
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
	 * @var ORM\EntityRepository<Entities\Devices\Properties\IProperty>[]
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
		Queries\FindDevicePropertiesQuery $queryObject,
		string $type = Entities\Devices\Properties\Property::class
	): ?Entities\Devices\Properties\IProperty {
		/** @var Entities\Devices\Properties\IProperty|null $property */
		$property = $queryObject->fetchOne($this->getRepository($type));

		return $property;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Throwable
	 */
	public function findAllBy(
		Queries\FindDevicePropertiesQuery $queryObject,
		string $type = Entities\Devices\Properties\Property::class
	): array {
		/** @var Array<Entities\Devices\Properties\IProperty>|DoctrineOrmQuery\ResultSet<Entities\Devices\Properties\IProperty> $result */
		$result = $queryObject->fetch($this->getRepository($type));

		if (is_array($result)) {
			return $result;
		}

		/** @var Entities\Devices\Properties\IProperty[] $data */
		$data = $result->toArray();

		return $data;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Throwable
	 */
	public function getResultSet(
		Queries\FindDevicePropertiesQuery $queryObject,
		string $type = Entities\Devices\Properties\Property::class
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
	 * @return ORM\EntityRepository<Entities\Devices\Properties\IProperty>
	 */
	private function getRepository(string $type): ORM\EntityRepository
	{
		if (!isset($this->repository[$type])) {
			/** @var  ORM\EntityRepository<Entities\Devices\Properties\IProperty> $repository */
			$repository = $this->managerRegistry->getRepository($type);

			if (!$repository instanceof ORM\EntityRepository) {
				throw new Exceptions\InvalidStateException('Entity repository could not be loaded');
			}

			$this->repository[$type] = $repository;
		}

		return $this->repository[$type];
	}

}
