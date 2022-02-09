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
use Nette;

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
	 * @param Entities\Devices\Properties\IProperty $property
	 *
	 * @return States\IDeviceProperty|null
	 */
	public function findOne(
		Entities\Devices\Properties\IProperty $property
	): ?States\IDeviceProperty {
		if ($this->repository === null) {
			throw new Exceptions\NotImplementedException('Device properties state repository is not registered');
		}

		return $this->repository->findOne($property);
	}

}
