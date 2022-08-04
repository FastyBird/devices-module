<?php declare(strict_types = 1);

/**
 * EntitiesSubscriber.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Subscribers
 * @since          0.1.0
 *
 * @date           22.03.20
 */

namespace FastyBird\DevicesModule\Subscribers;

use Doctrine\Common;
use Doctrine\ORM;
use FastyBird\DevicesModule;
use FastyBird\DevicesModule\DataStorage;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\Exchange\Entities as ExchangeEntities;
use FastyBird\Exchange\Publisher as ExchangePublisher;
use FastyBird\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Metadata\Types as MetadataTypes;
use League\Flysystem;
use Nette;
use Nette\Utils;
use ReflectionClass;

/**
 * Doctrine entities events
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Subscribers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class EntitiesSubscriber implements Common\EventSubscriber
{

	private const ACTION_CREATED = 'created';
	private const ACTION_UPDATED = 'updated';
	private const ACTION_DELETED = 'deleted';

	use Nette\SmartObject;

	/** @var Models\States\DevicePropertiesRepository */
	private Models\States\DevicePropertiesRepository $devicePropertiesStatesRepository;

	/** @var Models\States\DevicePropertiesManager */
	private Models\States\DevicePropertiesManager $devicePropertiesStatesManager;

	/** @var Models\States\ChannelPropertiesRepository */
	private Models\States\ChannelPropertiesRepository $channelPropertiesStatesRepository;

	/** @var Models\States\ChannelPropertiesManager */
	private Models\States\ChannelPropertiesManager $channelPropertiesStatesManager;

	/** @var Models\States\ConnectorPropertiesRepository */
	private Models\States\ConnectorPropertiesRepository $connectorPropertiesStatesRepository;

	/** @var Models\States\ConnectorPropertiesManager */
	private Models\States\ConnectorPropertiesManager $connectorPropertiesStatesManager;

	/** @var DataStorage\Writer */
	private DataStorage\Writer $configurationDataWriter;

	/** @var ExchangeEntities\EntityFactory */
	private ExchangeEntities\EntityFactory $entityFactory;

	/** @var ExchangePublisher\Publisher|null */
	private ?ExchangePublisher\Publisher $publisher;

	/** @var ORM\EntityManagerInterface */
	private ORM\EntityManagerInterface $entityManager;

	/**
	 * @param ORM\EntityManagerInterface $entityManager
	 * @param Models\States\DevicePropertiesRepository $devicePropertiesStatesRepository
	 * @param Models\States\DevicePropertiesManager $devicePropertiesStatesManager
	 * @param Models\States\ChannelPropertiesRepository $channelPropertiesStatesRepository
	 * @param Models\States\ChannelPropertiesManager $channelPropertiesStatesManager
	 * @param Models\States\ConnectorPropertiesRepository $connectorPropertiesStatesRepository
	 * @param Models\States\ConnectorPropertiesManager $connectorPropertiesStatesManager
	 * @param DataStorage\Writer $configurationDataWriter
	 * @param ExchangeEntities\EntityFactory $entityFactory
	 * @param ExchangePublisher\Publisher|null $publisher
	 */
	public function __construct(
		ORM\EntityManagerInterface $entityManager,
		Models\States\DevicePropertiesRepository $devicePropertiesStatesRepository,
		Models\States\DevicePropertiesManager $devicePropertiesStatesManager,
		Models\States\ChannelPropertiesRepository $channelPropertiesStatesRepository,
		Models\States\ChannelPropertiesManager $channelPropertiesStatesManager,
		Models\States\ConnectorPropertiesRepository $connectorPropertiesStatesRepository,
		Models\States\ConnectorPropertiesManager $connectorPropertiesStatesManager,
		DataStorage\Writer $configurationDataWriter,
		ExchangeEntities\EntityFactory $entityFactory,
		?ExchangePublisher\Publisher $publisher = null
	) {
		$this->devicePropertiesStatesRepository = $devicePropertiesStatesRepository;
		$this->devicePropertiesStatesManager = $devicePropertiesStatesManager;
		$this->channelPropertiesStatesRepository = $channelPropertiesStatesRepository;
		$this->channelPropertiesStatesManager = $channelPropertiesStatesManager;
		$this->connectorPropertiesStatesRepository = $connectorPropertiesStatesRepository;
		$this->connectorPropertiesStatesManager = $connectorPropertiesStatesManager;

		$this->configurationDataWriter = $configurationDataWriter;

		$this->entityFactory = $entityFactory;
		$this->publisher = $publisher;

		$this->entityManager = $entityManager;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSubscribedEvents(): array
	{
		return [
			ORM\Events::postPersist,
			ORM\Events::postUpdate,
			ORM\Events::postRemove,
		];
	}

	/**
	 * @param ORM\Event\LifecycleEventArgs $eventArgs
	 *
	 * @return void
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 * @throws Utils\JsonException
	 * @throws Flysystem\FilesystemException
	 */
	public function postPersist(ORM\Event\LifecycleEventArgs $eventArgs): void
	{
		// onFlush was executed before, everything already initialized
		$entity = $eventArgs->getObject();

		// Check for valid entity
		if (!$entity instanceof Entities\IEntity || !$this->validateNamespace($entity)) {
			return;
		}

		$this->publishEntity($entity, self::ACTION_CREATED);

		$this->configurationDataWriter->write();
	}

	/**
	 * @param ORM\Event\LifecycleEventArgs $eventArgs
	 *
	 * @return void
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 * @throws Utils\JsonException
	 * @throws Flysystem\FilesystemException
	 */
	public function postUpdate(ORM\Event\LifecycleEventArgs $eventArgs): void
	{
		$uow = $this->entityManager->getUnitOfWork();

		// onFlush was executed before, everything already initialized
		$entity = $eventArgs->getObject();

		// Get changes => should be already computed here (is a listener)
		$changeSet = $uow->getEntityChangeSet($entity);

		// If we have no changes left => don't create revision log
		if (count($changeSet) === 0) {
			return;
		}

		// Check for valid entity
		if (
			!$entity instanceof Entities\IEntity
			|| !$this->validateNamespace($entity)
			|| $uow->isScheduledForDelete($entity)
		) {
			return;
		}

		$this->publishEntity($entity, self::ACTION_UPDATED);

		$this->configurationDataWriter->write();
	}

	/**
	 * @param ORM\Event\LifecycleEventArgs $eventArgs
	 *
	 * @return void
	 *
	 * @throws Flysystem\FilesystemException
	 * @throws MetadataExceptions\FileNotFoundException
	 * @throws Utils\JsonException
	 */
	public function postRemove(ORM\Event\LifecycleEventArgs $eventArgs): void
	{
		// onFlush was executed before, everything already initialized
		$entity = $eventArgs->getObject();
		var_dump('TEST 2');
var_dump(get_class($entity));
		// Check for valid entity
		if (!$entity instanceof Entities\IEntity || !$this->validateNamespace($entity)) {
			return;
		}
var_dump('TEST');
		$this->publishEntity($entity, self::ACTION_DELETED);

		// Property states cleanup
		if ($entity instanceof DevicesModule\Entities\Connectors\Properties\IDynamicProperty) {
			try {
				$state = $this->connectorPropertiesStatesRepository->findOne($entity);

				if ($state !== null) {
					$this->connectorPropertiesStatesManager->delete($entity, $state);
				}
			} catch (Exceptions\NotImplementedException) {
				return;
			}
		} elseif ($entity instanceof DevicesModule\Entities\Devices\Properties\IDynamicProperty) {
			try {
				$state = $this->devicePropertiesStatesRepository->findOne($entity);

				if ($state !== null) {
					$this->devicePropertiesStatesManager->delete($entity, $state);
				}
			} catch (Exceptions\NotImplementedException) {
				return;
			}
		} elseif ($entity instanceof DevicesModule\Entities\Channels\Properties\IDynamicProperty) {
			try {
				$state = $this->channelPropertiesStatesRepository->findOne($entity);

				if ($state !== null) {
					$this->channelPropertiesStatesManager->delete($entity, $state);
				}
			} catch (Exceptions\NotImplementedException) {
				return;
			}
		}

		$this->configurationDataWriter->write();
	}

	/**
	 * @param Entities\IEntity $entity
	 * @param string $action
	 *
	 * @return void
	 *
	 * @throws Utils\JsonException
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	private function publishEntity(Entities\IEntity $entity, string $action): void
	{
		if ($this->publisher === null) {
			return;
		}

		$publishRoutingKey = null;

		switch ($action) {
			case self::ACTION_CREATED:
				foreach (DevicesModule\Constants::MESSAGE_BUS_CREATED_ENTITIES_ROUTING_KEYS_MAPPING as $class => $routingKey) {
					if ($this->validateEntity($entity, $class)) {
						$publishRoutingKey = MetadataTypes\RoutingKeyType::get($routingKey);
					}
				}

				break;

			case self::ACTION_UPDATED:
				foreach (DevicesModule\Constants::MESSAGE_BUS_UPDATED_ENTITIES_ROUTING_KEYS_MAPPING as $class => $routingKey) {
					if ($this->validateEntity($entity, $class)) {
						$publishRoutingKey = MetadataTypes\RoutingKeyType::get($routingKey);
					}
				}

				break;

			case self::ACTION_DELETED:
				foreach (DevicesModule\Constants::MESSAGE_BUS_DELETED_ENTITIES_ROUTING_KEYS_MAPPING as $class => $routingKey) {
					if ($this->validateEntity($entity, $class)) {
						$publishRoutingKey = MetadataTypes\RoutingKeyType::get($routingKey);
					}
				}

				break;
		}

		if ($publishRoutingKey !== null) {
			if ($entity instanceof Entities\Devices\Properties\IDynamicProperty) {
				try {
					$state = $this->devicePropertiesStatesRepository->findOne($entity);

					$this->publisher->publish(
						MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
						$publishRoutingKey,
						$this->entityFactory->create(
							Utils\Json::encode(
								array_merge(
									$entity->toArray(),
									$state !== null ? $state->toArray() : []
								)
							),
							$publishRoutingKey
						)
					);

				} catch (Exceptions\NotImplementedException) {
					$this->publisher->publish(
						MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
						$publishRoutingKey,
						$this->entityFactory->create(Utils\Json::encode($entity->toArray()), $publishRoutingKey)
					);
				}
			} elseif ($entity instanceof Entities\Channels\Properties\IDynamicProperty) {
				try {
					$state = $this->channelPropertiesStatesRepository->findOne($entity);

					$this->publisher->publish(
						MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
						$publishRoutingKey,
						$this->entityFactory->create(
							Utils\Json::encode(
								array_merge(
									$entity->toArray(),
									$state !== null ? $state->toArray() : []
								)
							),
							$publishRoutingKey
						)
					);

				} catch (Exceptions\NotImplementedException) {
					$this->publisher->publish(
						MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
						$publishRoutingKey,
						$this->entityFactory->create(Utils\Json::encode($entity->toArray()), $publishRoutingKey)
					);
				}
			} elseif ($entity instanceof Entities\Connectors\Properties\IDynamicProperty) {
				try {
					$state = $this->connectorPropertiesStatesRepository->findOne($entity);

					$this->publisher->publish(
						MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
						$publishRoutingKey,
						$this->entityFactory->create(
							Utils\Json::encode(
								array_merge(
									$entity->toArray(),
									$state !== null ? $state->toArray() : []
								)
							),
							$publishRoutingKey
						)
					);

				} catch (Exceptions\NotImplementedException) {
					$this->publisher->publish(
						MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
						$publishRoutingKey,
						$this->entityFactory->create(Utils\Json::encode($entity->toArray()), $publishRoutingKey)
					);
				}
			} else {
				$this->publisher->publish(
					MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
					$publishRoutingKey,
					$this->entityFactory->create(Utils\Json::encode($entity->toArray()), $publishRoutingKey)
				);
			}
		}
	}

	/**
	 * @param Entities\IEntity $entity
	 * @param string $class
	 *
	 * @return bool
	 */
	private function validateEntity(Entities\IEntity $entity, string $class): bool
	{
		$result = false;

		if (get_class($entity) === $class) {
			$result = true;
		}

		if (is_subclass_of($entity, $class)) {
			$result = true;
		}

		return $result;
	}

	/**
	 * @param object $entity
	 *
	 * @return bool
	 */
	private function validateNamespace(object $entity): bool
	{
		$rc = new ReflectionClass($entity);

		if (str_starts_with($rc->getNamespaceName(), 'FastyBird\DevicesModule')) {
			return true;
		}

		foreach ($rc->getInterfaces() as $interface) {
			if (str_starts_with($interface->getNamespaceName(), 'FastyBird\DevicesModule')) {
				return true;
			}
		}

		return false;
	}

}
