<?php declare(strict_types = 1);

/**
 * ControlsRepository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.4.0
 *
 * @date           29.09.21
 */

namespace FastyBird\DevicesModule\Models\Devices\Controls;

use Doctrine\ORM;
use Doctrine\Persistence;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineOrmQuery;
use Nette;
use Throwable;

/**
 * Device control structure repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ControlsRepository
{

	use Nette\SmartObject;

	/** @var ORM\EntityRepository<Entities\Devices\Controls\Control>|null */
	private ?ORM\EntityRepository $repository = null;

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
	 * @param Queries\FindDeviceControls $queryObject
	 *
	 * @return Entities\Devices\Controls\Control|null
	 */
	public function findOneBy(Queries\FindDeviceControls $queryObject): ?Entities\Devices\Controls\Control
	{
		/** @var Entities\Devices\Controls\Control|null $control */
		$control = $queryObject->fetchOne($this->getRepository());

		return $control;
	}

	/**
	 * @param Queries\FindDeviceControls $queryObject
	 *
	 * @return Entities\Devices\Controls\Control[]
	 *
	 * @throws Throwable
	 */
	public function findAllBy(Queries\FindDeviceControls $queryObject): array
	{
		/** @var Array<Entities\Devices\Controls\Control>|DoctrineOrmQuery\ResultSet<Entities\Devices\Controls\Control> $result */
		$result = $queryObject->fetch($this->getRepository());

		if (is_array($result)) {
			return $result;
		}

		/** @var Entities\Devices\Controls\Control[] $data */
		$data = $result->toArray();

		return $data;
	}

	/**
	 * @param Queries\FindDeviceControls $queryObject
	 *
	 * @return DoctrineOrmQuery\ResultSet<Entities\Devices\Controls\Control>
	 */
	public function getResultSet(
		Queries\FindDeviceControls $queryObject
	): DoctrineOrmQuery\ResultSet {
		$result = $queryObject->fetch($this->getRepository());

		if (!$result instanceof DoctrineOrmQuery\ResultSet) {
			throw new Exceptions\InvalidState('Result set for given query could not be loaded.');
		}

		return $result;
	}

	/**
	 * @param class-string $type
	 *
	 * @return ORM\EntityRepository<Entities\Devices\Controls\Control>
	 */
	private function getRepository(string $type = Entities\Devices\Controls\Control::class): ORM\EntityRepository
	{
		if ($this->repository === null) {
			/** @var ORM\EntityRepository<Entities\Devices\Controls\Control> $repository */
			$repository = $this->managerRegistry->getRepository($type);

			if (!$repository instanceof ORM\EntityRepository) {
				throw new Exceptions\InvalidState('Entity repository could not be loaded');
			}

			$this->repository = $repository;
		}

		return $this->repository;
	}

}
