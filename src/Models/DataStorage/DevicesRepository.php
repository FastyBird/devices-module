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
use FastyBird\Metadata\Exceptions as MetadataExceptions;
use IteratorAggregate;
use Nette;
use Ramsey\Uuid;
use RecursiveArrayIterator;

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

	/** @var Array<string, Array<string, mixed>> */
	private array $devices;

	private MetadataEntities\Modules\DevicesModule\DeviceEntityFactory $entityFactory;

	public function __construct(
		MetadataEntities\Modules\DevicesModule\DeviceEntityFactory $entityFactory
	) {
		$this->entityFactory = $entityFactory;

		$this->devices = [];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IDeviceEntity
	{
		if (array_key_exists($id->toString(), $this->devices)) {
			return $this->entityFactory->create($this->devices[$id->toString()]);
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
	): ?MetadataEntities\Modules\DevicesModule\IDeviceEntity {
		foreach ($this->devices as $device) {
			if (
				array_key_exists('device', $device)
				&& $connector->toString() === $device['device']
				&& array_key_exists('identifier', $device)
				&& $device['identifier'] === $identifier
			) {
				return $this->entityFactory->create($device);
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
		$devices = [];

		foreach ($this->devices as $device) {
			if (array_key_exists('device', $device) && $connector->toString() === $device['device']) {
				$devices[] = $this->entityFactory->create($device);
			}
		}

		return $devices;
	}

	/**
	 * {@inheritDoc}
	 */
	public function append(Uuid\UuidInterface $id, array $entity): void
	{
		$this->devices[$id->toString()] = $entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset(): void
	{
		$this->devices = [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return count($this->devices);
	}

	/**
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IDeviceEntity>
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$devices = [];

		foreach ($this->devices as $device) {
			$devices[] = $this->entityFactory->create($device);
		}

		return new RecursiveArrayIterator($devices);
	}

}
