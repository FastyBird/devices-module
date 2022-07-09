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
	private array $controls;

	private MetadataEntities\Modules\DevicesModule\DeviceControlEntityFactory $entityFactory;

	public function __construct(
		MetadataEntities\Modules\DevicesModule\DeviceControlEntityFactory $entityFactory
	) {
		$this->entityFactory = $entityFactory;

		$this->controls = [];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IDeviceControlEntity
	{
		if (array_key_exists($id->toString(), $this->controls)) {
			return $this->entityFactory->create($this->controls[$id->toString()]);
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

		foreach ($this->controls as $control) {
			if (array_key_exists('device', $control) && $device->toString() === $control['device']) {
				$controls[] = $this->entityFactory->create($control);
			}
		}

		return $controls;
	}

	/**
	 * {@inheritDoc}
	 */
	public function append(Uuid\UuidInterface $id, array $entity): void
	{
		$this->controls[$id->toString()] = $entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset(): void
	{
		$this->controls = [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return count($this->controls);
	}

	/**
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IDeviceControlEntity>
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$controls = [];

		foreach ($this->controls as $control) {
			$controls[] = $this->entityFactory->create($control);
		}

		return new RecursiveArrayIterator($controls);
	}

}
