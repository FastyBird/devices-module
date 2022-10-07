<?php declare(strict_types = 1);

/**
 * FindDevices.php
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
 * Find devices entities query
 *
 * @package          FastyBird:DevicesModule!
 * @subpackage       Queries
 *
 * @author           Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends  DoctrineOrmQuery\QueryObject<Entities\Devices\Device>
 */
class FindDevices extends DoctrineOrmQuery\QueryObject
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
			$qb->andWhere('d.id = :id')->setParameter('id', $id, Uuid\Doctrine\UuidBinaryType::NAME);
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
			$qb->andWhere('d.identifier = :identifier')->setParameter('identifier', $identifier);
		};
	}

	/**
	 * @param Uuid\UuidInterface $connectorId
	 *
	 * @return void
	 */
	public function byConnectorId(Uuid\UuidInterface $connectorId): void
	{
		$this->select[] = function (ORM\QueryBuilder $qb): void {
			$qb->addSelect('connector');
			$qb->join('d.connector', 'connector');
		};

		$this->filter[] = function (ORM\QueryBuilder $qb) use ($connectorId): void {
			$qb->andWhere('connector.id = :connector')
				->setParameter('connector', $connectorId, Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	/**
	 * @param Entities\Connectors\Connector $connector
	 *
	 * @return void
	 */
	public function forConnector(Entities\Connectors\Connector $connector): void
	{
		$this->select[] = function (ORM\QueryBuilder $qb): void {
			$qb->addSelect('connector');
			$qb->join('d.connector', 'connector');
		};

		$this->filter[] = function (ORM\QueryBuilder $qb) use ($connector): void {
			$qb->andWhere('connector.id = :connector')
				->setParameter('connector', $connector->getId(), Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	/**
	 * @param Entities\Devices\Device $device
	 *
	 * @return void
	 */
	public function forParent(Entities\Devices\Device $device): void
	{
		$this->select[] = function (ORM\QueryBuilder $qb): void {
			$qb->innerJoin('d.parents', 'dp');
		};

		$this->filter[] = function (ORM\QueryBuilder $qb) use ($device): void {
			$qb->andWhere('dp.id = :parentDevice')
				->setParameter('parentDevice', $device->getId(), Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	/**
	 * @param Entities\Devices\Device $device
	 *
	 * @return void
	 */
	public function forChild(Entities\Devices\Device $device): void
	{
		$this->select[] = function (ORM\QueryBuilder $qb): void {
			$qb->innerJoin('d.children', 'dch');
		};

		$this->filter[] = function (ORM\QueryBuilder $qb) use ($device): void {
			$qb->andWhere('dch.id = :childDevice')
				->setParameter('childDevice', $device->getId(), Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	/**
	 * @return void
	 */
	public function withSettableChannelProperties(): void
	{
		$this->select[] = function (ORM\QueryBuilder $qb): void {
			$qb->join('d.channels', 'channels');
			$qb->join('channels.properties', 'chProperties');
		};

		$this->filter[] = function (ORM\QueryBuilder $qb): void {
			$qb->andWhere('chProperties.settable = :settable')->setParameter('settable', true);
		};
	}

	/**
	 * @return void
	 */
	public function withChannels(): void
	{
		$this->select[] = function (ORM\QueryBuilder $qb): void {
			$qb->join('d.channels', 'channels');
		};

		$this->filter[] = function (ORM\QueryBuilder $qb): void {
			$qb->andWhere('SIZE(channels.children) = 0');
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
			throw new Exceptions\InvalidArgument('Provided sortDir value is not valid.');
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
	 * @phpstan-param ORM\EntityRepository<Entities\Devices\Device> $repository
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
	 * @phpstan-param ORM\EntityRepository<Entities\Devices\Device> $repository
	 */
	protected function createBasicDql(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $repository->createQueryBuilder('d');

		// $qb->select('nd');
		// $qb->leftJoin(Entities\Devices\NetworkDevice::class, 'nd', ORM\Query\Expr\Join::WITH, 'd = nd');

		// $qb->select('ld');
		// $qb->leftJoin(Entities\Devices\LocalDevice::class, 'ld', ORM\Query\Expr\Join::WITH, 'd = ld');

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
	 * @phpstan-param ORM\EntityRepository<Entities\Devices\Device> $repository
	 */
	protected function doCreateCountQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $this->createBasicDql($repository)->select('COUNT(d.id)');

		foreach ($this->select as $modifier) {
			$modifier($qb);
		}

		return $qb;
	}

}
