<?php declare(strict_types = 1);

/**
 * FindChannelsQuery.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Queries
 * @since          0.1.0
 *
 * @date           30.07.18
 */

namespace FastyBird\DevicesModule\Queries;

use Closure;
use Doctrine\Common;
use Doctrine\ORM;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use IPub\DoctrineOrmQuery;
use Ramsey\Uuid;

/**
 * Find device channels entities query
 *
 * @package          FastyBird:DevicesModule!
 * @subpackage       Queries
 *
 * @author           Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends  DoctrineOrmQuery\QueryObject<Entities\Channels\IChannel>
 */
class FindChannelsQuery extends DoctrineOrmQuery\QueryObject
{

	/** @var Closure[] */
	private array $filter = [];

	/** @var Closure[] */
	private array $select = [];

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return void
	 */
	public function byId(Uuid\UuidInterface $id): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($id): void {
			$qb->andWhere('ch.id = :id')->setParameter('id', $id, Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	/**
	 * @param string $key
	 *
	 * @return void
	 */
	public function byKey(string $key): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($key): void {
			$qb->andWhere('ch.key = :key')->setParameter('key', $key);
		};
	}

	/**
	 * @param string $identifier
	 *
	 * @return void
	 */
	public function byIdentifier(string $identifier): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($identifier): void {
			$qb->andWhere('ch.identifier = :identifier')->setParameter('identifier', $identifier);
		};
	}

	/**
	 * @param Entities\Devices\IDevice $device
	 *
	 * @return void
	 */
	public function forDevice(Entities\Devices\IDevice $device): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($device): void {
			$qb->andWhere('device.id = :device')->setParameter('device', $device->getId(), Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	/**
	 * @return void
	 */
	public function withProperties(): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb): void {
			$qb->andWhere('SIZE(ch.properties) <> 0');
		};
	}

	/**
	 * @return void
	 */
	public function withSettableProperties(): void
	{
		$this->select[] = function (ORM\QueryBuilder $qb): void {
			$qb->join('ch.properties', 'properties');
		};

		$this->filter[] = function (ORM\QueryBuilder $qb): void {
			$qb->andWhere('properties.settable = :settable')->setParameter('settable', true);
		};
	}

	/**
	 * @param string $sortBy
	 * @param string $sortDir
	 *
	 * @return void
	 */
	public function sortBy(string $sortBy, string $sortDir = Common\Collections\Criteria::ASC): void
	{
		if (!in_array($sortDir, [Common\Collections\Criteria::ASC, Common\Collections\Criteria::DESC], true)) {
			throw new Exceptions\InvalidArgumentException('Provided sortDir value is not valid.');
		}

		$this->filter[] = function (ORM\QueryBuilder $qb) use ($sortBy, $sortDir): void {
			$qb->addOrderBy($sortBy, $sortDir);
		};
	}

	/**
	 * @param ORM\EntityRepository $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<Entities\Channels\IChannel> $repository
	 */
	protected function doCreateQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $this->createBasicDql($repository);

		foreach ($this->select as $modifier) {
			$modifier($qb);
		}

		return $qb;
	}

	/**
	 * @param ORM\EntityRepository $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<Entities\Channels\IChannel> $repository
	 */
	private function createBasicDql(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $repository->createQueryBuilder('ch');
		$qb->addSelect('device');
		$qb->join('ch.device', 'device');

		foreach ($this->filter as $modifier) {
			$modifier($qb);
		}

		return $qb;
	}

	/**
	 * @param ORM\EntityRepository $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<Entities\Channels\IChannel> $repository
	 */
	protected function doCreateCountQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $this->createBasicDql($repository)->select('COUNT(ch.id)');

		foreach ($this->select as $modifier) {
			$modifier($qb);
		}

		return $qb;
	}

}
