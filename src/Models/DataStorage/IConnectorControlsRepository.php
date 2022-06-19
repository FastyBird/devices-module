<?php declare(strict_types = 1);

/**
 * IConnectorControlsRepository.php
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
interface IConnectorControlsRepository
{

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IConnectorControlEntity|null
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IConnectorControlEntity;

	/**
	 * @param Uuid\UuidInterface $connector
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IConnectorControlEntity[]
	 */
	public function findAllByConnector(Uuid\UuidInterface $connector): array;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IConnectorControlEntity $entity
	 *
	 * @return void
	 */
	public function append(MetadataEntities\Modules\DevicesModule\IConnectorControlEntity $entity): void;

	/**
	 * @return void
	 */
	public function reset(): void;

}
