<?php declare(strict_types = 1);

/**
 * StatesSubscriber.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Subscribers
 * @since          0.65.0
 *
 * @date           25.06.22
 */

namespace FastyBird\DevicesModule\Subscribers;

use FastyBird\DevicesModule\DataStorage;
use FastyBird\DevicesModule\Events;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\States;
use FastyBird\DevicesModule\Utilities;
use FastyBird\Exchange\Entities as ExchangeEntities;
use FastyBird\Exchange\Publisher as ExchangePublisher;
use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Metadata\Types as MetadataTypes;
use League\Flysystem;
use Nette;
use Nette\Utils;
use Ramsey\Uuid;
use Symfony\Component\EventDispatcher;

/**
 * Doctrine entities events
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Subscribers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class StatesSubscriber implements EventDispatcher\EventSubscriberInterface
{

	use Nette\SmartObject;

	/** @var DataStorage\Reader */
	private DataStorage\Reader $reader;

	/** @var Models\DataStorage\IConnectorPropertiesRepository */
	private Models\DataStorage\IConnectorPropertiesRepository $connectorPropertiesRepository;

	/** @var Models\DataStorage\IDevicePropertiesRepository */
	private Models\DataStorage\IDevicePropertiesRepository $devicePropertiesRepository;

	/** @var Models\DataStorage\IChannelPropertiesRepository */
	private Models\DataStorage\IChannelPropertiesRepository $channelPropertiesRepository;

	/** @var ExchangeEntities\EntityFactory */
	private ExchangeEntities\EntityFactory $entityFactory;

	/** @var ExchangePublisher\IPublisher|null */
	private ?ExchangePublisher\IPublisher $publisher;

	public function __construct(
		DataStorage\Reader $reader,
		Models\DataStorage\IConnectorPropertiesRepository $connectorPropertiesRepository,
		Models\DataStorage\IDevicePropertiesRepository $devicePropertiesRepository,
		Models\DataStorage\IChannelPropertiesRepository $channelPropertiesRepository,
		ExchangeEntities\EntityFactory $entityFactory,
		?ExchangePublisher\IPublisher $publisher = null
	) {
		$this->reader = $reader;
		$this->connectorPropertiesRepository = $connectorPropertiesRepository;
		$this->devicePropertiesRepository = $devicePropertiesRepository;
		$this->channelPropertiesRepository = $channelPropertiesRepository;

		$this->entityFactory = $entityFactory;

		$this->publisher = $publisher;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			Events\StateEntityCreatedEvent::class  => 'stateCreated',
			Events\StateEntityUpdatedEvent::class  => 'stateUpdated',
			Events\StateEntityDeletedEvent::class  => 'stateDeleted',
		];
	}

	/**
	 * @param Events\StateEntityCreatedEvent $event
	 *
	 * @return void
	 *
	 * @throws Flysystem\FilesystemException
	 * @throws MetadataExceptions\FileNotFoundException
	 * @throws Utils\JsonException
	 */
	public function stateCreated(Events\StateEntityCreatedEvent $event): void
	{
		$this->reader->read();

		$property = $this->findProperty($event->getState()->getId(), $event->getState());

		if ($property !== null) {
			$this->publishEntity($property, $event->getState());

			if (method_exists($property, 'getParent') && $property->getParent() !== null) {
				$parent = $this->findProperty($property->getParent(), $event->getState());

				if ($parent !== null) {
					$this->publishEntity($parent, $event->getState());
				}
			}
		}
	}

	/**
	 * @param Events\StateEntityUpdatedEvent $event
	 *
	 * @return void
	 *
	 * @throws Flysystem\FilesystemException
	 * @throws MetadataExceptions\FileNotFoundException
	 * @throws Utils\JsonException
	 */
	public function stateUpdated(Events\StateEntityUpdatedEvent $event): void
	{
		$this->reader->read();

		$property = $this->findProperty($event->getState()->getId(), $event->getState());

		if ($property !== null) {
			$this->publishEntity($property, $event->getState());

			if (method_exists($property, 'getParent') && $property->getParent() !== null) {
				$parent = $this->findProperty($property->getParent(), $event->getState());

				if ($parent !== null) {
					$this->publishEntity($parent, $event->getState());
				}
			}
		}
	}

	/**
	 * @param Events\StateEntityDeletedEvent $event
	 *
	 * @return void
	 *
	 * @throws Flysystem\FilesystemException
	 * @throws MetadataExceptions\FileNotFoundException
	 * @throws Utils\JsonException
	 */
	public function stateDeleted(Events\StateEntityDeletedEvent $event): void
	{
		$this->reader->read();

		$property = $this->connectorPropertiesRepository->findById($event->getId());

		if ($property === null) {
			$property = $this->devicePropertiesRepository->findById($event->getId());

			if ($property === null) {
				$property = $this->channelPropertiesRepository->findById($event->getId());
			}
		}

		if ($property !== null) {
			$this->publishEntity($property);

			if (method_exists($property, 'getParent') && $property->getParent() !== null) {
				$parent = $this->connectorPropertiesRepository->findById($property->getParent());

				if ($parent === null) {
					$parent = $this->devicePropertiesRepository->findById($property->getParent());

					if ($parent === null) {
						$parent = $this->channelPropertiesRepository->findById($property->getParent());
					}
				}

				if ($parent !== null) {
					$this->publishEntity($parent);
				}
			}
		}
	}

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity $property
	 * @param States\IConnectorProperty|States\IDeviceProperty|States\IChannelProperty|null $state
	 *
	 * @return void
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 * @throws Utils\JsonException
	 */
	private function publishEntity(
		$property,
		$state = null
	): void {
		if ($this->publisher === null) {
			return;
		}

		if (
			$property instanceof MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity
			|| $property instanceof MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity
		) {
			$routingKey = MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_CONNECTOR_PROPERTY_ENTITY_REPORTED);

		} elseif (
			$property instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
			|| $property instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
		) {
			$routingKey = MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_DEVICE_PROPERTY_ENTITY_REPORTED);

		} elseif (
			$property instanceof MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity
			|| $property instanceof MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
		) {
			$routingKey = MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_CHANNEL_PROPERTY_ENTITY_REPORTED);

		} else {
			return;
		}

		$actualValue = $state === null ? null : Utilities\ValueHelper::normalizeValue($property->getDataType(), $state->getActualValue(), $property->getFormat(), $property->getInvalid());
		$expectedValue = $state === null ? null : Utilities\ValueHelper::normalizeValue($property->getDataType(), $state->getExpectedValue(), $property->getFormat(), $property->getInvalid());

		$this->publisher->publish(
			MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
			$routingKey,
			$this->entityFactory->create(Utils\Json::encode(array_merge($property->toArray(), [
				'actualValue'   => Utilities\ValueHelper::flattenValue($actualValue),
				'expectedValue' => Utilities\ValueHelper::flattenValue($expectedValue),
				'pending'       => !($state === null) && $state->isPending(),
				'valid'         => !($state === null) && $state->isValid(),
			])), $routingKey)
		);
	}

	/**
	 * @param Uuid\UuidInterface $id
	 * @param States\IConnectorProperty|States\IDeviceProperty|States\IChannelProperty $state
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity|null
	 */
	private function findProperty(Uuid\UuidInterface $id, $state)
	{
		if ($state instanceof States\IConnectorProperty) {
			return $this->connectorPropertiesRepository->findById($id);

		} elseif ($state instanceof States\IDeviceProperty) {
			return $this->devicePropertiesRepository->findById($id);

		} elseif ($state instanceof States\IChannelProperty) {
			return $this->channelPropertiesRepository->findById($id);

		} else {
			return null;
		}
	}

}
