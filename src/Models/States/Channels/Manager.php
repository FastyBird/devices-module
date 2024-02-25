<?php declare(strict_types = 1);

/**
 * Manager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          1.0.0
 *
 * @date           08.02.22
 */

namespace FastyBird\Module\Devices\Models\States\Channels;

use DateTimeInterface;
use FastyBird\Module\Devices\Documents;
use FastyBird\Module\Devices\Exceptions;
use FastyBird\Module\Devices\Models;
use FastyBird\Module\Devices\States;
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
final class Manager
{

	use Nette\SmartObject;

	public function __construct(private readonly IManager|null $manager = null)
	{
	}

	/**
	 * @throws Exceptions\NotImplemented
	 *
	 * @interal
	 */
	public function create(
		Documents\Channels\Properties\Dynamic $property,
		Utils\ArrayHash $values,
	): States\ChannelProperty
	{
		if ($this->manager === null) {
			throw new Exceptions\NotImplemented('Channel properties state manager is not registered');
		}

		return $this->manager->create($property->getId(), $values);
	}

	/**
	 * @throws Exceptions\NotImplemented
	 *
	 * @interal
	 */
	public function update(
		Documents\Channels\Properties\Dynamic $property,
		States\ChannelProperty $state,
		Utils\ArrayHash $values,
	): States\ChannelProperty|false
	{
		if ($this->manager === null) {
			throw new Exceptions\NotImplemented('Channel properties state manager is not registered');
		}

		$result = $this->manager->update($property->getId(), $values);

		if ($result === false) {
			return false;
		}

		if (
			[
				States\Property::ACTUAL_VALUE_FIELD => $state->getActualValue(),
				States\Property::EXPECTED_VALUE_FIELD => $state->getExpectedValue(),
				States\Property::PENDING_FIELD => $state->getPending() instanceof DateTimeInterface
					? $state->getPending()->format(DateTimeInterface::ATOM)
					: $state->getPending(),
				States\Property::VALID_FIELD => $state->isValid(),
			] !== [
				States\Property::ACTUAL_VALUE_FIELD => $result->getActualValue(),
				States\Property::EXPECTED_VALUE_FIELD => $result->getExpectedValue(),
				States\Property::PENDING_FIELD => $result->getPending() instanceof DateTimeInterface
					? $result->getPending()->format(DateTimeInterface::ATOM)
					: $result->getPending(),
				States\Property::VALID_FIELD => $result->isValid(),
			]
		) {
			return $result;
		}

		return false;
	}

	/**
	 * @throws Exceptions\NotImplemented
	 *
	 * @interal
	 */
	public function delete(Uuid\UuidInterface $id): bool
	{
		if ($this->manager === null) {
			throw new Exceptions\NotImplemented('Channel properties state manager is not registered');
		}

		return $this->manager->delete($id);
	}

}
