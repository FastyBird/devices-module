<?php declare(strict_types = 1);

/**
 * DataExchangeConsumer.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Consumers
 * @since          0.72.0
 *
 * @date           10.07.22
 */

namespace FastyBird\DevicesModule\Consumers;

use FastyBird\DevicesModule;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Utilities;
use FastyBird\Exchange\Consumer as ExchangeConsumer;
use FastyBird\Exchange\Entities as ExchangeEntities;
use FastyBird\Exchange\Publisher as ExchangePublisher;
use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Types as MetadataTypes;
use Nette;
use Psr\EventDispatcher;
use Psr\Log;

/**
 * Exchange worker consumer for connectors
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Consumers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class DataExchangeConsumer implements ExchangeConsumer\IConsumer
{

	use Nette\SmartObject;

	private Models\DataStorage\IConnectorPropertiesRepository $connectorPropertiesRepository;

	private Models\DataStorage\IDevicePropertiesRepository $devicePropertiesRepository;

	private Models\DataStorage\IChannelPropertiesRepository $channelPropertiesRepository;

	private Models\States\ConnectorPropertiesManager $connectorPropertiesStatesManager;

	private Models\States\ConnectorPropertiesRepository $connectorPropertiesStatesRepository;

	private Models\States\DevicePropertiesManager $devicePropertiesStatesManager;

	private Models\States\DevicePropertiesRepository $devicePropertiesStatesRepository;

	private Models\States\ChannelPropertiesManager $channelPropertiesStatesManager;

	private Models\States\ChannelPropertiesRepository $channelPropertiesStatesRepository;

	private ExchangeEntities\EntityFactory $entityFactory;

	private ?ExchangePublisher\IPublisher $publisher;

	private ?EventDispatcher\EventDispatcherInterface $dispatcher;

	private Log\LoggerInterface $logger;

	public function __construct(
		Models\DataStorage\IConnectorPropertiesRepository $connectorPropertiesRepository,
		Models\DataStorage\IDevicePropertiesRepository $devicePropertiesRepository,
		Models\DataStorage\IChannelPropertiesRepository $channelPropertiesRepository,
		Models\States\ConnectorPropertiesManager $connectorPropertiesStatesManager,
		Models\States\ConnectorPropertiesRepository $connectorPropertiesStatesRepository,
		Models\States\DevicePropertiesManager $devicePropertiesStatesManager,
		Models\States\DevicePropertiesRepository $devicePropertiesStatesRepository,
		Models\States\ChannelPropertiesManager $channelPropertiesStatesManager,
		Models\States\ChannelPropertiesRepository $channelPropertiesStatesRepository,
		ExchangeEntities\EntityFactory $entityFactory,
		?ExchangePublisher\IPublisher $publisher,
		?EventDispatcher\EventDispatcherInterface $dispatcher,
		?Log\LoggerInterface $logger = null
	) {
		$this->connectorPropertiesRepository = $connectorPropertiesRepository;
		$this->devicePropertiesRepository = $devicePropertiesRepository;
		$this->channelPropertiesRepository = $channelPropertiesRepository;
		$this->connectorPropertiesStatesManager = $connectorPropertiesStatesManager;
		$this->connectorPropertiesStatesRepository = $connectorPropertiesStatesRepository;
		$this->devicePropertiesStatesManager = $devicePropertiesStatesManager;
		$this->devicePropertiesStatesRepository = $devicePropertiesStatesRepository;
		$this->channelPropertiesStatesManager = $channelPropertiesStatesManager;
		$this->channelPropertiesStatesRepository = $channelPropertiesStatesRepository;

		$this->entityFactory = $entityFactory;

		$this->publisher = $publisher;
		$this->dispatcher = $dispatcher;

		$this->logger = $logger ?? new Log\NullLogger();
	}

	public function consume(
		MetadataTypes\ModuleSourceType|MetadataTypes\PluginSourceType|MetadataTypes\ConnectorSourceType $source,
		MetadataTypes\RoutingKeyType $routingKey,
		?MetadataEntities\IEntity $entity
	): void {
		if ($entity !== null) {
			if (in_array($routingKey->getValue(), DevicesModule\Constants::PROPERTIES_ACTIONS_ROUTING_KEYS, true)) {
				if ($entity instanceof MetadataEntities\Actions\IActionConnectorPropertyEntity) {
					if ($entity->getAction()->equalsValue(MetadataTypes\PropertyActionType::ACTION_SET)) {
						$property = $this->connectorPropertiesRepository->findById($entity->getProperty());

						if (
							!$property instanceof MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity
							&& !$property instanceof MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity
						) {
							return;
						}

						$valueToWrite = $this->normalizeValue($property, $entity->getExpectedValue());

						$state = $this->connectorPropertiesStatesRepository->findOne($property);

						if ($state !== null) {
							$this->connectorPropertiesStatesManager->update($property, $state, Nette\Utils\ArrayHash::from([
								'expectedValue' => $valueToWrite,
								'pending'       => true,
							]));

						} else {
							$this->connectorPropertiesStatesManager->create($property, Nette\Utils\ArrayHash::from([
								'actualValue'   => null,
								'expectedValue' => $valueToWrite,
								'pending'       => true,
								'valid'         => false,
							]));
						}
					} elseif ($entity->getAction()->equalsValue(MetadataTypes\PropertyActionType::ACTION_GET)) {
						if ($this->publisher !== null) {
							$property = $this->connectorPropertiesRepository->findById($entity->getProperty());

							if ($property === null) {
								return;
							}

							$this->publisher->publish(
								MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
								MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_CONNECTOR_PROPERTY_ENTITY_REPORTED),
								$property
							);
						} else {
							$this->logger->warning('Exchange publisher is not configured', [
								'source' => 'devices-module',
								'type'   => 'exchange-consumer',
							]);
						}
					}
				} elseif ($entity instanceof MetadataEntities\Actions\IActionDevicePropertyEntity) {
					if ($entity->getAction()->equalsValue(MetadataTypes\PropertyActionType::ACTION_SET)) {
						$property = $this->devicePropertiesRepository->findById($entity->getProperty());

						if (
							!$property instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
							&& !$property instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
						) {
							return;
						}

						$valueToWrite = $this->normalizeValue($property, $entity->getExpectedValue());

						$state = $this->devicePropertiesStatesRepository->findOne($property);

						if ($state !== null) {
							$this->devicePropertiesStatesManager->update($property, $state, Nette\Utils\ArrayHash::from([
								'expectedValue' => $valueToWrite,
								'pending'       => true,
							]));

						} else {
							$this->devicePropertiesStatesManager->create($property, Nette\Utils\ArrayHash::from([
								'actualValue'   => null,
								'expectedValue' => $valueToWrite,
								'pending'       => true,
								'valid'         => false,
							]));
						}
					} elseif ($entity->getAction()->equalsValue(MetadataTypes\PropertyActionType::ACTION_GET)) {
						if ($this->publisher !== null) {
							$property = $this->devicePropertiesRepository->findById($entity->getProperty());

							if ($property === null) {
								return;
							}

							$this->publisher->publish(
								MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
								MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_DEVICE_PROPERTY_ENTITY_REPORTED),
								$property
							);
						} else {
							$this->logger->warning('Exchange publisher is not configured', [
								'source' => 'devices-module',
								'type'   => 'exchange-consumer',
							]);
						}
					}
				} elseif ($entity instanceof MetadataEntities\Actions\IActionChannelPropertyEntity) {
					if ($entity->getAction()->equalsValue(MetadataTypes\PropertyActionType::ACTION_SET)) {
						$property = $this->channelPropertiesRepository->findById($entity->getProperty());

						if (
							!$property instanceof MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity
							&& !$property instanceof MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
						) {
							return;
						}

						$valueToWrite = $this->normalizeValue($property, $entity->getExpectedValue());

						$state = $this->channelPropertiesStatesRepository->findOne($property);

						if ($state !== null) {
							$this->channelPropertiesStatesManager->update($property, $state, Nette\Utils\ArrayHash::from([
								'expectedValue' => $valueToWrite,
								'pending'       => true,
							]));

						} else {
							$this->channelPropertiesStatesManager->create($property, Nette\Utils\ArrayHash::from([
								'actualValue'   => null,
								'expectedValue' => $valueToWrite,
								'pending'       => true,
								'valid'         => false,
							]));
						}
					} elseif ($entity->getAction()->equalsValue(MetadataTypes\PropertyActionType::ACTION_GET)) {
						if ($this->publisher !== null) {
							$property = $this->channelPropertiesRepository->findById($entity->getProperty());

							if ($property === null) {
								return;
							}

							$this->publisher->publish(
								MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
								MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_CHANNEL_PROPERTY_ENTITY_REPORTED),
								$property
							);
						} else {
							$this->logger->warning('Exchange publisher is not configured', [
								'source' => 'devices-module',
								'type'   => 'exchange-consumer',
							]);
						}
					}
				}
			}
		} else {
			$this->logger->warning('Received data message without data', [
				'source' => 'devices-module',
				'type'   => 'exchange-consumer',
			]);
		}
	}

	private function normalizeValue(
		MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity $property,
		float|bool|int|string|null $expectedValue
	): float|bool|int|string|null {
		$valueToWrite = Utilities\ValueHelper::normalizeValue(
			$property->getDataType(),
			$expectedValue,
			$property->getFormat(),
			$property->getInvalid()
		);

		if (
			$valueToWrite instanceof MetadataTypes\SwitchPayloadType
			&& $property->getDataType()->equalsValue(MetadataTypes\DataTypeType::DATA_TYPE_SWITCH)
			&& $valueToWrite->equalsValue(MetadataTypes\SwitchPayloadType::PAYLOAD_TOGGLE)
		) {
			if ($property->getActualValue() === MetadataTypes\SwitchPayloadType::PAYLOAD_ON) {
				$valueToWrite = MetadataTypes\SwitchPayloadType::get(MetadataTypes\SwitchPayloadType::PAYLOAD_OFF);

			} else {
				$valueToWrite = MetadataTypes\SwitchPayloadType::get(MetadataTypes\SwitchPayloadType::PAYLOAD_ON);
			}
		}

		return Utilities\ValueHelper::flattenValue($valueToWrite);
	}

}
