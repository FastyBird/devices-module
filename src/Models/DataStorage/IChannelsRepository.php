<?php declare(strict_types = 1);

/**
 * IChannelsRepository.php
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

use FastyBird\Metadata\Entities as MetadataEntities;
use Ramsey\Uuid;

/**
 * Data storage channels repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IChannelsRepository
{

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IChannelEntity|null
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IChannelEntity;

	/**
	 * @param Uuid\UuidInterface $device
	 * @param string $identifier
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IChannelEntity|null
	 */
	public function findByIdentifier(
		Uuid\UuidInterface $device,
		string $identifier,
	): ?MetadataEntities\Modules\DevicesModule\IChannelEntity;

	/**
	 * @param Uuid\UuidInterface $device
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IChannelEntity[]
	 */
	public function findAllByDevice(Uuid\UuidInterface $device): array;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IChannelEntity $entity
	 *
	 * @return void
	 */
	public function append(MetadataEntities\Modules\DevicesModule\IChannelEntity $entity): void;

	/**
	 * @return void
	 */
	public function reset(): void;

}
