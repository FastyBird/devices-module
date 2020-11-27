<?php declare(strict_types = 1);

/**
 * IProperty.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           02.11.18
 */

namespace FastyBird\DevicesModule\Entities\Devices\Properties;

use FastyBird\DevicesModule\Entities;

/**
 * Device property entity interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IProperty extends Entities\IProperty
{

	/**
	 * System known properties
	 */
	public const PROPERTY_IP_ADDRESS = 'ip-address';
	public const PROPERTY_STATUS_LED = 'status-led';
	public const PROPERTY_INTERVAL = 'interval';
	public const PROPERTY_UPTIME = 'uptime';
	public const PROPERTY_FREE_HEAP = 'free-heap';
	public const PROPERTY_CPU_LOAD = 'cpu-load';
	public const PROPERTY_VCC = 'vcc';
	public const PROPERTY_SSID = 'ssid';
	public const PROPERTY_RSSI = 'rssi';

	/**
	 * @return Entities\Devices\IDevice
	 */
	public function getDevice(): Entities\Devices\IDevice;

}
