<?php declare(strict_types = 1);

/**
 * IDevicePropertiesRepository.php
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
use Ramsey\Uuid;

/**
 * Connector property repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IConnectorPropertiesRepository extends IPropertiesRepository
{

	/**
	 * @param Entities\Connectors\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity $property
	 *
	 * @return States\IConnectorProperty|null
	 */
	public function findOne(
		MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity|Entities\Connectors\Properties\IProperty $property
	): ?States\IConnectorProperty;

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return States\IConnectorProperty|null
	 */
	public function findOneById(
		Uuid\UuidInterface $id
	): ?States\IConnectorProperty;

}
