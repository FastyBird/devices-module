<?php declare(strict_types = 1);

/**
 * Routes.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Router
 * @since          0.1.0
 *
 * @date           13.03.20
 */

namespace FastyBird\DevicesModule\Router;

use FastyBird\DevicesModule;
use FastyBird\DevicesModule\Controllers;
use FastyBird\DevicesModule\Middleware;
use FastyBird\ModulesMetadata;
use FastyBird\SimpleAuth\Middleware as SimpleAuthMiddleware;
use FastyBird\WebServer\Router as WebServerRouter;
use IPub\SlimRouter\Routing;

/**
 * Module routes configuration
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Router
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class Routes implements WebServerRouter\IRoutes
{

	public const URL_ITEM_ID = 'id';

	public const URL_DEVICE_ID = 'device';
	public const URL_CHANNEL_ID = 'channel';

	public const RELATION_ENTITY = 'relationEntity';

	/** @var bool */
	private bool $usePrefix;

	/** @var Controllers\DevicesV1Controller */
	private Controllers\DevicesV1Controller $devicesV1Controller;

	/** @var Controllers\DeviceChildrenV1Controller */
	private Controllers\DeviceChildrenV1Controller $deviceChildrenV1Controller;

	/** @var Controllers\DevicePropertiesV1Controller */
	private Controllers\DevicePropertiesV1Controller $devicePropertiesV1Controller;

	/** @var Controllers\DeviceConfigurationV1Controller */
	private Controllers\DeviceConfigurationV1Controller $deviceConfigurationV1Controller;

	/** @var Controllers\DeviceConnectorV1Controller */
	private Controllers\DeviceConnectorV1Controller $deviceConnectorV1Controller;

	/** @var Controllers\ChannelsV1Controller */
	private Controllers\ChannelsV1Controller $channelsV1Controller;

	/** @var Controllers\ChannelPropertiesV1Controller */
	private Controllers\ChannelPropertiesV1Controller $channelPropertiesV1Controller;

	/** @var Controllers\ChannelConfigurationV1Controller */
	private Controllers\ChannelConfigurationV1Controller $channelConfigurationV1Controller;

	/** @var Controllers\ConnectorsV1Controller */
	private Controllers\ConnectorsV1Controller $connectorsV1Controller;

	/** @var Middleware\AccessMiddleware */
	private Middleware\AccessMiddleware $devicesAccessControlMiddleware;

	/** @var SimpleAuthMiddleware\AccessMiddleware */
	private SimpleAuthMiddleware\AccessMiddleware $accessControlMiddleware;

	/** @var SimpleAuthMiddleware\UserMiddleware */
	private SimpleAuthMiddleware\UserMiddleware $userMiddleware;

	public function __construct(
		bool $usePrefix,
		Controllers\DevicesV1Controller $devicesV1Controller,
		Controllers\DeviceChildrenV1Controller $deviceChildrenV1Controller,
		Controllers\DevicePropertiesV1Controller $devicePropertiesV1Controller,
		Controllers\DeviceConfigurationV1Controller $deviceConfigurationV1Controller,
		Controllers\DeviceConnectorV1Controller $deviceConnectorV1Controller,
		Controllers\ChannelsV1Controller $channelsV1Controller,
		Controllers\ChannelPropertiesV1Controller $channelPropertiesV1Controller,
		Controllers\ChannelConfigurationV1Controller $channelConfigurationV1Controller,
		Controllers\ConnectorsV1Controller $connectorsV1Controller,
		Middleware\AccessMiddleware $devicesAccessControlMiddleware,
		SimpleAuthMiddleware\AccessMiddleware $accessControlMiddleware,
		SimpleAuthMiddleware\UserMiddleware $userMiddleware
	) {
		$this->usePrefix = $usePrefix;

		$this->devicesV1Controller = $devicesV1Controller;
		$this->deviceChildrenV1Controller = $deviceChildrenV1Controller;
		$this->devicePropertiesV1Controller = $devicePropertiesV1Controller;
		$this->deviceConfigurationV1Controller = $deviceConfigurationV1Controller;
		$this->deviceConnectorV1Controller = $deviceConnectorV1Controller;
		$this->channelsV1Controller = $channelsV1Controller;
		$this->channelPropertiesV1Controller = $channelPropertiesV1Controller;
		$this->channelConfigurationV1Controller = $channelConfigurationV1Controller;
		$this->connectorsV1Controller = $connectorsV1Controller;

		$this->devicesAccessControlMiddleware = $devicesAccessControlMiddleware;
		$this->accessControlMiddleware = $accessControlMiddleware;
		$this->userMiddleware = $userMiddleware;
	}

	/**
	 * @param Routing\IRouter $router
	 *
	 * @return void
	 */
	public function registerRoutes(Routing\IRouter $router): void
	{
		if ($this->usePrefix) {
			$routes = $router->group('/' . ModulesMetadata\Constants::MODULE_DEVICES_PREFIX, function (Routing\RouteCollector $group): void {
				$this->buildRoutes($group);
			});

		} else {
			$routes = $this->buildRoutes($router);
		}

		$routes->addMiddleware($this->accessControlMiddleware);
		$routes->addMiddleware($this->userMiddleware);
		$routes->addMiddleware($this->devicesAccessControlMiddleware);
	}

	/**
	 * @param Routing\IRouter | Routing\IRouteCollector $group
	 *
	 * @return Routing\IRouteGroup
	 */
	private function buildRoutes($group): Routing\IRouteGroup
	{
		return $group->group('/v1', function (Routing\RouteCollector $group): void {
			$group->group('/devices', function (Routing\RouteCollector $group): void {
				/**
				 * DEVICES
				 */
				$route = $group->get('', [$this->devicesV1Controller, 'index']);
				$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICES);

				$route = $group->get('/{' . self::URL_ITEM_ID . '}', [$this->devicesV1Controller, 'read']);
				$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICE);

				$group->post('', [$this->devicesV1Controller, 'create']);

				$group->patch('/{' . self::URL_ITEM_ID . '}', [$this->devicesV1Controller, 'update']);

				$group->delete('/{' . self::URL_ITEM_ID . '}', [$this->devicesV1Controller, 'delete']);

				$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [
					$this->devicesV1Controller,
					'readRelationship',
				]);
				$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICE_RELATIONSHIP);
			});

			$group->group('/devices/{' . self::URL_DEVICE_ID . '}', function (Routing\RouteCollector $group): void {
				/**
				 * CHILDREN
				 */
				$route = $group->get('/children', [$this->deviceChildrenV1Controller, 'index']);
				$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICE_CHILDREN);

				/**
				 * DEVICE PROPERTIES
				 */
				$route = $group->get('/properties', [$this->devicePropertiesV1Controller, 'index']);
				$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICE_PROPERTIES);

				$route = $group->get('/properties/{' . self::URL_ITEM_ID . '}', [
					$this->devicePropertiesV1Controller,
					'read',
				]);
				$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICE_PROPERTY);

				$route = $group->get('/properties/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [
					$this->devicePropertiesV1Controller,
					'readRelationship',
				]);
				$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICE_PROPERTY_RELATIONSHIP);

				/**
				 * DEVICE CONFIGURATION
				 */
				$route = $group->get('/configuration', [$this->deviceConfigurationV1Controller, 'index']);
				$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICE_CONFIGURATION_ROWS);

				$route = $group->get('/configuration/{' . self::URL_ITEM_ID . '}', [
					$this->deviceConfigurationV1Controller,
					'read',
				]);
				$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICE_CONFIGURATION_ROW);

				$route = $group->get('/configuration/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [
					$this->deviceConfigurationV1Controller,
					'readRelationship',
				]);
				$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICE_CONFIGURATION_ROW_RELATIONSHIP);

				/**
				 * DEVICE CONNECTOR
				 */
				$route = $group->get('/connector', [$this->deviceConnectorV1Controller, 'read']);
				$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICE_CONNECTOR);

				$route = $group->get('/connector/relationships/{' . self::RELATION_ENTITY . '}', [
					$this->deviceConnectorV1Controller,
					'readRelationship',
				]);
				$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICE_CONNECTOR_RELATIONSHIP);

				$group->post('/connector', [$this->deviceConnectorV1Controller, 'create']);

				$group->patch('/connector', [$this->deviceConnectorV1Controller, 'update']);

				$group->group('/channels', function (Routing\RouteCollector $group): void {
					/**
					 * CHANNELS
					 */
					$route = $group->get('', [$this->channelsV1Controller, 'index']);
					$route->setName(DevicesModule\Constants::ROUTE_NAME_CHANNELS);

					$route = $group->get('/{' . self::URL_ITEM_ID . '}', [$this->channelsV1Controller, 'read']);
					$route->setName(DevicesModule\Constants::ROUTE_NAME_CHANNEL);

					$group->patch('/{' . self::URL_ITEM_ID . '}', [$this->channelsV1Controller, 'update']);

					$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [
						$this->channelsV1Controller,
						'readRelationship',
					]);
					$route->setName(DevicesModule\Constants::ROUTE_NAME_CHANNEL_RELATIONSHIP);

					$group->group('/{' . self::URL_CHANNEL_ID . '}', function (Routing\RouteCollector $group): void {
						/**
						 * CHANNEL PROPERTIES
						 */
						$route = $group->get('/properties', [$this->channelPropertiesV1Controller, 'index']);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_CHANNEL_PROPERTIES);

						$route = $group->get('/properties/{' . self::URL_ITEM_ID . '}', [
							$this->channelPropertiesV1Controller,
							'read',
						]);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_CHANNEL_PROPERTY);

						$route = $group->get('/properties/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [
							$this->channelPropertiesV1Controller,
							'readRelationship',
						]);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_CHANNEL_PROPERTY_RELATIONSHIP);

						/**
						 * CHANNEL CONFIGURATION
						 */
						$route = $group->get('/configuration', [$this->channelConfigurationV1Controller, 'index']);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_CHANNEL_CONFIGURATION_ROWS);

						$route = $group->get('/configuration/{' . self::URL_ITEM_ID . '}', [
							$this->channelConfigurationV1Controller,
							'read',
						]);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_CHANNEL_CONFIGURATION_ROW);

						$route = $group->get('/configuration/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [
							$this->channelConfigurationV1Controller,
							'readRelationship',
						]);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_CHANNEL_CONFIGURATION_ROW_RELATIONSHIP);
					});
				});
			});

			$group->group('/connectors', function (Routing\RouteCollector $group): void {
				/**
				 * CONNECTORS
				 */
				$route = $group->get('', [$this->connectorsV1Controller, 'index']);
				$route->setName(DevicesModule\Constants::ROUTE_NAME_CONNECTORS);

				$route = $group->get('/{' . self::URL_ITEM_ID . '}', [$this->connectorsV1Controller, 'read']);
				$route->setName(DevicesModule\Constants::ROUTE_NAME_CONNECTOR);

				$group->patch('/{' . self::URL_ITEM_ID . '}', [$this->connectorsV1Controller, 'update']);

				$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [
					$this->connectorsV1Controller,
					'readRelationship',
				]);
				$route->setName(DevicesModule\Constants::ROUTE_NAME_CONNECTOR_RELATIONSHIP);
			});
		});
	}

}
