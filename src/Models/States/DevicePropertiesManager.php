<?php declare(strict_types = 1);

/**
 * DevicePropertiesManager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.32.0
 *
 * @date           08.02.22
 */

namespace FastyBird\DevicesModule\Models\States;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\States;
use FastyBird\Exchange\Publisher as ExchangePublisher;
use FastyBird\Metadata\Helpers as MetadataHelpers;
use FastyBird\Metadata\Types as MetadataTypes;
use Nette;
use Nette\Utils;

/**
 * Device property states manager
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class DevicePropertiesManager
{

	use Nette\SmartObject;

	/** @var ExchangePublisher\IPublisher|null */
	protected ?ExchangePublisher\IPublisher $publisher;

	/** @var IDevicePropertiesManager|null */
	protected ?IDevicePropertiesManager $manager;

	public function __construct(
		?IDevicePropertiesManager $manager,
		?ExchangePublisher\IPublisher $publisher
	) {
		$this->manager = $manager;
		$this->publisher = $publisher;
	}

	/**
	 * @param Entities\Devices\Properties\IProperty $property
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IDeviceProperty
	 */
	public function create(
		Entities\Devices\Properties\IProperty $property,
		Utils\ArrayHash $values
	): States\IDeviceProperty {
		if ($this->manager === null) {
			throw new Exceptions\InvalidStateException('Device properties state manager is not registered');
		}

		/** @var States\IDeviceProperty $createdState */
		$createdState = $this->manager->create($property, $values);

		$this->publishEntity($property, $createdState);

		return $createdState;
	}

	/**
	 * @param Entities\Devices\Properties\IProperty $property
	 * @param States\IDeviceProperty $state
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IDeviceProperty
	 */
	public function update(
		Entities\Devices\Properties\IProperty $property,
		States\IDeviceProperty $state,
		Utils\ArrayHash $values
	): States\IDeviceProperty {
		if ($this->manager === null) {
			throw new Exceptions\InvalidStateException('Device properties state manager is not registered');
		}

		/** @var States\IDeviceProperty $updatedState */
		$updatedState = $this->manager->update($property, $state, $values);

		$this->publishEntity($property, $updatedState);

		return $updatedState;
	}

	/**
	 * @param Entities\Devices\Properties\IProperty $property
	 * @param States\IDeviceProperty $state
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Devices\Properties\IProperty $property,
		States\IDeviceProperty $state
	): bool {
		if ($this->manager === null) {
			throw new Exceptions\InvalidStateException('Device properties state manager is not registered');
		}

		$result = $this->manager->delete($property, $state);

		if ($result) {
			$this->publishEntity($property, null);
		}

		return $result;
	}

	private function publishEntity(
		Entities\Devices\Properties\IProperty $property,
		?States\IDeviceProperty $state
	): void {
		if ($this->publisher === null) {
			return;
		}

		$this->publisher->publish(
			MetadataTypes\ModuleOriginType::get(MetadataTypes\ModuleOriginType::ORIGIN_MODULE_DEVICES),
			MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_DEVICES_PROPERTY_ENTITY_UPDATED),
			Utils\ArrayHash::from(array_merge($property->toArray(), [
				'actual_value'   => $state === null ? null : MetadataHelpers\ValueHelper::normalizeValue($property->getDataType(), $state->getActualValue(), $property->getFormat()),
				'expected_value' => $state === null ? null : MetadataHelpers\ValueHelper::normalizeValue($property->getDataType(), $state->getExpectedValue(), $property->getFormat()),
				'pending'        => !($state === null) && $state->isPending(),
			]))
		);
	}
}
