<?php declare(strict_types = 1);

/**
 * StateEntities.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Subscribers
 * @since          1.0.0
 *
 * @date           22.10.22
 */

namespace FastyBird\Module\Devices\Subscribers;

use Exception;
use FastyBird\Library\Exchange\Documents as ExchangeEntities;
use FastyBird\Library\Exchange\Publisher as ExchangePublisher;
use FastyBird\Library\Metadata\Documents as MetadataDocuments;
use FastyBird\Library\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use FastyBird\Module\Devices\Entities;
use FastyBird\Module\Devices\Events;
use FastyBird\Module\Devices\Exceptions;
use FastyBird\Module\Devices\Models;
use FastyBird\Module\Devices\Queries;
use FastyBird\Module\Devices\States;
use FastyBird\Module\Devices\Utilities;
use IPub\Phone\Exceptions as PhoneExceptions;
use Nette;
use Nette\Utils;
use Symfony\Component\EventDispatcher;
use function array_merge;

/**
 * Devices state entities events
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Subscribers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class StateEntities implements EventDispatcher\EventSubscriberInterface
{

	use Nette\SmartObject;

	public function __construct(
		private readonly Models\Entities\Devices\Properties\PropertiesRepository $devicePropertiesRepository,
		private readonly Models\Entities\Channels\Properties\PropertiesRepository $channelPropertiesRepository,
		private readonly Utilities\ConnectorPropertiesStates $connectorPropertiesStates,
		private readonly Utilities\DevicePropertiesStates $devicePropertiesStates,
		private readonly Utilities\ChannelPropertiesStates $channelPropertiesStates,
		private readonly ExchangeEntities\DocumentFactory $entityFactory,
		private readonly ExchangePublisher\Publisher $publisher,
	)
	{
	}

	public static function getSubscribedEvents(): array
	{
		return [
			Events\ConnectorPropertyStateEntityCreated::class => 'stateCreated',
			Events\ConnectorPropertyStateEntityUpdated::class => 'stateUpdated',
			Events\ConnectorPropertyStateEntityDeleted::class => 'stateDeleted',
			Events\DevicePropertyStateEntityCreated::class => 'stateCreated',
			Events\DevicePropertyStateEntityUpdated::class => 'stateUpdated',
			Events\DevicePropertyStateEntityDeleted::class => 'stateDeleted',
			Events\ChannelPropertyStateEntityCreated::class => 'stateCreated',
			Events\ChannelPropertyStateEntityUpdated::class => 'stateUpdated',
			Events\ChannelPropertyStateEntityDeleted::class => 'stateDeleted',
		];
	}

	/**
	 * @throws Exception
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\FileNotFound
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidData
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\Logic
	 * @throws MetadataExceptions\MalformedInput
	 * @throws Utils\JsonException
	 * @throws PhoneExceptions\NoValidCountryException
	 * @throws PhoneExceptions\NoValidPhoneException
	 */
	public function stateCreated(
		Events\ConnectorPropertyStateEntityCreated|Events\DevicePropertyStateEntityCreated|Events\ChannelPropertyStateEntityCreated $event,
	): void
	{
		$this->processEntity($event->getProperty());
	}

	/**
	 * @throws Exception
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\FileNotFound
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidData
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\Logic
	 * @throws MetadataExceptions\MalformedInput
	 * @throws Utils\JsonException
	 * @throws PhoneExceptions\NoValidCountryException
	 * @throws PhoneExceptions\NoValidPhoneException
	 */
	public function stateUpdated(
		Events\ConnectorPropertyStateEntityUpdated|Events\DevicePropertyStateEntityUpdated|Events\ChannelPropertyStateEntityUpdated $event,
	): void
	{
		$this->processEntity($event->getProperty());
	}

	/**
	 * @throws Exception
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\FileNotFound
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidData
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\Logic
	 * @throws MetadataExceptions\MalformedInput
	 * @throws Utils\JsonException
	 * @throws PhoneExceptions\NoValidCountryException
	 * @throws PhoneExceptions\NoValidPhoneException
	 */
	public function stateDeleted(
		Events\ConnectorPropertyStateEntityDeleted|Events\DevicePropertyStateEntityDeleted|Events\ChannelPropertyStateEntityDeleted $event,
	): void
	{
		$this->publishEntity($event->getProperty());

		foreach ($this->findChildren($event->getProperty()) as $child) {
			$this->publishEntity($child);
		}
	}

	/**
	 * @throws Exception
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidData
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\FileNotFound
	 * @throws MetadataExceptions\Logic
	 * @throws MetadataExceptions\MalformedInput
	 * @throws Utils\JsonException
	 * @throws PhoneExceptions\NoValidCountryException
	 * @throws PhoneExceptions\NoValidPhoneException
	 */
	private function processEntity(
		MetadataDocuments\DevicesModule\DynamicProperty|MetadataDocuments\DevicesModule\MappedProperty|Entities\Connectors\Properties\Dynamic|Entities\Devices\Properties\Dynamic|Entities\Channels\Properties\Dynamic|Entities\Devices\Properties\Mapped|Entities\Channels\Properties\Mapped $property,
	): void
	{
		$state = null;

		if (
			$property instanceof MetadataDocuments\DevicesModule\ConnectorDynamicProperty
			|| $property instanceof Entities\Connectors\Properties\Dynamic
		) {
			$state = $this->connectorPropertiesStates->readValue($property);

		} elseif (
			$property instanceof MetadataDocuments\DevicesModule\DeviceDynamicProperty
			|| $property instanceof MetadataDocuments\DevicesModule\DeviceMappedProperty
			|| $property instanceof Entities\Devices\Properties\Dynamic
			|| $property instanceof Entities\Devices\Properties\Mapped
		) {
			$state = $this->devicePropertiesStates->readValue($property);

		} elseif (
			$property instanceof MetadataDocuments\DevicesModule\ChannelDynamicProperty
			|| $property instanceof MetadataDocuments\DevicesModule\ChannelMappedProperty
			|| $property instanceof Entities\Channels\Properties\Dynamic
			|| $property instanceof Entities\Channels\Properties\Mapped
		) {
			$state = $this->channelPropertiesStates->readValue($property);
		}

		$this->publishEntity($property, $state);

		foreach ($this->findChildren($property) as $child) {
			$this->publishEntity($child, $state);
		}
	}

	/**
	 * @throws Exception
	 * @throws MetadataExceptions\FileNotFound
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidData
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\Logic
	 * @throws MetadataExceptions\MalformedInput
	 * @throws Utils\JsonException
	 * @throws PhoneExceptions\NoValidCountryException
	 * @throws PhoneExceptions\NoValidPhoneException
	 */
	private function publishEntity(
		MetadataDocuments\DevicesModule\DynamicProperty|MetadataDocuments\DevicesModule\VariableProperty|MetadataDocuments\DevicesModule\MappedProperty|Entities\Property $property,
		States\ConnectorProperty|States\ChannelProperty|States\DeviceProperty|null $state = null,
	): void
	{
		if (
			$property instanceof MetadataDocuments\DevicesModule\ConnectorDynamicProperty
			|| $property instanceof Entities\Connectors\Properties\Dynamic
		) {
			$routingKey = MetadataTypes\RoutingKey::get(
				MetadataTypes\RoutingKey::CONNECTOR_PROPERTY_DOCUMENT_REPORTED,
			);

		} elseif (
			$property instanceof MetadataDocuments\DevicesModule\DeviceDynamicProperty
			|| $property instanceof MetadataDocuments\DevicesModule\DeviceMappedProperty
			|| $property instanceof Entities\Devices\Properties\Dynamic
			|| $property instanceof Entities\Devices\Properties\Mapped
		) {
			$routingKey = MetadataTypes\RoutingKey::get(
				MetadataTypes\RoutingKey::DEVICE_PROPERTY_DOCUMENT_REPORTED,
			);

		} elseif (
			$property instanceof MetadataDocuments\DevicesModule\ChannelDynamicProperty
			|| $property instanceof MetadataDocuments\DevicesModule\ChannelMappedProperty
			|| $property instanceof Entities\Channels\Properties\Dynamic
			|| $property instanceof Entities\Channels\Properties\Mapped
		) {
			$routingKey = MetadataTypes\RoutingKey::get(
				MetadataTypes\RoutingKey::CHANNEL_PROPERTY_DOCUMENT_REPORTED,
			);

		} else {
			return;
		}

		$this->publisher->publish(
			MetadataTypes\ModuleSource::get(MetadataTypes\ModuleSource::SOURCE_MODULE_DEVICES),
			$routingKey,
			$this->entityFactory->create(
				Utils\Json::encode(
					array_merge(
						$state?->toArray() ?? [],
						$property->toArray(),
					),
				),
				$routingKey,
			),
		);
	}

	/**
	 * @return array<Entities\Devices\Properties\Property|Entities\Channels\Properties\Property>
	 *
	 * @throws Exceptions\InvalidState
	 */
	private function findChildren(
		MetadataDocuments\DevicesModule\DynamicProperty|MetadataDocuments\DevicesModule\MappedProperty|Entities\Connectors\Properties\Dynamic|Entities\Devices\Properties\Dynamic|Entities\Devices\Properties\Mapped|Entities\Channels\Properties\Dynamic|Entities\Channels\Properties\Mapped $property,
	): array
	{
		if (
			$property instanceof MetadataDocuments\DevicesModule\ConnectorDynamicProperty
			|| $property instanceof Entities\Connectors\Properties\Dynamic
		) {
			return [];
		} elseif (
			$property instanceof MetadataDocuments\DevicesModule\DeviceDynamicProperty
			|| $property instanceof MetadataDocuments\DevicesModule\DeviceMappedProperty
			|| $property instanceof Entities\Devices\Properties\Dynamic
			|| $property instanceof Entities\Devices\Properties\Mapped
		) {
			$findDevicePropertiesQuery = new Queries\Entities\FindDeviceMappedProperties();
			$findDevicePropertiesQuery->byParentId($property->getId());

			return $this->devicePropertiesRepository->findAllBy(
				$findDevicePropertiesQuery,
				Entities\Devices\Properties\Mapped::class,
			);
		} elseif (
			$property instanceof MetadataDocuments\DevicesModule\ChannelDynamicProperty
			|| $property instanceof MetadataDocuments\DevicesModule\ChannelMappedProperty
			|| $property instanceof Entities\Channels\Properties\Dynamic
			|| $property instanceof Entities\Channels\Properties\Mapped
		) {
			$findDevicePropertiesQuery = new Queries\Entities\FindChannelMappedProperties();
			$findDevicePropertiesQuery->byParentId($property->getId());

			return $this->channelPropertiesRepository->findAllBy(
				$findDevicePropertiesQuery,
				Entities\Channels\Properties\Mapped::class,
			);
		}

		return [];
	}

}
