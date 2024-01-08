<?php declare(strict_types = 1);

/**
 * ControlsRepository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          1.0.0
 *
 * @date           29.09.21
 */

namespace FastyBird\Module\Devices\Models\Entities\Channels\Controls;

use Doctrine\ORM;
use Doctrine\Persistence;
use FastyBird\Module\Devices\Entities;
use FastyBird\Module\Devices\Exceptions;
use FastyBird\Module\Devices\Queries;
use FastyBird\Module\Devices\Utilities;
use IPub\DoctrineOrmQuery;
use Nette;
use Ramsey\Uuid;
use Throwable;
use function is_array;

/**
 * Device channel control structure repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ControlsRepository
{

	use Nette\SmartObject;

	/** @var ORM\EntityRepository<Entities\Channels\Controls\Control>|null */
	private ORM\EntityRepository|null $repository = null;

	public function __construct(
		private readonly Utilities\Database $database,
		private readonly Persistence\ManagerRegistry $managerRegistry,
	)
	{
	}

	/**
	 * @throws Exceptions\InvalidState
	 */
	public function find(
		Uuid\UuidInterface $id,
	): Entities\Channels\Controls\Control|null
	{
		return $this->database->query(
			fn (): Entities\Channels\Controls\Control|null => $this->getRepository()->find($id),
		);
	}

	/**
	 * @throws Exceptions\InvalidState
	 */
	public function findOneBy(
		Queries\Entities\FindChannelControls $queryObject,
	): Entities\Channels\Controls\Control|null
	{
		return $this->database->query(
			fn (): Entities\Channels\Controls\Control|null => $queryObject->fetchOne($this->getRepository()),
		);
	}

	/**
	 * @return array<Entities\Channels\Controls\Control>
	 *
	 * @throws Exceptions\InvalidState
	 */
	public function findAll(): array
	{
		return $this->database->query(
			fn (): array => $this->getRepository()->findAll(),
		);
	}

	/**
	 * @return array<Entities\Channels\Controls\Control>
	 *
	 * @throws Exceptions\InvalidState
	 */
	public function findAllBy(Queries\Entities\FindChannelControls $queryObject): array
	{
		try {
			/** @var array<Entities\Channels\Controls\Control> $result */
			$result = $this->getResultSet($queryObject)->toArray();

			return $result;
		} catch (Throwable $ex) {
			throw new Exceptions\InvalidState('Fetch all data by query failed', $ex->getCode(), $ex);
		}
	}

	/**
	 * @return DoctrineOrmQuery\ResultSet<Entities\Channels\Controls\Control>
	 *
	 * @throws Exceptions\InvalidState
	 */
	public function getResultSet(
		Queries\Entities\FindChannelControls $queryObject,
	): DoctrineOrmQuery\ResultSet
	{
		$result = $this->database->query(
			fn (): DoctrineOrmQuery\ResultSet|array => $queryObject->fetch($this->getRepository()),
		);

		if (is_array($result)) {
			throw new Exceptions\InvalidState('Result set could not be created');
		}

		return $result;
	}

	/**
	 * @param class-string<Entities\Channels\Controls\Control> $type
	 *
	 * @return ORM\EntityRepository<Entities\Channels\Controls\Control>
	 */
	private function getRepository(string $type = Entities\Channels\Controls\Control::class): ORM\EntityRepository
	{
		if ($this->repository === null) {
			$this->repository = $this->managerRegistry->getRepository($type);
		}

		return $this->repository;
	}

}
