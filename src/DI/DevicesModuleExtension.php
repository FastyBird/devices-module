<?php declare(strict_types = 1);

/**
 * DevicesModuleExtension.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     DI
 * @since          0.1.0
 *
 * @date           25.11.20
 */

namespace FastyBird\DevicesModule\DI;

use Contributte\Translation;
use Doctrine\Persistence;
use FastyBird\DevicesModule\Commands;
use FastyBird\DevicesModule\Consumers;
use FastyBird\DevicesModule\Controllers;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Helpers;
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

/**
 * Devices module extension container
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     DI
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class DevicesModuleExtension extends DI\CompilerExtension implements Translation\DI\TranslationProviderInterface
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
	 * {@inheritDoc}
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		// Http router
		$builder->addDefinition($this->prefix('middleware.access'))
			->setType(Middleware\AccessMiddleware::class);

		$builder->addDefinition($this->prefix('router.routes'))
			->setType(Router\Routes::class);

		// Console commands
		$builder->addDefinition($this->prefix('commands.create'))
			->setType(Commands\Devices\CreateCommand::class);

		$builder->addDefinition($this->prefix('commands.initialize'))
			->setType(Commands\InitializeCommand::class);

		// Database repositories
		$builder->addDefinition($this->prefix('models.deviceRepository'))
			->setType(Models\Devices\DeviceRepository::class);

		$builder->addDefinition($this->prefix('models.devicePropertyRepository'))
			->setType(Models\Devices\Properties\PropertyRepository::class);

		$builder->addDefinition($this->prefix('models.deviceConfigurationRepository'))
			->setType(Models\Devices\Configuration\RowRepository::class);

		$builder->addDefinition($this->prefix('models.channelRepository'))
			->setType(Models\Channels\ChannelRepository::class);

		$builder->addDefinition($this->prefix('models.channelPropertyRepository'))
			->setType(Models\Channels\Properties\PropertyRepository::class);

		$builder->addDefinition($this->prefix('models.channelConfigurationRepository'))
			->setType(Models\Channels\Configuration\RowRepository::class);

		// Database managers
		$builder->addDefinition($this->prefix('models.devicesManager'))
			->setType(Models\Devices\DevicesManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('models.devicesControlsManager'))
			->setType(Models\Devices\Controls\ControlsManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('models.devicesPropertiesManager'))
			->setType(Models\Devices\Properties\PropertiesManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('models.devicesConfigurationManager'))
			->setType(Models\Devices\Configuration\RowsManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('models.devicesConnectorManager'))
			->setType(Models\Devices\Connectors\ConnectorsManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('models.channelsManager'))
			->setType(Models\Channels\ChannelsManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('models.channelsControlsManager'))
			->setType(Models\Channels\Controls\ControlsManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('models.channelsPropertiesManager'))
			->setType(Models\Channels\Properties\PropertiesManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('models.channelsConfigurationManager'))
			->setType(Models\Channels\Configuration\RowsManager::class)
			->setArgument('entityCrud', '__placeholder__');

		// Events subscribers
		$builder->addDefinition($this->prefix('subscribers.entities'))
			->setType(Subscribers\EntitiesSubscriber::class);

		// Message bus consumers
		$builder->addDefinition($this->prefix('consumers.deviceProperty'))
			->setType(Consumers\DevicePropertyMessageConsumer::class);

		$builder->addDefinition($this->prefix('consumers.channelProperty'))
			->setType(Consumers\ChannelPropertyMessageConsumer::class);

		// API controllers
		$builder->addDefinition($this->prefix('controllers.devices'))
			->setType(Controllers\DevicesV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition($this->prefix('controllers.deviceChildren'))
			->setType(Controllers\DeviceChildrenV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition($this->prefix('controllers.deviceProperties'))
			->setType(Controllers\DevicePropertiesV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition($this->prefix('controllers.deviceConfiguration'))
			->setType(Controllers\DeviceConfigurationV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition($this->prefix('controllers.deviceConnector'))
			->setType(Controllers\DeviceConnectorV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition($this->prefix('controllers.channels'))
			->setType(Controllers\ChannelsV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition($this->prefix('controllers.channelProperites'))
			->setType(Controllers\ChannelPropertiesV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition($this->prefix('controllers.channelConfiguration'))
			->setType(Controllers\ChannelConfigurationV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition($this->prefix('controllers.connectors'))
			->setType(Controllers\ConnectorsV1Controller::class)
			->addTag('nette.inject');

		// API schemas
		$builder->addDefinition($this->prefix('schemas.device'))
			->setType(Schemas\Devices\DeviceSchema::class);

		$builder->addDefinition($this->prefix('schemas.device.properties'))
			->setType(Schemas\Devices\Properties\PropertySchema::class);

		$builder->addDefinition($this->prefix('schemas.device.connector'))
			->setType(Schemas\Devices\Connectors\ConnectorSchema::class);

		$builder->addDefinition($this->prefix('schemas.device.configuration.boolean'))
			->setType(Schemas\Devices\Configuration\BooleanRowSchema::class);

		$builder->addDefinition($this->prefix('schemas.device.configuration.number'))
			->setType(Schemas\Devices\Configuration\NumberRowSchema::class);

		$builder->addDefinition($this->prefix('schemas.device.configuration.select'))
			->setType(Schemas\Devices\Configuration\SelectRowSchema::class);

		$builder->addDefinition($this->prefix('schemas.device.configuration.text'))
			->setType(Schemas\Devices\Configuration\TextRowSchema::class);

		$builder->addDefinition($this->prefix('schemas.channel'))
			->setType(Schemas\Channels\ChannelSchema::class);

		$builder->addDefinition($this->prefix('schemas.channel.property'))
			->setType(Schemas\Channels\Properties\PropertySchema::class);

		$builder->addDefinition($this->prefix('schemas.configuration.boolean'))
			->setType(Schemas\Channels\Configuration\BooleanRowSchema::class);

		$builder->addDefinition($this->prefix('schemas.configuration.number'))
			->setType(Schemas\Channels\Configuration\NumberRowSchema::class);

		$builder->addDefinition($this->prefix('schemas.configuration.select'))
			->setType(Schemas\Channels\Configuration\SelectRowSchema::class);

		$builder->addDefinition($this->prefix('schemas.configuration.text'))
			->setType(Schemas\Channels\Configuration\TextRowSchema::class);

		$builder->addDefinition($this->prefix('schemas.connector'))
			->setType(Schemas\Connectors\ConnectorSchema::class);

		// API hydrators
		$builder->addDefinition($this->prefix('hydrators.device'))
			->setType(Hydrators\Devices\DeviceHydrator::class);

		$builder->addDefinition($this->prefix('hydrators.channel'))
			->setType(Hydrators\Channels\ChannelHydrator::class);

		$builder->addDefinition($this->prefix('hydrators.connectors'))
			->setType(Hydrators\Devices\Connectors\ConnectorHydrator::class);

		// Helpers
		$builder->addDefinition($this->prefix('helpers.property'))
			->setType(Helpers\PropertyHelper::class);
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

		$devicesControlsManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__models__devicesControlsManager');
		$devicesControlsManagerService->setBody('return new ' . Models\Devices\Controls\ControlsManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Devices\Controls\Control::class . '\'));');

		$devicesPropertiesManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__models__devicesPropertiesManager');
		$devicesPropertiesManagerService->setBody('return new ' . Models\Devices\Properties\PropertiesManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Devices\Properties\Property::class . '\'));');

		$devicesConfigurationManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__models__devicesConfigurationManager');
		$devicesConfigurationManagerService->setBody('return new ' . Models\Devices\Configuration\RowsManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Devices\Configuration\Row::class . '\'));');

		$connectorManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__models__devicesConnectorManager');
		$connectorManagerService->setBody('return new ' . Models\Devices\Connectors\ConnectorsManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Devices\Connectors\Connector::class . '\'));');

		$channelsManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__models__channelsManager');
		$channelsManagerService->setBody('return new ' . Models\Channels\ChannelsManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Channels\Channel::class . '\'));');

		$channelsControlsManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__models__channelsControlsManager');
		$channelsControlsManagerService->setBody('return new ' . Models\Channels\Controls\ControlsManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Channels\Controls\Control::class . '\'));');

		$channelsPropertiesManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__models__channelsPropertiesManager');
		$channelsPropertiesManagerService->setBody('return new ' . Models\Channels\Properties\PropertiesManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Channels\Properties\Property::class . '\'));');

		$channelsConfigurationManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__models__channelsConfigurationManager');
		$channelsConfigurationManagerService->setBody('return new ' . Models\Channels\Configuration\RowsManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Channels\Configuration\Row::class . '\'));');
	}

	/**
	 * @return string[]
	 */
	public function getTranslationResources(): array
	{
		return [
			__DIR__ . '/../Translations',
		];
	}

}
