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
 * @date           22.01.24
 */

namespace FastyBird\Module\Devices\Models\States\Async;

use DateTimeInterface;
use FastyBird\DateTimeFactory;
use FastyBird\Library\Application\Helpers as ApplicationHelpers;
use FastyBird\Library\Exchange\Publisher as ExchangePublisher;
use FastyBird\Library\Metadata\Documents as MetadataDocuments;
use FastyBird\Library\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use FastyBird\Library\Metadata\Utilities as MetadataUtilities;
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
use React\Promise;
use Throwable;
use function array_map;
use function array_merge;
use function boolval;
use function is_array;
use function is_bool;
use function React\Async\async;
use function React\Async\await;
use function strval;

/**
 * Useful channel dynamic property state helpers
 *
 * @extends Models\States\PropertiesManager<Documents\Channels\Properties\Dynamic, Documents\Channels\Properties\Mapped | null, States\ChannelProperty>
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ChannelPropertiesManager extends Models\States\PropertiesManager
{

	use Nette\SmartObject;

	public function __construct(
		private readonly bool $useExchange,
		private readonly Models\Configuration\Channels\Properties\Repository $channelPropertiesConfigurationRepository,
		private readonly Models\States\Channels\Async\Repository $channelPropertyStateRepository,
		private readonly Models\States\Channels\Async\Manager $channelPropertiesStatesManager,
		private readonly DateTimeFactory\Factory $dateTimeFactory,
		private readonly MetadataDocuments\DocumentFactory $documentFactory,
		private readonly ExchangePublisher\Async\Publisher $publisher,
		private readonly Caching\Cache $cache,
		Devices\Logger $logger,
		ObjectMapper\Processing\Processor $stateMapper,
		private readonly PsrEventDispatcher\EventDispatcherInterface|null $dispatcher = null,
	)
	{
		parent::__construct($logger, $stateMapper);
	}

	/**
	 * @return Promise\PromiseInterface<bool|Documents\States\Channels\Properties\Property|null>
	 *
	 * @throws Exceptions\InvalidState
	 */
	public function read(
		Documents\Channels\Properties\Dynamic|Documents\Channels\Properties\Mapped $property,
		MetadataTypes\Sources\Source|null $source,
	): Promise\PromiseInterface
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
				return Promise\reject(new Exceptions\InvalidState(
					'Requested action could not be published for write action',
					$ex->getCode(),
					$ex,
				));
			}
		} else {
			/** @phpstan-var Documents\States\Channels\Properties\Property|null $document */
			$document = $this->cache->load('read_' . $property->getId()->toString());

			if ($document !== null) {
				return Promise\resolve($document);
			}

			$deferred = new Promise\Deferred();

			$this->readState($property)
				->then(
					function (Documents\States\Channels\Properties\Property|null $document) use ($deferred, $property): void {
						$this->cache->save(
							'read_' . $property->getId()->toString(),
							$document,
							[
								Caching\Cache::Tags => array_merge(
									[$property->getId()->toString()],
									$property instanceof Documents\Channels\Properties\Mapped
										? [$property->getParent()->toString()]
										: [],
								),
							],
						);

						$deferred->resolve($document);
					},
				)
				->catch(static function (Throwable $ex) use ($deferred): void {
					$deferred->reject($ex);
				});

			return $deferred->promise();
		}
	}

	/**
	 * @return Promise\PromiseInterface<bool>
	 *
	 * @throws Exceptions\InvalidState
	 */
	public function write(
		Documents\Channels\Properties\Dynamic|Documents\Channels\Properties\Mapped $property,
		Utils\ArrayHash $data,
		MetadataTypes\Sources\Source|null $source,
	): Promise\PromiseInterface
	{
		if ($this->useExchange) {
			try {
				return $this->publisher->publish(
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
				return Promise\reject(new Exceptions\InvalidState(
					'Requested value could not be published for write action',
					$ex->getCode(),
					$ex,
				));
			}
		} else {
			return $this->writeState($property, $data, true, $source);
		}
	}

	/**
	 * @return Promise\PromiseInterface<bool>
	 *
	 * @throws Exceptions\InvalidState
	 */
	public function set(
		Documents\Channels\Properties\Dynamic|Documents\Channels\Properties\Mapped $property,
		Utils\ArrayHash $data,
		MetadataTypes\Sources\Source|null $source,
	): Promise\PromiseInterface
	{
		if ($this->useExchange) {
			try {
				return $this->publisher->publish(
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
				return Promise\reject(new Exceptions\InvalidState(
					'Requested value could not be published for set action',
					$ex->getCode(),
					$ex,
				));
			}
		} else {
			return $this->writeState($property, $data, false, $source);
		}
	}

	/**
	 * @param Documents\Channels\Properties\Dynamic|array<Documents\Channels\Properties\Dynamic> $property
	 *
	 * @return Promise\PromiseInterface<bool>
	 *
	 * @throws Exceptions\InvalidState
	 */
	public function setValidState(
		Documents\Channels\Properties\Dynamic|array $property,
		bool $state,
		MetadataTypes\Sources\Source|null $source,
	): Promise\PromiseInterface
	{
		if (is_array($property)) {
			$deferred = new Promise\Deferred();

			$promises = [];

			foreach ($property as $item) {
				$promises[] = $this->set(
					$item,
					Utils\ArrayHash::from([
						States\Property::VALID_FIELD => $state,
					]),
					$source,
				);
			}

			Promise\all($promises)
				->then(static function () use ($deferred): void {
					$deferred->resolve(true);
				})
				->catch(static function (Throwable $ex) use ($deferred): void {
					$deferred->reject($ex);
				});

			return $deferred->promise();
		}

		return $this->set(
			$property,
			Utils\ArrayHash::from([
				States\Property::VALID_FIELD => $state,
			]),
			$source,
		);
	}

	/**
	 * @param Documents\Channels\Properties\Dynamic|array<Documents\Channels\Properties\Dynamic> $property
	 *
	 * @return Promise\PromiseInterface<bool>
	 *
	 * @throws Exceptions\InvalidState
	 */
	public function setPendingState(
		Documents\Channels\Properties\Dynamic|array $property,
		bool $pending,
		MetadataTypes\Sources\Source|null $source,
	): Promise\PromiseInterface
	{
		if (is_array($property)) {
			$deferred = new Promise\Deferred();

			$promises = [];

			foreach ($property as $item) {
				$promises[] = $pending === false ? $this->set(
					$item,
					Utils\ArrayHash::from([
						States\Property::EXPECTED_VALUE_FIELD => null,
						States\Property::PENDING_FIELD => false,
					]),
					$source,
				) : $this->set(
					$item,
					Utils\ArrayHash::from([
						States\Property::PENDING_FIELD => $this->dateTimeFactory->getNow()->format(
							DateTimeInterface::ATOM,
						),
					]),
					$source,
				);
			}

			Promise\all($promises)
				->then(static function () use ($deferred): void {
					$deferred->resolve(true);
				})
				->catch(static function (Throwable $ex) use ($deferred): void {
					$deferred->reject($ex);
				});

			return $deferred->promise();
		}

		return $pending === false ? $this->set(
			$property,
			Utils\ArrayHash::from([
				States\Property::EXPECTED_VALUE_FIELD => null,
				States\Property::PENDING_FIELD => false,
			]),
			$source,
		) : $this->set(
			$property,
			Utils\ArrayHash::from([
				States\Property::PENDING_FIELD => $this->dateTimeFactory->getNow()->format(DateTimeInterface::ATOM),
			]),
			$source,
		);
	}

	/**
	 * @return Promise\PromiseInterface<bool>
	 */
	public function delete(Uuid\UuidInterface $id): Promise\PromiseInterface
	{
		try {
			$deferred = new Promise\Deferred();

			$this->channelPropertiesStatesManager->delete($id)
				->then(function (bool $result) use ($deferred, $id): void {
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

					$deferred->resolve($result);
				})
				->catch(static function (Throwable $ex) use ($deferred): void {
					$deferred->reject($ex);
				});

			return $deferred->promise();
		} catch (Exceptions\NotImplemented) {
			$this->logger->warning(
				'Channels states manager is not configured. State could not be fetched',
				[
					'source' => MetadataTypes\Sources\Module::DEVICES->value,
					'type' => 'async-channel-properties-states',
				],
			);
		}

		return Promise\resolve(false);
	}

	/**
	 * @return Promise\PromiseInterface<Documents\States\Channels\Properties\Property|null>
	 *
	 * @throws Exceptions\InvalidState
	 *
	 * @interal
	 */
	public function readState(
		Documents\Channels\Properties\Dynamic|Documents\Channels\Properties\Mapped $property,
	): Promise\PromiseInterface
	{
		$mappedProperty = null;

		if ($property instanceof Documents\Channels\Properties\Mapped) {
			$parent = $this->channelPropertiesConfigurationRepository->find($property->getParent());

			if (!$parent instanceof Documents\Channels\Properties\Dynamic) {
				return Promise\reject(new Exceptions\InvalidState('Mapped property parent could not be loaded'));
			}

			$mappedProperty = $property;

			$property = $parent;
		}

		$deferred = new Promise\Deferred();

		$this->channelPropertyStateRepository->find($property->getId())
			->then(
				function (
					States\ChannelProperty|null $state,
				) use (
					$deferred,
					$property,
					$mappedProperty,
				): void {
					if ($state === null) {
						$deferred->resolve(null);

						return;
					}

					try {
						$readValue = $this->convertStoredState($property, $mappedProperty, $state, true);
						$getValue = $this->convertStoredState($property, $mappedProperty, $state, false);

						$deferred->resolve($this->documentFactory->create(
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
						));
					} catch (Exceptions\InvalidActualValue $ex) {
						$this->channelPropertiesStatesManager->update($property, $state, Utils\ArrayHash::from([
							States\Property::ACTUAL_VALUE_FIELD => null,
							States\Property::VALID_FIELD => false,
						]))
							->then(function () use ($property, $deferred): void {
								$this->readState($property)
									->then(static function ($state) use ($deferred): void {
										$deferred->resolve($state);
									})
									->catch(static function (Throwable $ex) use ($deferred): void {
										$deferred->reject($ex);
									});
							})
							->catch(function (Throwable $ex) use ($deferred): void {
								if ($ex instanceof Exceptions\NotImplemented) {
									$this->logger->warning(
										'Channels states manager is not configured. State could not be fetched',
										[
											'source' => MetadataTypes\Sources\Module::DEVICES->value,
											'type' => 'async-channel-properties-states',
										],
									);
								}

								$deferred->reject($ex);
							});

						$this->logger->error(
							'Property stored actual value was not valid',
							[
								'source' => MetadataTypes\Sources\Module::DEVICES->value,
								'type' => 'async-channel-properties-states',
								'exception' => ApplicationHelpers\Logger::buildException($ex),
							],
						);
					} catch (Exceptions\InvalidExpectedValue $ex) {
						$this->channelPropertiesStatesManager->update($property, $state, Utils\ArrayHash::from([
							States\Property::EXPECTED_VALUE_FIELD => null,
							States\Property::PENDING_FIELD => false,
						]))
							->then(function () use ($property, $deferred): void {
								$this->readState($property)
									->then(static function ($state) use ($deferred): void {
										$deferred->resolve($state);
									})
									->catch(static function (Throwable $ex) use ($deferred): void {
										$deferred->reject($ex);
									});
							})
							->catch(function (Throwable $ex) use ($deferred): void {
								if ($ex instanceof Exceptions\NotImplemented) {
									$this->logger->warning(
										'Channels states manager is not configured. State could not be fetched',
										[
											'source' => MetadataTypes\Sources\Module::DEVICES->value,
											'type' => 'async-channel-properties-states',
										],
									);
								}

								$deferred->reject($ex);
							});

						$this->logger->error(
							'Property stored expected value was not valid',
							[
								'source' => MetadataTypes\Sources\Module::DEVICES->value,
								'type' => 'async-channel-properties-states',
								'exception' => ApplicationHelpers\Logger::buildException($ex),
							],
						);
					}
				},
			)
			->catch(function (Throwable $ex) use ($deferred): void {
				if ($ex instanceof Exceptions\NotImplemented) {
					$this->logger->warning(
						'Channels states repository is not configured. State could not be fetched',
						[
							'source' => MetadataTypes\Sources\Module::DEVICES->value,
							'type' => 'async-channel-properties-states',
						],
					);
				}

				$deferred->reject($ex);
			});

		return $deferred->promise();
	}

	/**
	 * @return Promise\PromiseInterface<bool>
	 *
	 * @throws Exceptions\InvalidState
	 *
	 * @interal
	 */
	public function writeState(
		Documents\Channels\Properties\Dynamic|Documents\Channels\Properties\Mapped $property,
		Utils\ArrayHash $data,
		bool $forWriting,
		MetadataTypes\Sources\Source|null $source,
	): Promise\PromiseInterface
	{
		$mappedProperty = null;

		if ($property instanceof Documents\Channels\Properties\Mapped) {
			$parent = $this->channelPropertiesConfigurationRepository->find($property->getParent());

			if (!$parent instanceof Documents\Channels\Properties\Dynamic) {
				return Promise\reject(new Exceptions\InvalidState('Mapped property parent could not be loaded'));
			}

			$mappedProperty = $property;

			$property = $parent;
		}

		$deferred = new Promise\Deferred();

		$this->channelPropertyStateRepository->find($property->getId())
			->then(async(
				function (
					States\ChannelProperty|null $state,
				) use (
					$deferred,
					$data,
					$property,
					$mappedProperty,
					$forWriting,
					$source,
				): void {
					/**
					 * IMPORTANT: ACTUAL VALUE field is meant to be used only by connectors for saving device actual value
					 */
					if ($data->offsetExists(States\Property::ACTUAL_VALUE_FIELD)) {
						if ($mappedProperty !== null) {
							$deferred->reject(new Exceptions\InvalidArgument(
								'Setting property actual value is not allowed for mapped properties',
							));

							return;
						}

						if ($forWriting === true) {
							$deferred->reject(new Exceptions\InvalidArgument(
								'Setting property actual value could be done only by "setValue" method',
							));

							return;
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
									'type' => 'async-channel-properties-states',
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
									$deferred->reject(new Exceptions\InvalidArgument(
										'Property is not settable, expected value could not written',
									));

									return;
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
										'type' => 'async-channel-properties-states',
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
						$deferred->resolve(true);

						return;
					}

					try {
						if ($state === null) {
							$result = await($this->channelPropertiesStatesManager->create(
								$property,
								$data,
							));

						} else {
							$result = await($this->channelPropertiesStatesManager->update(
								$property,
								$state,
								$data,
							));

							if (is_bool($result)) {
								$deferred->resolve(false);

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
								'type' => 'async-channel-properties-states',
								'property' => [
									'id' => $property->getId()->toString(),
									'state' => $result->toArray(),
								],
							],
						);

						$deferred->resolve(true);
					} catch (Throwable $ex) {
						if ($ex instanceof Exceptions\NotImplemented) {
							$this->logger->warning(
								'Channels states manager is not configured. State could not be saved',
								[
									'source' => MetadataTypes\Sources\Module::DEVICES->value,
									'type' => 'async-channel-properties-states',
								],
							);
						}

						$deferred->reject($ex);
					}
				},
			))
			->catch(static function (Throwable $ex) use ($deferred): void {
				$deferred->reject($ex);
			});

		return $deferred->promise();
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
