<?php declare(strict_types = 1);

/**
 * TPropertyMessageConsumer.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Consumers
 * @since          0.1.0
 *
 * @date           07.08.20
 */

namespace FastyBird\DevicesModule\Consumers;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Helpers;
use FastyBird\DevicesModule\States;
use Nette\Utils;

/**
 * Property message consumer
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Consumers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @property-read Helpers\PropertyHelper $propertyHelper
 */
trait TPropertyMessageConsumer
{

	/**
	 * @param Entities\IProperty $property
	 * @param States\IProperty $state
	 * @param Utils\ArrayHash $message
	 *
	 * @return mixed[]
	 */
	protected function handlePropertyState(
		Entities\IProperty $property,
		States\IProperty $state,
		Utils\ArrayHash $message
	): array {
		$message->offsetSet('pending', false);

		$message->offsetSet('value', $this->propertyHelper->normalizeValue($property, $state->getValue()));

		// Expected value is same as value stored in device
		if ($this->propertyHelper->normalizeValue($property, $state->getValue()) === $this->propertyHelper->normalizeValue($property, $message->offsetGet('expected'))) {
			$message->offsetSet('pending', false);
			$message->offsetSet('expected', null);

		} else {
			$message->offsetSet('pending', true);
		}

		return [
			'value'    => $this->propertyHelper->normalizeValue($property, $message->offsetGet('value')),
			'expected' => $this->propertyHelper->normalizeValue($property, $message->offsetGet('expected')),
			'pending'  => (bool) $message->offsetGet('pending'),
		];
	}

}
