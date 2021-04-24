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

use FastyBird\DevicesModule\Entities as DevicesModuleEntities;
use FastyBird\ModulesMetadata;

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
	public const ROUTE_NAME_DEVICE_CONNECTOR = 'device.connector';
	public const ROUTE_NAME_DEVICE_CONNECTOR_RELATIONSHIP = 'device.connector.relationship';
	public const ROUTE_NAME_CHANNELS = 'channels';
	public const ROUTE_NAME_CHANNEL = 'channel';
	public const ROUTE_NAME_CHANNEL_RELATIONSHIP = 'channel.relationship';
	public const ROUTE_NAME_CHANNEL_PROPERTIES = 'channel.properties';
	public const ROUTE_NAME_CHANNEL_PROPERTY = 'channel.property';
	public const ROUTE_NAME_CHANNEL_PROPERTY_RELATIONSHIP = 'channel.property.relationship';
	public const ROUTE_NAME_CHANNEL_CONFIGURATION_ROWS = 'channel.configuration.rows';
	public const ROUTE_NAME_CHANNEL_CONFIGURATION_ROW = 'channel.configuration.row';
	public const ROUTE_NAME_CHANNEL_CONFIGURATION_ROW_RELATIONSHIP = 'channel.configuration.row.relationship';
	public const ROUTE_NAME_CONNECTORS = 'connectors';
	public const ROUTE_NAME_CONNECTOR = 'connector';
	public const ROUTE_NAME_CONNECTOR_RELATIONSHIP = 'connector.relationship';

	/**
	 * Message bus routing keys mapping
	 */
	public const MESSAGE_BUS_CREATED_ENTITIES_ROUTING_KEYS_MAPPING = [
		DevicesModuleEntities\Devices\Device::class               => ModulesMetadata\Constants::MESSAGE_BUS_DEVICES_CREATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Devices\Properties\Property::class  => ModulesMetadata\Constants::MESSAGE_BUS_DEVICES_PROPERTY_CREATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Devices\Configuration\Row::class    => ModulesMetadata\Constants::MESSAGE_BUS_DEVICES_CONFIGURATION_CREATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Devices\Connectors\Connector::class => ModulesMetadata\Constants::MESSAGE_BUS_DEVICES_CONNECTOR_CREATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Channels\Channel::class             => ModulesMetadata\Constants::MESSAGE_BUS_CHANNELS_CREATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Channels\Properties\Property::class => ModulesMetadata\Constants::MESSAGE_BUS_CHANNELS_PROPERTY_CREATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Channels\Configuration\Row::class   => ModulesMetadata\Constants::MESSAGE_BUS_CHANNELS_CONFIGURATION_CREATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Connectors\Connector::class         => ModulesMetadata\Constants::MESSAGE_BUS_CONNECTOR_CREATED_ENTITY_ROUTING_KEY,
	];

	public const MESSAGE_BUS_UPDATED_ENTITIES_ROUTING_KEYS_MAPPING = [
		DevicesModuleEntities\Devices\Device::class               => ModulesMetadata\Constants::MESSAGE_BUS_DEVICES_UPDATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Devices\Properties\Property::class  => ModulesMetadata\Constants::MESSAGE_BUS_DEVICES_PROPERTY_UPDATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Devices\Configuration\Row::class    => ModulesMetadata\Constants::MESSAGE_BUS_DEVICES_CONFIGURATION_UPDATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Devices\Connectors\Connector::class => ModulesMetadata\Constants::MESSAGE_BUS_DEVICES_CONNECTOR_UPDATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Channels\Channel::class             => ModulesMetadata\Constants::MESSAGE_BUS_CHANNELS_UPDATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Channels\Properties\Property::class => ModulesMetadata\Constants::MESSAGE_BUS_CHANNELS_PROPERTY_UPDATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Channels\Configuration\Row::class   => ModulesMetadata\Constants::MESSAGE_BUS_CHANNELS_CONFIGURATION_UPDATED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Connectors\Connector::class         => ModulesMetadata\Constants::MESSAGE_BUS_CONNECTOR_UPDATED_ENTITY_ROUTING_KEY,
	];

	public const MESSAGE_BUS_DELETED_ENTITIES_ROUTING_KEYS_MAPPING = [
		DevicesModuleEntities\Devices\Device::class               => ModulesMetadata\Constants::MESSAGE_BUS_DEVICES_DELETED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Devices\Properties\Property::class  => ModulesMetadata\Constants::MESSAGE_BUS_DEVICES_PROPERTY_DELETED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Devices\Configuration\Row::class    => ModulesMetadata\Constants::MESSAGE_BUS_DEVICES_CONFIGURATION_DELETED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Devices\Connectors\Connector::class => ModulesMetadata\Constants::MESSAGE_BUS_DEVICES_CONNECTOR_DELETED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Channels\Channel::class             => ModulesMetadata\Constants::MESSAGE_BUS_CHANNELS_DELETED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Channels\Properties\Property::class => ModulesMetadata\Constants::MESSAGE_BUS_CHANNELS_PROPERTY_DELETED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Channels\Configuration\Row::class   => ModulesMetadata\Constants::MESSAGE_BUS_CHANNELS_CONFIGURATION_DELETED_ENTITY_ROUTING_KEY,
		DevicesModuleEntities\Connectors\Connector::class         => ModulesMetadata\Constants::MESSAGE_BUS_CONNECTOR_DELETED_ENTITY_ROUTING_KEY,
	];

}
