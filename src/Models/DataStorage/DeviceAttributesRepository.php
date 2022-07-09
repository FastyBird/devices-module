<?php declare(strict_types = 1);

/**
 * DeviceAttributesRepository.php
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
use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Exceptions as MetadataExceptions;
use IteratorAggregate;
use Nette;
use Ramsey\Uuid;
use RecursiveArrayIterator;

/**
 * Data storage device attributes repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @implements IteratorAggregate<int, MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity>
 */
final class DeviceAttributesRepository implements IDeviceAttributesRepository, Countable, IteratorAggregate
{

	use Nette\SmartObject;

	/** @var Array<string, Array<string, mixed>> */
	private array $attributes;

	private MetadataEntities\Modules\DevicesModule\DeviceAttributeEntityFactory $entityFactory;

	public function __construct(
		MetadataEntities\Modules\DevicesModule\DeviceAttributeEntityFactory $entityFactory
	) {
		$this->entityFactory = $entityFactory;

		$this->attributes = [];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity
	{
		if (array_key_exists($id->toString(), $this->attributes)) {
			return $this->entityFactory->create($this->attributes[$id->toString()]);
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
	): ?MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity {
		foreach ($this->attributes as $attribute) {
			if (
				array_key_exists('device', $attribute)
				&& $device->toString() === $attribute['device']
				&& array_key_exists('identifier', $attribute)
				&& $attribute['identifier'] === $identifier
			) {
				return $this->entityFactory->create($attribute);
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
		$attributes = [];

		foreach ($this->attributes as $attribute) {
			if (array_key_exists('device', $attribute) && $device->toString() === $attribute['device']) {
				$attributes[] = $this->entityFactory->create($attribute);
			}
		}

		return $attributes;
	}

	/**
	 * {@inheritDoc}
	 */
	public function append(Uuid\UuidInterface $id, array $entity): void
	{
		$this->attributes[$id->toString()] = $entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset(): void
	{
		$this->attributes = [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return count($this->attributes);
	}

	/**
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity>
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$attributes = [];

		foreach ($this->attributes as $attribute) {
			$attributes[] = $this->entityFactory->create($attribute);
		}

		return new RecursiveArrayIterator($attributes);
	}

}
