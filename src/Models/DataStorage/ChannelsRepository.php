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
use FastyBird\Metadata\Exceptions as MetadataExceptions;
use IteratorAggregate;
use Nette;
use Ramsey\Uuid;
use RecursiveArrayIterator;

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

	/** @var Array<string, Array<string, mixed>> */
	private array $channels;

	private MetadataEntities\Modules\DevicesModule\ChannelEntityFactory $entityFactory;

	public function __construct(
		MetadataEntities\Modules\DevicesModule\ChannelEntityFactory $entityFactory
	) {
		$this->entityFactory = $entityFactory;

		$this->channels = [];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IChannelEntity
	{
		if (array_key_exists($id->toString(), $this->channels)) {
			return $this->entityFactory->create($this->channels[$id->toString()]);
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
	): ?MetadataEntities\Modules\DevicesModule\IChannelEntity {
		foreach ($this->channels as $channel) {
			if (
				array_key_exists('device', $channel)
				&& $device->toString() === $channel['device']
				&& array_key_exists('identifier', $channel)
				&& $channel['identifier'] === $identifier
			) {
				return $this->entityFactory->create($channel);
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
		$channels = [];

		foreach ($this->channels as $channel) {
			if (array_key_exists('device', $channel) && $device->toString() === $channel['device']) {
				$channels[] = $this->entityFactory->create($channel);
			}
		}

		return $channels;
	}

	/**
	 * {@inheritDoc}
	 */
	public function append(Uuid\UuidInterface $id, array $entity): void
	{
		$this->channels[$id->toString()] = $entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset(): void
	{
		$this->channels = [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return count($this->channels);
	}

	/**
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IChannelEntity>
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$channels = [];

		foreach ($this->channels as $channel) {
			$channels[] = $this->entityFactory->create($channel);
		}

		return new RecursiveArrayIterator($channels);
	}

}
