<?php declare(strict_types = 1);

/**
 * IDevicePropertiesRepository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.9.0
 *
 * @date           09.01.22
 */

namespace FastyBird\DevicesModule\Models\States;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\States;
use FastyBird\Metadata\Entities as MetadataEntities;
use Ramsey\Uuid;

/**
 * Channel property repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IChannelPropertiesRepository
{

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity|Entities\Channels\Properties\Dynamic|Entities\Channels\Properties\Mapped $property
	 *
	 * @return States\ChannelProperty|null
	 */
	public function findOne(
		MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity|Entities\Channels\Properties\Dynamic|Entities\Channels\Properties\Mapped $property
	): ?States\ChannelProperty;

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return States\ChannelProperty|null
	 */
	public function findOneById(
		Uuid\UuidInterface $id
	): ?States\ChannelProperty;

}
