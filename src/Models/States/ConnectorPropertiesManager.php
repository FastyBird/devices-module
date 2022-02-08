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
		?ExchangePublisher\IPublisher $publisher
	) {
		$this->publisher = $publisher;
	}

	public function create(
		Entities\Connectors\Properties\IProperty $property,
		Utils\ArrayHash $values
	): States\IConnectorProperty {
		if ($this->manager === null) {
			throw new Exceptions\InvalidStateException('Connector properties state manager is not registered');
		}

		/** @var States\IConnectorProperty $createdState */
		$createdState = $this->manager->create($property, $values);

		if ($this->publisher !== null) {
			$this->publisher->publish(
				MetadataTypes\ModuleOriginType::get(MetadataTypes\ModuleOriginType::ORIGIN_MODULE_DEVICES),
				MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_CONNECTORS_PROPERTY_ENTITY_CREATED),
				Utils\ArrayHash::from(array_merge($property->toArray(), [
					'actual_value'   => MetadataHelpers\ValueHelper::normalizeValue($property->getDataType(), $createdState->getActualValue(), $property->getFormat()),
					'expected_value' => MetadataHelpers\ValueHelper::normalizeValue($property->getDataType(), $createdState->getExpectedValue(), $property->getFormat()),
					'pending'        => $createdState->isPending(),
				]))
			);
		}

		return $createdState;
	}

	public function update(
		Entities\Connectors\Properties\IProperty $property,
		States\IConnectorProperty $state,
		Utils\ArrayHash $values
	): States\IConnectorProperty {
		if ($this->manager === null) {
			throw new Exceptions\InvalidStateException('Connector properties state manager is not registered');
		}

		/** @var States\IConnectorProperty $updatedState */
		$updatedState = $this->manager->update(
			$property,
			$state,
			$values
		);

		if ($this->publisher !== null) {
			$this->publisher->publish(
				MetadataTypes\ModuleOriginType::get(MetadataTypes\ModuleOriginType::ORIGIN_MODULE_DEVICES),
				MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_CONNECTORS_PROPERTY_ENTITY_UPDATED),
				Utils\ArrayHash::from(array_merge($property->toArray(), [
					'actual_value'   => MetadataHelpers\ValueHelper::normalizeValue($property->getDataType(), $updatedState->getActualValue(), $property->getFormat()),
					'expected_value' => MetadataHelpers\ValueHelper::normalizeValue($property->getDataType(), $updatedState->getExpectedValue(), $property->getFormat()),
					'pending'        => $updatedState->isPending(),
				]))
			);
		}

		return $updatedState;
	}

	public function delete(
		Entities\Connectors\Properties\IProperty $property,
		States\IConnectorProperty $state
	): bool {
		if ($this->manager === null) {
			throw new Exceptions\InvalidStateException('Connector properties state manager is not registered');
		}

		$result = $this->manager->delete($property, $state);

		if ($result) {
			if ($this->publisher !== null) {
				$this->publisher->publish(
					MetadataTypes\ModuleOriginType::get(MetadataTypes\ModuleOriginType::ORIGIN_MODULE_DEVICES),
					MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_CONNECTORS_PROPERTY_ENTITY_UPDATED),
					Utils\ArrayHash::from(array_merge($property->toArray(), [
						'actual_value'   => null,
						'expected_value' => null,
						'pending'        => false,
					]))
				);
			}
		}

		return $result;
	}

}
