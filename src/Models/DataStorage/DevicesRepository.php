<?php declare(strict_types = 1);

/**
 * DevicesRepository.php
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
 * Data storage devices repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @implements IteratorAggregate<int, MetadataEntities\Modules\DevicesModule\IDeviceEntity>
 */
final class DevicesRepository implements IDevicesRepository, Countable, IteratorAggregate
{

	use Nette\SmartObject;

	/** @var SplObjectStorage<MetadataEntities\Modules\DevicesModule\IDeviceEntity, string> */
	private SplObjectStorage $devices;

	public function __construct()
	{
		$this->devices = new SplObjectStorage();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findById(Uuid\UuidInterface$id): ?MetadataEntities\Modules\DevicesModule\IDeviceEntity
	{
		$this->devices->rewind();

		foreach ($this->devices as $device) {
			if ($device->getId()->equals($id)) {
				return $device;
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findByIdentifier(string $identifier): ?MetadataEntities\Modules\DevicesModule\IDeviceEntity
	{
		$this->devices->rewind();

		foreach ($this->devices as $device) {
			if ($device->getIdentifier() === $identifier) {
				return $device;
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findAllByConnector(Uuid\UuidInterface$connector): array
	{
		$devices = [];

		$this->devices->rewind();

		foreach ($this->devices as $device) {
			if ($device->getConnector()->equals($connector)) {
				$devices[] = $device;
			}
		}

		return $devices;
	}

	/**
	 * {@inheritDoc}
	 */
	public function append(MetadataEntities\Modules\DevicesModule\IDeviceEntity $entity): void
	{
		if (!$this->devices->contains($entity)) {
			$this->devices->attach($entity, $entity->getId()->toString());
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return $this->devices->count();
	}

	/**
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IDeviceEntity>
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$devices = [];

		foreach ($this->devices as $device) {
			$devices[] = $device;
		}

		return new RecursiveArrayIterator($devices);
	}

}
