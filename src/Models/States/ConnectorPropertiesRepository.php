<?php declare(strict_types = 1);

/**
 * ConnectorPropertiesRepository.php
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
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\States;
use FastyBird\Metadata\Entities as MetadataEntities;
use Nette;
use Ramsey\Uuid;

/**
 * Connector property repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ConnectorPropertiesRepository
{

	use Nette\SmartObject;

	/** @var IConnectorPropertiesRepository|null */
	protected ?IConnectorPropertiesRepository $repository;

	public function __construct(
		?IConnectorPropertiesRepository $repository
	) {
		$this->repository = $repository;
	}

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity|Entities\Connectors\Properties\IDynamicProperty $property
	 *
	 * @return States\IConnectorProperty|null
	 */
	public function findOne(
		MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity|Entities\Connectors\Properties\IDynamicProperty $property
	): ?States\IConnectorProperty {
		if ($this->repository === null) {
			throw new Exceptions\NotImplementedException('Connector properties state repository is not registered');
		}

		return $this->repository->findOne($property);
	}

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return States\IConnectorProperty|null
	 */
	public function findOneById(
		Uuid\UuidInterface $id
	): ?States\IConnectorProperty {
		if ($this->repository === null) {
			throw new Exceptions\NotImplementedException('Connector properties state repository is not registered');
		}

		return $this->repository->findOneById($id);
	}

}
