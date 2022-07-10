<?php declare(strict_types = 1);

/**
 * IConnectorsRepository.php
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
 * Data storage connectors repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IConnectorsRepository
{

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IConnectorEntity|null
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IConnectorEntity;

	/**
	 * @param string $identifier
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IConnectorEntity|null
	 */
	public function findByIdentifier(string $identifier): ?MetadataEntities\Modules\DevicesModule\IConnectorEntity;

	/**
	 * @param Uuid\UuidInterface $id
	 * @param Array<string, mixed> $data
	 *
	 * @return void
	 */
	public function append(Uuid\UuidInterface $id, array $data): void;

	/**
	 * @return void
	 */
	public function reset(): void;

}
