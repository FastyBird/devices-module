<?php declare(strict_types = 1);

/**
 * DevicesModuleExtension.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     DI
 * @since          0.1.0
 *
 * @date           25.11.20
 */

namespace FastyBird\DevicesModule\DI;

use Doctrine\Persistence;
use FastyBird\DevicesModule\Commands;
use FastyBird\DevicesModule\Controllers;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Hydrators;
use FastyBird\DevicesModule\Middleware;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Router;
use FastyBird\DevicesModule\Schemas;
use FastyBird\DevicesModule\Subscribers;
use IPub\DoctrineCrud;
use Nette;
use Nette\DI;
use Nette\PhpGenerator;
use Nette\Schema;
use stdClass;

/**
 * Devices module extension container
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     DI
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class DevicesModuleExtension extends DI\CompilerExtension
{

	/**
	 * @param Nette\Configurator $config
	 * @param string $extensionName
	 *
	 * @return void
	 */
	public static function register(
		Nette\Configurator $config,
		string $extensionName = 'fbDevicesModule'
	): void {
		$config->onCompile[] = function (
			Nette\Configurator $config,
			DI\Compiler $compiler
		) use ($extensionName): void {
			$compiler->addExtension($extensionName, new DevicesModuleExtension());
		};
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConfigSchema(): Schema\Schema
	{
		return Schema\Expect::structure([
			'apiPrefix' => Schema\Expect::bool(false),
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		/** @var stdClass $configuration */
		$configuration = $this->getConfig();

		// Http router
		$builder->addDefinition($this->prefix('middleware.access'), new DI\Definitions\ServiceDefinition())
			->setType(Middleware\AccessMiddleware::class);

		$builder->addDefinition($this->prefix('router.routes'), new DI\Definitions\ServiceDefinition())
			->setType(Router\Routes::class)
			->setArguments(['usePrefix' => $configuration->apiPrefix]);

		$builder->addDefinition($this->prefix('router.validator'), new DI\Definitions\ServiceDefinition())
			->setType(Router\Validator::class);

		// Console commands
		$builder->addDefinition($this->prefix('commands.initialize'), new DI\Definitions\ServiceDefinition())
			->setType(Commands\InitializeCommand::class);

		// Database repositories
		$builder->addDefinition($this->prefix('models.devicesRepository'), new DI\Definitions\ServiceDefinition())
			->setType(Models\Devices\DevicesRepository::class);

		$builder->addDefinition($this->prefix('models.devicePropertiesRepository'), new DI\Definitions\ServiceDefinition())
			->setType(Models\Devices\Properties\PropertiesRepository::class);

		$builder->addDefinition($this->prefix('models.deviceControlsRepository'), new DI\Definitions\ServiceDefinition())
			->setType(Models\Devices\Controls\ControlsRepository::class);

		$builder->addDefinition($this->prefix('models.channelsRepository'), new DI\Definitions\ServiceDefinition())
			->setType(Models\Channels\ChannelsRepository::class);

		$builder->addDefinition($this->prefix('models.channelPropertiesRepository'), new DI\Definitions\ServiceDefinition())
			->setType(Models\Channels\Properties\PropertiesRepository::class);

		$builder->addDefinition($this->prefix('models.channelControlsRepository'), new DI\Definitions\ServiceDefinition())
			->setType(Models\Channels\Controls\ControlsRepository::class);

		$builder->addDefinition($this->prefix('models.connectorsRepository'), new DI\Definitions\ServiceDefinition())
			->setType(Models\Connectors\ConnectorsRepository::class);

		$builder->addDefinition($this->prefix('models.connectorPropertiesRepository'), new DI\Definitions\ServiceDefinition())
			->setType(Models\Connectors\Properties\PropertiesRepository::class);

		$builder->addDefinition($this->prefix('models.connectorControlsRepository'), new DI\Definitions\ServiceDefinition())
			->setType(Models\Connectors\Controls\ControlsRepository::class);

		// Database managers
		$builder->addDefinition($this->prefix('models.devicesManager'), new DI\Definitions\ServiceDefinition())
			->setType(Models\Devices\DevicesManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('models.devicesPropertiesManager'), new DI\Definitions\ServiceDefinition())
			->setType(Models\Devices\Properties\PropertiesManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('models.devicesControlsManager'), new DI\Definitions\ServiceDefinition())
			->setType(Models\Devices\Controls\ControlsManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('models.channelsManager'), new DI\Definitions\ServiceDefinition())
			->setType(Models\Channels\ChannelsManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('models.channelsPropertiesManager'), new DI\Definitions\ServiceDefinition())
			->setType(Models\Channels\Properties\PropertiesManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('models.channelsControlsManager'), new DI\Definitions\ServiceDefinition())
			->setType(Models\Channels\Controls\ControlsManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('models.connectorsManager'), new DI\Definitions\ServiceDefinition())
			->setType(Models\Connectors\ConnectorsManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('models.connectorsPropertiesManager'), new DI\Definitions\ServiceDefinition())
			->setType(Models\Connectors\Properties\PropertiesManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('models.connectorsControlsManager'), new DI\Definitions\ServiceDefinition())
			->setType(Models\Connectors\Controls\ControlsManager::class)
			->setArgument('entityCrud', '__placeholder__');

		// Events subscribers
		$builder->addDefinition($this->prefix('subscribers.entities'), new DI\Definitions\ServiceDefinition())
			->setType(Subscribers\EntitiesSubscriber::class);

		// API controllers
		$builder->addDefinition($this->prefix('controllers.devices'), new DI\Definitions\ServiceDefinition())
			->setType(Controllers\DevicesV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition($this->prefix('controllers.deviceChildren'), new DI\Definitions\ServiceDefinition())
			->setType(Controllers\DeviceChildrenV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition($this->prefix('controllers.deviceProperties'), new DI\Definitions\ServiceDefinition())
			->setType(Controllers\DevicePropertiesV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition($this->prefix('controllers.devicePropertyChildren'), new DI\Definitions\ServiceDefinition())
			->setType(Controllers\DevicePropertyChildrenV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition($this->prefix('controllers.deviceControls'), new DI\Definitions\ServiceDefinition())
			->setType(Controllers\DeviceControlsV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition($this->prefix('controllers.channels'), new DI\Definitions\ServiceDefinition())
			->setType(Controllers\ChannelsV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition($this->prefix('controllers.channelProperties'), new DI\Definitions\ServiceDefinition())
			->setType(Controllers\ChannelPropertiesV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition($this->prefix('controllers.channelPropertyChildren'), new DI\Definitions\ServiceDefinition())
			->setType(Controllers\ChannelPropertyChildrenV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition($this->prefix('controllers.channelControls'), new DI\Definitions\ServiceDefinition())
			->setType(Controllers\ChannelControlsV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition($this->prefix('controllers.connectors'), new DI\Definitions\ServiceDefinition())
			->setType(Controllers\ConnectorsV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition($this->prefix('controllers.connectorProperties'), new DI\Definitions\ServiceDefinition())
			->setType(Controllers\ConnectorPropertiesV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition($this->prefix('controllers.connectorsControls'), new DI\Definitions\ServiceDefinition())
			->setType(Controllers\ConnectorControlsV1Controller::class)
			->addTag('nette.inject');

		// API schemas
		$builder->addDefinition($this->prefix('schemas.device.virtual'), new DI\Definitions\ServiceDefinition())
			->setType(Schemas\Devices\VirtualDeviceSchema::class);

		$builder->addDefinition($this->prefix('schemas.device.property.dynamic'), new DI\Definitions\ServiceDefinition())
			->setType(Schemas\Devices\Properties\DynamicPropertySchema::class);

		$builder->addDefinition($this->prefix('schemas.device.property.static'), new DI\Definitions\ServiceDefinition())
			->setType(Schemas\Devices\Properties\StaticPropertySchema::class);

		$builder->addDefinition($this->prefix('schemas.device.control'), new DI\Definitions\ServiceDefinition())
			->setType(Schemas\Devices\Controls\ControlSchema::class);

		$builder->addDefinition($this->prefix('schemas.channel'), new DI\Definitions\ServiceDefinition())
			->setType(Schemas\Channels\ChannelSchema::class);

		$builder->addDefinition($this->prefix('schemas.channel.property.dynamic'), new DI\Definitions\ServiceDefinition())
			->setType(Schemas\Channels\Properties\DynamicPropertySchema::class);

		$builder->addDefinition($this->prefix('schemas.channel.property.static'), new DI\Definitions\ServiceDefinition())
			->setType(Schemas\Channels\Properties\StaticPropertySchema::class);

		$builder->addDefinition($this->prefix('schemas.control'), new DI\Definitions\ServiceDefinition())
			->setType(Schemas\Channels\Controls\ControlSchema::class);

		$builder->addDefinition($this->prefix('schemas.connector.virtual'), new DI\Definitions\ServiceDefinition())
			->setType(Schemas\Connectors\VirtualConnectorSchema::class);

		$builder->addDefinition($this->prefix('schemas.connector.property.dynamic'), new DI\Definitions\ServiceDefinition())
			->setType(Schemas\Connectors\Properties\DynamicPropertySchema::class);

		$builder->addDefinition($this->prefix('schemas.connector.property.static'), new DI\Definitions\ServiceDefinition())
			->setType(Schemas\Connectors\Properties\StaticPropertySchema::class);

		$builder->addDefinition($this->prefix('schemas.connector.controls'), new DI\Definitions\ServiceDefinition())
			->setType(Schemas\Connectors\Controls\ControlSchema::class);

		// API hydrators
		$builder->addDefinition($this->prefix('hydrators.device.virtual'), new DI\Definitions\ServiceDefinition())
			->setType(Hydrators\Devices\VirtualDeviceHydrator::class);

		$builder->addDefinition($this->prefix('hydrators.device.property.dynamic'), new DI\Definitions\ServiceDefinition())
			->setType(Hydrators\Properties\DeviceDynamicPropertyHydrator::class);

		$builder->addDefinition($this->prefix('hydrators.device.property.static'), new DI\Definitions\ServiceDefinition())
			->setType(Hydrators\Properties\DeviceStaticPropertyHydrator::class);

		$builder->addDefinition($this->prefix('hydrators.channel'), new DI\Definitions\ServiceDefinition())
			->setType(Hydrators\Channels\ChannelHydrator::class);

		$builder->addDefinition($this->prefix('hydrators.channel.property.dynamic'), new DI\Definitions\ServiceDefinition())
			->setType(Hydrators\Properties\ChannelDynamicPropertyHydrator::class);

		$builder->addDefinition($this->prefix('hydrators.channel.property.static'), new DI\Definitions\ServiceDefinition())
			->setType(Hydrators\Properties\ChannelStaticPropertyHydrator::class);

		$builder->addDefinition($this->prefix('hydrators.connectors.virtual'), new DI\Definitions\ServiceDefinition())
			->setType(Hydrators\Connectors\VirtualConnectorHydrator::class);

		$builder->addDefinition($this->prefix('hydrators.connector.property.dynamic'), new DI\Definitions\ServiceDefinition())
			->setType(Hydrators\Properties\ConnectorDynamicPropertyHydrator::class);

		$builder->addDefinition($this->prefix('hydrators.connector.property.static'), new DI\Definitions\ServiceDefinition())
			->setType(Hydrators\Properties\ConnectorStaticPropertyHydrator::class);

		// States repositories
		$builder->addDefinition($this->prefix('states.repositories.connectors.properties'), new DI\Definitions\ServiceDefinition())
			->setType(Models\States\ConnectorPropertiesRepository::class);

		$builder->addDefinition($this->prefix('states.repositories.devices.properties'), new DI\Definitions\ServiceDefinition())
			->setType(Models\States\DevicePropertiesRepository::class);

		$builder->addDefinition($this->prefix('states.repositories.channels.properties'), new DI\Definitions\ServiceDefinition())
			->setType(Models\States\ChannelPropertiesRepository::class);

		// States managers
		$builder->addDefinition($this->prefix('states.managers.connectors.properties'), new DI\Definitions\ServiceDefinition())
			->setType(Models\States\ConnectorPropertiesManager::class);

		$builder->addDefinition($this->prefix('states.managers.devices.properties'), new DI\Definitions\ServiceDefinition())
			->setType(Models\States\DevicePropertiesManager::class);

		$builder->addDefinition($this->prefix('states.managers.channels.properties'), new DI\Definitions\ServiceDefinition())
			->setType(Models\States\ChannelPropertiesManager::class);
	}

	/**
	 * {@inheritDoc}
	 */
	public function beforeCompile(): void
	{
		parent::beforeCompile();

		$builder = $this->getContainerBuilder();

		/**
		 * Doctrine entities
		 */

		$ormAnnotationDriverService = $builder->getDefinition('nettrineOrmAnnotations.annotationDriver');

		if ($ormAnnotationDriverService instanceof DI\Definitions\ServiceDefinition) {
			$ormAnnotationDriverService->addSetup('addPaths', [[__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Entities']]);
		}

		$ormAnnotationDriverChainService = $builder->getDefinitionByType(Persistence\Mapping\Driver\MappingDriverChain::class);

		if ($ormAnnotationDriverChainService instanceof DI\Definitions\ServiceDefinition) {
			$ormAnnotationDriverChainService->addSetup('addDriver', [
				$ormAnnotationDriverService,
				'FastyBird\DevicesModule\Entities',
			]);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function afterCompile(
		PhpGenerator\ClassType $class
	): void {
		$builder = $this->getContainerBuilder();

		$entityFactoryServiceName = $builder->getByType(DoctrineCrud\Crud\IEntityCrudFactory::class, true);

		$devicesManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__models__devicesManager');
		$devicesManagerService->setBody('return new ' . Models\Devices\DevicesManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Devices\Device::class . '\'));');

		$devicesPropertiesManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__models__devicesPropertiesManager');
		$devicesPropertiesManagerService->setBody('return new ' . Models\Devices\Properties\PropertiesManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Devices\Properties\Property::class . '\'));');

		$devicesControlsManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__models__devicesControlsManager');
		$devicesControlsManagerService->setBody('return new ' . Models\Devices\Controls\ControlsManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Devices\Controls\Control::class . '\'));');

		$channelsManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__models__channelsManager');
		$channelsManagerService->setBody('return new ' . Models\Channels\ChannelsManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Channels\Channel::class . '\'));');

		$channelsPropertiesManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__models__channelsPropertiesManager');
		$channelsPropertiesManagerService->setBody('return new ' . Models\Channels\Properties\PropertiesManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Channels\Properties\Property::class . '\'));');

		$channelsControlsManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__models__channelsControlsManager');
		$channelsControlsManagerService->setBody('return new ' . Models\Channels\Controls\ControlsManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Channels\Controls\Control::class . '\'));');

		$connectorsManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__models__connectorsManager');
		$connectorsManagerService->setBody('return new ' . Models\Connectors\ConnectorsManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Connectors\Connector::class . '\'));');

		$connectorsPropertiesManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__models__connectorsPropertiesManager');
		$connectorsPropertiesManagerService->setBody('return new ' . Models\Connectors\Properties\PropertiesManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Connectors\Properties\Property::class . '\'));');

		$connectorsControlsManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__models__connectorsControlsManager');
		$connectorsControlsManagerService->setBody('return new ' . Models\Connectors\Controls\ControlsManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Connectors\Controls\Control::class . '\'));');
	}

}
