<?php declare(strict_types = 1);

/**
 * Constants.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     common
 * @since          0.1.0
 *
 * @date           18.03.20
 */

namespace FastyBird\DevicesModule;

/**
 * Service constants
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     common
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Constants
{

	/**
	 * Module routing
	 */

	public const ROUTE_NAME_DEVICES = 'devices';
	public const ROUTE_NAME_DEVICE = 'device';
	public const ROUTE_NAME_DEVICE_RELATIONSHIP = 'device.relationship';
	public const ROUTE_NAME_DEVICE_CHILDREN = 'device.children';
	public const ROUTE_NAME_DEVICE_PROPERTIES = 'device.properties';
	public const ROUTE_NAME_DEVICE_PROPERTY = 'device.property';
	public const ROUTE_NAME_DEVICE_PROPERTY_RELATIONSHIP = 'device.property.relationship';
	public const ROUTE_NAME_DEVICE_CONFIGURATION_ROWS = 'device.configuration.rows';
	public const ROUTE_NAME_DEVICE_CONFIGURATION_ROW = 'device.configuration.row';
	public const ROUTE_NAME_DEVICE_CONFIGURATION_ROW_RELATIONSHIP = 'device.configuration.row.relationship';
	public const ROUTE_NAME_DEVICE_HARDWARE = 'device.hardware';
	public const ROUTE_NAME_DEVICE_HARDWARE_RELATIONSHIP = 'device.hardware.relationship';
	public const ROUTE_NAME_DEVICE_FIRMWARE = 'device.firmware';
	public const ROUTE_NAME_DEVICE_FIRMWARE_RELATIONSHIP = 'device.firmware.relationship';
	public const ROUTE_NAME_DEVICE_CREDENTIALS = 'device.credentials';
	public const ROUTE_NAME_DEVICE_CREDENTIALS_RELATIONSHIP = 'device.credentials.relationship';
	public const ROUTE_NAME_CHANNELS = 'channels';
	public const ROUTE_NAME_CHANNEL = 'channel';
	public const ROUTE_NAME_CHANNEL_RELATIONSHIP = 'channel.relationship';
	public const ROUTE_NAME_CHANNEL_PROPERTIES = 'channel.properties';
	public const ROUTE_NAME_CHANNEL_PROPERTY = 'channel.property';
	public const ROUTE_NAME_CHANNEL_PROPERTY_RELATIONSHIP = 'channel.property.relationship';
	public const ROUTE_NAME_CHANNEL_CONFIGURATION_ROWS = 'channel.configuration.rows';
	public const ROUTE_NAME_CHANNEL_CONFIGURATION_ROW = 'channel.configuration.row';
	public const ROUTE_NAME_CHANNEL_CONFIGURATION_ROW_RELATIONSHIP = 'channel.configuration.row.relationship';

	/**
	 * Data types
	 */
	public const DATA_TYPE_BOOLEAN = 'boolean';
	public const DATA_TYPE_NUMBER = 'number';
	public const DATA_TYPE_SELECT = 'select';
	public const DATA_TYPE_TEXT = 'text';

	/**
	 * Control actions
	 */
	public const CONTROL_CONFIG = 'configure';
	public const CONTROL_RESET = 'reset';
	public const CONTROL_RECONNECT = 'reconnect';
	public const CONTROL_FACTORY_RESET = 'factory-reset';
	public const CONTROL_OTA = 'ota';

}
