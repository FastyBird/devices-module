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
use FastyBird\DevicesModule\Helpers;
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

	/** @var Helpers\EntityKeyHelper */
	private Helpers\EntityKeyHelper $entityKeyGenerator;

	/** @var Models\States\IDevicePropertyRepository|null */
	private ?Models\States\IDevicePropertyRepository $devicePropertyStateRepository;

	/** @var Models\States\IChannelPropertyRepository|null */
	private ?Models\States\IChannelPropertyRepository $channelPropertyStateRepository;

	/** @var ExchangePublisher\Publisher|null */
	private ?ExchangePublisher\Publisher $publisher;

	/** @var ORM\EntityManagerInterface */
	private ORM\EntityManagerInterface $entityManager;

	public function __construct(
		Helpers\EntityKeyHelper $entityKeyGenerator,
		ORM\EntityManagerInterface $entityManager,
		?ExchangePublisher\Publisher $publisher = null,
		?Models\States\IDevicePropertyRepository $devicePropertyStateRepository = null,
		?Models\States\IChannelPropertyRepository $channelPropertyStateRepository = null
	) {
		$this->entityKeyGenerator = $entityKeyGenerator;
		$this->devicePropertyStateRepository = $devicePropertyStateRepository;
		$this->channelPropertyStateRepository = $channelPropertyStateRepository;
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
			ORM\Events::prePersist,
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
				&& $this->devicePropertyStateRepository !== null
			) {
				$state = $this->devicePropertyStateRepository->findOne($entity);

				$this->publisher->publish(
					MetadataTypes\ModuleOriginType::get(MetadataTypes\ModuleOriginType::ORIGIN_MODULE_DEVICES),
					$publishRoutingKey,
					Utils\ArrayHash::from(array_merge($state !== null ? [
						'actual_value'   => MetadataHelpers\ValueHelper::normalizeValue($entity->getDataType(), $state->getActualValue(), $entity->getFormat()),
						'expected_value' => MetadataHelpers\ValueHelper::normalizeValue($entity->getDataType(), $state->getExpectedValue(), $entity->getFormat()),
						'pending'        => $state->isPending(),
					] : [], $entity->toArray()))
				);
			} elseif (
				$entity instanceof Entities\Channels\Properties\IProperty
				&& $entity->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_DYNAMIC)
				&& $this->channelPropertyStateRepository !== null
			) {
				$state = $this->channelPropertyStateRepository->findOne($entity);

				$this->publisher->publish(
					MetadataTypes\ModuleOriginType::get(MetadataTypes\ModuleOriginType::ORIGIN_MODULE_DEVICES),
					$publishRoutingKey,
					Utils\ArrayHash::from(array_merge($state !== null ? [
						'actual_value'   => MetadataHelpers\ValueHelper::normalizeValue($entity->getDataType(), $state->getActualValue(), $entity->getFormat()),
						'expected_value' => MetadataHelpers\ValueHelper::normalizeValue($entity->getDataType(), $state->getExpectedValue(), $entity->getFormat()),
						'pending'        => $state->isPending(),
					] : [], $entity->toArray()))
				);
			} else {
				$this->publisher->publish(
					MetadataTypes\ModuleOriginType::get(MetadataTypes\ModuleOriginType::ORIGIN_MODULE_DEVICES),
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
