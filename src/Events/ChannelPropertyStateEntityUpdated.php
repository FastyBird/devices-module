<?php declare(strict_types = 1);

/**
 * ChannelPropertyStateEntityUpdated.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Events
 * @since          1.0.0
 *
 * @date           29.07.23
 */

namespace FastyBird\Module\Devices\Events;

use FastyBird\Library\Metadata\Documents as MetadataDocuments;
use FastyBird\Module\Devices\States;
use Symfony\Contracts\EventDispatcher;

/**
 * Channel property state entity was updated event
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Events
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class ChannelPropertyStateEntityUpdated extends EventDispatcher\Event
{

	public function __construct(
		private readonly MetadataDocuments\DevicesModule\ChannelDynamicProperty $property,
		private readonly States\ChannelProperty $previousState,
		private readonly States\ChannelProperty $state,
	)
	{
	}

	public function getProperty(): MetadataDocuments\DevicesModule\ChannelDynamicProperty
	{
		return $this->property;
	}

	public function getPreviousState(): States\ChannelProperty
	{
		return $this->previousState;
	}

	public function getState(): States\ChannelProperty
	{
		return $this->state;
	}

}
