<?php declare(strict_types = 1);

/**
 * IConnectorPropertiesRepository.php
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
 * Data storage connector properties repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IConnectorPropertiesRepository
{

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity|null
	 */
	public function findById(Uuid\UuidInterface $id);

	/**
	 * @param Uuid\UuidInterface $connector
	 * @param string $identifier
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity|null
	 */
	public function findByIdentifier(Uuid\UuidInterface $connector, string $identifier);

	/**
	 * @param Uuid\UuidInterface $connector
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity[]|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity[]|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity[]
	 */
	public function findAllByConnector(Uuid\UuidInterface $connector): array;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity $entity
	 *
	 * @return void
	 */
	public function append($entity): void;

	/**
	 * @return void
	 */
	public function reset(): void;

}
