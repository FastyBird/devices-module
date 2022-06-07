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
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\States;
use FastyBird\DevicesModule\Utilities;
use FastyBird\Exchange\Publisher as ExchangePublisher;
use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Types as MetadataTypes;
use Nette;
use Nette\Utils;

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

	/** @var MetadataEntities\GlobalEntityFactory */
	protected MetadataEntities\GlobalEntityFactory $entityFactory;

	/** @var ExchangePublisher\IPublisher|null */
	protected ?ExchangePublisher\IPublisher $publisher;

	/** @var IChannelPropertiesManager|null */
	protected ?IChannelPropertiesManager $manager;

	public function __construct(
		MetadataEntities\GlobalEntityFactory $entityFactory,
		?IChannelPropertiesManager $manager,
		?ExchangePublisher\IPublisher $publisher
	) {
		$this->entityFactory = $entityFactory;
		$this->manager = $manager;
		$this->publisher = $publisher;
	}

	/**
	 * @param Entities\Channels\Properties\IProperty $property
	 * @param Utils\ArrayHash $values
	 * @param bool $publishState
	 *
	 * @return States\IChannelProperty
	 */
	public function create(
		Entities\Channels\Properties\IProperty $property,
		Utils\ArrayHash $values,
		bool $publishState = true
	): States\IChannelProperty {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Channel properties state manager is not registered');
		}

		if ($property->getParent() !== null) {
			throw new Exceptions\InvalidStateException('Child property can\'t have state');
		}

		/** @var States\IChannelProperty $createdState */
		$createdState = $this->manager->create($property, $values);

		if ($publishState) {
			$this->publishEntity($property, $createdState);
		}

		return $createdState;
	}

	/**
	 * @param Entities\Channels\Properties\IProperty $property
	 * @param States\IChannelProperty $state
	 * @param Utils\ArrayHash $values
	 * @param bool $publishState
	 *
	 * @return States\IChannelProperty
	 */
	public function update(
		Entities\Channels\Properties\IProperty $property,
		States\IChannelProperty $state,
		Utils\ArrayHash $values,
		bool $publishState = true
	): States\IChannelProperty {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Channel properties state manager is not registered');
		}

		if ($property->getParent() !== null) {
			throw new Exceptions\InvalidStateException('Child property can\'t have state');
		}

		$storedState = $state->toArray();

		/** @var States\IChannelProperty $updatedState */
		$updatedState = $this->manager->update($property, $state, $values);

		if ($storedState !== $updatedState->toArray() && $publishState) {
			$this->publishEntity($property, $updatedState);

			foreach ($property->getChildren() as $child) {
				$this->publishEntity($child, $updatedState);
			}
		}

		return $updatedState;
	}

	/**
	 * @param Entities\Channels\Properties\IProperty $property
	 * @param States\IChannelProperty $state
	 * @param bool $publishState
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Channels\Properties\IProperty $property,
		States\IChannelProperty $state,
		bool $publishState = true
	): bool {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Channel properties state manager is not registered');
		}

		if ($property->getParent() !== null) {
			throw new Exceptions\InvalidStateException('Child property can\'t have state');
		}

		$result = $this->manager->delete($property, $state);

		if ($result && $publishState) {
			$this->publishEntity($property, null);

			foreach ($property->getChildren() as $child) {
				$this->publishEntity($child, null);
			}
		}

		return $result;
	}

	private function publishEntity(
		Entities\Channels\Properties\IProperty $property,
		?States\IChannelProperty $state
	): void {
		if ($this->publisher === null) {
			return;
		}

		$actualValue = $state === null ? null : Utilities\ValueHelper::normalizeValue($property->getDataType(), $state->getActualValue(), $property->getFormat(), $property->getInvalid());
		$expectedValue = $state === null ? null : Utilities\ValueHelper::normalizeValue($property->getDataType(), $state->getExpectedValue(), $property->getFormat(), $property->getInvalid());

		$this->publisher->publish(
			$property->getSource(),
			MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_CHANNEL_PROPERTY_ENTITY_REPORTED),
			$this->entityFactory->create(Utils\Json::encode(array_merge($property->toArray(), [
				'actual_value'   => is_scalar($actualValue) || $actualValue === null ? $actualValue : strval($actualValue),
				'expected_value' => is_scalar($expectedValue) || $expectedValue === null ? $expectedValue : strval($expectedValue),
				'pending'        => !($state === null) && $state->isPending(),
				'valid'          => !($state === null) && $state->isValid(),
			])), MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_CHANNEL_PROPERTY_ENTITY_REPORTED))
		);
	}

}
