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
 * Device property repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IDevicePropertiesRepository extends IPropertiesRepository
{

	/**
	 * @param Entities\Devices\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity $property
	 *
	 * @return States\IDeviceProperty|null
	 */
	public function findOne(
		$property
	): ?States\IDeviceProperty;

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return States\IDeviceProperty|null
	 */
	public function findOneById(
		Uuid\UuidInterface $id
	): ?States\IDeviceProperty;

}
