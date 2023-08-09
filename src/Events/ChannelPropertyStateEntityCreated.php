<?php declare(strict_types = 1);

/**
 * ChannelPropertyStateEntityCreated.php
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

use FastyBird\Library\Metadata\Entities as MetadataEntities;
use FastyBird\Module\Devices\Entities;
use FastyBird\Module\Devices\States;
use Symfony\Contracts\EventDispatcher;

/**
 * Channel property state entity was created event
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Events
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class ChannelPropertyStateEntityCreated extends EventDispatcher\Event
{

	public function __construct(
		private readonly MetadataEntities\DevicesModule\ChannelDynamicProperty|Entities\Channels\Properties\Dynamic $property,
		private readonly States\ChannelProperty $state,
	)
	{
	}

	public function getProperty(): MetadataEntities\DevicesModule\ChannelDynamicProperty|Entities\Channels\Properties\Dynamic
	{
		return $this->property;
	}

	public function getState(): States\ChannelProperty
	{
		return $this->state;
	}

}