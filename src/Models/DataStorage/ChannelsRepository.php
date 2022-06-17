<?php declare(strict_types = 1);

/**
 * ChannelsRepository.php
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
 * Data storage channels repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @implements IteratorAggregate<int, MetadataEntities\Modules\DevicesModule\IChannelEntity>
 */
final class ChannelsRepository implements IChannelsRepository, Countable, IteratorAggregate
{

	use Nette\SmartObject;

	/** @var SplObjectStorage<MetadataEntities\Modules\DevicesModule\IChannelEntity, string> */
	private SplObjectStorage $channels;

	public function __construct()
	{
		$this->channels = new SplObjectStorage();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IChannelEntity
	{
		$this->channels->rewind();

		foreach ($this->channels as $channel) {
			if ($channel->getId()->equals($id)) {
				return $channel;
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findByIdentifier(string $identifier): ?MetadataEntities\Modules\DevicesModule\IChannelEntity
	{
		$this->channels->rewind();

		foreach ($this->channels as $channel) {
			if ($channel->getIdentifier() === $identifier) {
				return $channel;
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findAllByDevice(Uuid\UuidInterface $device): array
	{
		$channels = [];

		$this->channels->rewind();

		foreach ($this->channels as $channel) {
			if ($channel->getDevice()->equals($device)) {
				$channels[] = $channel;
			}
		}

		return $channels;
	}

	/**
	 * {@inheritDoc}
	 */
	public function append(MetadataEntities\Modules\DevicesModule\IChannelEntity $entity): void
	{
		if (!$this->channels->contains($entity)) {
			$this->channels->attach($entity, $entity->getId()->toString());
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return $this->channels->count();
	}

	/**
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IChannelEntity>
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$channels = [];

		foreach ($this->channels as $channel) {
			$channels[] = $channel;
		}

		return new RecursiveArrayIterator($channels);
	}

}
