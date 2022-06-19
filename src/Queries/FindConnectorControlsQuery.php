<?php declare(strict_types = 1);

/**
 * FindConnectorControlsQuery.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Queries
 * @since          0.4.0
 *
 * @date           29.09.21
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
 * Find connector properties entities query
 *
 * @package          FastyBird:DevicesModule!
 * @subpackage       Queries
 *
 * @author           Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends  DoctrineOrmQuery\QueryObject<Entities\Connectors\Controls\IControl>
 */
class FindConnectorControlsQuery extends DoctrineOrmQuery\QueryObject
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
			$qb->andWhere('c.id = :id')->setParameter('id', $id, Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	/**
	 * @param string $name
	 *
	 * @return void
	 */
	public function byName(string $name): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($name): void {
			$qb->andWhere('c.name = :name')->setParameter('name', $name);
		};
	}

	/**
	 * @param Entities\Connectors\IConnector $connector
	 *
	 * @return void
	 */
	public function forConnector(Entities\Connectors\IConnector $connector): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($connector): void {
			$qb->andWhere('connector.id = :connector')
				->setParameter('connector', $connector->getId(), Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	/**
	 * @param Uuid\UuidInterface $connectorId
	 *
	 * @return void
	 */
	public function byConnectorId(Uuid\UuidInterface $connectorId): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($connectorId): void {
			$qb->andWhere('connector.id = :connector')
				->setParameter('connector', $connectorId, Uuid\Doctrine\UuidBinaryType::NAME);
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
	 * @phpstan-param ORM\EntityRepository<Entities\Connectors\Controls\IControl> $repository
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
	 * @phpstan-param ORM\EntityRepository<Entities\Connectors\Controls\IControl> $repository
	 */
	private function createBasicDql(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $repository->createQueryBuilder('c');
		$qb->addSelect('connector');
		$qb->join('c.connector', 'connector');

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
	 * @phpstan-param ORM\EntityRepository<Entities\Connectors\Controls\IControl> $repository
	 */
	protected function doCreateCountQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $this->createBasicDql($repository)->select('COUNT(c.id)');

		foreach ($this->select as $modifier) {
			$modifier($qb);
		}

		return $qb;
	}

}
