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
use FastyBird\Exchange\Entities as ExchangeEntities;
use FastyBird\Exchange\Publisher as ExchangePublisher;
use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Metadata\Types as MetadataTypes;
use Nette;
use Nette\Utils;
use Ramsey\Uuid;

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

	/** @var ExchangePublisher\IPublisher|null */
	protected ?ExchangePublisher\IPublisher $publisher;

	/** @var IChannelPropertiesManager|null */
	protected ?IChannelPropertiesManager $manager;

	/** @var Models\DataStorage\IChannelPropertiesRepository */
	private Models\DataStorage\IChannelPropertiesRepository $channelPropertiesRepository;

	public function __construct(
		Models\DataStorage\IChannelPropertiesRepository $channelPropertiesRepository,
		ExchangeEntities\EntityFactory $entityFactory,
		?IChannelPropertiesManager $manager,
		?ExchangePublisher\IPublisher $publisher
	) {
		$this->channelPropertiesRepository = $channelPropertiesRepository;
		$this->entityFactory = $entityFactory;
		$this->manager = $manager;
		$this->publisher = $publisher;
	}

	/**
	 * @param Entities\Channels\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity $property
	 * @param Utils\ArrayHash $values
	 * @param bool $publishState
	 *
	 * @return States\IChannelProperty
	 *
	 * @throws Utils\JsonException
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function create(
		$property,
		Utils\ArrayHash $values,
		bool $publishState = true
	): States\IChannelProperty {
		if ($this->manager === null) {
			throw new Exceptions\NotImplementedException('Channel properties state manager is not registered');
		}

		if ($property->getParent() !== null) {
			throw new Exceptions\InvalidStateException('Child property can\'t have state');
		}

		$createdState = $this->manager->create($property, $values);

		if ($publishState) {
			$this->publishEntity($property, $createdState);
		}

		return $createdState;
	}

	/**
	 * @param Entities\Channels\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity $property
	 * @param States\IChannelProperty $state
	 * @param Utils\ArrayHash $values
	 * @param bool $publishState
	 *
	 * @return States\IChannelProperty
	 *
	 * @throws Utils\JsonException
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function update(
		$property,
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

		$updatedState = $this->manager->update($property, $state, $values);

		if ($storedState !== $updatedState->toArray() && $publishState) {
			$this->publishEntity($property, $updatedState);

			foreach ($property->getChildren() as $child) {
				if ($child instanceof Uuid\UuidInterface) {
					$child = $this->channelPropertiesRepository->findById($child);

					if (
						$child instanceof MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity
						|| $child instanceof MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
					) {
						$this->publishEntity($child, $updatedState);
					}
				} else {
					$this->publishEntity($child, $updatedState);
				}
			}
		}

		return $updatedState;
	}

	/**
	 * @param Entities\Channels\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity $property
	 * @param States\IChannelProperty $state
	 * @param bool $publishState
	 *
	 * @return bool
	 *
	 * @throws Utils\JsonException
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function delete(
		$property,
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
				if ($child instanceof Uuid\UuidInterface) {
					$child = $this->channelPropertiesRepository->findById($child);

					if (
						$child instanceof MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity
						|| $child instanceof MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
					) {
						$this->publishEntity($child, null);
					}
				} else {
					$this->publishEntity($child, null);
				}
			}
		}

		return $result;
	}

	/**
	 * @param Entities\Channels\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity $property
	 * @param States\IChannelProperty|null $state
	 *
	 * @return void
	 *
	 * @throws Utils\JsonException
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	private function publishEntity(
		$property,
		?States\IChannelProperty $state
	): void {
		if ($this->publisher === null) {
			return;
		}

		$actualValue = $state === null ? null : Utilities\ValueHelper::normalizeValue($property->getDataType(), $state->getActualValue(), $property->getFormat(), $property->getInvalid());
		$expectedValue = $state === null ? null : Utilities\ValueHelper::normalizeValue($property->getDataType(), $state->getExpectedValue(), $property->getFormat(), $property->getInvalid());

		$this->publisher->publish(
			MetadataTypes\ModuleSourceType::get(MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES),
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
