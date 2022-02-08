<?php declare(strict_types = 1);

/**
 * Constants.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     common
 * @since          0.1.0
 *
 * @date           18.03.20
 */

namespace FastyBird\DevicesModule;

use FastyBird\DevicesModule\Entities as DevicesModuleEntities;
use FastyBird\Metadata;

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
	public const ROUTE_NAME_DEVICE_CONTROLS = 'device.controls';
	public const ROUTE_NAME_DEVICE_CONTROL = 'device.control';
	public const ROUTE_NAME_DEVICE_CONTROL_RELATIONSHIP = 'device.control.relationship';
	public const ROUTE_NAME_CHANNELS = 'channels';
	public const ROUTE_NAME_CHANNEL = 'channel';
	public const ROUTE_NAME_CHANNEL_RELATIONSHIP = 'channel.relationship';
	public const ROUTE_NAME_CHANNEL_PROPERTIES = 'channel.properties';
	public const ROUTE_NAME_CHANNEL_PROPERTY = 'channel.property';
	public const ROUTE_NAME_CHANNEL_PROPERTY_RELATIONSHIP = 'channel.property.relationship';
	public const ROUTE_NAME_CHANNEL_CONTROLS = 'channel.controls';
	public const ROUTE_NAME_CHANNEL_CONTROL = 'channel.control';
	public const ROUTE_NAME_CHANNEL_CONTROL_RELATIONSHIP = 'channel.control.relationship';
	public const ROUTE_NAME_CONNECTORS = 'connectors';
	public const ROUTE_NAME_CONNECTOR = 'connector';
	public const ROUTE_NAME_CONNECTOR_RELATIONSHIP = 'connector.relationship';
	public const ROUTE_NAME_CONNECTOR_PROPERTIES = 'connector.properties';
	public const ROUTE_NAME_CONNECTOR_PROPERTY = 'connector.property';
	public const ROUTE_NAME_CONNECTOR_PROPERTY_RELATIONSHIP = 'connector.property.relationship';
	public const ROUTE_NAME_CONNECTOR_CONTROLS = 'connector.controls';
	public const ROUTE_NAME_CONNECTOR_CONTROL = 'connector.control';
	public const ROUTE_NAME_CONNECTOR_CONTROL_RELATIONSHIP = 'connector.control.relationship';

	/**
	 * Message bus routing keys mapping
	 */
	public const MESSAGE_BUS_CREATED_ENTITIES_ROUTING_KEYS_MAPPING = [
		DevicesModuleEntities\Devices\Device::class                 => Metadata\Constants::MESSAGE_BUS_DEVICES_CREATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Devices\Properties\Property::class    => Metadata\Constants::MESSAGE_BUS_DEVICES_PROPERTY_CREATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Devices\Controls\Control::class       => Metadata\Constants::MESSAGE_BUS_DEVICES_CONTROL_CREATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Channels\Channel::class               => Metadata\Constants::MESSAGE_BUS_CHANNELS_CREATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Channels\Properties\Property::class   => Metadata\Constants::MESSAGE_BUS_CHANNELS_PROPERTY_CREATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Channels\Controls\Control::class      => Metadata\Constants::MESSAGE_BUS_CHANNELS_CONTROL_CREATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Connectors\Connector::class           => Metadata\Constants::MESSAGE_BUS_CONNECTORS_CREATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Connectors\Properties\Property::class => Metadata\Constants::MESSAGE_BUS_CONNECTORS_PROPERTY_CREATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Connectors\Controls\Control::class    => Metadata\Constants::MESSAGE_BUS_CONNECTORS_CONTROL_CREATED_ENTITY_ROUTING_KEY,
	];

	public const MESSAGE_BUS_UPDATED_ENTITIES_ROUTING_KEYS_MAPPING = [
		DevicesModuleEntities\Devices\Device::class                 => Metadata\Constants::MESSAGE_BUS_DEVICES_UPDATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Devices\Properties\Property::class    => Metadata\Constants::MESSAGE_BUS_DEVICES_PROPERTY_UPDATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Devices\Controls\Control::class       => Metadata\Constants::MESSAGE_BUS_DEVICES_CONTROL_UPDATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Channels\Channel::class               => Metadata\Constants::MESSAGE_BUS_CHANNELS_UPDATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Channels\Properties\Property::class   => Metadata\Constants::MESSAGE_BUS_CHANNELS_PROPERTY_UPDATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Channels\Controls\Control::class      => Metadata\Constants::MESSAGE_BUS_CHANNELS_CONTROL_UPDATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Connectors\Connector::class           => Metadata\Constants::MESSAGE_BUS_CONNECTORS_UPDATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Connectors\Properties\Property::class => Metadata\Constants::MESSAGE_BUS_CONNECTORS_PROPERTY_UPDATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Connectors\Controls\Control::class    => Metadata\Constants::MESSAGE_BUS_CONNECTORS_CONTROL_UPDATED_ENTITY_ROUTING_KEY,
	];

	public const MESSAGE_BUS_DELETED_ENTITIES_ROUTING_KEYS_MAPPING = [
		DevicesModuleEntities\Devices\Device::class                 => Metadata\Constants::MESSAGE_BUS_DEVICES_DELETED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Devices\Properties\Property::class    => Metadata\Constants::MESSAGE_BUS_DEVICES_PROPERTY_DELETED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Devices\Controls\Control::class       => Metadata\Constants::MESSAGE_BUS_DEVICES_CONTROL_DELETED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Channels\Channel::class               => Metadata\Constants::MESSAGE_BUS_CHANNELS_DELETED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Channels\Properties\Property::class   => Metadata\Constants::MESSAGE_BUS_CHANNELS_PROPERTY_DELETED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Channels\Controls\Control::class      => Metadata\Constants::MESSAGE_BUS_CHANNELS_CONTROL_DELETED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Connectors\Connector::class           => Metadata\Constants::MESSAGE_BUS_CONNECTORS_DELETED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Connectors\Properties\Property::class => Metadata\Constants::MESSAGE_BUS_CONNECTORS_PROPERTY_DELETED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Connectors\Controls\Control::class    => Metadata\Constants::MESSAGE_BUS_CONNECTORS_CONTROL_DELETED_ENTITY_ROUTING_KEY,
	];

}
