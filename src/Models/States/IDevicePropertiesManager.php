<?php declare(strict_types = 1);

/**
 * IDevicePropertiesManager.php
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
 * Device properties manager interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IDevicePropertiesManager extends IPropertiesManager
{

	/**
	 * @param Entities\Devices\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity $property
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IDeviceProperty
	 */
	public function create(
		$property,
		Utils\ArrayHash $values
	): States\IDeviceProperty;

	/**
	 * @param Entities\Devices\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity $property
	 * @param States\IDeviceProperty $state
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IDeviceProperty
	 */
	public function update(
		$property,
		States\IDeviceProperty $state,
		Utils\ArrayHash $values
	): States\IDeviceProperty;

	/**
	 * @param Entities\Devices\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity $property
	 * @param States\IDeviceProperty $state
	 *
	 * @return bool
	 */
	public function delete(
		$property,
		States\IDeviceProperty $state
	): bool;

}
