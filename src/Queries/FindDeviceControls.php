<?php declare(strict_types = 1);

/**
 * FindDeviceControls.php
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
use function in_array;

/**
 * Find device controls entities query
 *
 * @phpstan-extends  DoctrineOrmQuery\QueryObject<Entities\Devices\Controls\Control>
 *
 * @package          FastyBird:DevicesModule!
 * @subpackage       Queries
 * @author           Adam Kadlec <adam.kadlec@fastybird.com>
 */
class FindDeviceControls extends DoctrineOrmQuery\QueryObject
{

	/** @var Array<Closure> */
	private array $filter = [];

	/** @var Array<Closure> */
	private array $select = [];

	public function byId(Uuid\UuidInterface $id): void
	{
		$this->filter[] = static function (ORM\QueryBuilder $qb) use ($id): void {
			$qb->andWhere('c.id = :id')->setParameter('id', $id, Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	public function byName(string $name): void
	{
		$this->filter[] = static function (ORM\QueryBuilder $qb) use ($name): void {
			$qb->andWhere('c.name = :name')->setParameter('name', $name);
		};
	}

	public function forDevice(Entities\Devices\Device $device): void
	{
		$this->filter[] = static function (ORM\QueryBuilder $qb) use ($device): void {
			$qb->andWhere('device.id = :device')
				->setParameter('device', $device->getId(), Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	public function byDeviceId(Uuid\UuidInterface $deviceId): void
	{
		$this->filter[] = static function (ORM\QueryBuilder $qb) use ($deviceId): void {
			$qb->andWhere('device.id = :device')
				->setParameter('device', $deviceId, Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

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
	 * @phpstan-param ORM\EntityRepository<Entities\Devices\Controls\Control> $repository
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
	 * @phpstan-param ORM\EntityRepository<Entities\Devices\Controls\Control> $repository
	 */
	private function createBasicDql(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $repository->createQueryBuilder('c');
		$qb->addSelect('device');
		$qb->join('c.device', 'device');

		foreach ($this->filter as $modifier) {
			$modifier($qb);
		}

		return $qb;
	}

	/**
	 * @phpstan-param ORM\EntityRepository<Entities\Devices\Controls\Control> $repository
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
