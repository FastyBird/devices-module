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
	private array $connectors;

	private MetadataEntities\Modules\DevicesModule\ConnectorEntityFactory $entityFactory;

	public function __construct(
		MetadataEntities\Modules\DevicesModule\ConnectorEntityFactory $entityFactory
	) {
		$this->entityFactory = $entityFactory;

		$this->connectors = [];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IConnectorEntity
	{
		if (array_key_exists($id->toString(), $this->connectors)) {
			return $this->entityFactory->create($this->connectors[$id->toString()]);
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
		foreach ($this->connectors as $connector) {
			if (
				array_key_exists('identifier', $connector)
				&& $connector['identifier'] === $identifier
			) {
				return $this->entityFactory->create($connector);
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function append(Uuid\UuidInterface $id, array $entity): void
	{
		$this->connectors[$id->toString()] = $entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset(): void
	{
		$this->connectors = [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return count($this->connectors);
	}

	/**
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IConnectorEntity>
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$connectors = [];

		foreach ($this->connectors as $connector) {
			$connectors[] = $this->entityFactory->create($connector);
		}

		return new RecursiveArrayIterator($connectors);
	}

}
