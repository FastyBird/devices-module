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
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\States;
use FastyBird\Exchange\Publisher as ExchangePublisher;
use FastyBird\Metadata\Helpers as MetadataHelpers;
use FastyBird\Metadata\Types as MetadataTypes;
use Nette;
use Nette\Utils;

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

	/** @var ExchangePublisher\IPublisher|null */
	protected ?ExchangePublisher\IPublisher $publisher;

	/** @var IConnectorPropertiesManager|null */
	protected ?IConnectorPropertiesManager $manager;

	public function __construct(
		?IConnectorPropertiesManager $manager,
		?ExchangePublisher\IPublisher $publisher
	) {
		$this->manager = $manager;
		$this->publisher = $publisher;
	}

	/**
	 * @param Entities\Connectors\Properties\IProperty $property
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IConnectorProperty
	 */
	public function create(
		Entities\Connectors\Properties\IProperty $property,
		Utils\ArrayHash $values
	): States\IConnectorProperty {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Connector properties state manager is not registered');
		}

		/** @var States\IConnectorProperty $createdState */
		$createdState = $this->manager->create($property, $values);

		$this->publishEntity($property, $createdState);

		return $createdState;
	}

	/**
	 * @param Entities\Connectors\Properties\IProperty $property
	 * @param States\IConnectorProperty $state
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IConnectorProperty
	 */
	public function update(
		Entities\Connectors\Properties\IProperty $property,
		States\IConnectorProperty $state,
		Utils\ArrayHash $values
	): States\IConnectorProperty {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Connector properties state manager is not registered');
		}

		/** @var States\IConnectorProperty $updatedState */
		$updatedState = $this->manager->update($property, $state, $values);

		$this->publishEntity($property, $updatedState);

		return $updatedState;
	}

	/**
	 * @param Entities\Connectors\Properties\IProperty $property
	 * @param States\IConnectorProperty $state
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Connectors\Properties\IProperty $property,
		States\IConnectorProperty $state
	): bool {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Connector properties state manager is not registered');
		}

		$result = $this->manager->delete($property, $state);

		if ($result) {
			$this->publishEntity($property, null);
		}

		return $result;
	}

	private function publishEntity(
		Entities\Connectors\Properties\IProperty $property,
		?States\IConnectorProperty $state
	): void {
		if ($this->publisher === null) {
			return;
		}

		$this->publisher->publish(
			$property->getSource(),
			MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_CONNECTORS_PROPERTY_ENTITY_UPDATED),
			Utils\ArrayHash::from(array_merge($property->toArray(), [
				'actual_value'   => $state === null ? null : MetadataHelpers\ValueHelper::normalizeValue($property->getDataType(), $state->getActualValue(), $property->getFormat()),
				'expected_value' => $state === null ? null : MetadataHelpers\ValueHelper::normalizeValue($property->getDataType(), $state->getExpectedValue(), $property->getFormat()),
				'pending'        => !($state === null) && $state->isPending(),
			]))
		);
	}

}
