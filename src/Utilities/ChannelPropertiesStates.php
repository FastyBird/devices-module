<?php declare(strict_types = 1);

/**
 * ChannelPropertiesStates.php
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
use Orisai\ObjectMapper;
use function array_merge;
use function assert;
use function is_array;

/**
 * Useful channel dynamic property state helpers
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Utilities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ChannelPropertiesStates
{

	use Nette\SmartObject;

	/**
	 * @param Models\Configuration\Channels\Properties\Repository<MetadataDocuments\DevicesModule\ChannelDynamicProperty|MetadataDocuments\DevicesModule\ChannelMappedProperty> $channelPropertiesRepository
	 */
	public function __construct(
		private readonly Models\Configuration\Channels\Properties\Repository $channelPropertiesRepository,
		private readonly Models\States\ChannelPropertiesRepository $channelPropertyStateRepository,
		private readonly Models\States\ChannelPropertiesManager $channelPropertiesStatesManager,
		private readonly Devices\Logger $logger,
		private readonly ObjectMapper\Processing\Processor $stateMapper,
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
		MetadataDocuments\DevicesModule\ChannelDynamicProperty|MetadataDocuments\DevicesModule\ChannelMappedProperty|Entities\Channels\Properties\Dynamic|Entities\Channels\Properties\Mapped $property,
	): States\ChannelProperty|null
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
		MetadataDocuments\DevicesModule\ChannelDynamicProperty|MetadataDocuments\DevicesModule\ChannelMappedProperty|Entities\Channels\Properties\Dynamic|Entities\Channels\Properties\Mapped $property,
	): States\ChannelProperty|null
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
		MetadataDocuments\DevicesModule\ChannelDynamicProperty|MetadataDocuments\DevicesModule\ChannelMappedProperty|Entities\Channels\Properties\Dynamic|Entities\Channels\Properties\Mapped $property,
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
		MetadataDocuments\DevicesModule\ChannelDynamicProperty|MetadataDocuments\DevicesModule\ChannelMappedProperty|Entities\Channels\Properties\Dynamic|Entities\Channels\Properties\Mapped $property,
		Utils\ArrayHash $data,
	): void
	{
		$this->saveValue($property, $data, false);
	}

	/**
	 * @param MetadataDocuments\DevicesModule\ChannelDynamicProperty|MetadataDocuments\DevicesModule\ChannelMappedProperty|array<MetadataDocuments\DevicesModule\ChannelDynamicProperty|MetadataDocuments\DevicesModule\ChannelMappedProperty>|Entities\Channels\Properties\Dynamic|Entities\Channels\Properties\Mapped|array<Entities\Channels\Properties\Dynamic|Entities\Channels\Properties\Mapped> $property
	 *
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\MalformedInput
	 */
	public function setValidState(
		MetadataDocuments\DevicesModule\ChannelDynamicProperty|MetadataDocuments\DevicesModule\ChannelMappedProperty|Entities\Channels\Properties\Dynamic|Entities\Channels\Properties\Mapped|array $property,
		bool $state,
	): void
	{
		if (is_array($property)) {
			foreach ($property as $item) {
				$this->writeValue($item, Utils\ArrayHash::from([
					States\Property::VALID_KEY => $state,
				]));
			}
		} else {
			$this->writeValue($property, Utils\ArrayHash::from([
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
		MetadataDocuments\DevicesModule\ChannelDynamicProperty|MetadataDocuments\DevicesModule\ChannelMappedProperty|Entities\Channels\Properties\Dynamic|Entities\Channels\Properties\Mapped $property,
		bool $forReading,
	): States\ChannelProperty|null
	{
		if ($property instanceof Entities\Channels\Properties\Property) {
			$findPropertyQuery = new Queries\Configuration\FindChannelProperties();
			$findPropertyQuery->byId($property->getId());

			$property = $this->channelPropertiesRepository->findOneBy($findPropertyQuery);
			assert(
				$property instanceof MetadataDocuments\DevicesModule\ChannelDynamicProperty
				|| $property instanceof MetadataDocuments\DevicesModule\ChannelMappedProperty,
			);
		}

		if ($property instanceof MetadataDocuments\DevicesModule\ChannelMappedProperty) {
			$findPropertyQuery = new Queries\Configuration\FindChannelProperties();
			$findPropertyQuery->byId($property->getParent());

			$parent = $this->channelPropertiesRepository->findOneBy($findPropertyQuery);

			if (!$parent instanceof MetadataDocuments\DevicesModule\ChannelDynamicProperty) {
				throw new Exceptions\InvalidState('Mapped property parent could not be loaded');
			}

			$property = $parent;
		}

		try {
			$state = $this->channelPropertyStateRepository->findOne($property);

			if ($state !== null) {
				try {
					if ($state->getActualValue() !== null) {
						$state = $forReading ? $this->updateState(
							$state,
							[
								States\Property::ACTUAL_VALUE_KEY => ValueHelper::normalizeReadValue(
									$property->getDataType(),
									$state->getActualValue(),
									$property->getFormat(),
									$property->getScale(),
									$property->getInvalid(),
								),
							],
						) : $this->updateState(
							$state,
							[
								States\Property::ACTUAL_VALUE_KEY => ValueHelper::normalizeValue(
									$property->getDataType(),
									$state->getActualValue(),
									$property->getFormat(),
									$property->getInvalid(),
								),
							],
						);
					}
				} catch (Exceptions\InvalidArgument $ex) {
					$this->channelPropertiesStatesManager->update($property, $state, Utils\ArrayHash::from([
						States\Property::ACTUAL_VALUE_KEY => null,
						States\Property::VALID_KEY => false,
					]));

					$this->logger->error(
						'Property stored actual value was not valid',
						[
							'source' => MetadataTypes\ModuleSource::SOURCE_MODULE_DEVICES,
							'type' => 'channel-properties-states',
							'exception' => BootstrapHelpers\Logger::buildException($ex),
						],
					);

					return $this->loadValue($property, $forReading);
				}

				try {
					if ($state->getExpectedValue() !== null) {
						$state = $forReading ? $this->updateState(
							$state,
							[
								States\Property::EXPECTED_VALUE_KEY => ValueHelper::normalizeReadValue(
									$property->getDataType(),
									$state->getExpectedValue(),
									$property->getFormat(),
									$property->getScale(),
									$property->getInvalid(),
								),
							],
						) : $this->updateState(
							$state,
							[
								States\Property::EXPECTED_VALUE_KEY => ValueHelper::normalizeValue(
									$property->getDataType(),
									$state->getExpectedValue(),
									$property->getFormat(),
									$property->getInvalid(),
								),
							],
						);
					}
				} catch (Exceptions\InvalidArgument $ex) {
					$this->channelPropertiesStatesManager->update($property, $state, Utils\ArrayHash::from([
						States\Property::EXPECTED_VALUE_KEY => null,
						States\Property::PENDING_KEY => false,
					]));

					$this->logger->error(
						'Property stored expected value was not valid',
						[
							'source' => MetadataTypes\ModuleSource::SOURCE_MODULE_DEVICES,
							'type' => 'channel-properties-states',
							'exception' => BootstrapHelpers\Logger::buildException($ex),
						],
					);

					return $this->loadValue($property, $forReading);
				}
			}

			return $state;
		} catch (Exceptions\NotImplemented) {
			$this->logger->warning(
				'Channels states repository is not configured. State could not be fetched',
				[
					'source' => MetadataTypes\ModuleSource::SOURCE_MODULE_DEVICES,
					'type' => 'channel-properties-states',
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
		MetadataDocuments\DevicesModule\ChannelDynamicProperty|MetadataDocuments\DevicesModule\ChannelMappedProperty|Entities\Channels\Properties\Dynamic|Entities\Channels\Properties\Mapped $property,
		Utils\ArrayHash $data,
		bool $forWriting,
	): void
	{
		if ($property instanceof Entities\Channels\Properties\Property) {
			$findPropertyQuery = new Queries\Configuration\FindChannelProperties();
			$findPropertyQuery->byId($property->getId());

			$property = $this->channelPropertiesRepository->findOneBy($findPropertyQuery);
			assert(
				$property instanceof MetadataDocuments\DevicesModule\ChannelDynamicProperty
				|| $property instanceof MetadataDocuments\DevicesModule\ChannelMappedProperty,
			);
		}

		if ($property instanceof MetadataDocuments\DevicesModule\ChannelMappedProperty) {
			$findPropertyQuery = new Queries\Configuration\FindChannelProperties();
			$findPropertyQuery->byId($property->getParent());

			$parent = $this->channelPropertiesRepository->findOneBy($findPropertyQuery);

			if (!$parent instanceof MetadataDocuments\DevicesModule\ChannelDynamicProperty) {
				throw new Exceptions\InvalidState('Mapped property parent could not be loaded');
			}

			$property = $parent;
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
							'type' => 'channel-properties-states',
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
							'type' => 'channel-properties-states',
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
							'type' => 'channel-properties-states',
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
							'type' => 'channel-properties-states',
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
				$state = $this->channelPropertiesStatesManager->create(
					$property,
					$data,
				);

				$this->logger->debug(
					'Channel property state was created',
					[
						'source' => MetadataTypes\ModuleSource::SOURCE_MODULE_DEVICES,
						'type' => 'channel-properties-states',
						'property' => [
							'id' => $property->getId()->toString(),
							'state' => $state->toArray(),
						],
					],
				);
			} else {
				$state = $this->channelPropertiesStatesManager->update(
					$property,
					$state,
					$data,
				);

				$this->logger->debug(
					'Channel property state was updated',
					[
						'source' => MetadataTypes\ModuleSource::SOURCE_MODULE_DEVICES,
						'type' => 'channel-properties-states',
						'property' => [
							'id' => $property->getId()->toString(),
							'state' => $state->toArray(),
						],
					],
				);
			}
		} catch (Exceptions\NotImplemented) {
			$this->logger->warning(
				'Channels states manager is not configured. State could not be saved',
				[
					'source' => MetadataTypes\ModuleSource::SOURCE_MODULE_DEVICES,
					'type' => 'channel-properties-states',
				],
			);
		}
	}

	/**
	 * @param array<string, mixed> $update
	 *
	 * @throws Exceptions\InvalidArgument
	 */
	private function updateState(
		States\ChannelProperty $state,
		array $update,
	): States\ChannelProperty
	{
		try {
			$options = new ObjectMapper\Processing\Options();
			$options->setAllowUnknownFields();

			return $this->stateMapper->process(
				array_merge(
					$state->toArray(),
					$update,
				),
				$state::class,
				$options,
			);
		} catch (ObjectMapper\Exception\InvalidData $ex) {
			$errorPrinter = new ObjectMapper\Printers\ErrorVisualPrinter(
				new ObjectMapper\Printers\TypeToStringConverter(),
			);

			throw new Exceptions\InvalidArgument('Could not map data to state: ' . $errorPrinter->printError($ex));
		}
	}

}
