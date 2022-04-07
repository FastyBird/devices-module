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
use FastyBird\DevicesModule\Utilities;
use FastyBird\Exchange\Publisher as ExchangePublisher;
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
	 * @param bool $publishState
	 *
	 * @return States\IConnectorProperty
	 */
	public function create(
		Entities\Connectors\Properties\IProperty $property,
		Utils\ArrayHash $values,
		bool $publishState = true
	): States\IConnectorProperty {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Connector properties state manager is not registered');
		}

		/** @var States\IConnectorProperty $createdState */
		$createdState = $this->manager->create($property, $values);

		if ($publishState) {
			$this->publishEntity($property, $createdState);
		}

		return $createdState;
	}

	/**
	 * @param Entities\Connectors\Properties\IProperty $property
	 * @param States\IConnectorProperty $state
	 * @param Utils\ArrayHash $values
	 * @param bool $publishState
	 *
	 * @return States\IConnectorProperty
	 */
	public function update(
		Entities\Connectors\Properties\IProperty $property,
		States\IConnectorProperty $state,
		Utils\ArrayHash $values,
		bool $publishState = true
	): States\IConnectorProperty {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Connector properties state manager is not registered');
		}

		$storedState = $state->toArray();

		/** @var States\IConnectorProperty $updatedState */
		$updatedState = $this->manager->update($property, $state, $values);

		if ($storedState !== $updatedState->toArray() && $publishState) {
			$this->publishEntity($property, $updatedState);
		}

		return $updatedState;
	}

	/**
	 * @param Entities\Connectors\Properties\IProperty $property
	 * @param States\IConnectorProperty $state
	 * @param bool $publishState
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Connectors\Properties\IProperty $property,
		States\IConnectorProperty $state,
		bool $publishState = true
	): bool {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Connector properties state manager is not registered');
		}

		$result = $this->manager->delete($property, $state);

		if ($result && $publishState) {
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

		$actualValue = $state === null ? null : Utilities\ValueHelper::normalizeValue($property->getDataType(), $state->getActualValue(), $property->getFormat(), $property->getInvalid());
		$expectedValue = $state === null ? null : Utilities\ValueHelper::normalizeValue($property->getDataType(), $state->getExpectedValue(), $property->getFormat(), $property->getInvalid());

		$this->publisher->publish(
			$property->getSource(),
			MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_CONNECTOR_PROPERTY_ENTITY_REPORTED),
			Utils\ArrayHash::from(array_merge($property->toArray(), [
				'actual_value'   => is_scalar($actualValue) || $actualValue === null ? $actualValue : strval($actualValue),
				'expected_value' => is_scalar($expectedValue) || $expectedValue === null ? $expectedValue : strval($expectedValue),
				'pending'        => !($state === null) && $state->isPending(),
				'valid'          => !($state === null) && $state->isValid(),
			]))
		);
	}

}
