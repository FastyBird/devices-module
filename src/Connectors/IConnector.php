<?php declare(strict_types = 1);

/**
 * IConnector.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Connectors
 * @since          0.60.0
 *
 * @date           31.05.22
 */

namespace FastyBird\DevicesModule\Connectors;

use FastyBird\Metadata\Entities as MetadataEntities;

/**
 * Devices connector interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Connectors
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IConnector
{

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity $entity
	 *
	 * @return void
	 */
	public function writeProperty($entity): void;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IConnectorControlEntity|MetadataEntities\Modules\DevicesModule\IDeviceControlEntity|MetadataEntities\Modules\DevicesModule\IChannelControlEntity $entity
	 *
	 * @return void
	 */
	public function writeControl($entity): void;

}
