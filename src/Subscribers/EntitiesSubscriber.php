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

use Consistence;
use Doctrine\Common;
use Doctrine\ORM;
use Doctrine\Persistence;
use FastyBird\ApplicationExchange\Publisher as ApplicationExchangePublisher;
use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\DateTimeFactory;
use FastyBird\DevicesModule;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Helpers;
use FastyBird\DevicesModule\Models;
use FastyBird\ModulesMetadata;
use Nette;
use Ramsey\Uuid;
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

	/** @var Helpers\NumberHashHelper */
	private Helpers\NumberHashHelper $numberHashHelper;

	/** @var DateTimeFactory\DateTimeFactory */
	private DateTimeFactory\DateTimeFactory $dateTimeFactory;

	/** @var Models\States\IPropertyRepository|null */
	private ?Models\States\IPropertyRepository $propertyStateRepository;

	/** @var ApplicationExchangePublisher\IPublisher */
	private ApplicationExchangePublisher\IPublisher $publisher;

	/** @var ORM\EntityManagerInterface */
	private ORM\EntityManagerInterface $entityManager;

	public function __construct(
		Helpers\NumberHashHelper $numberHashHelper,
		DateTimeFactory\DateTimeFactory $dateTimeFactory,
		ApplicationExchangePublisher\IPublisher $publisher,
		ORM\EntityManagerInterface $entityManager,
		?Models\States\IPropertyRepository $propertyStateRepository = null
	) {
		$this->numberHashHelper = $numberHashHelper;
		$this->dateTimeFactory = $dateTimeFactory;
		$this->propertyStateRepository = $propertyStateRepository;
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
			ORM\Events::preFlush,
			ORM\Events::onFlush,
			ORM\Events::prePersist,
			ORM\Events::postPersist,
			ORM\Events::postUpdate,
		];
	}

	/**
	 * @param ORM\Event\LifecycleEventArgs $eventArgs
	 *
	 * @return void
	 */
	public function prePersist(ORM\Event\LifecycleEventArgs $eventArgs): void
	{
		$entity = $eventArgs->getObject();

		// Check for valid entity
		if (!$entity instanceof DatabaseEntities\IEntity || !$this->validateNamespace($entity)) {
			return;
		}

		if (method_exists($entity, 'setKey')) {
			$entity->setKey($this->numberHashHelper->alphaIdToHash($this->dateTimeFactory->getNow()->getTimestamp()));
		}
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
		if (!$entity instanceof DatabaseEntities\IEntity || !$this->validateNamespace($entity)) {
			return;
		}

		$this->processEntityAction($entity, self::ACTION_CREATED);
	}

	/**
	 * @param DatabaseEntities\IEntity $entity
	 * @param string $action
	 *
	 * @return void
	 */
	private function processEntityAction(DatabaseEntities\IEntity $entity, string $action): void
	{
		if ($entity instanceof Entities\Devices\Controls\IControl) {
			$entity = $entity->getDevice();
			$action = self::ACTION_UPDATED;
		}

		if ($entity instanceof Entities\Channels\Controls\IControl) {
			$entity = $entity->getChannel();
			$action = self::ACTION_UPDATED;
		}

		$publishRoutingKey = null;

		switch ($action) {
			case self::ACTION_CREATED:
				foreach (DevicesModule\Constants::MESSAGE_BUS_CREATED_ENTITIES_ROUTING_KEYS_MAPPING as $class => $routingKey) {
					if ($this->validateEntity($entity, $class)) {
						$publishRoutingKey = $routingKey;
					}
				}
				break;

			case self::ACTION_UPDATED:
				foreach (DevicesModule\Constants::MESSAGE_BUS_UPDATED_ENTITIES_ROUTING_KEYS_MAPPING as $class => $routingKey) {
					if ($this->validateEntity($entity, $class)) {
						$publishRoutingKey = $routingKey;
					}
				}
				break;

			case self::ACTION_DELETED:
				foreach (DevicesModule\Constants::MESSAGE_BUS_DELETED_ENTITIES_ROUTING_KEYS_MAPPING as $class => $routingKey) {
					if ($this->validateEntity($entity, $class)) {
						$publishRoutingKey = $routingKey;
					}
				}
				break;
		}

		if ($publishRoutingKey !== null) {
			if (
				(
					$entity instanceof Entities\Devices\Properties\IProperty
					|| $entity instanceof Entities\Channels\Properties\IProperty
				) && $this->propertyStateRepository !== null
			) {
				$state = $this->propertyStateRepository->findOne($entity->getId());

				$this->publisher->publish(
					ModulesMetadata\Constants::MODULE_DEVICES_ORIGIN,
					$publishRoutingKey,
					array_merge($state !== null ? $state->toArray() : [], $this->toArray($entity))
				);

			} else {
				$this->publisher->publish(
					ModulesMetadata\Constants::MODULE_DEVICES_ORIGIN,
					$publishRoutingKey,
					$this->toArray($entity)
				);
			}
		}
	}

	/**
	 * @param DatabaseEntities\IEntity $entity
	 * @param string $class
	 *
	 * @return bool
	 */
	private function validateEntity(DatabaseEntities\IEntity $entity, string $class): bool
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
	 * @param DatabaseEntities\IEntity $entity
	 *
	 * @return mixed[]
	 */
	private function toArray(DatabaseEntities\IEntity $entity): array
	{
		if (method_exists($entity, 'toArray')) {
			return $entity->toArray();
		}

		$metadata = $this->entityManager->getClassMetadata(get_class($entity));

		$fields = [];

		foreach ($metadata->fieldMappings as $field) {
			if (isset($field['fieldName'])) {
				$fields[] = $field['fieldName'];
			}
		}

		try {
			$rc = new ReflectionClass(get_class($entity));

			foreach ($rc->getProperties() as $property) {
				$fields[] = $property->getName();
			}

		} catch (ReflectionException $ex) {
			// Nothing to do, reflection could not be loaded
		}

		$fields = array_unique($fields);

		$values = [];

		foreach ($fields as $field) {
			try {
				$value = $this->getPropertyValue($entity, $field);

				if ($value instanceof Consistence\Enum\Enum) {
					$value = $value->getValue();

				} elseif ($value instanceof Uuid\UuidInterface) {
					$value = $value->toString();
				}

				if (is_object($value)) {
					continue;
				}

				$key = preg_replace('/(?<!^)[A-Z]/', '_$0', $field);

				if ($key !== null) {
					$values[strtolower($key)] = $value;
				}

			} catch (Exceptions\PropertyNotExistsException $ex) {
				// No need to do anything
			}
		}

		return $values;
	}

	/**
	 * @param DatabaseEntities\IEntity $entity
	 * @param string $property
	 *
	 * @return mixed
	 *
	 * @throws Exceptions\PropertyNotExistsException
	 */
	private function getPropertyValue(DatabaseEntities\IEntity $entity, string $property)
	{
		$ucFirst = ucfirst($property);

		$methods = [
			'get' . $ucFirst,
			'is' . $ucFirst,
			'has' . $ucFirst,
		];

		foreach ($methods as $method) {
			$callable = [$entity, $method];

			if (is_callable($callable)) {
				return call_user_func($callable);
			}
		}

		if (!property_exists($entity, $property)) {
			throw new Exceptions\PropertyNotExistsException(sprintf('Property "%s" does not exists on entity', $property));
		}

		return $entity->{$property};
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
			!$entity instanceof DatabaseEntities\IEntity
			|| !$this->validateNamespace($entity)
			|| $uow->isScheduledForDelete($entity)
		) {
			return;
		}

		if (
			$entity instanceof Entities\Channels\Controls\IControl
			&& $uow->isScheduledForUpdate($entity->getChannel())
		) {
			return;
		}

		if (
			$entity instanceof Entities\Devices\Controls\IControl
			&& $uow->isScheduledForUpdate($entity->getDevice())
		) {
			return;
		}

		$this->processEntityAction($entity, self::ACTION_UPDATED);
	}

	/**
	 * @return void
	 */
	public function preFlush(): void
	{
		$uow = $this->entityManager->getUnitOfWork();

		foreach ($uow->getScheduledEntityDeletions() as $entity) {
			// Check for valid entity
			if (!$entity instanceof DatabaseEntities\IEntity || !$this->validateNamespace($entity)) {
				continue;
			}

			if (
				(
					$entity instanceof Entities\Devices\Controls\IControl
					|| $entity instanceof Entities\Channels\Controls\IControl
				)
				&& $entity->getName() === ModulesMetadata\Constants::CONTROL_CONFIG
			) {
				if ($entity instanceof Entities\Devices\Controls\IControl) {
					foreach ($entity->getDevice()->getConfiguration() as $row) {
						$uow->scheduleForDelete($row);
					}
				}

				if ($entity instanceof Entities\Channels\Controls\IControl) {
					foreach ($entity->getChannel()->getConfiguration() as $row) {
						$uow->scheduleForDelete($row);
					}
				}
			}
		}
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
			// Doctrine is fine deleting elements multiple times. We are not.
			$hash = $this->getHash($entity, $uow->getEntityIdentifier($entity));

			if (in_array($hash, $processedEntities, true)) {
				continue;
			}

			$processedEntities[] = $hash;

			// Check for valid entity
			if (!$entity instanceof DatabaseEntities\IEntity || !$this->validateNamespace($entity)) {
				continue;
			}

			if (
				$entity instanceof Entities\Devices\Controls\IControl
				&& $uow->isScheduledForDelete($entity->getDevice())
			) {
				continue;
			}

			if (
				$entity instanceof Entities\Channels\Controls\IControl
				&& $uow->isScheduledForDelete($entity->getChannel())
			) {
				continue;
			}

			$processEntities[] = $entity;
		}

		foreach ($processEntities as $entity) {
			// Check for valid entity
			if (!$entity instanceof DatabaseEntities\IEntity || !$this->validateNamespace($entity)) {
				continue;
			}

			$this->processEntityAction($entity, self::ACTION_DELETED);
		}
	}

	/**
	 * @param DatabaseEntities\IEntity $entity
	 * @param mixed[] $identifier
	 *
	 * @return string
	 */
	private function getHash(DatabaseEntities\IEntity $entity, array $identifier): string
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

}
