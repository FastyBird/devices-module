<?php declare(strict_types = 1);

/**
 * IConnectorControlsRepository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DataStorage!
 * @subpackage     Models
 * @since          0.62.0
 *
 * @date           16.06.22
 */

namespace FastyBird\DevicesModule\Models\DataStorage;

use Countable;
use FastyBird\Metadata\Entities as MetadataEntities;
use IteratorAggregate;
use Ramsey\Uuid;

/**
 * Data storage connector properties repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @extends IteratorAggregate<int, MetadataEntities\Modules\DevicesModule\IConnectorControlEntity>
 */
interface IConnectorControlsRepository extends Countable, IteratorAggregate
{

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IConnectorControlEntity|null
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IConnectorControlEntity;

	/**
	 * @param Uuid\UuidInterface $connector
	 *
	 * @return Array<int, MetadataEntities\Modules\DevicesModule\IConnectorControlEntity>
	 */
	public function findAllByConnector(Uuid\UuidInterface $connector): array;

	/**
	 * @param Uuid\UuidInterface $id
	 * @param Array<string, mixed> $data
	 *
	 * @return void
	 */
	public function append(Uuid\UuidInterface $id, array $data): void;

	/**
	 * @return void
	 */
	public function clear(): void;

	/**
	 * @param Uuid\UuidInterface|Array<int, Uuid\UuidInterface> $id
	 *
	 * @return void
	 */
	public function reset(Uuid\UuidInterface|array $id): void;

}
