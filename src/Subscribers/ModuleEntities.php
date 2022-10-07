<?php declare(strict_types = 1);

/**
 * ModuleEntities.php
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
use function array_merge;
use function count;
use function is_a;
use function str_starts_with;

/**
 * Doctrine entities events
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Subscribers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ModuleEntities implements Common\EventSubscriber
{

	use Nette\SmartObject;

	private const ACTION_CREATED = 'created';

	private const ACTION_UPDATED = 'updated';

	private const ACTION_DELETED = 'deleted';

	public function __construct(
		private ORM\EntityManagerInterface $entityManager,
		private Models\States\DevicePropertiesRepository $devicePropertiesStatesRepository,
		private Models\States\DevicePropertiesManager $devicePropertiesStatesManager,
		private Models\States\ChannelPropertiesRepository $channelPropertiesStatesRepository,
		private Models\States\ChannelPropertiesManager $channelPropertiesStatesManager,
		private Models\States\ConnectorPropertiesRepository $connectorPropertiesStatesRepository,
		private Models\States\ConnectorPropertiesManager $connectorPropertiesStatesManager,
		private DataStorage\Writer $configurationDataWriter,
		private ExchangeEntities\EntityFactory $entityFactory,
		private ExchangePublisher\Publisher|null $publisher = null,
	)
	{
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
	 * @throws MetadataExceptions\FileNotFoundException
	 * @throws Utils\JsonException
	 * @throws Flysystem\FilesystemException
	 */
	public function postPersist(ORM\Event\LifecycleEventArgs $eventArgs): void
	{
		// onFlush was executed before, everything already initialized
		$entity = $eventArgs->getObject();

		// Check for valid entity
		if (!$entity instanceof Entities\Entity || !$this->validateNamespace($entity)) {
			return;
		}

		$this->publishEntity($entity, self::ACTION_CREATED);

		$this->configurationDataWriter->write();
	}

	/**
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
			!$entity instanceof Entities\Entity
			|| !$this->validateNamespace($entity)
			|| $uow->isScheduledForDelete($entity)
		) {
			return;
		}

		$this->publishEntity($entity, self::ACTION_UPDATED);

		$this->configurationDataWriter->write();
	}

	/**
	 * @throws Flysystem\FilesystemException
	 * @throws MetadataExceptions\FileNotFoundException
	 * @throws Utils\JsonException
	 */
	public function postRemove(ORM\Event\LifecycleEventArgs $eventArgs): void
	{
		// onFlush was executed before, everything already initialized
		$entity = $eventArgs->getObject();

		// Check for valid entity
		if (!$entity instanceof Entities\Entity || !$this->validateNamespace($entity)) {
			return;
		}

		$this->publishEntity($entity, self::ACTION_DELETED);

		// Property states cleanup
		if ($entity instanceof Entities\Connectors\Properties\Dynamic) {
			try {
				$state = $this->connectorPropertiesStatesRepository->findOne($entity);

				if ($state !== null) {
					$this->connectorPropertiesStatesManager->delete($entity, $state);
				}
			} catch (Exceptions\NotImplemented) {
				return;
			}
		} elseif ($entity instanceof Entities\Devices\Properties\Dynamic) {
			try {
				$state = $this->devicePropertiesStatesRepository->findOne($entity);

				if ($state !== null) {
					$this->devicePropertiesStatesManager->delete($entity, $state);
				}
			} catch (Exceptions\NotImplemented) {
				return;
			}
		} elseif ($entity instanceof DevicesModule\Entities\Channels\Properties\Dynamic) {
			try {
				$state = $this->channelPropertiesStatesRepository->findOne($entity);

				if ($state !== null) {
					$this->channelPropertiesStatesManager->delete($entity, $state);
				}
			} catch (Exceptions\NotImplemented) {
				return;
			}
		}

		$this->configurationDataWriter->write();
	}

	/**
	 * @throws Utils\JsonException
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	private function publishEntity(Entities\Entity $entity, string $action): void
	{
		if ($this->publisher === null) {
			return;
		}

		$publishRoutingKey = null;

		switch ($action) {
			case self::ACTION_CREATED:
				foreach (DevicesModule\Constants::MESSAGE_BUS_CREATED_ENTITIES_ROUTING_KEYS_MAPPING as $class => $routingKey) {
					if (is_a($entity, $class)) {
						$publishRoutingKey = MetadataTypes\RoutingKeyType::get($routingKey);
					}
				}

				break;
			case self::ACTION_UPDATED:
				foreach (DevicesModule\Constants::MESSAGE_BUS_UPDATED_ENTITIES_ROUTING_KEYS_MAPPING as $class => $routingKey) {
					if (is_a($entity, $class)) {
						$publishRoutingKey = MetadataTypes\RoutingKeyType::get($routingKey);
					}
				}

				break;
			case self::ACTION_DELETED:
				foreach (DevicesModule\Constants::MESSAGE_BUS_DELETED_ENTITIES_ROUTING_KEYS_MAPPING as $class => $routingKey) {
					if (is_a($entity, $class)) {
						$publishRoutingKey = MetadataTypes\RoutingKeyType::get($routingKey);
					}
				}

				break;
		}

		if ($publishRoutingKey !== null) {
			if ($entity instanceof Entities\Devices\Properties\Dynamic) {
				try {
					$state = $this->devicePropertiesStatesRepository->findOne($entity);

					$this->publisher->publish(
						MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
						$publishRoutingKey,
						$this->entityFactory->create(
							Utils\Json::encode(
								array_merge(
									$entity->toArray(),
									$state?->toArray() ?? [],
								),
							),
							$publishRoutingKey,
						),
					);

				} catch (Exceptions\NotImplemented) {
					$this->publisher->publish(
						MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
						$publishRoutingKey,
						$this->entityFactory->create(Utils\Json::encode($entity->toArray()), $publishRoutingKey),
					);
				}
			} elseif ($entity instanceof Entities\Channels\Properties\Dynamic) {
				try {
					$state = $this->channelPropertiesStatesRepository->findOne($entity);

					$this->publisher->publish(
						MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
						$publishRoutingKey,
						$this->entityFactory->create(
							Utils\Json::encode(
								array_merge(
									$entity->toArray(),
									$state?->toArray() ?? [],
								),
							),
							$publishRoutingKey,
						),
					);

				} catch (Exceptions\NotImplemented) {
					$this->publisher->publish(
						MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
						$publishRoutingKey,
						$this->entityFactory->create(Utils\Json::encode($entity->toArray()), $publishRoutingKey),
					);
				}
			} elseif ($entity instanceof Entities\Connectors\Properties\Dynamic) {
				try {
					$state = $this->connectorPropertiesStatesRepository->findOne($entity);

					$this->publisher->publish(
						MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
						$publishRoutingKey,
						$this->entityFactory->create(
							Utils\Json::encode(
								array_merge(
									$entity->toArray(),
									$state?->toArray() ?? [],
								),
							),
							$publishRoutingKey,
						),
					);

				} catch (Exceptions\NotImplemented) {
					$this->publisher->publish(
						MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
						$publishRoutingKey,
						$this->entityFactory->create(Utils\Json::encode($entity->toArray()), $publishRoutingKey),
					);
				}
			} else {
				$this->publisher->publish(
					MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
					$publishRoutingKey,
					$this->entityFactory->create(Utils\Json::encode($entity->toArray()), $publishRoutingKey),
				);
			}
		}
	}

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
