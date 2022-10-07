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
use function array_key_exists;
use function array_merge;
use function count;
use function is_a;
use function is_subclass_of;
use function strval;

/**
 * Data storage device properties repository
 *
 * @implements IteratorAggregate<int, MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity>
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class DevicePropertiesRepository implements Countable, IteratorAggregate
{

	use Nette\SmartObject;

	/** @var Array<string, Array<string, mixed>> */
	private array $rawData;

	/** @var Array<string, MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity> */
	private array $entities;

	public function __construct(
		private Models\States\DevicePropertiesRepository $statesRepository,
		private MetadataEntities\Modules\DevicesModule\DevicePropertyEntityFactory $entityFactory,
	)
	{
		$this->rawData = [];
		$this->entities = [];
	}

	/**
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findById(
		Uuid\UuidInterface $id,
	): MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|null
	{
		if (array_key_exists($id->toString(), $this->rawData)) {
			return $this->getEntity($id, $this->rawData[$id->toString()]);
		}

		return null;
	}

	/**
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findByIdentifier(
		Uuid\UuidInterface $device,
		string $identifier,
	): MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|null
	{
		foreach ($this->rawData as $id => $rawDataRow) {
			if (
				array_key_exists('device', $rawDataRow)
				&& $device->toString() === $rawDataRow['device']
				&& array_key_exists('identifier', $rawDataRow)
				&& Utils\Strings::lower(strval($rawDataRow['identifier'])) === Utils\Strings::lower($identifier)
			) {
				return $this->getEntity(Uuid\Uuid::fromString($id), $rawDataRow);
			}
		}

		return null;
	}

	/**
	 * @param class-string<T>|null $type
	 *
	 * @return Array<int, MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity>
	 *
	 * @template T
	 *
	 * @phpstan-return ($type is null ? Array<int, MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity> : Array<int, T>)
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findAllByDevice(
		Uuid\UuidInterface $device,
		string|null $type = null,
	): array
	{
		$entities = [];

		foreach ($this->rawData as $id => $rawDataRow) {
			if (array_key_exists('device', $rawDataRow) && $device->toString() === $rawDataRow['device']) {
				$entity = $this->getEntity(Uuid\Uuid::fromString($id), $rawDataRow);

				if ($type === null || is_a($entity, $type) || is_subclass_of($entity, $type)) {
					$entities[] = $entity;
				}
			}
		}

		return $entities;
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
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity>
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$entities = [];

		foreach ($this->rawData as $id => $rawDataRow) {
			$entities[] = $this->getEntity(Uuid\Uuid::fromString($id), $rawDataRow);
		}

		/** @var RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity> $result */
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
	): MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
	{
		if (!array_key_exists($id->toString(), $this->entities)) {
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
				$entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity
				|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
				|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
			) {
				$this->entities[$id->toString()] = $entity;
			} else {
				throw new Exceptions\InvalidState('Device property entity could not be created');
			}
		}

		return $this->entities[$id->toString()];
	}

	/**
	 * @return Array<string, mixed>
	 */
	private function loadPropertyState(Uuid\UuidInterface $id): array
	{
		try {
			$entityState = $this->statesRepository->findOneById($id);

			return $entityState?->toArray() ?? [];
		} catch (Exceptions\NotImplemented) {
			return [];
		}
	}

}
