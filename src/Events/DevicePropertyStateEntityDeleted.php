<?php declare(strict_types = 1);

/**
 * DevicePropertyStateEntityDeleted.php
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
use Symfony\Contracts\EventDispatcher;

/**
 * Device property state entity was deleted event
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Events
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class DevicePropertyStateEntityDeleted extends EventDispatcher\Event
{

	public function __construct(
		private readonly MetadataEntities\DevicesModule\DeviceDynamicProperty|Entities\Devices\Properties\Dynamic $property,
	)
	{
	}

	public function getProperty(): MetadataEntities\DevicesModule\DeviceDynamicProperty|Entities\Devices\Properties\Dynamic
	{
		return $this->property;
	}

}
