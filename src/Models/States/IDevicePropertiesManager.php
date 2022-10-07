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
interface IDevicePropertiesManager
{

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|Entities\Devices\Properties\Dynamic|Entities\Devices\Properties\Mapped $property
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\DeviceProperty
	 */
	public function create(
		MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|Entities\Devices\Properties\Dynamic|Entities\Devices\Properties\Mapped $property,
		Utils\ArrayHash $values
	): States\DeviceProperty;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|Entities\Devices\Properties\Dynamic|Entities\Devices\Properties\Mapped $property
	 * @param States\DeviceProperty $state
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\DeviceProperty
	 */
	public function update(
		MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|Entities\Devices\Properties\Dynamic|Entities\Devices\Properties\Mapped $property,
		States\DeviceProperty $state,
		Utils\ArrayHash $values
	): States\DeviceProperty;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|Entities\Devices\Properties\Dynamic|Entities\Devices\Properties\Mapped $property
	 * @param States\DeviceProperty $state
	 *
	 * @return bool
	 */
	public function delete(
		MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|Entities\Devices\Properties\Dynamic|Entities\Devices\Properties\Mapped $property,
		States\DeviceProperty $state
	): bool;

}
