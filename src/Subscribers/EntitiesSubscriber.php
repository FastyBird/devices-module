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
use FastyBird\ApplicationExchange\Publisher as ApplicationExchangePublisher;
use FastyBird\DevicesModule;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Helpers;
use FastyBird\DevicesModule\Models;
use FastyBird\ModulesMetadata;
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;
use Nette;
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

	/** @var Helpers\EntityKeyHelper */
	private Helpers\EntityKeyHelper $entityKeyGenerator;

	/** @var Models\States\IPropertyRepository|null */
	private ?Models\States\IPropertyRepository $propertyStateRepository;

	/** @var ApplicationExchangePublisher\IPublisher */
	private ApplicationExchangePublisher\IPublisher $publisher;

	/** @var ORM\EntityManagerInterface */
	private ORM\EntityManagerInterface $entityManager;

	public function __construct(
		Helpers\EntityKeyHelper $entityKeyGenerator,
		ApplicationExchangePublisher\IPublisher $publisher,
		ORM\EntityManagerInterface $entityManager,
		?Models\States\IPropertyRepository $propertyStateRepository = null
	)
	{
		$this->entityKeyGenerator = $entityKeyGenerator;
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
	 * @return void
	 */
	public function preFlush(): void
	{
		$uow = $this->entityManager->getUnitOfWork();

		foreach ($uow->getScheduledEntityDeletions() as $entity) {
			// Check for valid entity
			if (!$entity instanceof Entities\IEntity || !$this->validateNamespace($entity)) {
				continue;
			}

			if (
				(
					$entity instanceof Entities\Devices\Controls\IControl
					|| $entity instanceof Entities\Channels\Controls\IControl
				)
				&& $entity->getName() === ModulesMetadataTypes\ControlNameType::TYPE_CONFIGURE
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

			$this->processEntityAction($entity, self::ACTION_DELETED);
		}
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
		if (!$entity instanceof Entities\IEntity || !$this->validateNamespace($entity)) {
			return;
		}

		if (method_exists($entity, 'setKey') && method_exists($entity, 'getKey')) {
			if ($entity->getKey() === null) {
				$entity->setKey($this->entityKeyGenerator->generate($entity));
			}
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
		if (!$entity instanceof Entities\IEntity || !$this->validateNamespace($entity)) {
			return;
		}

		$this->processEntityAction($entity, self::ACTION_CREATED);
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

		$this->processEntityAction($entity, self::ACTION_UPDATED);
	}

	/**
	 * @param Entities\IEntity $entity
	 * @param string $action
	 *
	 * @return void
	 */
	private function processEntityAction(Entities\IEntity $entity, string $action): void
	{
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
					array_merge($state !== null ? [
						'actual_value'   => Helpers\PropertyHelper::normalizeValue($entity, $state->getActualValue()),
						'expected_value' => Helpers\PropertyHelper::normalizeValue($entity, $state->getExpectedValue()),
						'pending'        => $state->isPending(),
					] : [], $entity->toArray())
				);
			} else {
				$this->publisher->publish(
					ModulesMetadata\Constants::MODULE_DEVICES_ORIGIN,
					$publishRoutingKey,
					$entity->toArray()
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
