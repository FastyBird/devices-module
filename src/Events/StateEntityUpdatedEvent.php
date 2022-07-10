<?php declare(strict_types = 1);

/**
 * StateEntityUpdatedEvent.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Events
 * @since          0.65.0
 *
 * @date           22.06.22
 */

namespace FastyBird\DevicesModule\Events;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\States;
use FastyBird\Metadata\Entities as MetadataEntities;
use Symfony\Contracts\EventDispatcher;

/**
 * State entity was updated event
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Events
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class StateEntityUpdatedEvent extends EventDispatcher\Event
{

	private MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity|Entities\IProperty $property;

	private States\IDeviceProperty|States\IChannelProperty|States\IConnectorProperty $previousState;

	private States\IDeviceProperty|States\IChannelProperty|States\IConnectorProperty $state;

	public function __construct(
		MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity|Entities\IProperty $property,
		States\IConnectorProperty|States\IChannelProperty|States\IDeviceProperty $previousState,
		States\IConnectorProperty|States\IChannelProperty|States\IDeviceProperty $state
	) {
		$this->property = $property;
		$this->previousState = $previousState;
		$this->state = $state;
	}

	public function getProperty(): MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity|Entities\IProperty
	{
		return $this->property;
	}

	public function getPreviousState(): States\IConnectorProperty|States\IChannelProperty|States\IDeviceProperty
	{
		return $this->previousState;
	}

	public function getState(): States\IConnectorProperty|States\IChannelProperty|States\IDeviceProperty
	{
		return $this->state;
	}

}
