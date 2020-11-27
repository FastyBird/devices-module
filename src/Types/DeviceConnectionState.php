<?php declare(strict_types = 1);

/**
 * DeviceConnectionState.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Types
 * @since          0.1.0
 *
 * @date           25.03.18
 */

namespace FastyBird\DevicesModule\Types;

use Consistence;

/**
 * Device connection state types
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Types
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class DeviceConnectionState extends Consistence\Enum\Enum
{

	/**
	 * Define device states
	 */
	public const STATE_CONNECTED = 'connected';
	public const STATE_DISCONNECTED = 'disconnected';
	public const STATE_INIT = 'init';
	public const STATE_READY = 'ready';
	public const STATE_SLEEPING = 'sleeping';
	public const STATE_LOST = 'lost';
	public const STATE_ALERT = 'alert';
	public const STATE_UNKNOWN = 'unknown';

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return (string) self::getValue();
	}

}
