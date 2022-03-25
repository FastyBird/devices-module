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
use FastyBird\Metadata;
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
	public const URL_CONNECTOR_ID = 'connector';
	public const URL_PROPERTY_ID = 'property';

	public const RELATION_ENTITY = 'relationEntity';

	/** @var bool */
	private bool $usePrefix;

	/** @var Controllers\DevicesV1Controller */
	private Controllers\DevicesV1Controller $devicesV1Controller;

	/** @var Controllers\DeviceParentsV1Controller */
	private Controllers\DeviceParentsV1Controller $deviceParentsV1Controller;

	/** @var Controllers\DeviceChildrenV1Controller */
	private Controllers\DeviceChildrenV1Controller $deviceChildrenV1Controller;

	/** @var Controllers\DevicePropertiesV1Controller */
	private Controllers\DevicePropertiesV1Controller $devicePropertiesV1Controller;

	/** @var Controllers\DevicePropertyChildrenV1Controller */
	private Controllers\DevicePropertyChildrenV1Controller $devicePropertyChildrenV1Controller;

	/** @var Controllers\DeviceControlsV1Controller */
	private Controllers\DeviceControlsV1Controller $deviceControlsV1Controller;

	/** @var Controllers\ChannelsV1Controller */
	private Controllers\ChannelsV1Controller $channelsV1Controller;

	/** @var Controllers\ChannelPropertiesV1Controller */
	private Controllers\ChannelPropertiesV1Controller $channelPropertiesV1Controller;

	/** @var Controllers\ChannelPropertyChildrenV1Controller */
	private Controllers\ChannelPropertyChildrenV1Controller $channelPropertyChildrenV1Controller;

	/** @var Controllers\ChannelControlsV1Controller */
	private Controllers\ChannelControlsV1Controller $channelControlsV1Controller;

	/** @var Controllers\ConnectorsV1Controller */
	private Controllers\ConnectorsV1Controller $connectorsV1Controller;

	/** @var Controllers\ConnectorPropertiesV1Controller */
	private Controllers\ConnectorPropertiesV1Controller $connectorPropertiesV1Controller;

	/** @var Controllers\ConnectorControlsV1Controller */
	private Controllers\ConnectorControlsV1Controller $connectorControlsV1Controller;

	/** @var Middleware\AccessMiddleware */
	private Middleware\AccessMiddleware $devicesAccessControlMiddleware;

	/** @var SimpleAuthMiddleware\AccessMiddleware */
	private SimpleAuthMiddleware\AccessMiddleware $accessControlMiddleware;

	/** @var SimpleAuthMiddleware\UserMiddleware */
	private SimpleAuthMiddleware\UserMiddleware $userMiddleware;

	public function __construct(
		bool $usePrefix,
		Controllers\DevicesV1Controller $devicesV1Controller,
		Controllers\DeviceParentsV1Controller $deviceParentsV1Controller,
		Controllers\DeviceChildrenV1Controller $deviceChildrenV1Controller,
		Controllers\DevicePropertiesV1Controller $devicePropertiesV1Controller,
		Controllers\DevicePropertyChildrenV1Controller $devicePropertyChildrenV1Controller,
		Controllers\DeviceControlsV1Controller $deviceControlsV1Controller,
		Controllers\ChannelsV1Controller $channelsV1Controller,
		Controllers\ChannelPropertiesV1Controller $channelPropertiesV1Controller,
		Controllers\ChannelPropertyChildrenV1Controller $channelPropertyChildrenV1Controller,
		Controllers\ChannelControlsV1Controller $channelControlsV1Controller,
		Controllers\ConnectorsV1Controller $connectorsV1Controller,
		Controllers\ConnectorPropertiesV1Controller $connectorPropertiesV1Controller,
		Controllers\ConnectorControlsV1Controller $connectorControlsV1Controller,
		Middleware\AccessMiddleware $devicesAccessControlMiddleware,
		SimpleAuthMiddleware\AccessMiddleware $accessControlMiddleware,
		SimpleAuthMiddleware\UserMiddleware $userMiddleware
	) {
		$this->usePrefix = $usePrefix;

		$this->devicesV1Controller = $devicesV1Controller;
		$this->deviceParentsV1Controller = $deviceParentsV1Controller;
		$this->deviceChildrenV1Controller = $deviceChildrenV1Controller;
		$this->devicePropertiesV1Controller = $devicePropertiesV1Controller;
		$this->devicePropertyChildrenV1Controller = $devicePropertyChildrenV1Controller;
		$this->deviceControlsV1Controller = $deviceControlsV1Controller;
		$this->channelsV1Controller = $channelsV1Controller;
		$this->channelPropertiesV1Controller = $channelPropertiesV1Controller;
		$this->channelPropertyChildrenV1Controller = $channelPropertyChildrenV1Controller;
		$this->channelControlsV1Controller = $channelControlsV1Controller;
		$this->connectorsV1Controller = $connectorsV1Controller;
		$this->connectorPropertiesV1Controller = $connectorPropertiesV1Controller;
		$this->connectorControlsV1Controller = $connectorControlsV1Controller;

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
			$routes = $router->group('/' . Metadata\Constants::MODULE_DEVICES_PREFIX, function (
				Routing\RouteCollector $group
			): void {
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
	 * @param Routing\IRouter|Routing\IRouteCollector $group
	 *
	 * @return Routing\IRouteGroup
	 */
	private function buildRoutes($group): Routing\IRouteGroup
	{
		return $group->group('/v1', function (Routing\RouteCollector $group): void {
			/**
			 * DEVICES
			 */
			$group->group('/devices', function (Routing\RouteCollector $group): void {
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

				$group->group('/{' . self::URL_DEVICE_ID . '}', function (Routing\RouteCollector $group): void {
					/**
					 * PARENTS
					 */
					$route = $group->get('/parents', [$this->deviceParentsV1Controller, 'index']);
					$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICE_PARENTS);

					/**
					 * CHILDREN
					 */
					$route = $group->get('/children', [$this->deviceChildrenV1Controller, 'index']);
					$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICE_CHILDREN);

					/**
					 * DEVICE PROPERTIES
					 */
					$group->group('/properties', function (Routing\RouteCollector $group): void {
						$route = $group->get('', [$this->devicePropertiesV1Controller, 'index']);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICE_PROPERTIES);

						$route = $group->get('/{' . self::URL_ITEM_ID . '}', [
							$this->devicePropertiesV1Controller,
							'read',
						]);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICE_PROPERTY);

						$group->post('', [$this->devicePropertiesV1Controller, 'create']);

						$group->patch('/{' . self::URL_ITEM_ID . '}', [$this->devicePropertiesV1Controller, 'update']);

						$group->delete('/{' . self::URL_ITEM_ID . '}', [$this->devicePropertiesV1Controller, 'delete']);

						$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [
							$this->devicePropertiesV1Controller,
							'readRelationship',
						]);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICE_PROPERTY_RELATIONSHIP);

						$group->group('/{' . self::URL_PROPERTY_ID . '}', function (Routing\RouteCollector $group): void {
							/**
							 * CHILDREN
							 */
							$route = $group->get('/children', [$this->devicePropertyChildrenV1Controller, 'index']);
							$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICE_PROPERTY_CHILDREN);
						});
					});

					/**
					 * DEVICE CONTROLS
					 */
					$group->group('/controls', function (Routing\RouteCollector $group): void {
						$route = $group->get('', [$this->deviceControlsV1Controller, 'index']);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICE_CONTROLS);

						$route = $group->get('/{' . self::URL_ITEM_ID . '}', [
							$this->deviceControlsV1Controller,
							'read',
						]);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICE_CONTROL);

						$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [
							$this->deviceControlsV1Controller,
							'readRelationship',
						]);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_DEVICE_CONTROL_RELATIONSHIP);
					});

					/**
					 * CHANNELS
					 */
					$group->group('/channels', function (Routing\RouteCollector $group): void {
						$route = $group->get('', [$this->channelsV1Controller, 'index']);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_CHANNELS);

						$route = $group->get('/{' . self::URL_ITEM_ID . '}', [$this->channelsV1Controller, 'read']);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_CHANNEL);

						$group->post('', [$this->channelsV1Controller, 'create']);

						$group->patch('/{' . self::URL_ITEM_ID . '}', [$this->channelsV1Controller, 'update']);

						$group->delete('/{' . self::URL_ITEM_ID . '}', [$this->channelsV1Controller, 'delete']);

						$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [
							$this->channelsV1Controller,
							'readRelationship',
						]);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_CHANNEL_RELATIONSHIP);

						$group->group(
							'/{' . self::URL_CHANNEL_ID . '}',
							function (Routing\RouteCollector $group): void {
								/**
								 * CHANNEL PROPERTIES
								 */
								$group->group('/properties', function (Routing\RouteCollector $group): void {
									$route = $group->get('', [$this->channelPropertiesV1Controller, 'index']);
									$route->setName(DevicesModule\Constants::ROUTE_NAME_CHANNEL_PROPERTIES);

									$route = $group->get('/{' . self::URL_ITEM_ID . '}', [
										$this->channelPropertiesV1Controller,
										'read',
									]);
									$route->setName(DevicesModule\Constants::ROUTE_NAME_CHANNEL_PROPERTY);

									$group->post('', [$this->channelPropertiesV1Controller, 'create']);

									$group->patch('/{' . self::URL_ITEM_ID . '}', [
										$this->channelPropertiesV1Controller,
										'update',
									]);

									$group->delete('/{' . self::URL_ITEM_ID . '}', [
										$this->channelPropertiesV1Controller,
										'delete',
									]);

									$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [
										$this->channelPropertiesV1Controller,
										'readRelationship',
									]);
									$route->setName(DevicesModule\Constants::ROUTE_NAME_CHANNEL_PROPERTY_RELATIONSHIP);

									$group->group('/{' . self::URL_PROPERTY_ID . '}', function (Routing\RouteCollector $group): void {
										/**
										 * CHILDREN
										 */
										$route = $group->get('/children', [$this->channelPropertyChildrenV1Controller, 'index']);
										$route->setName(DevicesModule\Constants::ROUTE_NAME_CHANNEL_PROPERTY_CHILDREN);
									});
								});

								/**
								 * CHANNEL CONTROLS
								 */
								$group->group('/controls', function (Routing\RouteCollector $group): void {
									$route = $group->get('', [$this->channelControlsV1Controller, 'index']);
									$route->setName(DevicesModule\Constants::ROUTE_NAME_CHANNEL_CONTROLS);

									$route = $group->get('/{' . self::URL_ITEM_ID . '}', [
										$this->channelControlsV1Controller,
										'read',
									]);
									$route->setName(DevicesModule\Constants::ROUTE_NAME_CHANNEL_CONTROL);

									$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [
										$this->channelControlsV1Controller,
										'readRelationship',
									]);
									$route->setName(DevicesModule\Constants::ROUTE_NAME_CHANNEL_CONTROL_RELATIONSHIP);
								});
							}
						);
					});
				});
			});

			/**
			 * CONNECTORS
			 */
			$group->group('/connectors', function (Routing\RouteCollector $group): void {
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

				$group->group('/{' . self::URL_CONNECTOR_ID . '}', function (Routing\RouteCollector $group): void {
					/**
					 * CONNECTOR PROPERTIES
					 */
					$group->group('/properties', function (Routing\RouteCollector $group): void {
						$route = $group->get('', [$this->connectorPropertiesV1Controller, 'index']);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_CONNECTOR_PROPERTIES);

						$route = $group->get('/{' . self::URL_ITEM_ID . '}', [
							$this->connectorPropertiesV1Controller,
							'read',
						]);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_CONNECTOR_PROPERTY);

						$group->post('', [$this->connectorPropertiesV1Controller, 'create']);

						$group->patch('/{' . self::URL_ITEM_ID . '}', [$this->connectorPropertiesV1Controller, 'update']);

						$group->delete('/{' . self::URL_ITEM_ID . '}', [$this->connectorPropertiesV1Controller, 'delete']);

						$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [
							$this->connectorPropertiesV1Controller,
							'readRelationship',
						]);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_CONNECTOR_PROPERTY_RELATIONSHIP);
					});

					/**
					 * CONNECTOR CONTROLS
					 */
					$group->group('/controls', function (Routing\RouteCollector $group): void {
						$route = $group->get('', [$this->connectorControlsV1Controller, 'index']);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_CONNECTOR_CONTROLS);

						$route = $group->get('/{' . self::URL_ITEM_ID . '}', [
							$this->connectorControlsV1Controller,
							'read',
						]);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_CONNECTOR_CONTROL);

						$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [
							$this->connectorControlsV1Controller,
							'readRelationship',
						]);
						$route->setName(DevicesModule\Constants::ROUTE_NAME_CONNECTOR_CONTROL_RELATIONSHIP);
					});
				});
			});
		});
	}

}
