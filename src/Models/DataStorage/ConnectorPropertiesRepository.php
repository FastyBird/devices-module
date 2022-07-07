<?php declare(strict_types = 1);

/**
 * ConnectorPropertiesRepository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DataStorage!
 * @subpackage     Models
 * @since          0.62.0
 *
 * @date           17.06.22
 */

namespace FastyBird\DevicesModule\Models\DataStorage;

use Countable;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Exceptions as MetadataExceptions;
use IteratorAggregate;
use Nette;
use Nette\Utils;
use Ramsey\Uuid;
use RecursiveArrayIterator;
use SplObjectStorage;

/**
 * Data storage connector properties repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @implements IteratorAggregate<int, MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity>
 */
final class ConnectorPropertiesRepository implements IConnectorPropertiesRepository, Countable, IteratorAggregate
{

	use Nette\SmartObject;

	/** @var SplObjectStorage<MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity, string> */
	private SplObjectStorage $properties;

	/** @var Models\States\ConnectorPropertiesRepository */
	private Models\States\ConnectorPropertiesRepository $statesRepository;

	/** @var MetadataEntities\Modules\DevicesModule\ConnectorPropertyEntityFactory */
	private MetadataEntities\Modules\DevicesModule\ConnectorPropertyEntityFactory $entityFactory;

	public function __construct(
		Models\States\ConnectorPropertiesRepository $statesRepository,
		MetadataEntities\Modules\DevicesModule\ConnectorPropertyEntityFactory $entityFactory
	) {
		$this->statesRepository = $statesRepository;
		$this->entityFactory = $entityFactory;

		$this->properties = new SplObjectStorage();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findById(
		Uuid\UuidInterface $id
	): MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity|null {
		$this->properties->rewind();

		foreach ($this->properties as $property) {
			if ($property->getId()->equals($id)) {
				return $property;
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findByIdentifier(
		Uuid\UuidInterface $connector,
		string $identifier
	): MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity|null {
		$this->properties->rewind();

		foreach ($this->properties as $property) {
			if (
				$property->getConnector()->equals($connector)
				&& $property->getIdentifier() === $identifier
			) {
				return $property;
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findAllByConnector(Uuid\UuidInterface $connector): array
	{
		$properties = [];

		$this->properties->rewind();

		foreach ($this->properties as $property) {
			if ($property->getConnector()->equals($connector)) {
				$properties[] = $property;
			}
		}

		return $properties;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Utils\JsonException
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function append(
		MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity $entity
	): void {
		$existing = $this->findById($entity->getId());

		if ($existing !== null) {
			$this->properties->detach($existing);
		}

		if (!$this->properties->contains($entity)) {
			$entity = $this->entityFactory->create(
				Utils\Json::encode(array_merge(
					$entity->toArray(),
					$this->loadPropertyState($entity->getId())
				))
			);

			if (
				$entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity
				|| $entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity
				|| $entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity
			) {
				$this->properties->attach($entity, $entity->getId()->toString());
			}
		}
	}

	/**
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @return void
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 * @throws Utils\JsonException
	 */
	public function loadState(?Uuid\UuidInterface $id = null): void
	{
		if ($id === null) {
			foreach ($this->properties as $property) {
				$this->append($property);
			}
		} else {
			$property = $this->findById($id);

			if ($property === null) {
				return;
			}

			$this->append($property);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset(): void
	{
		$this->properties = new SplObjectStorage();
	}

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return $this->properties->count();
	}

	/**
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity>
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$properties = [];

		foreach ($this->properties as $property) {
			$properties[] = $property;
		}

		return new RecursiveArrayIterator($properties);
	}

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return mixed[]
	 */
	private function loadPropertyState(Uuid\UuidInterface $id): array
	{
		try {
			$propertyState = $this->statesRepository->findOneById($id);

			return $propertyState !== null ? $propertyState->toArray() : [];
		} catch (Exceptions\NotImplementedException $ex) {
			return [];
		}
	}

}
