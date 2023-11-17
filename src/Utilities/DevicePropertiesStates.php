<?php declare(strict_types = 1);

/**
 * DevicePropertiesStates.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Utilities
 * @since          1.0.0
 *
 * @date           23.08.22
 */

namespace FastyBird\Module\Devices\Utilities;

use FastyBird\Library\Bootstrap\Helpers as BootstrapHelpers;
use FastyBird\Library\Metadata\Documents as MetadataDocuments;
use FastyBird\Library\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use FastyBird\Module\Devices;
use FastyBird\Module\Devices\Entities;
use FastyBird\Module\Devices\Exceptions;
use FastyBird\Module\Devices\Models;
use FastyBird\Module\Devices\Queries;
use FastyBird\Module\Devices\States;
use Nette;
use Nette\Utils;
use function is_array;

/**
 * Useful device dynamic property state helpers
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Utilities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class DevicePropertiesStates
{

	use Nette\SmartObject;

	/**
	 * @param Models\Configuration\Devices\Properties\Repository<MetadataDocuments\DevicesModule\DeviceDynamicProperty|MetadataDocuments\DevicesModule\DeviceVariableProperty|MetadataDocuments\DevicesModule\DeviceMappedProperty> $devicePropertiesRepository
	 */
	public function __construct(
		private readonly Models\Configuration\Devices\Properties\Repository $devicePropertiesRepository,
		private readonly Models\States\DevicePropertiesRepository $devicePropertyStateRepository,
		private readonly Models\States\DevicePropertiesManager $devicePropertiesStatesManager,
		private readonly Devices\Logger $logger,
	)
	{
	}

	/**
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\MalformedInput
	 */
	public function readValue(
		MetadataDocuments\DevicesModule\DeviceDynamicProperty|MetadataDocuments\DevicesModule\DeviceMappedProperty|Entities\Devices\Properties\Dynamic|Entities\Devices\Properties\Mapped $property,
	): States\DeviceProperty|null
	{
		return $this->loadValue($property, true);
	}

	/**
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\MalformedInput
	 */
	public function getValue(
		MetadataDocuments\DevicesModule\DeviceDynamicProperty|MetadataDocuments\DevicesModule\DeviceMappedProperty|Entities\Devices\Properties\Dynamic|Entities\Devices\Properties\Mapped $property,
	): States\DeviceProperty|null
	{
		return $this->loadValue($property, false);
	}

	/**
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\MalformedInput
	 */
	public function writeValue(
		MetadataDocuments\DevicesModule\DeviceDynamicProperty|MetadataDocuments\DevicesModule\DeviceMappedProperty|Entities\Devices\Properties\Dynamic|Entities\Devices\Properties\Mapped $property,
		Utils\ArrayHash $data,
	): void
	{
		$this->saveValue($property, $data, true);
	}

	/**
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\MalformedInput
	 */
	public function setValue(
		MetadataDocuments\DevicesModule\DeviceDynamicProperty|MetadataDocuments\DevicesModule\DeviceMappedProperty|Entities\Devices\Properties\Dynamic|Entities\Devices\Properties\Mapped $property,
		Utils\ArrayHash $data,
	): void
	{
		$this->saveValue($property, $data, false);
	}

	/**
	 * @param MetadataDocuments\DevicesModule\DeviceDynamicProperty|MetadataDocuments\DevicesModule\DeviceMappedProperty|array<MetadataDocuments\DevicesModule\DeviceDynamicProperty|MetadataDocuments\DevicesModule\DeviceMappedProperty>|Entities\Devices\Properties\Dynamic|Entities\Devices\Properties\Mapped|array<Entities\Devices\Properties\Dynamic|Entities\Devices\Properties\Mapped> $property
	 *
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\MalformedInput
	 */
	public function setValidState(
		MetadataDocuments\DevicesModule\DeviceDynamicProperty|MetadataDocuments\DevicesModule\DeviceMappedProperty|Entities\Devices\Properties\Dynamic|Entities\Devices\Properties\Mapped|array $property,
		bool $state,
	): void
	{
		if (is_array($property)) {
			foreach ($property as $item) {
				$this->setValue($item, Utils\ArrayHash::from([
					States\Property::VALID_KEY => $state,
				]));
			}
		} else {
			$this->setValue($property, Utils\ArrayHash::from([
				States\Property::VALID_KEY => $state,
			]));
		}
	}

	/**
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\MalformedInput
	 */
	private function loadValue(
		MetadataDocuments\DevicesModule\DeviceDynamicProperty|MetadataDocuments\DevicesModule\DeviceMappedProperty|Entities\Devices\Properties\Dynamic|Entities\Devices\Properties\Mapped $property,
		bool $forReading,
	): States\DeviceProperty|null
	{
		if ($property instanceof MetadataDocuments\DevicesModule\DeviceMappedProperty) {
			$findPropertyQuery = new Queries\Configuration\FindDeviceProperties();
			$findPropertyQuery->byId($property->getParent());

			$parent = $this->devicePropertiesRepository->findOneBy($findPropertyQuery);

			if (!$parent instanceof MetadataDocuments\DevicesModule\DeviceDynamicProperty) {
				throw new Exceptions\InvalidState('Mapped property parent could not be loaded');
			}

			$property = $parent;
		} elseif ($property instanceof Entities\Devices\Properties\Mapped) {
			$property = $property->getParent();

			if (!$property instanceof Entities\Devices\Properties\Dynamic) {
				throw new Exceptions\InvalidState('Mapped property parent is invalid type');
			}
		}

		try {
			$state = $this->devicePropertyStateRepository->findOne($property);

			if ($state !== null) {
				try {
					if ($state->getActualValue() !== null) {
						if ($forReading) {
							$state->setActualValue(
								ValueHelper::normalizeReadValue(
									$property->getDataType(),
									$state->getActualValue(),
									$property->getFormat(),
									$property->getScale(),
									$property->getInvalid(),
								),
							);

						} else {
							$state->setActualValue(
								ValueHelper::normalizeValue(
									$property->getDataType(),
									$state->getActualValue(),
									$property->getFormat(),
									$property->getInvalid(),
								),
							);
						}
					}
				} catch (Exceptions\InvalidArgument $ex) {
					$this->devicePropertiesStatesManager->update($property, $state, Utils\ArrayHash::from([
						States\Property::ACTUAL_VALUE_KEY => null,
						States\Property::VALID_KEY => false,
					]));

					$this->logger->error(
						'Property stored actual value was not valid',
						[
							'source' => MetadataTypes\ModuleSource::SOURCE_MODULE_DEVICES,
							'type' => 'device-properties-states',
							'exception' => BootstrapHelpers\Logger::buildException($ex),
						],
					);

					return $this->loadValue($property, $forReading);
				}

				try {
					if ($state->getExpectedValue() !== null) {
						if ($forReading) {
							$state->setExpectedValue(
								ValueHelper::normalizeReadValue(
									$property->getDataType(),
									$state->getExpectedValue(),
									$property->getFormat(),
									$property->getScale(),
									$property->getInvalid(),
								),
							);

						} else {
							$state->setExpectedValue(
								ValueHelper::normalizeValue(
									$property->getDataType(),
									$state->getExpectedValue(),
									$property->getFormat(),
									$property->getInvalid(),
								),
							);
						}
					}
				} catch (Exceptions\InvalidArgument $ex) {
					$this->devicePropertiesStatesManager->update($property, $state, Utils\ArrayHash::from([
						States\Property::EXPECTED_VALUE_KEY => null,
						States\Property::PENDING_KEY => false,
					]));

					$this->logger->error(
						'Property stored expected value was not valid',
						[
							'source' => MetadataTypes\ModuleSource::SOURCE_MODULE_DEVICES,
							'type' => 'device-properties-states',
							'exception' => BootstrapHelpers\Logger::buildException($ex),
						],
					);

					return $this->loadValue($property, $forReading);
				}
			}

			return $state;
		} catch (Exceptions\NotImplemented) {
			$this->logger->warning(
				'Devices states repository is not configured. State could not be fetched',
				[
					'source' => MetadataTypes\ModuleSource::SOURCE_MODULE_DEVICES,
					'type' => 'device-properties-states',
				],
			);
		}

		return null;
	}

	/**
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\MalformedInput
	 */
	private function saveValue(
		MetadataDocuments\DevicesModule\DeviceDynamicProperty|MetadataDocuments\DevicesModule\DeviceMappedProperty|Entities\Devices\Properties\Dynamic|Entities\Devices\Properties\Mapped $property,
		Utils\ArrayHash $data,
		bool $forWriting,
	): void
	{
		if ($property instanceof MetadataDocuments\DevicesModule\DeviceMappedProperty) {
			$findPropertyQuery = new Queries\Configuration\FindDeviceProperties();
			$findPropertyQuery->byId($property->getParent());

			$parent = $this->devicePropertiesRepository->findOneBy($findPropertyQuery);

			if (!$parent instanceof MetadataDocuments\DevicesModule\DeviceDynamicProperty) {
				throw new Exceptions\InvalidState('Mapped property parent could not be loaded');
			}

			$property = $parent;
		} elseif ($property instanceof Entities\Devices\Properties\Mapped) {
			$property = $property->getParent();

			if (!$property instanceof Entities\Devices\Properties\Dynamic) {
				throw new Exceptions\InvalidState('Mapped property parent is invalid type');
			}
		}

		$state = $this->loadValue($property, $forWriting);

		if ($data->offsetExists(States\Property::ACTUAL_VALUE_KEY)) {
			if ($forWriting) {
				try {
					$data->offsetSet(
						States\Property::ACTUAL_VALUE_KEY,
						ValueHelper::flattenValue(
							ValueHelper::normalizeWriteValue(
								$property->getDataType(),
								/** @phpstan-ignore-next-line */
								$data->offsetGet(States\Property::ACTUAL_VALUE_KEY),
								$property->getFormat(),
								$property->getScale(),
								$property->getInvalid(),
							),
						),
					);
				} catch (Exceptions\InvalidArgument $ex) {
					$data->offsetSet(States\Property::ACTUAL_VALUE_KEY, null);
					$data->offsetSet(States\Property::VALID_KEY, false);

					$this->logger->error(
						'Provided property actual value is not valid',
						[
							'source' => MetadataTypes\ModuleSource::SOURCE_MODULE_DEVICES,
							'type' => 'device-properties-states',
							'exception' => BootstrapHelpers\Logger::buildException($ex),
						],
					);
				}
			} else {
				try {
					$data->offsetSet(
						States\Property::ACTUAL_VALUE_KEY,
						ValueHelper::flattenValue(
							ValueHelper::normalizeValue(
								$property->getDataType(),
								/** @phpstan-ignore-next-line */
								$data->offsetGet(States\Property::ACTUAL_VALUE_KEY),
								$property->getFormat(),
								$property->getInvalid(),
							),
						),
					);
				} catch (Exceptions\InvalidArgument $ex) {
					$data->offsetSet(States\Property::ACTUAL_VALUE_KEY, null);
					$data->offsetSet(States\Property::VALID_KEY, false);

					$this->logger->error(
						'Provided property actual value is not valid',
						[
							'source' => MetadataTypes\ModuleSource::SOURCE_MODULE_DEVICES,
							'type' => 'device-properties-states',
							'exception' => BootstrapHelpers\Logger::buildException($ex),
						],
					);
				}
			}
		}

		if ($data->offsetExists(States\Property::EXPECTED_VALUE_KEY)) {
			if ($forWriting) {
				try {
					$data->offsetSet(
						States\Property::EXPECTED_VALUE_KEY,
						ValueHelper::flattenValue(
							ValueHelper::normalizeWriteValue(
								$property->getDataType(),
								/** @phpstan-ignore-next-line */
								$data->offsetGet(States\Property::EXPECTED_VALUE_KEY),
								$property->getFormat(),
								$property->getScale(),
								$property->getInvalid(),
							),
						),
					);
				} catch (Exceptions\InvalidArgument $ex) {
					$data->offsetSet(States\Property::EXPECTED_VALUE_KEY, null);
					$data->offsetSet(States\Property::PENDING_KEY, false);

					$this->logger->error(
						'Provided property expected value was not valid',
						[
							'source' => MetadataTypes\ModuleSource::SOURCE_MODULE_DEVICES,
							'type' => 'device-properties-states',
							'exception' => BootstrapHelpers\Logger::buildException($ex),
						],
					);
				}
			} else {
				try {
					$data->offsetSet(
						States\Property::EXPECTED_VALUE_KEY,
						ValueHelper::flattenValue(
							ValueHelper::normalizeValue(
								$property->getDataType(),
								/** @phpstan-ignore-next-line */
								$data->offsetGet(States\Property::EXPECTED_VALUE_KEY),
								$property->getFormat(),
								$property->getInvalid(),
							),
						),
					);
				} catch (Exceptions\InvalidArgument $ex) {
					$data->offsetSet(States\Property::EXPECTED_VALUE_KEY, null);
					$data->offsetSet(States\Property::PENDING_KEY, false);

					$this->logger->error(
						'Provided property expected value was not valid',
						[
							'source' => MetadataTypes\ModuleSource::SOURCE_MODULE_DEVICES,
							'type' => 'device-properties-states',
							'exception' => BootstrapHelpers\Logger::buildException($ex),
						],
					);
				}
			}
		}

		try {
			// In case synchronization failed...
			if ($state === null) {
				// ...create state in storage
				$state = $this->devicePropertiesStatesManager->create(
					$property,
					$data,
				);

				$this->logger->debug(
					'Device property state was created',
					[
						'source' => MetadataTypes\ModuleSource::SOURCE_MODULE_DEVICES,
						'type' => 'device-properties-states',
						'property' => [
							'id' => $property->getId()->toString(),
							'state' => $state->toArray(),
						],
					],
				);
			} else {
				$state = $this->devicePropertiesStatesManager->update(
					$property,
					$state,
					$data,
				);

				$this->logger->debug(
					'Device property state was updated',
					[
						'source' => MetadataTypes\ModuleSource::SOURCE_MODULE_DEVICES,
						'type' => 'device-properties-states',
						'property' => [
							'id' => $property->getId()->toString(),
							'state' => $state->toArray(),
						],
					],
				);
			}
		} catch (Exceptions\NotImplemented) {
			$this->logger->warning(
				'Devices states manager is not configured. State could not be saved',
				[
					'source' => MetadataTypes\ModuleSource::SOURCE_MODULE_DEVICES,
					'type' => 'device-properties-states',
				],
			);
		}
	}

}
