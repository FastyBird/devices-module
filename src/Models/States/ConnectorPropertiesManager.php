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
use FastyBird\Exchange\Entities as ExchangeEntities;
use FastyBird\Exchange\Publisher as ExchangePublisher;
use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Exceptions as MetadataExceptions;
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

	/** @var ExchangeEntities\EntityFactory */
	protected ExchangeEntities\EntityFactory $entityFactory;

	/** @var ExchangePublisher\IPublisher|null */
	protected ?ExchangePublisher\IPublisher $publisher;

	/** @var IConnectorPropertiesManager|null */
	protected ?IConnectorPropertiesManager $manager;

	public function __construct(
		ExchangeEntities\EntityFactory $entityFactory,
		?IConnectorPropertiesManager $manager,
		?ExchangePublisher\IPublisher $publisher
	) {
		$this->entityFactory = $entityFactory;
		$this->manager = $manager;
		$this->publisher = $publisher;
	}

	/**
	 * @param Entities\Connectors\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity $property
	 * @param Utils\ArrayHash $values
	 * @param bool $publishState
	 *
	 * @return States\IConnectorProperty
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 * @throws Utils\JsonException
	 */
	public function create(
		$property,
		Utils\ArrayHash $values,
		bool $publishState = true
	): States\IConnectorProperty {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Connector properties state manager is not registered');
		}

		$createdState = $this->manager->create($property, $values);

		if ($publishState) {
			$this->publishEntity($property, $createdState);
		}

		return $createdState;
	}

	/**
	 * @param Entities\Connectors\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity $property
	 * @param States\IConnectorProperty $state
	 * @param Utils\ArrayHash $values
	 * @param bool $publishState
	 *
	 * @return States\IConnectorProperty
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 * @throws Utils\JsonException
	 */
	public function update(
		$property,
		States\IConnectorProperty $state,
		Utils\ArrayHash $values,
		bool $publishState = true
	): States\IConnectorProperty {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Connector properties state manager is not registered');
		}

		$storedState = $state->toArray();

		$updatedState = $this->manager->update($property, $state, $values);

		if ($storedState !== $updatedState->toArray() && $publishState) {
			$this->publishEntity($property, $updatedState);
		}

		return $updatedState;
	}

	/**
	 * @param Entities\Connectors\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity $property
	 * @param States\IConnectorProperty $state
	 * @param bool $publishState
	 *
	 * @return bool
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 * @throws Utils\JsonException
	 */
	public function delete(
		$property,
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

	/**
	 * @param Entities\Connectors\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity $property
	 * @param States\IConnectorProperty|null $state
	 *
	 * @return void
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 * @throws Utils\JsonException
	 */
	private function publishEntity(
		$property,
		?States\IConnectorProperty $state
	): void {
		if ($this->publisher === null) {
			return;
		}

		$actualValue = $state === null ? null : Utilities\ValueHelper::normalizeValue($property->getDataType(), $state->getActualValue(), $property->getFormat(), $property->getInvalid());
		$expectedValue = $state === null ? null : Utilities\ValueHelper::normalizeValue($property->getDataType(), $state->getExpectedValue(), $property->getFormat(), $property->getInvalid());

		$this->publisher->publish(
			MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
			MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_CONNECTOR_PROPERTY_ENTITY_REPORTED),
			$this->entityFactory->create(Utils\Json::encode(array_merge($property->toArray(), [
				'actual_value'   => is_scalar($actualValue) || $actualValue === null ? $actualValue : strval($actualValue),
				'expected_value' => is_scalar($expectedValue) || $expectedValue === null ? $expectedValue : strval($expectedValue),
				'pending'        => !($state === null) && $state->isPending(),
				'valid'          => !($state === null) && $state->isValid(),
			])), MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_CONNECTOR_PROPERTY_ENTITY_REPORTED))
		);
	}

}
