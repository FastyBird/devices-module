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
use FastyBird\Metadata\Types as MetadataTypes;
use IteratorAggregate;
use Nette;
use Ramsey\Uuid;
use RecursiveArrayIterator;

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

	/** @var Array<string, Array<string, mixed>> */
	private array $rawData;

	/** @var Array<string, MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity> */
	private array $properties;

	private Models\States\ConnectorPropertiesRepository $statesRepository;

	private MetadataEntities\Modules\DevicesModule\ConnectorPropertyEntityFactory $entityFactory;

	public function __construct(
		Models\States\ConnectorPropertiesRepository $statesRepository,
		MetadataEntities\Modules\DevicesModule\ConnectorPropertyEntityFactory $entityFactory
	) {
		$this->statesRepository = $statesRepository;
		$this->entityFactory = $entityFactory;

		$this->rawData = [];
		$this->properties = [];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findById(
		Uuid\UuidInterface $id
	): MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity|null {
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
	public function findByIdentifier(
		Uuid\UuidInterface $connector,
		string $identifier
	): MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity|null {
		foreach ($this->rawData as $id => $property) {
			if (
				array_key_exists('connector', $property)
				&& $connector->toString() === $property['connector']
				&& array_key_exists('identifier', $property)
				&& $property['identifier'] === $identifier
			) {
				return $this->getEntity(Uuid\Uuid::fromString($id), $property);
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findAllByConnector(Uuid\UuidInterface $connector): array
	{
		$properties = [];

		foreach ($this->rawData as $id => $property) {
			if (array_key_exists('connector', $property) && $connector->toString() === $property['connector']) {
				$properties[] = $this->getEntity(Uuid\Uuid::fromString($id), $this->rawData[$id]);
			}
		}

		return $properties;
	}

	/**
	 * {@inheritDoc}
	 */
	public function append(Uuid\UuidInterface $id, array $data): void
	{
		$this->rawData[$id->toString()] = $data;

		if (!array_key_exists($id->toString(), $this->properties)) {
			unset($this->properties[$id->toString()]);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset(): void
	{
		$this->rawData = [];
		$this->properties = [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return count($this->rawData);
	}

	/**
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity>
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$properties = [];

		foreach ($this->rawData as $id => $property) {
			$properties[] = $this->getEntity(Uuid\Uuid::fromString($id), $property);
		}

		return new RecursiveArrayIterator($properties);
	}

	/**
	 * @param Uuid\UuidInterface $id
	 * @param Array<string, mixed> $data
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	private function getEntity(
		Uuid\UuidInterface $id,
		array $data
	): MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity {
		if (!array_key_exists($id->toString(), $this->properties)) {
			$state = [];

			if (
				array_key_exists('type', $data)
				&& (
					$data['type'] === MetadataTypes\PropertyTypeType::TYPE_DYNAMIC
					|| $data['type'] === MetadataTypes\PropertyTypeType::TYPE_MAPPED
				)
			) {
				$state = $this->loadPropertyState($id);
			}

			$entity = $this->entityFactory->create(array_merge($state, $data));

			if (
				$entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity
				|| $entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity
				|| $entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity
			) {
				$this->properties[$id->toString()] = $entity;
			} else {
				throw new Exceptions\InvalidStateException('Connector property entity could not be created');
			}
		}

		return $this->properties[$id->toString()];
	}

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return Array<string, mixed>
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
