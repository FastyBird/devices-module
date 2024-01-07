<?php declare(strict_types = 1);

/**
 * FindConnectorProperties.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Queries
 * @since          1.0.0
 *
 * @date           08.02.22
 */

namespace FastyBird\Module\Devices\Queries\Entities;

use Closure;
use Doctrine\Common;
use Doctrine\ORM;
use FastyBird\Module\Devices\Entities;
use FastyBird\Module\Devices\Exceptions;
use IPub\DoctrineOrmQuery;
use Ramsey\Uuid;
use function in_array;

/**
 * Find connector properties entities query
 *
 * @template T of Entities\Connectors\Properties\Property
 * @extends  DoctrineOrmQuery\QueryObject<T>
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Queries
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class FindConnectorProperties extends DoctrineOrmQuery\QueryObject
{

	/** @var array<Closure(ORM\QueryBuilder $qb): void> */
	protected array $filter = [];

	/** @var array<Closure(ORM\QueryBuilder $qb): void> */
	protected array $select = [];

	public function byId(Uuid\UuidInterface $id): void
	{
		$this->filter[] = static function (ORM\QueryBuilder $qb) use ($id): void {
			$qb->andWhere('p.id = :id')->setParameter('id', $id, Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	public function byIdentifier(string $identifier): void
	{
		$this->filter[] = static function (ORM\QueryBuilder $qb) use ($identifier): void {
			$qb->andWhere('p.identifier = :identifier')->setParameter('identifier', $identifier);
		};
	}

	public function startWithIdentifier(string $identifier): void
	{
		$this->filter[] = static function (ORM\QueryBuilder $qb) use ($identifier): void {
			$qb->andWhere('p.identifier LIKE :identifier')->setParameter('identifier', $identifier . '%');
		};
	}

	public function endWithIdentifier(string $identifier): void
	{
		$this->filter[] = static function (ORM\QueryBuilder $qb) use ($identifier): void {
			$qb->andWhere('p.identifier LIKE :identifier')->setParameter('identifier', '%' . $identifier);
		};
	}

	public function forConnector(Entities\Connectors\Connector $connector): void
	{
		$this->filter[] = static function (ORM\QueryBuilder $qb) use ($connector): void {
			$qb->andWhere('connector.id = :connector')
				->setParameter('connector', $connector->getId(), Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	public function byConnectorId(Uuid\UuidInterface $connectorId): void
	{
		$this->filter[] = static function (ORM\QueryBuilder $qb) use ($connectorId): void {
			$qb->andWhere('connector.id = :connector')
				->setParameter('connector', $connectorId, Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	public function settable(bool $state): void
	{
		$this->filter[] = static function (ORM\QueryBuilder $qb) use ($state): void {
			$qb->andWhere('p.settable = :settable')->setParameter('settable', $state);
		};
	}

	public function queryable(bool $state): void
	{
		$this->filter[] = static function (ORM\QueryBuilder $qb) use ($state): void {
			$qb->andWhere('p.queryable = :queryable')->setParameter('queryable', $state);
		};
	}

	/**
	 * @throws Exceptions\InvalidArgument
	 */
	public function sortBy(string $sortBy, string $sortDir = Common\Collections\Criteria::ASC): void
	{
		if (!in_array($sortDir, [Common\Collections\Criteria::ASC, Common\Collections\Criteria::DESC], true)) {
			throw new Exceptions\InvalidArgument('Provided sortDir value is not valid.');
		}

		$this->filter[] = static function (ORM\QueryBuilder $qb) use ($sortBy, $sortDir): void {
			$qb->addOrderBy($sortBy, $sortDir);
		};
	}

	/**
	 * @param ORM\EntityRepository<T> $repository
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
	 * @param ORM\EntityRepository<T> $repository
	 */
	private function createBasicDql(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $repository->createQueryBuilder('p');
		$qb->addSelect('connector');
		$qb->join('p.connector', 'connector');

		foreach ($this->filter as $modifier) {
			$modifier($qb);
		}

		return $qb;
	}

	/**
	 * @param ORM\EntityRepository<T> $repository
	 */
	protected function doCreateCountQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $this->createBasicDql($repository)->select('COUNT(p.id)');

		foreach ($this->select as $modifier) {
			$modifier($qb);
		}

		return $qb;
	}

}
