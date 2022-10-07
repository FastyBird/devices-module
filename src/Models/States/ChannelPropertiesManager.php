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
use FastyBird\DevicesModule\Utilities;
use FastyBird\Exchange\Entities as ExchangeEntities;
use FastyBird\Metadata\Entities as MetadataEntities;
use Nette;
use Nette\Utils;
use Psr\EventDispatcher as PsrEventDispatcher;
use function strval;

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

	public function __construct(
		protected ExchangeEntities\EntityFactory $entityFactory,
		protected IChannelPropertiesManager|null $manager,
		private PsrEventDispatcher\EventDispatcherInterface|null $dispatcher,
	)
	{
	}

	public function create(
		MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity|Entities\Channels\Properties\Dynamic|Entities\Channels\Properties\Mapped $property,
		Utils\ArrayHash $values,
	): States\ChannelProperty
	{
		if ($this->manager === null) {
			throw new Exceptions\NotImplemented('Channel properties state manager is not registered');
		}

		if ($property->getParent() !== null) {
			throw new Exceptions\InvalidState('Child property can\'t have state');
		}

		if (
			$values->offsetExists('actualValue')
			&& $values->offsetExists('expectedValue')
		) {
			$actualValue = Utilities\ValueHelper::normalizeValue(
				$property->getDataType(),
				strval($values->offsetGet('actualValue')),
				$property->getFormat(),
				$property->getInvalid(),
			);

			$expectedValue = Utilities\ValueHelper::normalizeValue(
				$property->getDataType(),
				strval($values->offsetGet('expectedValue')),
				$property->getFormat(),
				$property->getInvalid(),
			);

			if ($expectedValue === $actualValue) {
				$values->offsetSet('expectedValue', null);
				$values->offsetSet('pending', null);
			}
		}

		$createdState = $this->manager->create($property, $values);

		$this->dispatcher?->dispatch(new Events\StateEntityCreated($property, $createdState));

		return $createdState;
	}

	public function update(
		MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity|Entities\Channels\Properties\Dynamic|Entities\Channels\Properties\Mapped $property,
		States\ChannelProperty $state,
		Utils\ArrayHash $values,
	): States\ChannelProperty
	{
		if ($this->manager === null) {
			throw new Exceptions\NotImplemented('Channel properties state manager is not registered');
		}

		if ($property->getParent() !== null) {
			throw new Exceptions\InvalidState('Child property can\'t have state');
		}

		$updatedState = $this->manager->update($property, $state, $values);

		$actualValue = Utilities\ValueHelper::normalizeValue(
			$property->getDataType(),
			$updatedState->getActualValue(),
			$property->getFormat(),
			$property->getInvalid(),
		);

		$expectedValue = Utilities\ValueHelper::normalizeValue(
			$property->getDataType(),
			$updatedState->getExpectedValue(),
			$property->getFormat(),
			$property->getInvalid(),
		);

		if ($expectedValue === $actualValue) {
			$updatedState = $this->manager->update(
				$property,
				$updatedState,
				Utils\ArrayHash::from([
					'expectedValue' => null,
					'pending' => null,
				]),
			);
		}

		$this->dispatcher?->dispatch(new Events\StateEntityUpdated($property, $state, $updatedState));

		return $updatedState;
	}

	public function delete(
		MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity|Entities\Channels\Properties\Dynamic|Entities\Channels\Properties\Mapped $property,
		States\ChannelProperty $state,
	): bool
	{
		if ($this->manager === null) {
			throw new Exceptions\NotImplemented('Channel properties state manager is not registered');
		}

		if ($property->getParent() !== null) {
			throw new Exceptions\InvalidState('Child property can\'t have state');
		}

		$result = $this->manager->delete($property, $state);

		$this->dispatcher?->dispatch(new Events\StateEntityDeleted($property));

		return $result;
	}

}
