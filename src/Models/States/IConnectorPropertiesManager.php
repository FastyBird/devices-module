<?php declare(strict_types = 1);

/**
 * IConnectorPropertiesManager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.31.0
 *
 * @date           08.02.22
 */

namespace FastyBird\DevicesModule\Models\States;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\States;
use FastyBird\Metadata\Entities as MetadataEntities;
use Nette\Utils;

/**
 * Connector properties manager interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IConnectorPropertiesManager extends IPropertiesManager
{

	/**
	 * @param Entities\Connectors\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity $property
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IConnectorProperty
	 */
	public function create(
		MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity|Entities\Connectors\Properties\IProperty $property,
		Utils\ArrayHash $values
	): States\IConnectorProperty;

	/**
	 * @param Entities\Connectors\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity $property
	 * @param States\IConnectorProperty $state
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IConnectorProperty
	 */
	public function update(
		MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity|Entities\Connectors\Properties\IProperty $property,
		States\IConnectorProperty $state,
		Utils\ArrayHash $values
	): States\IConnectorProperty;

	/**
	 * @param Entities\Connectors\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity $property
	 * @param States\IConnectorProperty $state
	 *
	 * @return bool
	 */
	public function delete(
		MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity|Entities\Connectors\Properties\IProperty $property,
		States\IConnectorProperty $state
	): bool;

}
