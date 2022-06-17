<?php declare(strict_types = 1);

/**
 * ChannelControlsRepository.php
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
 * Data storage channel controls repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @implements IteratorAggregate<int, MetadataEntities\Modules\DevicesModule\IChannelControlEntity>
 */
final class ChannelControlsRepository implements IChannelControlsRepository, Countable, IteratorAggregate
{

	use Nette\SmartObject;

	/** @var SplObjectStorage<MetadataEntities\Modules\DevicesModule\IChannelControlEntity, string> */
	private SplObjectStorage $controls;

	public function __construct()
	{
		$this->controls = new SplObjectStorage();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IChannelControlEntity
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
	public function findAllByChannel(Uuid\UuidInterface $channel): array
	{
		$controls = [];

		$this->controls->rewind();

		foreach ($this->controls as $control) {
			if ($control->getChannel()->equals($channel)) {
				$controls[] = $control;
			}
		}

		return $controls;
	}

	/**
	 * {@inheritDoc}
	 */
	public function append(MetadataEntities\Modules\DevicesModule\IChannelControlEntity $entity): void
	{
		if (!$this->controls->contains($entity)) {
			$this->controls->attach($entity, $entity->getId()->toString());
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return $this->controls->count();
	}

	/**
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IChannelControlEntity>
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
