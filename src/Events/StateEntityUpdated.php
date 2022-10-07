<?php declare(strict_types = 1);

/**
 * StateEntityUpdated.php
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
class StateEntityUpdated extends EventDispatcher\Event
{

	/** @var MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity|Entities\Property */
	private MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity|Entities\Property $property;

	/** @var States\DeviceProperty|States\ChannelProperty|States\ConnectorProperty */
	private States\DeviceProperty|States\ChannelProperty|States\ConnectorProperty $previousState;

	/** @var States\DeviceProperty|States\ChannelProperty|States\ConnectorProperty */
	private States\DeviceProperty|States\ChannelProperty|States\ConnectorProperty $state;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity|Entities\Property $property
	 * @param States\ConnectorProperty|States\ChannelProperty|States\DeviceProperty $previousState
	 * @param States\ConnectorProperty|States\ChannelProperty|States\DeviceProperty $state
	 */
	public function __construct(
		MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity|Entities\Property $property,
		States\ConnectorProperty|States\ChannelProperty|States\DeviceProperty $previousState,
		States\ConnectorProperty|States\ChannelProperty|States\DeviceProperty $state
	) {
		$this->property = $property;
		$this->previousState = $previousState;
		$this->state = $state;
	}

	/**
	 * @return MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity|Entities\Property
	 */
	public function getProperty(): MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity|Entities\Property
	{
		return $this->property;
	}

	/**
	 * @return States\ConnectorProperty|States\ChannelProperty|States\DeviceProperty
	 */
	public function getPreviousState(): States\ConnectorProperty|States\ChannelProperty|States\DeviceProperty
	{
		return $this->previousState;
	}

	/**
	 * @return States\ConnectorProperty|States\ChannelProperty|States\DeviceProperty
	 */
	public function getState(): States\ConnectorProperty|States\ChannelProperty|States\DeviceProperty
	{
		return $this->state;
	}

}
