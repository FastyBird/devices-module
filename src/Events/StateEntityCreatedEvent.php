<?php declare(strict_types = 1);

/**
 * StateEntityCreatedEvent.php
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

use FastyBird\DevicesModule\States;
use Symfony\Contracts\EventDispatcher;

/**
 * State entity was created event
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Events
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class StateEntityCreatedEvent extends EventDispatcher\Event
{

	/** @var States\IChannelProperty|States\IDeviceProperty|States\IConnectorProperty */
	private $state;

	/**
	 * @param States\IChannelProperty|States\IDeviceProperty|States\IConnectorProperty $state
	 */
	public function __construct(
		$state
	) {
		$this->state = $state;
	}

	/**
	 * @return States\IChannelProperty|States\IConnectorProperty|States\IDeviceProperty
	 */
	public function getState()
	{
		return $this->state;
	}

}
