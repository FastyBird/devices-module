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
use FastyBird\Metadata\Entities as MetadataEntities;
use IteratorAggregate;
use Nette;
use Ramsey\Uuid;
use RecursiveArrayIterator;
use SplObjectStorage;

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

	/** @var SplObjectStorage<MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity, string> */
	private SplObjectStorage $properties;

	public function __construct()
	{
		$this->properties = new SplObjectStorage();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findById(Uuid\UuidInterface $id)
	{
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
	public function findByIdentifier(string $identifier)
	{
		$this->properties->rewind();

		foreach ($this->properties as $property) {
			if ($property->getIdentifier() === $identifier) {
				return $property;
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findAllByDevice(Uuid\UuidInterface $device): array
	{
		$properties = [];

		$this->properties->rewind();

		foreach ($this->properties as $property) {
			if ($property->getDevice()->equals($device)) {
				$properties[] = $property;
			}
		}

		return $properties;
	}

	/**
	 * {@inheritDoc}
	 */
	public function append($entity): void
	{
		if (
			!$entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity
			&& !$entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
			&& !$entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
		) {
			throw new Exceptions\InvalidArgumentException('Provided entity is not valid instance');
		}

		if (!$this->properties->contains($entity)) {
			$this->properties->attach($entity, $entity->getId()->toString());
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return $this->properties->count();
	}

	/**
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity>
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$properties = [];

		foreach ($this->properties as $property) {
			$properties[] = $property;
		}

		return new RecursiveArrayIterator($properties);
	}

}
