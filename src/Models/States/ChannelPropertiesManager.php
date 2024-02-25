<?php declare(strict_types = 1);

/**
 * ChannelPropertiesStates.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          1.0.0
 *
 * @date           23.08.22
 */

namespace FastyBird\Module\Devices\Models\States;

use DateTimeInterface;
use FastyBird\DateTimeFactory;
use FastyBird\Library\Application\Helpers as ApplicationHelpers;
use FastyBird\Library\Exchange\Publisher as ExchangePublisher;
use FastyBird\Library\Metadata\Documents as MetadataDocuments;
use FastyBird\Library\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use FastyBird\Library\Metadata\Utilities as MetadataUtilities;
use FastyBird\Library\Tools\Exceptions as ToolsExceptions;
use FastyBird\Module\Devices;
use FastyBird\Module\Devices\Documents;
use FastyBird\Module\Devices\Events;
use FastyBird\Module\Devices\Exceptions;
use FastyBird\Module\Devices\Models;
use FastyBird\Module\Devices\Queries;
use FastyBird\Module\Devices\States;
use FastyBird\Module\Devices\Types;
use Nette;
use Nette\Caching;
use Nette\Utils;
use Orisai\ObjectMapper;
use Psr\EventDispatcher as PsrEventDispatcher;
use Ramsey\Uuid;
use Throwable;
use TypeError;
use ValueError;
use function array_map;
use function array_merge;
use function assert;
use function boolval;
use function is_array;
use function strval;

/**
 * Useful channel dynamic property state helpers
 *
 * @extends PropertiesManager<Documents\Channels\Properties\Dynamic, Documents\Channels\Properties\Mapped | null, States\ChannelProperty>
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ChannelPropertiesManager extends PropertiesManager
{

	use Nette\SmartObject;

	public function __construct(
		private readonly bool $useExchange,
		private readonly Models\Configuration\Channels\Properties\Repository $channelPropertiesConfigurationRepository,
		private readonly Channels\Repository $channelPropertyStateRepository,
		private readonly Channels\Manager $channelPropertiesStatesManager,
		private readonly DateTimeFactory\Factory $dateTimeFactory,
		private readonly MetadataDocuments\DocumentFactory $documentFactory,
		private readonly ExchangePublisher\Publisher $publisher,
		private readonly Caching\Cache $cache,
		Devices\Logger $logger,
		ObjectMapper\Processing\Processor $stateMapper,
		private readonly PsrEventDispatcher\EventDispatcherInterface|null $dispatcher = null,
	)
	{
		parent::__construct($logger, $stateMapper);
	}

	/**
	 * @throws Exceptions\InvalidState
	 */
	public function read(
		Documents\Channels\Properties\Dynamic|Documents\Channels\Properties\Mapped $property,
		MetadataTypes\Sources\Source|null $source,
	): bool|Documents\States\Channels\Properties\Property|null
	{
		if ($this->useExchange) {
			try {
				return $this->publisher->publish(
					$source ?? MetadataTypes\Sources\Module::DEVICES,
					Devices\Constants::MESSAGE_BUS_CHANNEL_PROPERTY_ACTION_ROUTING_KEY,
					$this->documentFactory->create(
						Documents\States\Channels\Properties\Actions\Action::class,
						[
							'action' => Types\PropertyAction::GET->value,
							'channel' => $property->getChannel()->toString(),
							'property' => $property->getId()->toString(),
						],
					),
				);
			} catch (Throwable $ex) {
				throw new Exceptions\InvalidState(
					'Requested action could not be published for write action',
					$ex->getCode(),
					$ex,
				);
			}
		} else {
			$document = $this->cache->load(
				'read_' . $property->getId()->toString(),
				fn () => $this->readState($property),
				[
					Caching\Cache::Tags => array_merge(
						[$property->getId()->toString()],
						$property instanceof Documents\Channels\Properties\Mapped
							? [$property->getParent()->toString()]
							: [],
					),
				],
			);
			assert($document instanceof Documents\States\Channels\Properties\Property || $document === null);

			return $document;
		}
	}

	/**
	 * @throws Exceptions\InvalidArgument
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws ToolsExceptions\InvalidArgument
	 * @throws TypeError
	 * @throws ValueError
	 */
	public function write(
		Documents\Channels\Properties\Dynamic|Documents\Channels\Properties\Mapped $property,
		Utils\ArrayHash $data,
		MetadataTypes\Sources\Source|null $source,
	): void
	{
		if ($this->useExchange) {
			try {
				$this->publisher->publish(
					$source ?? MetadataTypes\Sources\Module::DEVICES,
					Devices\Constants::MESSAGE_BUS_CHANNEL_PROPERTY_ACTION_ROUTING_KEY,
					$this->documentFactory->create(
						Documents\States\Channels\Properties\Actions\Action::class,
						array_merge(
							[
								'action' => Types\PropertyAction::SET->value,
								'channel' => $property->getChannel()->toString(),
								'property' => $property->getId()->toString(),
							],
							[
								'write' => array_map(
									// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
									static fn (bool|int|float|string|DateTimeInterface|MetadataTypes\Payloads\Payload|null $item): bool|int|float|string|null => MetadataUtilities\Value::flattenValue(
										$item,
									),
									(array) $data,
								),
							],
						),
					),
				);
			} catch (Throwable $ex) {
				throw new Exceptions\InvalidState(
					'Requested value could not be published for write action',
					$ex->getCode(),
					$ex,
				);
			}
		} else {
			$this->writeState($property, $data, true, $source);
		}
	}

	/**
	 * @throws Exceptions\InvalidArgument
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws ToolsExceptions\InvalidArgument
	 * @throws TypeError
	 * @throws ValueError
	 */
	public function set(
		Documents\Channels\Properties\Dynamic|Documents\Channels\Properties\Mapped $property,
		Utils\ArrayHash $data,
		MetadataTypes\Sources\Source|null $source,
	): void
	{
		if ($this->useExchange) {
			try {
				$this->publisher->publish(
					$source ?? MetadataTypes\Sources\Module::DEVICES,
					Devices\Constants::MESSAGE_BUS_CHANNEL_PROPERTY_ACTION_ROUTING_KEY,
					$this->documentFactory->create(
						Documents\States\Channels\Properties\Actions\Action::class,
						array_merge(
							[
								'action' => Types\PropertyAction::SET->value,
								'channel' => $property->getChannel()->toString(),
								'property' => $property->getId()->toString(),
							],
							[
								'set' => array_map(
									// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
									static fn (bool|int|float|string|DateTimeInterface|MetadataTypes\Payloads\Payload|null $item): bool|int|float|string|null => MetadataUtilities\Value::flattenValue(
										$item,
									),
									(array) $data,
								),
							],
						),
					),
				);
			} catch (Throwable $ex) {
				throw new Exceptions\InvalidState(
					'Requested value could not be published for set action',
					$ex->getCode(),
					$ex,
				);
			}
		} else {
			$this->writeState($property, $data, false, $source);
		}
	}

	/**
	 * @param Documents\Channels\Properties\Dynamic|array<Documents\Channels\Properties\Dynamic> $property
	 *
	 * @throws Exceptions\InvalidArgument
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws ToolsExceptions\InvalidArgument
	 * @throws TypeError
	 * @throws ValueError
	 */
	public function setValidState(
		Documents\Channels\Properties\Dynamic|array $property,
		bool $state,
		MetadataTypes\Sources\Source|null $source,
	): void
	{
		if (is_array($property)) {
			foreach ($property as $item) {
				$this->set(
					$item,
					Utils\ArrayHash::from([
						States\Property::VALID_FIELD => $state,
					]),
					$source,
				);
			}
		} else {
			$this->set(
				$property,
				Utils\ArrayHash::from([
					States\Property::VALID_FIELD => $state,
				]),
				$source,
			);
		}
	}

	/**
	 * @param Documents\Channels\Properties\Dynamic|array<Documents\Channels\Properties\Dynamic> $property
	 *
	 * @throws Exceptions\InvalidArgument
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws ToolsExceptions\InvalidArgument
	 * @throws TypeError
	 * @throws ValueError
	 */
	public function setPendingState(
		Documents\Channels\Properties\Dynamic|array $property,
		bool $pending,
		MetadataTypes\Sources\Source|null $source,
	): void
	{
		if (is_array($property)) {
			foreach ($property as $item) {
				if ($pending === false) {
					$this->set(
						$item,
						Utils\ArrayHash::from([
							States\Property::EXPECTED_VALUE_FIELD => null,
							States\Property::PENDING_FIELD => false,
						]),
						$source,
					);
				} else {
					$this->set(
						$item,
						Utils\ArrayHash::from([
							States\Property::PENDING_FIELD => $this->dateTimeFactory->getNow()->format(
								DateTimeInterface::ATOM,
							),
						]),
						$source,
					);
				}
			}
		} else {
			if ($pending === false) {
				$this->set(
					$property,
					Utils\ArrayHash::from([
						States\Property::EXPECTED_VALUE_FIELD => null,
						States\Property::PENDING_FIELD => false,
					]),
					$source,
				);
			} else {
				$this->set(
					$property,
					Utils\ArrayHash::from([
						States\Property::PENDING_FIELD => $this->dateTimeFactory->getNow()->format(
							DateTimeInterface::ATOM,
						),
					]),
					$source,
				);
			}
		}
	}

	/**
	 * @throws Exceptions\InvalidState
	 */
	public function delete(Uuid\UuidInterface $id): bool
	{
		try {
			$result = $this->channelPropertiesStatesManager->delete($id);

			if ($result) {
				$this->dispatcher?->dispatch(new Events\ChannelPropertyStateEntityDeleted(
					$id,
					MetadataTypes\Sources\Module::DEVICES,
				));

				foreach ($this->findChildren($id) as $child) {
					$this->dispatcher?->dispatch(new Events\ChannelPropertyStateEntityDeleted(
						$child->getId(),
						MetadataTypes\Sources\Module::DEVICES,
					));
				}
			}

			return $result;
		} catch (Exceptions\NotImplemented) {
			$this->logger->warning(
				'Channels states manager is not configured. State could not be fetched',
				[
					'source' => MetadataTypes\Sources\Module::DEVICES->value,
					'type' => 'channel-properties-states',
				],
			);
		}

		return false;
	}

	/**
	 * @throws Exceptions\InvalidArgument
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\Mapping
	 * @throws MetadataExceptions\MalformedInput
	 * @throws ToolsExceptions\InvalidArgument
	 * @throws TypeError
	 * @throws ValueError
	 *
	 * @interal
	 */
	public function readState(
		Documents\Channels\Properties\Dynamic|Documents\Channels\Properties\Mapped $property,
	): Documents\States\Channels\Properties\Property|null
	{
		$mappedProperty = null;

		if ($property instanceof Documents\Channels\Properties\Mapped) {
			$parent = $this->channelPropertiesConfigurationRepository->find($property->getParent());

			if (!$parent instanceof Documents\Channels\Properties\Dynamic) {
				throw new Exceptions\InvalidState('Mapped property parent could not be loaded');
			}

			$mappedProperty = $property;

			$property = $parent;
		}

		try {
			$state = $this->channelPropertyStateRepository->find($property->getId());

		} catch (Exceptions\NotImplemented) {
			$this->logger->warning(
				'Channels states repository is not configured. State could not be fetched',
				[
					'source' => MetadataTypes\Sources\Module::DEVICES->value,
					'type' => 'channel-properties-states',
				],
			);

			return null;
		}

		try {
			if ($state === null) {
				return null;
			}

			$readValue = $this->convertStoredState($property, $mappedProperty, $state, true);
			$getValue = $this->convertStoredState($property, $mappedProperty, $state, false);

			return $this->documentFactory->create(
				Documents\States\Channels\Properties\Property::class,
				[
					'id' => $property->getId()->toString(),
					'channel' => $property->getChannel()->toString(),
					'read' => $readValue->toArray(),
					'get' => $getValue->toArray(),
					'valid' => $state->isValid(),
					'pending' => $state->getPending() instanceof DateTimeInterface
						? $state->getPending()->format(DateTimeInterface::ATOM)
						: $state->getPending(),
					'created_at' => $readValue->getCreatedAt()?->format(DateTimeInterface::ATOM),
					'updated_at' => $readValue->getUpdatedAt()?->format(DateTimeInterface::ATOM),
				],
			);
		} catch (Exceptions\InvalidActualValue $ex) {
			try {
				$this->channelPropertiesStatesManager->update($property, $state, Utils\ArrayHash::from([
					States\Property::ACTUAL_VALUE_FIELD => null,
					States\Property::VALID_FIELD => false,
				]));

				$this->logger->error(
					'Property stored actual value was not valid',
					[
						'source' => MetadataTypes\Sources\Module::DEVICES->value,
						'type' => 'channel-properties-states',
						'exception' => ApplicationHelpers\Logger::buildException($ex),
					],
				);

				return $this->readState($property);
			} catch (Exceptions\NotImplemented) {
				$this->logger->warning(
					'Channels states manager is not configured. State could not be fetched',
					[
						'source' => MetadataTypes\Sources\Module::DEVICES->value,
						'type' => 'channel-properties-states',
					],
				);

				return null;
			}
		} catch (Exceptions\InvalidExpectedValue $ex) {
			try {
				$this->channelPropertiesStatesManager->update($property, $state, Utils\ArrayHash::from([
					States\Property::EXPECTED_VALUE_FIELD => null,
					States\Property::PENDING_FIELD => false,
				]));

				$this->logger->error(
					'Property stored expected value was not valid',
					[
						'source' => MetadataTypes\Sources\Module::DEVICES->value,
						'type' => 'channel-properties-states',
						'exception' => ApplicationHelpers\Logger::buildException($ex),
					],
				);

				return $this->readState($property);
			} catch (Exceptions\NotImplemented) {
				$this->logger->warning(
					'Channels states manager is not configured. State could not be fetched',
					[
						'source' => MetadataTypes\Sources\Module::DEVICES->value,
						'type' => 'channel-properties-states',
					],
				);

				return null;
			}
		}
	}

	/**
	 * @throws Exceptions\InvalidArgument
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws ToolsExceptions\InvalidArgument
	 * @throws TypeError
	 * @throws ValueError
	 *
	 * @interal
	 */
	public function writeState(
		Documents\Channels\Properties\Dynamic|Documents\Channels\Properties\Mapped $property,
		Utils\ArrayHash $data,
		bool $forWriting,
		MetadataTypes\Sources\Source|null $source,
	): void
	{
		$mappedProperty = null;

		if ($property instanceof Documents\Channels\Properties\Mapped) {
			$parent = $this->channelPropertiesConfigurationRepository->find($property->getParent());

			if (!$parent instanceof Documents\Channels\Properties\Dynamic) {
				throw new Exceptions\InvalidState('Mapped property parent could not be loaded');
			}

			$mappedProperty = $property;

			$property = $parent;
		}

		try {
			$state = $this->channelPropertyStateRepository->find($property->getId());
		} catch (Exceptions\NotImplemented) {
			$state = null;
		}

		/**
		 * IMPORTANT: ACTUAL VALUE field is meant to be used only by connectors for saving device actual value
		 */
		if ($data->offsetExists(States\Property::ACTUAL_VALUE_FIELD)) {
			if ($mappedProperty !== null) {
				throw new Exceptions\InvalidArgument(
					'Setting property actual value is not allowed for mapped properties',
				);
			}

			if ($forWriting === true) {
				throw new Exceptions\InvalidArgument(
					'Setting property actual value could be done only by "setValue" method',
				);
			}

			try {
				if (
					$property->getInvalid() !== null
					&& strval(
						MetadataUtilities\Value::flattenValue(
							// @phpstan-ignore-next-line
							$data->offsetGet(States\Property::ACTUAL_VALUE_FIELD),
						),
					) === strval(
						MetadataUtilities\Value::flattenValue($property->getInvalid()),
					)
				) {
					$data->offsetSet(States\Property::ACTUAL_VALUE_FIELD, null);
					$data->offsetSet(States\Property::VALID_FIELD, false);

				} else {
					$actualValue = $this->convertWriteActualValue(
						// @phpstan-ignore-next-line
						$data->offsetGet(States\Property::ACTUAL_VALUE_FIELD),
						$property,
					);

					$data->offsetSet(
						States\Property::ACTUAL_VALUE_FIELD,
						MetadataUtilities\Value::flattenValue($actualValue),
					);

					if ($data->offsetExists(States\Property::VALID_FIELD)) {
						$data->offsetSet(
							States\Property::VALID_FIELD,
							boolval($data->offsetGet(States\Property::VALID_FIELD)),
						);
					} else {
						$data->offsetSet(States\Property::VALID_FIELD, true);
					}
				}
			} catch (MetadataExceptions\InvalidValue $ex) {
				$data->offsetUnset(States\Property::ACTUAL_VALUE_FIELD);
				$data->offsetSet(States\Property::VALID_FIELD, false);

				$this->logger->error(
					'Provided property actual value is not valid',
					[
						'source' => MetadataTypes\Sources\Module::DEVICES->value,
						'type' => 'channel-properties-states',
						'exception' => ApplicationHelpers\Logger::buildException($ex),
					],
				);
			}
		}

		/**
		 * IMPORTANT: EXPECTED VALUE field is meant to be used mainly by user interface for saving value which should
		 * be then written into device
		 */
		if ($data->offsetExists(States\Property::EXPECTED_VALUE_FIELD)) {
			if (
				$data->offsetGet(States\Property::EXPECTED_VALUE_FIELD) !== null
				&& $data->offsetGet(States\Property::EXPECTED_VALUE_FIELD) !== ''
			) {
				try {
					$expectedValue = $this->convertWriteExpectedValue(
						// @phpstan-ignore-next-line
						$data->offsetGet(States\Property::EXPECTED_VALUE_FIELD),
						$property,
						$mappedProperty,
						$forWriting,
					);

					if (
						$expectedValue !== null
						&& (
							!$property->isSettable()
							|| (
								$mappedProperty !== null
								&& !$mappedProperty->isSettable()
							)
						)
					) {
						throw new Exceptions\InvalidArgument(
							'Property is not settable, expected value could not written',
						);
					}

					$data->offsetSet(
						States\Property::EXPECTED_VALUE_FIELD,
						MetadataUtilities\Value::flattenValue($expectedValue),
					);
					$data->offsetSet(
						States\Property::PENDING_FIELD,
						$expectedValue !== null,
					);
				} catch (MetadataExceptions\InvalidValue $ex) {
					$data->offsetSet(States\Property::EXPECTED_VALUE_FIELD, null);
					$data->offsetSet(States\Property::PENDING_FIELD, false);

					$this->logger->error(
						'Provided property expected value was not valid',
						[
							'source' => MetadataTypes\Sources\Module::DEVICES->value,
							'type' => 'channel-properties-states',
							'exception' => ApplicationHelpers\Logger::buildException($ex),
						],
					);
				}
			} else {
				$data->offsetSet(States\Property::EXPECTED_VALUE_FIELD, null);
				$data->offsetSet(States\Property::PENDING_FIELD, false);
			}
		}

		try {
			if ($state !== null) {
				$actualValue = MetadataUtilities\Value::flattenValue(
					$this->convertReadValue($state->getActualValue(), $property, null, false),
				);
				$expectedValue = MetadataUtilities\Value::flattenValue(
					$this->convertWriteExpectedValue($state->getExpectedValue(), $property, null, false),
				);

				if (
					$data->offsetExists(States\Property::EXPECTED_VALUE_FIELD)
					&& $data->offsetGet(States\Property::EXPECTED_VALUE_FIELD) === $actualValue
				) {
					$data->offsetUnset(States\Property::EXPECTED_VALUE_FIELD);
					$data->offsetUnset(States\Property::PENDING_FIELD);

				} elseif (
					$data->offsetExists(States\Property::ACTUAL_VALUE_FIELD)
					&& $data->offsetGet(States\Property::ACTUAL_VALUE_FIELD) === $expectedValue
				) {
					$data->offsetSet(States\Property::EXPECTED_VALUE_FIELD, null);
					$data->offsetSet(States\Property::PENDING_FIELD, false);
				}
			}
		} catch (MetadataExceptions\InvalidValue) {
			// Could be ignored
		}

		if ($data->count() === 0) {
			return;
		}

		try {
			if ($state === null) {
				$result = $this->channelPropertiesStatesManager->create(
					$property,
					$data,
				);

			} else {
				$result = $this->channelPropertiesStatesManager->update(
					$property,
					$state,
					$data,
				);

				if ($result === false) {
					return;
				}
			}

			$readValue = $this->convertStoredState($property, null, $result, true);
			$getValue = $this->convertStoredState($property, null, $result, false);

			if ($state === null) {
				$this->dispatcher?->dispatch(
					new Events\ChannelPropertyStateEntityCreated(
						$property,
						$readValue,
						$getValue,
						$source ?? MetadataTypes\Sources\Module::DEVICES,
					),
				);
			} else {
				$this->dispatcher?->dispatch(
					new Events\ChannelPropertyStateEntityUpdated(
						$property,
						$readValue,
						$getValue,
						$source ?? MetadataTypes\Sources\Module::DEVICES,
					),
				);
			}

			foreach ($this->findChildren($property->getId()) as $child) {
				$readValue = $this->convertStoredState($property, $child, $result, true);
				$getValue = $this->convertStoredState($property, $child, $result, false);

				if ($state === null) {
					$this->dispatcher?->dispatch(
						new Events\ChannelPropertyStateEntityCreated(
							$child,
							$readValue,
							$getValue,
							$source ?? MetadataTypes\Sources\Module::DEVICES,
						),
					);
				} else {
					$this->dispatcher?->dispatch(
						new Events\ChannelPropertyStateEntityUpdated(
							$child,
							$readValue,
							$getValue,
							$source ?? MetadataTypes\Sources\Module::DEVICES,
						),
					);
				}
			}

			$this->logger->debug(
				$state === null ? 'Channel property state was created' : 'Channel property state was updated',
				[
					'source' => MetadataTypes\Sources\Module::DEVICES->value,
					'type' => 'channel-properties-states',
					'property' => [
						'id' => $property->getId()->toString(),
						'state' => $result->toArray(),
					],
				],
			);
		} catch (Exceptions\NotImplemented) {
			$this->logger->warning(
				'Channels states manager is not configured. State could not be saved',
				[
					'source' => MetadataTypes\Sources\Module::DEVICES->value,
					'type' => 'channel-properties-states',
				],
			);
		}
	}

	/**
	 * @return array<Documents\Channels\Properties\Mapped>
	 *
	 * @throws Exceptions\InvalidState
	 */
	private function findChildren(Uuid\UuidInterface $id): array
	{
		$findPropertiesQuery = new Queries\Configuration\FindChannelMappedProperties();
		$findPropertiesQuery->byParentId($id);

		return $this->channelPropertiesConfigurationRepository->findAllBy(
			$findPropertiesQuery,
			Documents\Channels\Properties\Mapped::class,
		);
	}

}
