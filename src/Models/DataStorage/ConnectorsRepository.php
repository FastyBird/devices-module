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
use IteratorAggregate;
use Nette;
use Ramsey\Uuid;
use RecursiveArrayIterator;
use SplObjectStorage;

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

	/** @var SplObjectStorage<MetadataEntities\Modules\DevicesModule\IConnectorEntity, string> */
	private SplObjectStorage $connectors;

	public function __construct()
	{
		$this->connectors = new SplObjectStorage();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IConnectorEntity
	{
		$this->connectors->rewind();

		foreach ($this->connectors as $connector) {
			if ($connector->getId()->equals($id)) {
				return $connector;
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findByIdentifier(string $identifier): ?MetadataEntities\Modules\DevicesModule\IConnectorEntity
	{
		$this->connectors->rewind();

		foreach ($this->connectors as $connector) {
			if ($connector->getIdentifier() === $identifier) {
				return $connector;
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function append(MetadataEntities\Modules\DevicesModule\IConnectorEntity $entity): void
	{
		$existing = $this->findById($entity->getId());

		if ($existing !== null) {
			$this->connectors->detach($existing);
		}

		if (!$this->connectors->contains($entity)) {
			$this->connectors->attach($entity, $entity->getId()->toString());
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset(): void
	{
		$this->connectors = new SplObjectStorage();
	}

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return $this->connectors->count();
	}

	/**
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IConnectorEntity>
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$connectors = [];

		foreach ($this->connectors as $connector) {
			$connectors[] = $connector;
		}

		return new RecursiveArrayIterator($connectors);
	}

}
