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

namespace FastyBird\Module\Devices\Models\Connectors\Properties;

use Doctrine\ORM;
use Doctrine\Persistence;
use Exception;
use FastyBird\Module\Devices\Entities;
use FastyBird\Module\Devices\Queries;
use IPub\DoctrineOrmQuery;
use IPub\DoctrineOrmQuery\Exceptions as DoctrineOrmQueryExceptions;
use Nette;
use function is_array;

/**
 * Connector channel property structure repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class PropertiesRepository
{

	use Nette\SmartObject;

	/** @var Array<ORM\EntityRepository<Entities\Connectors\Properties\Property>> */
	private array $repository = [];

	public function __construct(private readonly Persistence\ManagerRegistry $managerRegistry)
	{
	}

	/**
	 * @phpstan-param class-string<Entities\Connectors\Properties\Property> $type
	 *
	 * @throws DoctrineOrmQueryExceptions\InvalidStateException
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 */
	public function findOneBy(
		Queries\FindConnectorProperties $queryObject,
		string $type = Entities\Connectors\Properties\Property::class,
	): Entities\Connectors\Properties\Property|null
	{
		return $queryObject->fetchOne($this->getRepository($type));
	}

	/**
	 * @phpstan-param class-string<Entities\Connectors\Properties\Property> $type
	 *
	 * @phpstan-return Array<Entities\Connectors\Properties\Property>
	 *
	 * @throws Exception
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 */
	public function findAllBy(
		Queries\FindConnectorProperties $queryObject,
		string $type = Entities\Connectors\Properties\Property::class,
	): array
	{
		/** @var Array<Entities\Connectors\Properties\Property>|DoctrineOrmQuery\ResultSet<Entities\Connectors\Properties\Property> $result */
		$result = $queryObject->fetch($this->getRepository($type));

		if (is_array($result)) {
			return $result;
		}

		/** @var Array<Entities\Connectors\Properties\Property> $data */
		$data = $result->toArray();

		return $data;
	}

	/**
	 * @phpstan-param class-string<Entities\Connectors\Properties\Property> $type
	 *
	 * @phpstan-return DoctrineOrmQuery\ResultSet<Entities\Connectors\Properties\Property>
	 *
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 */
	public function getResultSet(
		Queries\FindConnectorProperties $queryObject,
		string $type = Entities\Connectors\Properties\Property::class,
	): DoctrineOrmQuery\ResultSet
	{
		/** @var DoctrineOrmQuery\ResultSet<Entities\Connectors\Properties\Property> $result */
		$result = $queryObject->fetch($this->getRepository($type));

		return $result;
	}

	/**
	 * @param class-string<Entities\Connectors\Properties\Property> $type
	 *
	 * @return ORM\EntityRepository<Entities\Connectors\Properties\Property>
	 */
	private function getRepository(string $type): ORM\EntityRepository
	{
		if (!isset($this->repository[$type])) {
			$this->repository[$type] = $this->managerRegistry->getRepository($type);
		}

		return $this->repository[$type];
	}

}