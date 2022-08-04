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
use FastyBird\DevicesModule\Events;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\States;
use FastyBird\DevicesModule\Utilities;
use FastyBird\Metadata\Entities as MetadataEntities;
use Nette;
use Nette\Utils;
use Psr\EventDispatcher as PsrEventDispatcher;

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

	/** @var IDevicePropertiesManager|null */
	protected ?IDevicePropertiesManager $manager;

	/** @var PsrEventDispatcher\EventDispatcherInterface|null */
	private ?PsrEventDispatcher\EventDispatcherInterface $dispatcher;

	/**
	 * @param IDevicePropertiesManager|null $manager
	 * @param PsrEventDispatcher\EventDispatcherInterface|null $dispatcher
	 */
	public function __construct(
		?IDevicePropertiesManager $manager,
		?PsrEventDispatcher\EventDispatcherInterface $dispatcher
	) {
		$this->manager = $manager;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|Entities\Devices\Properties\IDynamicProperty|Entities\Devices\Properties\IMappedProperty $property
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IDeviceProperty
	 */
	public function create(
		MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|Entities\Devices\Properties\IDynamicProperty|Entities\Devices\Properties\IMappedProperty $property,
		Utils\ArrayHash $values
	): States\IDeviceProperty {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Device properties state manager is not registered');
		}

		if ($property->getParent() !== null) {
			throw new Exceptions\InvalidStateException('Child property can\'t have state');
		}

		if (
			$values->offsetExists('actualValue')
			&& $values->offsetExists('expectedValue')
		) {
			$actualValue = Utilities\ValueHelper::normalizeValue(
				$property->getDataType(),
				strval($values->offsetGet('actualValue')),
				$property->getFormat(),
				$property->getInvalid()
			);

			$expectedValue = Utilities\ValueHelper::normalizeValue(
				$property->getDataType(),
				strval($values->offsetGet('expectedValue')),
				$property->getFormat(),
				$property->getInvalid()
			);

			if ($expectedValue === $actualValue) {
				$values->offsetSet('expectedValue', null);
				$values->offsetSet('pending', null);
			}
		}

		$createdState = $this->manager->create($property, $values);

		$this->dispatcher?->dispatch(new Events\StateEntityCreatedEvent($property, $createdState));

		return $createdState;
	}

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|Entities\Devices\Properties\IDynamicProperty|Entities\Devices\Properties\IMappedProperty $property
	 * @param States\IDeviceProperty $state
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IDeviceProperty
	 */
	public function update(
		MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|Entities\Devices\Properties\IDynamicProperty|Entities\Devices\Properties\IMappedProperty $property,
		States\IDeviceProperty $state,
		Utils\ArrayHash $values
	): States\IDeviceProperty {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Device properties state manager is not registered');
		}

		if ($property->getParent() !== null) {
			throw new Exceptions\InvalidStateException('Child property can\'t have state');
		}

		$updatedState = $this->manager->update($property, $state, $values);

		$actualValue = Utilities\ValueHelper::normalizeValue(
			$property->getDataType(),
			$updatedState->getActualValue(),
			$property->getFormat(),
			$property->getInvalid()
		);

		$expectedValue = Utilities\ValueHelper::normalizeValue(
			$property->getDataType(),
			$updatedState->getExpectedValue(),
			$property->getFormat(),
			$property->getInvalid()
		);

		if ($expectedValue === $actualValue) {
			$updatedState = $this->manager->update(
				$property,
				$updatedState,
				Utils\ArrayHash::from([
					'expectedValue' => null,
					'pending'       => null,
				])
			);
		}

		$this->dispatcher?->dispatch(new Events\StateEntityUpdatedEvent($property, $state, $updatedState));

		return $updatedState;
	}

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|Entities\Devices\Properties\IDynamicProperty|Entities\Devices\Properties\IMappedProperty $property
	 * @param States\IDeviceProperty $state
	 *
	 * @return bool
	 */
	public function delete(
		MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|Entities\Devices\Properties\IDynamicProperty|Entities\Devices\Properties\IMappedProperty $property,
		States\IDeviceProperty $state
	): bool {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Device properties state manager is not registered');
		}

		if ($property->getParent() !== null) {
			throw new Exceptions\InvalidStateException('Child property can\'t have state');
		}

		$result = $this->manager->delete($property, $state);

		$this->dispatcher?->dispatch(new Events\StateEntityDeletedEvent($property));

		return $result;
	}

}
