<?php declare(strict_types = 1);

/**
 * DevicePropertiesRepository.php
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
 * Device property repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class DevicePropertiesRepository
{

	use Nette\SmartObject;

	/** @var IDevicePropertiesRepository|null */
	protected ?IDevicePropertiesRepository $repository;

	public function __construct(
		?IDevicePropertiesRepository $repository
	) {
		$this->repository = $repository;
	}

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|Entities\Devices\Properties\IDynamicProperty|Entities\Devices\Properties\IMappedProperty $property
	 *
	 * @return States\IDeviceProperty|null
	 */
	public function findOne(
		MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|Entities\Devices\Properties\IDynamicProperty|Entities\Devices\Properties\IMappedProperty $property
	): ?States\IDeviceProperty {
		if ($this->repository === null) {
			throw new Exceptions\NotImplementedException('Device properties state repository is not registered');
		}

		if ($property->getParent() !== null) {
			$parent = $property->getParent();

			if (
				$parent instanceof Entities\Devices\Properties\IDynamicProperty
				|| $parent instanceof Entities\Devices\Properties\IMappedProperty
			) {
				return $this->repository->findOne($parent);

			} elseif ($parent instanceof Uuid\UuidInterface) {
				return $this->repository->findOneById($parent);

			} else {
				return null;
			}
		}

		return $this->repository->findOne($property);
	}

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return States\IDeviceProperty|null
	 */
	public function findOneById(
		Uuid\UuidInterface $id
	): ?States\IDeviceProperty {
		if ($this->repository === null) {
			throw new Exceptions\NotImplementedException('Device properties state repository is not registered');
		}

		return $this->repository->findOneById($id);
	}

}
