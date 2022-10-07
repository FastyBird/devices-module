<?php declare(strict_types = 1);

/**
 * ChannelsRepository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           23.04.17
 */

namespace FastyBird\DevicesModule\Models\Channels;

use Doctrine\ORM;
use Doctrine\Persistence;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineOrmQuery;
use Nette;
use Throwable;

/**
 * Channel repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ChannelsRepository
{

	use Nette\SmartObject;

	/** @var ORM\EntityRepository<Entities\Channels\Channel>|null */
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
	 * @param Queries\FindChannels $queryObject
	 *
	 * @return Entities\Channels\Channel|null
	 */
	public function findOneBy(Queries\FindChannels $queryObject): ?Entities\Channels\Channel
	{
		/** @var Entities\Channels\Channel|null $channel */
		$channel = $queryObject->fetchOne($this->getRepository());

		return $channel;
	}

	/**
	 * @param Queries\FindChannels $queryObject
	 *
	 * @return Entities\Channels\Channel[]
	 *
	 * @throws Throwable
	 */
	public function findAllBy(Queries\FindChannels $queryObject): array
	{
		/** @var Array<Entities\Channels\Channel>|DoctrineOrmQuery\ResultSet<Entities\Channels\Channel> $result */
		$result = $queryObject->fetch($this->getRepository());

		if (is_array($result)) {
			return $result;
		}

		/** @var Entities\Channels\Channel[] $data */
		$data = $result->toArray();

		return $data;
	}

	/**
	 * @param Queries\FindChannels $queryObject
	 *
	 * @return DoctrineOrmQuery\ResultSet<Entities\Channels\Channel>
	 */
	public function getResultSet(
		Queries\FindChannels $queryObject
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
	 * @return ORM\EntityRepository<Entities\Channels\Channel>
	 */
	private function getRepository(string $type = Entities\Channels\Channel::class): ORM\EntityRepository
	{
		if ($this->repository === null) {
			/** @var ORM\EntityRepository<Entities\Channels\Channel> $repository */
			$repository = $this->managerRegistry->getRepository($type);

			if (!$repository instanceof ORM\EntityRepository) {
				throw new Exceptions\InvalidState('Entity repository could not be loaded');
			}

			$this->repository = $repository;
		}

		return $this->repository;
	}

}
