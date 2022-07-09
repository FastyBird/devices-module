<?php declare(strict_types = 1);

/**
 * IDeviceControlsRepository.php
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
 * Data storage device properties repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IDeviceControlsRepository
{

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IDeviceControlEntity|null
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IDeviceControlEntity;

	/**
	 * @param Uuid\UuidInterface $device
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IDeviceControlEntity[]
	 */
	public function findAllByDevice(Uuid\UuidInterface $device): array;

	/**
	 * @param Uuid\UuidInterface $id
	 * @param Array<string, mixed> $entity
	 *
	 * @return void
	 */
	public function append(Uuid\UuidInterface $id, array $entity): void;

	/**
	 * @return void
	 */
	public function reset(): void;

}
