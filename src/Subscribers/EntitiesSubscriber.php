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
use Doctrine\Persistence;
use FastyBird\DevicesModule;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\Exchange\Publisher as ExchangePublisher;
use FastyBird\Metadata\Helpers as MetadataHelpers;
use FastyBird\Metadata\Types as MetadataTypes;
use Nette;
use Nette\Utils;
use ReflectionClass;
use ReflectionException;

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

	/** @var ExchangePublisher\Publisher|null */
	private ?ExchangePublisher\Publisher $publisher;

	/** @var ORM\EntityManagerInterface */
	private ORM\EntityManagerInterface $entityManager;

	public function __construct(
		ORM\EntityManagerInterface $entityManager,
		Models\States\DevicePropertiesRepository $devicePropertiesStatesRepository,
		Models\States\DevicePropertiesManager $devicePropertiesStatesManager,
		Models\States\ChannelPropertiesRepository $channelPropertiesStatesRepository,
		Models\States\ChannelPropertiesManager $channelPropertiesStatesManager,
		Models\States\ConnectorPropertiesRepository $connectorPropertiesStatesRepository,
		Models\States\ConnectorPropertiesManager $connectorPropertiesStatesManager,
		?ExchangePublisher\Publisher $publisher = null
	) {
		$this->devicePropertiesStatesRepository = $devicePropertiesStatesRepository;
		$this->devicePropertiesStatesManager = $devicePropertiesStatesManager;
		$this->channelPropertiesStatesRepository = $channelPropertiesStatesRepository;
		$this->channelPropertiesStatesManager = $channelPropertiesStatesManager;
		$this->connectorPropertiesStatesRepository = $connectorPropertiesStatesRepository;
		$this->connectorPropertiesStatesManager = $connectorPropertiesStatesManager;
		$this->publisher = $publisher;
		$this->entityManager = $entityManager;
	}

	/**
	 * Register events
	 *
	 * @return string[]
	 */
	public function getSubscribedEvents(): array
	{
		return [
			ORM\Events::onFlush,
			ORM\Events::postPersist,
			ORM\Events::postUpdate,
		];
	}

	/**
	 * @param object $entity
	 *
	 * @return bool
	 */
	private function validateNamespace(object $entity): bool
	{
		try {
			$rc = new ReflectionClass($entity);

		} catch (ReflectionException $ex) {
			return false;
		}

		return str_starts_with($rc->getNamespaceName(), 'FastyBird\DevicesModule');
	}

	/**
	 * @return void
	 */
	public function onFlush(): void
	{
		$uow = $this->entityManager->getUnitOfWork();

		$processedEntities = [];

		$processEntities = [];

		foreach ($uow->getScheduledEntityDeletions() as $entity) {
			// Check for valid entity
			if (!$entity instanceof Entities\IEntity || !$this->validateNamespace($entity)) {
				continue;
			}

			// Doctrine is fine deleting elements multiple times. We are not.
			$hash = $this->getHash($entity, $uow->getEntityIdentifier($entity));

			if (in_array($hash, $processedEntities, true)) {
				continue;
			}

			$processedEntities[] = $hash;

			$processEntities[] = $entity;
		}

		foreach ($processEntities as $entity) {
			// Check for valid entity
			if (!$entity instanceof Entities\IEntity || !$this->validateNamespace($entity)) {
				continue;
			}

			$this->publishEntity($entity, self::ACTION_DELETED);

			// Property states cleanup
			if ($entity instanceof DevicesModule\Entities\Connectors\Properties\IProperty) {
				try {
					$state = $this->connectorPropertiesStatesRepository->findOne($entity);

					if ($state !== null) {
						$this->connectorPropertiesStatesManager->delete($entity, $state);
					}
				} catch (Exceptions\NotImplementedException $ex) {
					return;
				}
			} elseif ($entity instanceof DevicesModule\Entities\Devices\Properties\IProperty) {
				try {
					$state = $this->devicePropertiesStatesRepository->findOne($entity);

					if ($state !== null) {
						$this->devicePropertiesStatesManager->delete($entity, $state);
					}
				} catch (Exceptions\NotImplementedException $ex) {
					return;
				}
			} elseif ($entity instanceof DevicesModule\Entities\Channels\Properties\IProperty) {
				try {
					$state = $this->channelPropertiesStatesRepository->findOne($entity);

					if ($state !== null) {
						$this->channelPropertiesStatesManager->delete($entity, $state);
					}
				} catch (Exceptions\NotImplementedException $ex) {
					return;
				}
			}
		}
	}

	/**
	 * @param Entities\IEntity $entity
	 * @param mixed[] $identifier
	 *
	 * @return string
	 */
	private function getHash(Entities\IEntity $entity, array $identifier): string
	{
		return implode(
			' ',
			array_merge(
				[$this->getRealClass(get_class($entity))],
				$identifier
			)
		);
	}

	/**
	 * @param string $class
	 *
	 * @return string
	 */
	private function getRealClass(string $class): string
	{
		$pos = strrpos($class, '\\' . Persistence\Proxy::MARKER . '\\');

		if ($pos === false) {
			return $class;
		}

		return substr($class, $pos + Persistence\Proxy::MARKER_LENGTH + 2);
	}

	/**
	 * @param Entities\IEntity $entity
	 * @param string $action
	 *
	 * @return void
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
			if (
				$entity instanceof Entities\Devices\Properties\IProperty
				&& $entity->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_DYNAMIC)
			) {
				try {
					$state = $this->devicePropertiesStatesRepository->findOne($entity);

				} catch (Exceptions\NotImplementedException $ex) {
					$this->publisher->publish(
						$entity->getSource(),
						$publishRoutingKey,
						Utils\ArrayHash::from($entity->toArray())
					);

					return;
				}

				$actualValue = $state ? MetadataHelpers\ValueHelper::normalizeValue($entity->getDataType(), $state->getActualValue(), $entity->getFormat()) : null;
				$expectedValue = $state ? MetadataHelpers\ValueHelper::normalizeValue($entity->getDataType(), $state->getExpectedValue(), $entity->getFormat()) : null;

				$this->publisher->publish(
					$entity->getSource(),
					$publishRoutingKey,
					Utils\ArrayHash::from(array_merge($state !== null ? [
						'actual_value'   => is_scalar($actualValue) || $actualValue === null ? $actualValue : strval($actualValue),
						'expected_value' => is_scalar($expectedValue) || $expectedValue === null ? $expectedValue : strval($expectedValue),
						'pending'        => $state->isPending(),
					] : [], $entity->toArray()))
				);
			} elseif (
				$entity instanceof Entities\Channels\Properties\IProperty
				&& $entity->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_DYNAMIC)
			) {
				try {
					$state = $this->channelPropertiesStatesRepository->findOne($entity);

				} catch (Exceptions\NotImplementedException $ex) {
					$this->publisher->publish(
						$entity->getSource(),
						$publishRoutingKey,
						Utils\ArrayHash::from($entity->toArray())
					);

					return;
				}

				$actualValue = $state ? MetadataHelpers\ValueHelper::normalizeValue($entity->getDataType(), $state->getActualValue(), $entity->getFormat()) : null;
				$expectedValue = $state ? MetadataHelpers\ValueHelper::normalizeValue($entity->getDataType(), $state->getExpectedValue(), $entity->getFormat()) : null;

				$this->publisher->publish(
					$entity->getSource(),
					$publishRoutingKey,
					Utils\ArrayHash::from(array_merge($state !== null ? [
						'actual_value'   => is_scalar($actualValue) || $actualValue === null ? $actualValue : strval($actualValue),
						'expected_value' => is_scalar($expectedValue) || $expectedValue === null ? $expectedValue : strval($expectedValue),
						'pending'        => $state->isPending(),
					] : [], $entity->toArray()))
				);
			} elseif (
				$entity instanceof Entities\Connectors\Properties\IProperty
				&& $entity->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_DYNAMIC)
			) {
				try {
					$state = $this->connectorPropertiesStatesRepository->findOne($entity);

				} catch (Exceptions\NotImplementedException $ex) {
					$this->publisher->publish(
						$entity->getSource(),
						$publishRoutingKey,
						Utils\ArrayHash::from($entity->toArray())
					);

					return;
				}

				$actualValue = $state ? MetadataHelpers\ValueHelper::normalizeValue($entity->getDataType(), $state->getActualValue(), $entity->getFormat()) : null;
				$expectedValue = $state ? MetadataHelpers\ValueHelper::normalizeValue($entity->getDataType(), $state->getExpectedValue(), $entity->getFormat()) : null;

				$this->publisher->publish(
					$entity->getSource(),
					$publishRoutingKey,
					Utils\ArrayHash::from(array_merge($state !== null ? [
						'actual_value'   => is_scalar($actualValue) || $actualValue === null ? $actualValue : strval($actualValue),
						'expected_value' => is_scalar($expectedValue) || $expectedValue === null ? $expectedValue : strval($expectedValue),
						'pending'        => $state->isPending(),
					] : [], $entity->toArray()))
				);
			} else {
				$this->publisher->publish(
					$entity->getSource(),
					$publishRoutingKey,
					Utils\ArrayHash::from($entity->toArray())
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
	 * @param ORM\Event\LifecycleEventArgs $eventArgs
	 *
	 * @return void
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
	}

	/**
	 * @param ORM\Event\LifecycleEventArgs $eventArgs
	 *
	 * @return void
	 */
	public function postUpdate(ORM\Event\LifecycleEventArgs $eventArgs): void
	{
		$uow = $this->entityManager->getUnitOfWork();

		// onFlush was executed before, everything already initialized
		$entity = $eventArgs->getObject();

		// Get changes => should be already computed here (is a listener)
		$changeset = $uow->getEntityChangeSet($entity);

		// If we have no changes left => don't create revision log
		if (count($changeset) === 0) {
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
	}

}
