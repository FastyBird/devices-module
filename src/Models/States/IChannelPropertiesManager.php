<?php declare(strict_types = 1);

/**
 * IChannelPropertiesManager.php
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
use Nette\Utils;

/**
 * Channel properties manager interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IChannelPropertiesManager extends IPropertiesManager
{

	/**
	 * @param Entities\Channels\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity $property
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IChannelProperty
	 */
	public function create(
		MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|Entities\Channels\Properties\IProperty $property,
		Utils\ArrayHash $values
	): States\IChannelProperty;

	/**
	 * @param Entities\Channels\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity $property
	 * @param States\IChannelProperty $state
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IChannelProperty
	 */
	public function update(
		MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|Entities\Channels\Properties\IProperty $property,
		States\IChannelProperty $state,
		Utils\ArrayHash $values
	): States\IChannelProperty;

	/**
	 * @param Entities\Channels\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity $property
	 * @param States\IChannelProperty $state
	 *
	 * @return bool
	 */
	public function delete(
		MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|Entities\Channels\Properties\IProperty $property,
		States\IChannelProperty $state
	): bool;

}
