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
use IteratorAggregate;
use Nette;
use Ramsey\Uuid;
use RecursiveArrayIterator;
use SplObjectStorage;

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

	/** @var SplObjectStorage<MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity, string> */
	private SplObjectStorage $attributes;

	public function __construct()
	{
		$this->attributes = new SplObjectStorage();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity
	{
		$this->attributes->rewind();

		foreach ($this->attributes as $attribute) {
			if ($attribute->getId()->equals($id)) {
				return $attribute;
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findByIdentifier(
		Uuid\UuidInterface $device,
		string $identifier
	): ?MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity {
		$this->attributes->rewind();

		foreach ($this->attributes as $attribute) {
			if (
				$attribute->getDevice()->equals($device)
				&& $attribute->getIdentifier() === $identifier
			) {
				return $attribute;
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findAllByDevice(Uuid\UuidInterface $device): array
	{
		$attributes = [];

		$this->attributes->rewind();

		foreach ($this->attributes as $attribute) {
			if ($attribute->getDevice()->equals($device)) {
				$attributes[] = $attribute;
			}
		}

		return $attributes;
	}

	/**
	 * {@inheritDoc}
	 */
	public function append(MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity $entity): void
	{
		$existing = $this->findById($entity->getId());

		if ($existing !== null) {
			$this->attributes->detach($existing);
		}

		if (!$this->attributes->contains($entity)) {
			$this->attributes->attach($entity, $entity->getId()->toString());
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset(): void
	{
		$this->attributes = new SplObjectStorage();
	}

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return $this->attributes->count();
	}

	/**
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity>
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$attributes = [];

		foreach ($this->attributes as $attribute) {
			$attributes[] = $attribute;
		}

		return new RecursiveArrayIterator($attributes);
	}

}
