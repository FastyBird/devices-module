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
interface IChannelPropertiesRepository extends IPropertiesRepository
{

	/**
	 * @param Entities\Channels\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity $property
	 *
	 * @return States\IChannelProperty|null
	 */
	public function findOne(
		MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|Entities\Channels\Properties\IProperty $property
	): ?States\IChannelProperty;

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return States\IChannelProperty|null
	 */
	public function findOneById(
		Uuid\UuidInterface $id
	): ?States\IChannelProperty;

}
