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

namespace FastyBird\Module\Devices\Models\Channels;

use Doctrine\ORM;
use Doctrine\Persistence;
use FastyBird\Module\Devices\Entities;
use FastyBird\Module\Devices\Exceptions;
use FastyBird\Module\Devices\Queries;
use FastyBird\Module\Devices\Utilities;
use IPub\DoctrineOrmQuery;
use Nette;
use function is_array;

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

	/** @var array<ORM\EntityRepository<Entities\Channels\Channel>> */
	private array $repository = [];

	public function __construct(
		private readonly Utilities\Database $database,
		private readonly Persistence\ManagerRegistry $managerRegistry,
	)
	{
	}

	/**
	 * @template T of Entities\Channels\Channel
	 *
	 * @phpstan-param Queries\FindChannels<T> $queryObject
	 * @phpstan-param class-string<T> $type
	 *
	 * @throws Exceptions\InvalidState
	 */
	public function findOneBy(
		Queries\FindChannels $queryObject,
		string $type = Entities\Channels\Channel::class,
	): Entities\Channels\Channel|null
	{
		return $this->database->query(
			fn (): Entities\Channels\Channel|null => $queryObject->fetchOne($this->getRepository($type)),
		);
	}

	/**
	 * @template T of Entities\Channels\Channel
	 *
	 * @phpstan-param Queries\FindChannels<T> $queryObject
	 * @phpstan-param class-string<T> $type
	 *
	 * @phpstan-return array<T>
	 *
	 * @throws Exceptions\InvalidState
	 */
	public function findAllBy(
		Queries\FindChannels $queryObject,
		string $type = Entities\Channels\Channel::class,
	): array
	{
		// @phpstan-ignore-next-line
		return $this->database->query(
			function () use ($queryObject, $type): array {
				/** @var array<T>|DoctrineOrmQuery\ResultSet<T> $result */
				$result = $queryObject->fetch($this->getRepository($type));

				if (is_array($result)) {
					return $result;
				}

				/** @var array<T> $data */
				$data = $result->toArray();

				return $data;
			},
		);
	}

	/**
	 * @template T of Entities\Channels\Channel
	 *
	 * @phpstan-param Queries\FindChannels<T> $queryObject
	 * @phpstan-param class-string<T> $type
	 *
	 * @phpstan-return DoctrineOrmQuery\ResultSet<T>
	 *
	 * @throws Exceptions\InvalidState
	 */
	public function getResultSet(
		Queries\FindChannels $queryObject,
		string $type = Entities\Channels\Channel::class,
	): DoctrineOrmQuery\ResultSet
	{
		return $this->database->query(
			function () use ($queryObject, $type): DoctrineOrmQuery\ResultSet {
				$result = $queryObject->fetch($this->getRepository($type));

				if (is_array($result)) {
					throw new Exceptions\InvalidState('Err');
				}

				return $result;
			},
		);
	}

	/**
	 * @template T of Entities\Channels\Channel
	 *
	 * @phpstan-param class-string<T> $type
	 *
	 * @phpstan-return ORM\EntityRepository<T>
	 */
	private function getRepository(string $type): ORM\EntityRepository
	{
		if (!isset($this->repository[$type])) {
			$this->repository[$type] = $this->managerRegistry->getRepository($type);
		}

		/** @var ORM\EntityRepository<T> $repository */
		$repository = $this->repository[$type];

		return $repository;
	}

}
