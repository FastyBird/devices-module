<?php declare(strict_types = 1);

/**
 * ChannelPropertiesRepository.php
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

use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Metadata\Types as MetadataTypes;
use Nette;
use Nette\Utils;
use Ramsey\Uuid;
use RecursiveArrayIterator;

/**
 * Data storage channel properties repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ChannelPropertiesRepository implements IChannelPropertiesRepository
{

	use Nette\SmartObject;

	/** @var Array<string, Array<string, mixed>> */
	private array $rawData;

	/** @var Array<string, MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity> */
	private array $entities;

	/** @var Models\States\ChannelPropertiesRepository */
	private Models\States\ChannelPropertiesRepository $statesRepository;

	/** @var MetadataEntities\Modules\DevicesModule\ChannelPropertyEntityFactory */
	private MetadataEntities\Modules\DevicesModule\ChannelPropertyEntityFactory $entityFactory;

	/**
	 * @param Models\States\ChannelPropertiesRepository $statesRepository
	 * @param MetadataEntities\Modules\DevicesModule\ChannelPropertyEntityFactory $entityFactory
	 */
	public function __construct(
		Models\States\ChannelPropertiesRepository $statesRepository,
		MetadataEntities\Modules\DevicesModule\ChannelPropertyEntityFactory $entityFactory
	) {
		$this->statesRepository = $statesRepository;
		$this->entityFactory = $entityFactory;

		$this->rawData = [];
		$this->entities = [];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findById(
		Uuid\UuidInterface $id
	): MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity|null {
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
	public function findByIdentifier(
		Uuid\UuidInterface $channel,
		string $identifier
	): MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity|null {
		foreach ($this->rawData as $id => $entity) {
			if (
				array_key_exists('channel', $entity)
				&& $channel->toString() === $entity['channel']
				&& array_key_exists('identifier', $entity)
				&& Utils\Strings::lower($entity['identifier']) === Utils\Strings::lower($identifier)
			) {
				return $this->getEntity(Uuid\Uuid::fromString($id), $entity);
			}
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
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity>
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$entities = [];

		foreach ($this->rawData as $id => $entity) {
			$entities[] = $this->getEntity(Uuid\Uuid::fromString($id), $entity);
		}

		return new RecursiveArrayIterator($entities);
	}

	/**
	 * @param Uuid\UuidInterface $id
	 * @param Array<string, mixed> $data
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	private function getEntity(
		Uuid\UuidInterface $id,
		array $data
	): MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity {
		if (!array_key_exists($id->toString(), $this->entities)) {
			$state = [];

			if (
				array_key_exists('type', $data)
				&& (
					$data['type'] === MetadataTypes\PropertyTypeType::TYPE_DYNAMIC
					|| $data['type'] === MetadataTypes\PropertyTypeType::TYPE_MAPPED
				)
			) {
				$state = $this->loadPropertyState($id);
			}

			$entity = $this->entityFactory->create(array_merge($state, $data));

			if (
				$entity instanceof MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity
				|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity
				|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
			) {
				$this->entities[$id->toString()] = $entity;
			} else {
				throw new Exceptions\InvalidStateException('Channel property entity could not be created');
			}
		}

		return $this->entities[$id->toString()];
	}

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return Array<string, mixed>
	 */
	private function loadPropertyState(Uuid\UuidInterface $id): array
	{
		try {
			$entityState = $this->statesRepository->findOneById($id);

			return $entityState !== null ? $entityState->toArray() : [];
		} catch (Exceptions\NotImplementedException $ex) {
			return [];
		}
	}

}
