<?php declare(strict_types = 1);

/**
 * DynamicProperties.php
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

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Events;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\States;
use FastyBird\Exchange\Entities as ExchangeEntities;
use FastyBird\Exchange\Publisher as ExchangePublisher;
use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Metadata\Types as MetadataTypes;
use Nette;
use Nette\Utils;
use Symfony\Component\EventDispatcher;

/**
 * Doctrine entities events
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Subscribers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class DynamicProperties implements EventDispatcher\EventSubscriberInterface
{

	use Nette\SmartObject;

	/** @var Models\DataStorage\ConnectorPropertiesRepository */
	private Models\DataStorage\ConnectorPropertiesRepository $connectorPropertiesRepository;

	/** @var Models\DataStorage\DevicePropertiesRepository */
	private Models\DataStorage\DevicePropertiesRepository $devicePropertiesRepository;

	/** @var Models\DataStorage\ChannelPropertiesRepository */
	private Models\DataStorage\ChannelPropertiesRepository $channelPropertiesRepository;

	/** @var ExchangeEntities\EntityFactory */
	private ExchangeEntities\EntityFactory $entityFactory;

	/** @var ExchangePublisher\IPublisher|null */
	private ?ExchangePublisher\IPublisher $publisher;

	/**
	 * @param Models\DataStorage\ConnectorPropertiesRepository $connectorPropertiesRepository
	 * @param Models\DataStorage\DevicePropertiesRepository $devicePropertiesRepository
	 * @param Models\DataStorage\ChannelPropertiesRepository $channelPropertiesRepository
	 * @param ExchangeEntities\EntityFactory $entityFactory
	 * @param ExchangePublisher\IPublisher|null $publisher
	 */
	public function __construct(
		Models\DataStorage\ConnectorPropertiesRepository $connectorPropertiesRepository,
		Models\DataStorage\DevicePropertiesRepository $devicePropertiesRepository,
		Models\DataStorage\ChannelPropertiesRepository $channelPropertiesRepository,
		ExchangeEntities\EntityFactory $entityFactory,
		?ExchangePublisher\IPublisher $publisher = null
	) {
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
			Events\StateEntityCreated::class => 'stateCreated',
			Events\StateEntityUpdated::class => 'stateUpdated',
			Events\StateEntityDeleted::class => 'stateDeleted',
		];
	}

	/**
	 * @param Events\StateEntityCreated $event
	 *
	 * @return void
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 * @throws Utils\JsonException
	 */
	public function stateCreated(Events\StateEntityCreated $event): void
	{
		$this->refreshRepository($event->getProperty());

		$this->publishEntity($event->getProperty(), $event->getState());

		$parent = $this->findParent($event->getProperty());

		if ($parent !== null) {
			$this->refreshRepository($parent);

			$this->publishEntity($parent, $event->getState());
		}
	}

	/**
	 * @param Events\StateEntityUpdated $event
	 *
	 * @return void
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 * @throws Utils\JsonException
	 */
	public function stateUpdated(Events\StateEntityUpdated $event): void
	{
		if ($event->getPreviousState()->toArray() !== $event->getState()->toArray()) {
			$this->refreshRepository($event->getProperty());
		}

		$this->publishEntity($event->getProperty(), $event->getState());

		$parent = $this->findParent($event->getProperty());

		if ($parent !== null) {
			if ($event->getPreviousState()->toArray() !== $event->getState()->toArray()) {
				$this->refreshRepository($parent);
			}

			$this->publishEntity($parent, $event->getState());
		}
	}

	/**
	 * @param Events\StateEntityDeleted $event
	 *
	 * @return void
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 * @throws Utils\JsonException
	 */
	public function stateDeleted(Events\StateEntityDeleted $event): void
	{
		$this->refreshRepository($event->getProperty());

		$this->publishEntity($event->getProperty());

		$parent = $this->findParent($event->getProperty());

		if ($parent !== null) {
			$this->refreshRepository($parent);

			$this->publishEntity($parent);
		}
	}

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity|Entities\Property $property
	 * @param States\ConnectorProperty|States\DeviceProperty|States\ChannelProperty|null $state
	 *
	 * @return void
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 * @throws Utils\JsonException
	 */
	private function publishEntity(
		MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity|Entities\Property $property,
		States\ConnectorProperty|States\ChannelProperty|States\DeviceProperty|null $state = null
	): void {
		if ($this->publisher === null) {
			return;
		}

		if (
			$property instanceof MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity
			|| $property instanceof MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity
			|| $property instanceof Entities\Connectors\Properties\Property
		) {
			$routingKey = MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_CONNECTOR_PROPERTY_ENTITY_REPORTED);

		} elseif (
			$property instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
			|| $property instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
			|| $property instanceof Entities\Devices\Properties\Property
		) {
			$routingKey = MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_DEVICE_PROPERTY_ENTITY_REPORTED);

		} elseif (
			$property instanceof MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity
			|| $property instanceof MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
			|| $property instanceof Entities\Channels\Properties\Property
		) {
			$routingKey = MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_CHANNEL_PROPERTY_ENTITY_REPORTED);

		} else {
			return;
		}

		$this->publisher->publish(
			MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
			$routingKey,
			$this->entityFactory->create(
				Utils\Json::encode(
					array_merge(
						$property->toArray(),
						$state ? $state->toArray() : []
					)
				),
				$routingKey
			)
		);
	}

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity|Entities\Property $property
	 *
	 * @return MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity|Entities\Property|null
	 */
	private function findParent(
		MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity|Entities\Property $property
	): MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity|Entities\Property|null {
		if (
			$property instanceof MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity
			|| $property instanceof MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity
			|| $property instanceof Entities\Connectors\Properties\Dynamic
		) {
			return null;
		} elseif (
			$property instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
			|| $property instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
		) {
			if ($property->getParent() !== null) {
				return $this->devicePropertiesRepository->findById($property->getParent());
			}
		} elseif (
			$property instanceof MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity
			|| $property instanceof MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
		) {
			if ($property->getParent() !== null) {
				return $this->channelPropertiesRepository->findById($property->getParent());
			}
		} elseif (
			$property instanceof Entities\Devices\Properties\Dynamic
			|| $property instanceof Entities\Devices\Properties\Mapped
		) {
			return $property->getParent();
		} elseif (
			$property instanceof Entities\Channels\Properties\Dynamic
			|| $property instanceof Entities\Channels\Properties\Mapped
		) {
			return $property->getParent();
		}

		return null;
	}

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity|Entities\Property $property
	 *
	 * @return void
	 */
	private function refreshRepository(
		MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity|Entities\Property $property
	): void {
		if (
			$property instanceof MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity
			|| $property instanceof MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity
			|| $property instanceof Entities\Connectors\Properties\Dynamic
		) {
			$this->connectorPropertiesRepository->reset($property->getId());
		} elseif (
			$property instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
			|| $property instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
			|| $property instanceof Entities\Devices\Properties\Dynamic
			|| $property instanceof Entities\Devices\Properties\Mapped
		) {
			$this->devicePropertiesRepository->reset($property->getId());
		} elseif (
			$property instanceof MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity
			|| $property instanceof MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
			|| $property instanceof Entities\Channels\Properties\Dynamic
			|| $property instanceof Entities\Channels\Properties\Mapped
		) {
			$this->channelPropertiesRepository->reset($property->getId());
		}
	}

}
