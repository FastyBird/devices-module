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
use Ramsey\Uuid;
use RecursiveArrayIterator;

/**
 * Data storage connectors repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @implements IteratorAggregate<int, MetadataEntities\Modules\DevicesModule\IConnectorEntity>
 */
final class ConnectorsRepository implements IConnectorsRepository, Countable, IteratorAggregate
{

	use Nette\SmartObject;

	/** @var Array<string, Array<string, mixed>> */
	private array $rawData;

	/** @var Array<string, MetadataEntities\Modules\DevicesModule\IConnectorEntity> */
	private array $connectors;

	private MetadataEntities\Modules\DevicesModule\ConnectorEntityFactory $entityFactory;

	public function __construct(
		MetadataEntities\Modules\DevicesModule\ConnectorEntityFactory $entityFactory
	) {
		$this->entityFactory = $entityFactory;

		$this->rawData = [];
		$this->connectors = [];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IConnectorEntity
	{
		if (array_key_exists($id->toString(), $this->rawData)) {
			return $this->getEntity($id, $this->rawData[$id->toString()]);
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findByIdentifier(string $identifier): ?MetadataEntities\Modules\DevicesModule\IConnectorEntity
	{
		foreach ($this->rawData as $id => $connector) {
			if (
				array_key_exists('identifier', $connector)
				&& $connector['identifier'] === $identifier
			) {
				return $this->getEntity(Uuid\Uuid::fromString($id), $connector);
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function append(Uuid\UuidInterface $id, array $data): void
	{
		$this->rawData[$id->toString()] = $data;

		if (!array_key_exists($id->toString(), $this->connectors)) {
			unset($this->connectors[$id->toString()]);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset(): void
	{
		$this->rawData = [];
		$this->connectors = [];
	}

	/**
	 * {@inheritDoc}
	 */
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
		$connectors = [];

		foreach ($this->rawData as $id => $connector) {
			$connectors[] = $this->getEntity(Uuid\Uuid::fromString($id), $connector);
		}

		return new RecursiveArrayIterator($connectors);
	}

	/**
	 * @param Uuid\UuidInterface $id
	 * @param Array<string, mixed> $data
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IConnectorEntity
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	private function getEntity(
		Uuid\UuidInterface $id,
		array $data
	): MetadataEntities\Modules\DevicesModule\IConnectorEntity {
		if (!array_key_exists($id->toString(), $this->connectors)) {
			$this->connectors[$id->toString()] = $this->entityFactory->create($data);
		}

		return $this->connectors[$id->toString()];
	}

}
