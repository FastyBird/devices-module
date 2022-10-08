<?php declare(strict_types = 1);

/**
 * AttributesRepository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.57.0
 *
 * @date           22.04.22
 */

namespace FastyBird\DevicesModule\Models\Devices\Attributes;

use Doctrine\ORM;
use Doctrine\Persistence;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineOrmQuery;
use Nette;
use Throwable;
use function is_array;

/**
 * Device attribute structure repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class AttributesRepository
{

	use Nette\SmartObject;

	/** @var ORM\EntityRepository<Entities\Devices\Attributes\Attribute>|null */
	private ORM\EntityRepository|null $repository = null;

	public function __construct(private readonly Persistence\ManagerRegistry $managerRegistry)
	{
	}

	public function findOneBy(Queries\FindDeviceAttributes $queryObject): Entities\Devices\Attributes\Attribute|null
	{
		return $queryObject->fetchOne($this->getRepository());
	}

	/**
	 * @return Array<Entities\Devices\Attributes\Attribute>
	 *
	 * @throws Throwable
	 */
	public function findAllBy(Queries\FindDeviceAttributes $queryObject): array
	{
		/** @var Array<Entities\Devices\Attributes\Attribute>|DoctrineOrmQuery\ResultSet<Entities\Devices\Attributes\Attribute> $result */
		$result = $queryObject->fetch($this->getRepository());

		if (is_array($result)) {
			return $result;
		}

		/** @var Array<Entities\Devices\Attributes\Attribute> $data */
		$data = $result->toArray();

		return $data;
	}

	/**
	 * @return DoctrineOrmQuery\ResultSet<Entities\Devices\Attributes\Attribute>
	 */
	public function getResultSet(
		Queries\FindDeviceAttributes $queryObject,
	): DoctrineOrmQuery\ResultSet
	{
		$result = $queryObject->fetch($this->getRepository());

		if (!$result instanceof DoctrineOrmQuery\ResultSet) {
			throw new Exceptions\InvalidState('Result set for given query could not be loaded.');
		}

		return $result;
	}

	/**
	 * @param class-string $type
	 *
	 * @return ORM\EntityRepository<Entities\Devices\Attributes\Attribute>
	 */
	private function getRepository(string $type = Entities\Devices\Attributes\Attribute::class): ORM\EntityRepository
	{
		if ($this->repository === null) {
			/** @var ORM\EntityRepository<Entities\Devices\Attributes\Attribute> $repository */
			$repository = $this->managerRegistry->getRepository($type);

			if (!$repository instanceof ORM\EntityRepository) {
				throw new Exceptions\InvalidState('Entity repository could not be loaded');
			}

			$this->repository = $repository;
		}

		return $this->repository;
	}

}
