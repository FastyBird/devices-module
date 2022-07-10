<?php declare(strict_types = 1);

/**
 * DeviceControlsRepository.php
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
 * Data storage device controls repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @implements IteratorAggregate<int, MetadataEntities\Modules\DevicesModule\IDeviceControlEntity>
 */
final class DeviceControlsRepository implements IDeviceControlsRepository, Countable, IteratorAggregate
{

	use Nette\SmartObject;

	/** @var Array<string, Array<string, mixed>> */
	private array $rawData;

	/** @var Array<string, MetadataEntities\Modules\DevicesModule\IDeviceControlEntity> */
	private array $controls;

	private MetadataEntities\Modules\DevicesModule\DeviceControlEntityFactory $entityFactory;

	public function __construct(
		MetadataEntities\Modules\DevicesModule\DeviceControlEntityFactory $entityFactory
	) {
		$this->entityFactory = $entityFactory;

		$this->rawData = [];
		$this->controls = [];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IDeviceControlEntity
	{
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
	public function findAllByDevice(Uuid\UuidInterface $device): array
	{
		$controls = [];

		foreach ($this->rawData as $id => $control) {
			if (array_key_exists('device', $control) && $device->toString() === $control['device']) {
				$controls[] = $this->getEntity(Uuid\Uuid::fromString($id), $this->rawData[$id]);
			}
		}

		return $controls;
	}

	/**
	 * {@inheritDoc}
	 */
	public function append(Uuid\UuidInterface $id, array $data): void
	{
		$this->rawData[$id->toString()] = $data;

		if (!array_key_exists($id->toString(), $this->controls)) {
			unset($this->controls[$id->toString()]);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset(): void
	{
		$this->rawData = [];
		$this->controls = [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return count($this->rawData);
	}

	/**
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IDeviceControlEntity>
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$controls = [];

		foreach ($this->rawData as $id => $control) {
			$controls[] = $this->getEntity(Uuid\Uuid::fromString($id), $control);
		}

		return new RecursiveArrayIterator($controls);
	}

	/**
	 * @param Uuid\UuidInterface $id
	 * @param Array<string, mixed> $data
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IDeviceControlEntity
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	private function getEntity(
		Uuid\UuidInterface $id,
		array $data
	): MetadataEntities\Modules\DevicesModule\IDeviceControlEntity {
		if (!array_key_exists($id->toString(), $this->controls)) {
			$this->controls[$id->toString()] = $this->entityFactory->create($data);
		}

		return $this->controls[$id->toString()];
	}

}
