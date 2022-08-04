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

use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Exceptions as MetadataExceptions;
use Nette;
use Ramsey\Uuid;
use RecursiveArrayIterator;

/**
 * Data storage channel controls repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ChannelControlsRepository implements IChannelControlsRepository
{

	use Nette\SmartObject;

	/** @var Array<string, Array<string, mixed>> */
	private array $rawData;

	/** @var Array<string, MetadataEntities\Modules\DevicesModule\IChannelControlEntity> */
	private array $entities;

	/** @var MetadataEntities\Modules\DevicesModule\ChannelControlEntityFactory */
	private MetadataEntities\Modules\DevicesModule\ChannelControlEntityFactory $entityFactory;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\ChannelControlEntityFactory $entityFactory
	 */
	public function __construct(
		MetadataEntities\Modules\DevicesModule\ChannelControlEntityFactory $entityFactory
	) {
		$this->entityFactory = $entityFactory;

		$this->rawData = [];
		$this->entities = [];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IChannelControlEntity
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
	public function findAllByChannel(Uuid\UuidInterface $channel): array
	{
		$entities = [];

		foreach ($this->rawData as $id => $entity) {
			if (array_key_exists('channel', $entity) && $channel->toString() === $entity['channel']) {
				$entities[] = $this->getEntity(Uuid\Uuid::fromString($id), $this->rawData[$id]);
			}
		}

		return $entities;
	}

	/**
	 * {@inheritDoc}
	 */
	public function append(Uuid\UuidInterface $id, array $data): void
	{
		$this->rawData[$id->toString()] = $data;

		if (array_key_exists($id->toString(), $this->entities)) {
			unset($this->entities[$id->toString()]);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function clear(): void
	{
		$this->rawData = [];
		$this->entities = [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset(Uuid\UuidInterface|array $id): void
	{
		if ($id instanceof Uuid\UuidInterface) {
			if (array_key_exists($id->toString(), $this->entities)) {
				unset($this->entities[$id->toString()]);
			}
		} else {
			$ids = $id;

			foreach ($ids as $id) {
				if (array_key_exists($id->toString(), $this->entities)) {
					unset($this->entities[$id->toString()]);
				}
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return count($this->rawData);
	}

	/**
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IChannelControlEntity>
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$entities = [];

		foreach ($this->rawData as $id => $entity) {
			$entities[] = $this->getEntity(Uuid\Uuid::fromString($id), $entity);
		}

		/** @var RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IChannelControlEntity> $result */
		$result = new RecursiveArrayIterator($entities);

		return $result;
	}

	/**
	 * @param Uuid\UuidInterface $id
	 * @param Array<string, mixed> $data
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IChannelControlEntity
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	private function getEntity(
		Uuid\UuidInterface $id,
		array $data
	): MetadataEntities\Modules\DevicesModule\IChannelControlEntity {
		if (!array_key_exists($id->toString(), $this->entities)) {
			$this->entities[$id->toString()] = $this->entityFactory->create($data);
		}

		return $this->entities[$id->toString()];
	}

}
