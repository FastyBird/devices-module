<?php declare(strict_types = 1);

/**
 * DevicePropertiesRepository.php
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
use Nette\Utils;
use Ramsey\Uuid;
use RecursiveArrayIterator;

/**
 * Data storage device properties repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @implements IteratorAggregate<int, MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity>
 */
final class DevicePropertiesRepository implements IDevicePropertiesRepository, Countable, IteratorAggregate
{

	use Nette\SmartObject;

	/** @var Array<string, Array<string, mixed>> */
	private array $properties;

	private Models\States\DevicePropertiesRepository $statesRepository;

	private MetadataEntities\Modules\DevicesModule\DevicePropertyEntityFactory $entityFactory;

	public function __construct(
		Models\States\DevicePropertiesRepository $statesRepository,
		MetadataEntities\Modules\DevicesModule\DevicePropertyEntityFactory $entityFactory
	) {
		$this->statesRepository = $statesRepository;
		$this->entityFactory = $entityFactory;

		$this->properties = [];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findById(
		Uuid\UuidInterface $id
	): MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity|null {
		if (array_key_exists($id->toString(), $this->properties)) {
			$data = $this->properties[$id->toString()];

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
				$entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
				|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity
				|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
			) {
				return $entity;
			}

			return null;
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findByIdentifier(
		Uuid\UuidInterface $device,
		string $identifier
	): MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity|null {
		foreach ($this->properties as $id => $property) {
			if (
				array_key_exists('device', $property)
				&& $device->toString() === $property['device']
				&& array_key_exists('identifier', $property)
				&& $property['identifier'] === $identifier
			) {
				$state = [];

				if (
					array_key_exists('type', $property)
					&& (
						$property['type'] === MetadataTypes\PropertyTypeType::TYPE_DYNAMIC
						|| $property['type'] === MetadataTypes\PropertyTypeType::TYPE_MAPPED
					)
				) {
					$state = $this->loadPropertyState(Uuid\Uuid::fromString($id));
				}

				$entity = $this->entityFactory->create(array_merge($state, $property));

				if (
					$entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
					|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity
					|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
				) {
					return $entity;
				}

				return null;
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findAllByDevice(Uuid\UuidInterface $device): array
	{
		$properties = [];

		foreach ($this->properties as $id => $property) {
			if (array_key_exists('device', $property) && $device->toString() === $property['device']) {
				$state = [];

				if (
					array_key_exists('type', $property)
					&& (
						$property['type'] === MetadataTypes\PropertyTypeType::TYPE_DYNAMIC
						|| $property['type'] === MetadataTypes\PropertyTypeType::TYPE_MAPPED
					)
				) {
					$state = $this->loadPropertyState(Uuid\Uuid::fromString($id));
				}

				$entity = $this->entityFactory->create(array_merge($state, $property));

				if (
					$entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
					|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity
					|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
				) {
					$properties[] = $entity;
				}
			}
		}

		return $properties;
	}

	/**
	 * {@inheritDoc}
	 */
	public function append(Uuid\UuidInterface $id, array $entity): void
	{
		$this->properties[$id->toString()] = $entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset(): void
	{
		$this->properties = [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return count($this->properties);
	}

	/**
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity>
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$properties = [];

		foreach ($this->properties as $id => $property) {
			$state = [];

			if (
				array_key_exists('type', $property)
				&& (
					$property['type'] === MetadataTypes\PropertyTypeType::TYPE_DYNAMIC
					|| $property['type'] === MetadataTypes\PropertyTypeType::TYPE_MAPPED
				)
			) {
				$state = $this->loadPropertyState(Uuid\Uuid::fromString($id));
			}

			$entity = $this->entityFactory->create(array_merge($state, $property));

			if (
				$entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
				|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity
				|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
			) {
				$properties[] = $entity;
			}
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
