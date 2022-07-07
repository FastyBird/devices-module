<?php declare(strict_types = 1);

/**
 * ConnectorPropertiesManager.php
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
use FastyBird\Metadata\Entities as MetadataEntities;
use Nette;
use Nette\Utils;
use Psr\EventDispatcher as PsrEventDispatcher;

/**
 * Connector property states manager
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ConnectorPropertiesManager
{

	use Nette\SmartObject;

	/** @var IConnectorPropertiesManager|null */
	protected ?IConnectorPropertiesManager $manager;

	/** @var PsrEventDispatcher\EventDispatcherInterface|null */
	private ?PsrEventDispatcher\EventDispatcherInterface $dispatcher;

	public function __construct(
		?IConnectorPropertiesManager $manager,
		?PsrEventDispatcher\EventDispatcherInterface $dispatcher
	) {
		$this->manager = $manager;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * @param Entities\Connectors\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity $property
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IConnectorProperty
	 */
	public function create(
		MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity|Entities\Connectors\Properties\IProperty $property,
		Utils\ArrayHash $values
	): States\IConnectorProperty {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Connector properties state manager is not registered');
		}

		$createdState = $this->manager->create($property, $values);

		$this->dispatcher?->dispatch(new Events\StateEntityCreatedEvent($createdState));

		return $createdState;
	}

	/**
	 * @param Entities\Connectors\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity $property
	 * @param States\IConnectorProperty $state
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IConnectorProperty
	 */
	public function update(
		MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity|Entities\Connectors\Properties\IProperty $property,
		States\IConnectorProperty $state,
		Utils\ArrayHash $values
	): States\IConnectorProperty {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Connector properties state manager is not registered');
		}

		$updatedState = $this->manager->update($property, $state, $values);

		$this->dispatcher?->dispatch(new Events\StateEntityUpdatedEvent($state, $updatedState));

		return $updatedState;
	}

	/**
	 * @param Entities\Connectors\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity $property
	 * @param States\IConnectorProperty $state
	 *
	 * @return bool
	 */
	public function delete(
		MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity|Entities\Connectors\Properties\IProperty $property,
		States\IConnectorProperty $state
	): bool {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Connector properties state manager is not registered');
		}

		$result = $this->manager->delete($property, $state);

		$this->dispatcher?->dispatch(new Events\StateEntityDeletedEvent($property->getId()));

		return $result;
	}

}
