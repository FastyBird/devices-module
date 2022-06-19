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
use IteratorAggregate;
use Nette;
use Ramsey\Uuid;
use RecursiveArrayIterator;
use SplObjectStorage;

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

	/** @var SplObjectStorage<MetadataEntities\Modules\DevicesModule\IDeviceControlEntity, string> */
	private SplObjectStorage $controls;

	public function __construct()
	{
		$this->controls = new SplObjectStorage();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IDeviceControlEntity
	{
		$this->controls->rewind();

		foreach ($this->controls as $control) {
			if ($control->getId()->equals($id)) {
				return $control;
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findAllByDevice(Uuid\UuidInterface $device): array
	{
		$controls = [];

		$this->controls->rewind();

		foreach ($this->controls as $control) {
			if ($control->getDevice()->equals($device)) {
				$controls[] = $control;
			}
		}

		return $controls;
	}

	/**
	 * {@inheritDoc}
	 */
	public function append(MetadataEntities\Modules\DevicesModule\IDeviceControlEntity $entity): void
	{
		$existing = $this->findById($entity->getId());

		if ($existing !== null) {
			$this->controls->detach($existing);
		}

		if (!$this->controls->contains($entity)) {
			$this->controls->attach($entity, $entity->getId()->toString());
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset(): void
	{
		$this->controls = new SplObjectStorage();
	}

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return $this->controls->count();
	}

	/**
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IDeviceControlEntity>
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$controls = [];

		foreach ($this->controls as $control) {
			$controls[] = $control;
		}

		return new RecursiveArrayIterator($controls);
	}

}
