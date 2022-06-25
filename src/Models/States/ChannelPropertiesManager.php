<?php declare(strict_types = 1);

/**
 * ChannelPropertiesManager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.32.0
 *
 * @date           08.02.22
 */

namespace FastyBird\DevicesModule\Models\States;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Events;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\States;
use FastyBird\Exchange\Entities as ExchangeEntities;
use FastyBird\Metadata\Entities as MetadataEntities;
use Nette;
use Nette\Utils;
use Psr\EventDispatcher as PsrEventDispatcher;

/**
 * Channel property states manager
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ChannelPropertiesManager
{

	use Nette\SmartObject;

	/** @var ExchangeEntities\EntityFactory */
	protected ExchangeEntities\EntityFactory $entityFactory;

	/** @var IChannelPropertiesManager|null */
	protected ?IChannelPropertiesManager $manager;

	/** @var PsrEventDispatcher\EventDispatcherInterface|null */
	private ?PsrEventDispatcher\EventDispatcherInterface $dispatcher;

	public function __construct(
		ExchangeEntities\EntityFactory $entityFactory,
		?IChannelPropertiesManager $manager,
		?PsrEventDispatcher\EventDispatcherInterface $dispatcher
	) {
		$this->entityFactory = $entityFactory;
		$this->manager = $manager;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * @param Entities\Channels\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity $property
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IChannelProperty
	 */
	public function create(
		$property,
		Utils\ArrayHash $values
	): States\IChannelProperty {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Channel properties state manager is not registered');
		}

		if ($property->getParent() !== null) {
			throw new Exceptions\InvalidStateException('Child property can\'t have state');
		}

		$createdState = $this->manager->create($property, $values);

		if ($this->dispatcher !== null) {
			$this->dispatcher->dispatch(new Events\StateEntityCreatedEvent($createdState));
		}

		return $createdState;
	}

	/**
	 * @param Entities\Channels\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity $property
	 * @param States\IChannelProperty $state
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IChannelProperty
	 */
	public function update(
		$property,
		States\IChannelProperty $state,
		Utils\ArrayHash $values
	): States\IChannelProperty {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Channel properties state manager is not registered');
		}

		if ($property->getParent() !== null) {
			throw new Exceptions\InvalidStateException('Child property can\'t have state');
		}

		$updatedState = $this->manager->update($property, $state, $values);

		if ($this->dispatcher !== null) {
			$this->dispatcher->dispatch(new Events\StateEntityUpdatedEvent($state, $updatedState));
		}

		return $updatedState;
	}

	/**
	 * @param Entities\Channels\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity $property
	 * @param States\IChannelProperty $state
	 *
	 * @return bool
	 */
	public function delete(
		$property,
		States\IChannelProperty $state
	): bool {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Channel properties state manager is not registered');
		}

		if ($property->getParent() !== null) {
			throw new Exceptions\InvalidStateException('Child property can\'t have state');
		}

		$result = $this->manager->delete($property, $state);

		if ($this->dispatcher !== null) {
			$this->dispatcher->dispatch(new Events\StateEntityDeletedEvent($property->getId()));
		}

		return $result;
	}

}
