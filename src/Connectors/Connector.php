<?php declare(strict_types = 1);

/**
 * Connector.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Connectors
 * @since          0.60.0
 *
 * @date           31.05.22
 */

namespace FastyBird\DevicesModule\Connectors;

use FastyBird\DevicesModule;
use FastyBird\DevicesModule\Connectors;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Types\ConnectionStateType;
use FastyBird\Metadata\Types\ConnectorPropertyNameType;
use FastyBird\Metadata\Types\DataTypeType;
use Nette;
use Nette\Utils;
use Psr\Log;
use React\EventLoop;
use SplObjectStorage;
use SplQueue;
use Throwable;

/**
 * Devices connector
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Connectors
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Connector
{

	use Nette\SmartObject;

	/** @var bool */
	private bool $stopped = true;

	/** @var IConnector|null */
	private ?IConnector $service = null;

	/** @var MetadataEntities\Modules\DevicesModule\IConnectorEntity|null */
	private ?MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector = null;

	/** @var SplObjectStorage<IConnector, null> */
	private SplObjectStorage $connectors;

	/** @var SplQueue<Connectors\Messages\IMessage> */
	private SplQueue $queue;

	/** @var Models\Connectors\Properties\IPropertiesRepository */
	private Models\Connectors\Properties\IPropertiesRepository $connectorPropertiesRepository;

	/** @var Models\Connectors\Properties\IPropertiesManager */
	private Models\Connectors\Properties\IPropertiesManager $connectorPropertiesManager;

	/** @var Models\States\ConnectorPropertiesRepository */
	private Models\States\ConnectorPropertiesRepository $connectorPropertiesStateRepository;

	/** @var Models\States\ConnectorPropertiesManager */
	private Models\States\ConnectorPropertiesManager $connectorPropertiesStateManager;

	/** @var EventLoop\LoopInterface */
	private EventLoop\LoopInterface $eventLoop;

	/** @var Log\LoggerInterface */
	private Log\LoggerInterface $logger;

	public function __construct(
		Models\Connectors\Properties\IPropertiesRepository $connectorPropertiesRepository,
		Models\Connectors\Properties\IPropertiesManager $connectorPropertiesManager,
		Models\States\ConnectorPropertiesRepository $connectorPropertiesStateRepository,
		Models\States\ConnectorPropertiesManager $connectorPropertiesStateManager,
		EventLoop\LoopInterface $eventLoop,
		?Log\LoggerInterface $logger = null
	) {
		$this->connectorPropertiesRepository = $connectorPropertiesRepository;
		$this->connectorPropertiesManager = $connectorPropertiesManager;
		$this->connectorPropertiesStateRepository = $connectorPropertiesStateRepository;
		$this->connectorPropertiesStateManager = $connectorPropertiesStateManager;

		$this->eventLoop = $eventLoop;

		$this->logger = $logger ?? new Log\NullLogger();

		$this->connectors = new SplObjectStorage();
		$this->queue = new SplQueue();
	}

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector
	 *
	 * @return void
	 *
	 * @throws Exceptions\TerminateException
	 */
	public function execute(MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector): void
	{
		$this->connectors->rewind();

		$this->connector = $connector;

		foreach ($this->connectors as $service) {
			if ($connector->getType() === $service->getType()) {
				$this->logger->debug('Preparing connector to start', [
					'source'    => 'devices-module',
					'type'      => 'connector',
				]);

				try {
					$this->service = $service;
					$this->service->initialize($connector);

					$this->service->execute();

					$this->stopped = false;

					$this->setConnectorState(ConnectionStateType::get(ConnectionStateType::STATE_RUNNING));

					$this->eventLoop->addPeriodicTimer(0.01, function (): void {
						$this->processQueue();
					});
				} catch (Throwable $ex) {
					throw new Exceptions\TerminateException('Connector can\'t be started');
				}

				return;
			}
		}

		throw new Exceptions\InvalidArgumentException(sprintf('Connector %s is not registered', $connector->getId()->toString()));
	}

	/**
	 * @return void
	 */
	public function terminate(): void
	{
		$this->stopped = true;

		$this->logger->debug('Stopping connector...', [
			'source'    => 'devices-module',
			'type'      => 'connector',
		]);

		if ($this->service !== null) {
			$this->service->terminate();
		}

		$this->setConnectorState(ConnectionStateType::get(ConnectionStateType::STATE_STOPPED));
	}

	/**
	 * @param IConnector $connector
	 *
	 * @return void
	 */
	public function registerConnector(IConnector $connector): void
	{
		if ($this->connectors->contains($connector) === false) {
			$this->connectors->attach($connector);
		}
	}

	/**
	 * @param Messages\IMessage $message
	 *
	 * @return void
	 */
	public function handleMessage(Connectors\Messages\IMessage $message): void
	{
		$this->queue->enqueue($message);
	}

	/**
	 * @param ConnectionStateType $state
	 *
	 * @return void
	 */
	private function setConnectorState(ConnectionStateType $state): void
	{
		if ($this->connector === null) {
			return;
		}

		$findProperty = new Queries\FindConnectorPropertiesQuery();
		$findProperty->byConnectorId($this->connector->getId());
		$findProperty->byIdentifier(ConnectorPropertyNameType::NAME_STATE);

		$property = $this->connectorPropertiesRepository->findOneBy($findProperty);

		if ($property === null) {
			$property = $this->connectorPropertiesManager->create(Utils\ArrayHash::from([
				'connector'  => $this->connector,
				'entity'     => Entities\Connectors\Properties\DynamicProperty::class,
				'identifier' => ConnectorPropertyNameType::NAME_STATE,
				'data_type'  => DataTypeType::get(DataTypeType::DATA_TYPE_ENUM),
				'unit'       => null,
				'format'     => [
					ConnectionStateType::STATE_RUNNING,
					ConnectionStateType::STATE_STOPPED,
					ConnectionStateType::STATE_UNKNOWN,
					ConnectionStateType::STATE_SLEEPING,
					ConnectionStateType::STATE_ALERT,
				],
				'settable'   => false,
				'queryable'  => false,
			]));
		}

		$propertyState = $this->connectorPropertiesStateRepository->findOne($property);

		if ($propertyState === null) {
			$this->connectorPropertiesStateManager->create($property, Utils\ArrayHash::from([
				'actualValue'   => $state->getValue(),
				'expectedValue' => null,
				'pending'       => false,
			]));

		} else {
			$this->connectorPropertiesStateManager->update($property, $propertyState, Utils\ArrayHash::from([
				'actualValue'   => $state->getValue(),
				'expectedValue' => null,
				'pending'       => false,
			]));
		}
	}

	/**
	 * @return void
	 */
	private function processQueue(): void
	{
		if ($this->queue->isEmpty()) {
			return;
		}

		$message = $this->queue->dequeue();

		if ($message instanceof Connectors\Messages\ExchangeMessage) {
			try {
				if (in_array($message->getRoutingKey()
					->getValue(), DevicesModule\Constants::PROPERTIES_ACTIONS_ROUTING_KEYS, true)) {
					$this->handlePropertyCommand($message->getEntity());

				} elseif (in_array($message->getRoutingKey()
					->getValue(), DevicesModule\Constants::CONTROLS_ACTIONS_ROUTING_KEYS, true)) {
					$this->handleControlCommand($message->getEntity());

				} elseif (Utils\Strings::startsWith($message->getRoutingKey()
					->getValue(), DevicesModule\Constants::ENTITY_PREFIX_KEY)) {
					if (Utils\Strings::contains($message->getRoutingKey()
						->getValue(), DevicesModule\Constants::ENTITY_REPORTED_KEY)) {
						$this->handleEntityReported($message->getEntity());

					} elseif (Utils\Strings::contains($message->getRoutingKey()
						->getValue(), DevicesModule\Constants::ENTITY_CREATED_KEY)) {
						$this->handleEntityCreated($message->getEntity());

					} elseif (Utils\Strings::contains($message->getRoutingKey()
						->getValue(), DevicesModule\Constants::ENTITY_UPDATED_KEY)) {
						$this->handleEntityUpdated($message->getEntity());

					} elseif (Utils\Strings::contains($message->getRoutingKey()
						->getValue(), DevicesModule\Constants::ENTITY_DELETED_KEY)) {
						$this->handleEntityDeleted($message->getEntity());
					}
				} else {
					$this->logger->debug('Received unknown exchange message', [
						'source' => 'devices-module',
						'type'   => 'connector',
					]);
				}
			} catch (Throwable $ex) {
				$this->logger->error('An unexpected error occurred during processing queue item', [
					'source'    => 'devices-module',
					'type'      => 'connector',
					'exception' => [
						'message' => $ex->getMessage(),
						'code'    => $ex->getCode(),
					],
				]);
			}
		}
	}

	/**
	 * @param MetadataEntities\IEntity $entity
	 *
	 * @return void
	 */
	private function handlePropertyCommand(
		MetadataEntities\IEntity $entity
	): void {
		if ($this->service === null) {
			return;
		}

		if (
			// Connector
			$entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity
			// Device
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
			// Channel
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
		) {
			$this->service->writeProperty($entity);
		}
	}

	/**
	 * @param MetadataEntities\IEntity $entity
	 *
	 * @return void
	 */
	private function handleControlCommand(
		MetadataEntities\IEntity $entity
	): void {
		if ($this->service === null) {
			return;
		}

		if (
			$entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorControlEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceControlEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelControlEntity
		) {
			$this->service->writeControl($entity);
		}
	}

	/**
	 * @param MetadataEntities\IEntity $entity
	 *
	 * @return void
	 */
	private function handleEntityReported(MetadataEntities\IEntity $entity): void
	{
		if ($this->service === null) {
			return;
		}

		if ($entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceEntity) {
			$this->service->notifyDevice($entity);

		} elseif ($entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity) {
			$this->service->notifyDeviceAttribute($entity);

		} elseif (
			$entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
		) {
			$this->service->notifyDeviceProperty($entity);

		} elseif ($entity instanceof MetadataEntities\Modules\DevicesModule\IChannelEntity) {
			$this->service->notifyChannel($entity);

		} elseif (
			$entity instanceof MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
		) {
			$this->service->notifyChannelProperty($entity);
		}
	}

	/**
	 * @param MetadataEntities\IEntity $entity
	 *
	 * @return void
	 */
	private function handleEntityCreated(MetadataEntities\IEntity $entity): void
	{
		if ($this->service === null) {
			return;
		}

		if ($entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceEntity) {
			$this->service->initializeDevice($entity);

		} elseif ($entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity) {
			$this->service->initializeDeviceAttribute($entity);

		} elseif (
			$entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
		) {
			$this->service->initializeDeviceProperty($entity);

		} elseif ($entity instanceof MetadataEntities\Modules\DevicesModule\IChannelEntity) {
			$this->service->initializeChannel($entity);

		} elseif (
			$entity instanceof MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
		) {
			$this->service->initializeChannelProperty($entity);
		}
	}

	/**
	 * @param MetadataEntities\IEntity $entity
	 *
	 * @return void
	 */
	private function handleEntityUpdated(MetadataEntities\IEntity $entity): void
	{
		if ($this->service === null) {
			return;
		}

		if ($entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceEntity) {
			$this->service->initializeDevice($entity);

		} elseif ($entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity) {
			$this->service->initializeDeviceAttribute($entity);

		} elseif (
			$entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
		) {
			$this->service->initializeDeviceProperty($entity);

		} elseif ($entity instanceof MetadataEntities\Modules\DevicesModule\IChannelEntity) {
			$this->service->initializeChannel($entity);

		} elseif (
			$entity instanceof MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
		) {
			$this->service->initializeChannelProperty($entity);
		}
	}

	/**
	 * @param MetadataEntities\IEntity $entity
	 *
	 * @return void
	 */
	private function handleEntityDeleted(MetadataEntities\IEntity $entity): void
	{
		if ($this->service === null) {
			return;
		}

		if ($entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceEntity) {
			$this->service->removeDevice($entity);

		} elseif ($entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity) {
			$this->service->removeDeviceAttribute($entity);

		} elseif (
			$entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
		) {
			$this->service->removeDeviceProperty($entity);

		} elseif ($entity instanceof MetadataEntities\Modules\DevicesModule\IChannelEntity) {
			$this->service->removeChannel($entity);

		} elseif (
			$entity instanceof MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
		) {
			$this->service->removeChannelProperty($entity);
		}
	}

}
