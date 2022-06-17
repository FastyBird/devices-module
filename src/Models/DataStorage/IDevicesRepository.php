<?php declare(strict_types = 1);

/**
 * IDevicesRepository.php
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
 * Data storage devices repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IDevicesRepository
{

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IDeviceEntity|null
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IDeviceEntity;

	/**
	 * @param string $identifier
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IDeviceEntity|null
	 */
	public function findByIdentifier(string $identifier): ?MetadataEntities\Modules\DevicesModule\IDeviceEntity;

	/**
	 * @param Uuid\UuidInterface $connector
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IDeviceEntity[]
	 */
	public function findAllByConnector(Uuid\UuidInterface $connector): array;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDeviceEntity $entity
	 *
	 * @return void
	 */
	public function append(MetadataEntities\Modules\DevicesModule\IDeviceEntity $entity): void;

}
