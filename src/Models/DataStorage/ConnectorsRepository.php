<?php declare(strict_types = 1);

/**
 * ConnectorsRepository.php
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
use FastyBird\Metadata\Exceptions as MetadataExceptions;
use IteratorAggregate;
use Nette;
use Nette\Utils;
use Ramsey\Uuid;
use RecursiveArrayIterator;
use function array_key_exists;
use function count;
use function strval;

/**
 * Data storage connectors repository
 *
 * @implements IteratorAggregate<int, MetadataEntities\Modules\DevicesModule\IConnectorEntity>
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ConnectorsRepository implements Countable, IteratorAggregate
{

	use Nette\SmartObject;

	/** @var Array<string, Array<string, mixed>> */
	private array $rawData;

	/** @var Array<string, MetadataEntities\Modules\DevicesModule\IConnectorEntity> */
	private array $entities;

	public function __construct(
		private MetadataEntities\Modules\DevicesModule\ConnectorEntityFactory $entityFactory,
	)
	{
		$this->rawData = [];
		$this->entities = [];
	}

	/**
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findById(Uuid\UuidInterface $id): MetadataEntities\Modules\DevicesModule\IConnectorEntity|null
	{
		if (array_key_exists($id->toString(), $this->rawData)) {
			return $this->getEntity($id, $this->rawData[$id->toString()]);
		}

		return null;
	}

	/**
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findByIdentifier(string $identifier): MetadataEntities\Modules\DevicesModule\IConnectorEntity|null
	{
		foreach ($this->rawData as $id => $rawDataRow) {
			if (
				array_key_exists('identifier', $rawDataRow)
				&& Utils\Strings::lower(strval($rawDataRow['identifier'])) === Utils\Strings::lower($identifier)
			) {
				return $this->getEntity(Uuid\Uuid::fromString($id), $rawDataRow);
			}
		}

		return null;
	}

	/**
	 * @param Array<string, mixed> $data
	 */
	public function append(Uuid\UuidInterface $id, array $data): void
	{
		$this->rawData[$id->toString()] = $data;

		if (array_key_exists($id->toString(), $this->entities)) {
			unset($this->entities[$id->toString()]);
		}
	}

	public function clear(): void
	{
		$this->rawData = [];
		$this->entities = [];
	}

	/**
	 * @param Uuid\UuidInterface|Array<Uuid\UuidInterface> $id
	 */
	public function reset(Uuid\UuidInterface|array $id): void
	{
		if ($id instanceof Uuid\UuidInterface) {
			if (array_key_exists($id->toString(), $this->entities)) {
				unset($this->entities[$id->toString()]);
			}
		} else {
			$ids = $id;

			foreach ($ids as $id) {
				if (array_key_exists($id->toString(), $this->entities)) {
					unset($this->entities[$id->toString()]);
				}
			}
		}
	}

	public function count(): int
	{
		return count($this->rawData);
	}

	/**
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IConnectorEntity>
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$entities = [];

		foreach ($this->rawData as $id => $rawDataRow) {
			$entities[] = $this->getEntity(Uuid\Uuid::fromString($id), $rawDataRow);
		}

		/** @var RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IConnectorEntity> $result */
		$result = new RecursiveArrayIterator($entities);

		return $result;
	}

	/**
	 * @param Array<string, mixed> $data
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	private function getEntity(
		Uuid\UuidInterface $id,
		array $data,
	): MetadataEntities\Modules\DevicesModule\IConnectorEntity
	{
		if (!array_key_exists($id->toString(), $this->entities)) {
			$this->entities[$id->toString()] = $this->entityFactory->create($data);
		}

		return $this->entities[$id->toString()];
	}

}
