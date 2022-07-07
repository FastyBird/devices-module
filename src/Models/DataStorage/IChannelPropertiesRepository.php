<?php declare(strict_types = 1);

/**
 * IChannelPropertiesRepository.php
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
 * Data storage channel properties repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IChannelPropertiesRepository
{

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity|null
	 */
	public function findById(Uuid\UuidInterface $id): MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|null;

	/**
	 * @param Uuid\UuidInterface $channel
	 * @param string $identifier
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity|null
	 */
	public function findByIdentifier(Uuid\UuidInterface $channel, string $identifier): MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|null;

	/**
	 * @param Uuid\UuidInterface $channel
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity[]|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity[]|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity[]
	 */
	public function findAllByChannel(Uuid\UuidInterface $channel): array;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity $entity
	 *
	 * @return void
	 */
	public function append(
		MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity $entity
	): void;

	/**
	 * @return void
	 */
	public function reset(): void;

}
