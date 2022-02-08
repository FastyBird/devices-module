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
use FastyBird\Exchange\Publisher as ExchangePublisher;
use FastyBird\Metadata\Helpers as MetadataHelpers;
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

	/** @var ExchangePublisher\IPublisher|null */
	protected ?ExchangePublisher\IPublisher $publisher;

	/** @var IChannelPropertiesManager|null */
	protected ?IChannelPropertiesManager $manager;

	public function __construct(
		?IChannelPropertiesManager $manager,
		?ExchangePublisher\IPublisher $publisher
	) {
		$this->manager = $manager;
		$this->publisher = $publisher;
	}

	/**
	 * @param Entities\Channels\Properties\IProperty $property
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IChannelProperty
	 */
	public function create(
		Entities\Channels\Properties\IProperty $property,
		Utils\ArrayHash $values
	): States\IChannelProperty {
		if ($this->manager === null) {
			throw new Exceptions\InvalidStateException('Channel properties state manager is not registered');
		}

		/** @var States\IChannelProperty $createdState */
		$createdState = $this->manager->create($property, $values);

		if ($this->publisher !== null) {
			$this->publisher->publish(
				MetadataTypes\ModuleOriginType::get(MetadataTypes\ModuleOriginType::ORIGIN_MODULE_DEVICES),
				MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_CHANNELS_PROPERTY_ENTITY_CREATED),
				Utils\ArrayHash::from(array_merge($property->toArray(), [
					'actual_value'   => MetadataHelpers\ValueHelper::normalizeValue($property->getDataType(), $createdState->getActualValue(), $property->getFormat()),
					'expected_value' => MetadataHelpers\ValueHelper::normalizeValue($property->getDataType(), $createdState->getExpectedValue(), $property->getFormat()),
					'pending'        => $createdState->isPending(),
				]))
			);
		}

		return $createdState;
	}

	/**
	 * @param Entities\Channels\Properties\IProperty $property
	 * @param States\IChannelProperty $state
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IChannelProperty
	 */
	public function update(
		Entities\Channels\Properties\IProperty $property,
		States\IChannelProperty $state,
		Utils\ArrayHash $values
	): States\IChannelProperty {
		if ($this->manager === null) {
			throw new Exceptions\InvalidStateException('Channel properties state manager is not registered');
		}

		/** @var States\IChannelProperty $updatedState */
		$updatedState = $this->manager->update($property, $state, $values);

		if ($this->publisher !== null) {
			$this->publisher->publish(
				MetadataTypes\ModuleOriginType::get(MetadataTypes\ModuleOriginType::ORIGIN_MODULE_DEVICES),
				MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_CHANNELS_PROPERTY_ENTITY_UPDATED),
				Utils\ArrayHash::from(array_merge($property->toArray(), [
					'actual_value'   => MetadataHelpers\ValueHelper::normalizeValue($property->getDataType(), $updatedState->getActualValue(), $property->getFormat()),
					'expected_value' => MetadataHelpers\ValueHelper::normalizeValue($property->getDataType(), $updatedState->getExpectedValue(), $property->getFormat()),
					'pending'        => $updatedState->isPending(),
				]))
			);
		}

		return $updatedState;
	}

	/**
	 * @param Entities\Channels\Properties\IProperty $property
	 * @param States\IChannelProperty $state
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Channels\Properties\IProperty $property,
		States\IChannelProperty $state
	): bool {
		if ($this->manager === null) {
			throw new Exceptions\InvalidStateException('Channel properties state manager is not registered');
		}

		$result = $this->manager->delete($property, $state);

		if ($result) {
			if ($this->publisher !== null) {
				$this->publisher->publish(
					MetadataTypes\ModuleOriginType::get(MetadataTypes\ModuleOriginType::ORIGIN_MODULE_DEVICES),
					MetadataTypes\RoutingKeyType::get(MetadataTypes\RoutingKeyType::ROUTE_CHANNELS_PROPERTY_ENTITY_UPDATED),
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
