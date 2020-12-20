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
		$builder->addDefinition(null)
			->setType(Middleware\AccessMiddleware::class);

		$builder->addDefinition(null)
			->setType(Router\Routes::class);

		// Console commands
		$builder->addDefinition(null)
			->setType(Commands\Devices\CreateCommand::class);

		$builder->addDefinition(null)
			->setType(Commands\InitializeCommand::class);

		// Database repositories
		$builder->addDefinition(null)
			->setType(Models\Devices\DeviceRepository::class);

		$builder->addDefinition(null)
			->setType(Models\Devices\Properties\PropertyRepository::class);

		$builder->addDefinition(null)
			->setType(Models\Devices\Configuration\RowRepository::class);

		$builder->addDefinition(null)
			->setType(Models\Channels\ChannelRepository::class);

		$builder->addDefinition(null)
			->setType(Models\Channels\Properties\PropertyRepository::class);

		$builder->addDefinition(null)
			->setType(Models\Channels\Configuration\RowRepository::class);

		// Database managers
		$builder->addDefinition($this->prefix('doctrine.devicesManager'))
			->setType(Models\Devices\DevicesManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('doctrine.devicesControlsManager'))
			->setType(Models\Devices\Controls\ControlsManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('doctrine.devicesPropertiesManager'))
			->setType(Models\Devices\Properties\PropertiesManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('doctrine.devicesConfigurationManager'))
			->setType(Models\Devices\Configuration\RowsManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('doctrine.hardwareManager'))
			->setType(Models\Devices\PhysicalDevice\HardwareManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('doctrine.firmwareManager'))
			->setType(Models\Devices\PhysicalDevice\FirmwareManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('doctrine.credentialsManager'))
			->setType(Models\Devices\Credentials\CredentialsManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('doctrine.channelsManager'))
			->setType(Models\Channels\ChannelsManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('doctrine.channelsControlsManager'))
			->setType(Models\Channels\Controls\ControlsManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('doctrine.channelsPropertiesManager'))
			->setType(Models\Channels\Properties\PropertiesManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('doctrine.channelsConfigurationManager'))
			->setType(Models\Channels\Configuration\RowsManager::class)
			->setArgument('entityCrud', '__placeholder__');

		// API controllers
		$builder->addDefinition(null)
			->setType(Controllers\DevicesV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition(null)
			->setType(Controllers\DeviceChildrenV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition(null)
			->setType(Controllers\DevicePropertiesV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition(null)
			->setType(Controllers\DeviceConfigurationV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition(null)
			->setType(Controllers\DeviceHardwareV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition(null)
			->setType(Controllers\DeviceFirmwareV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition(null)
			->setType(Controllers\DeviceCredentialsV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition(null)
			->setType(Controllers\ChannelsV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition(null)
			->setType(Controllers\ChannelPropertiesV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition(null)
			->setType(Controllers\ChannelConfigurationV1Controller::class)
			->addTag('nette.inject');

		// API schemas
		$builder->addDefinition(null)
			->setType(Schemas\Devices\NetworkDeviceSchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Devices\LocalDeviceSchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Devices\Properties\PropertySchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Devices\Hardware\HardwareSchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Devices\Firmware\FirmwareSchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Devices\Credentials\CredentialsSchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Devices\Configuration\BooleanRowSchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Devices\Configuration\NumberRowSchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Devices\Configuration\SelectRowSchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Devices\Configuration\TextRowSchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Channels\ChannelSchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Channels\Properties\PropertySchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Channels\Configuration\BooleanRowSchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Channels\Configuration\NumberRowSchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Channels\Configuration\SelectRowSchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Channels\Configuration\TextRowSchema::class);

		// API hydrators
		$builder->addDefinition(null)
			->setType(Hydrators\Devices\NetworkDeviceHydrator::class);

		$builder->addDefinition(null)
			->setType(Hydrators\Devices\LocalDeviceHydrator::class);

		$builder->addDefinition(null)
			->setType(Hydrators\Channels\ChannelHydrator::class);

		$builder->addDefinition(null)
			->setType(Hydrators\Credentials\CredentialsHydrator::class);

		// Helpers
		$builder->addDefinition(null)
			->setType(Helpers\PropertyHelper::class);

		// Events subscribers
		$builder->addDefinition(null)
			->setType(Subscribers\EntitiesSubscriber::class);
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

		$devicesManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__doctrine__devicesManager');
		$devicesManagerService->setBody('return new ' . Models\Devices\DevicesManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Devices\Device::class . '\'));');

		$devicesControlsManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__doctrine__devicesControlsManager');
		$devicesControlsManagerService->setBody('return new ' . Models\Devices\Controls\ControlsManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Devices\Controls\Control::class . '\'));');

		$devicesPropertiesManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__doctrine__devicesPropertiesManager');
		$devicesPropertiesManagerService->setBody('return new ' . Models\Devices\Properties\PropertiesManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Devices\Properties\Property::class . '\'));');

		$devicesConfigurationManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__doctrine__devicesConfigurationManager');
		$devicesConfigurationManagerService->setBody('return new ' . Models\Devices\Configuration\RowsManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Devices\Configuration\Row::class . '\'));');

		$hardwareManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__doctrine__hardwareManager');
		$hardwareManagerService->setBody('return new ' . Models\Devices\PhysicalDevice\HardwareManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Devices\PhysicalDevice\Hardware::class . '\'));');

		$firmwareManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__doctrine__firmwareManager');
		$firmwareManagerService->setBody('return new ' . Models\Devices\PhysicalDevice\FirmwareManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Devices\PhysicalDevice\Firmware::class . '\'));');

		$credentialsManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__doctrine__credentialsManager');
		$credentialsManagerService->setBody('return new ' . Models\Devices\Credentials\CredentialsManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Devices\Credentials\Credentials::class . '\'));');

		$channelsManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__doctrine__channelsManager');
		$channelsManagerService->setBody('return new ' . Models\Channels\ChannelsManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Channels\Channel::class . '\'));');

		$channelsControlsManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__doctrine__channelsControlsManager');
		$channelsControlsManagerService->setBody('return new ' . Models\Channels\Controls\ControlsManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Channels\Controls\Control::class . '\'));');

		$channelsPropertiesManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__doctrine__channelsPropertiesManager');
		$channelsPropertiesManagerService->setBody('return new ' . Models\Channels\Properties\PropertiesManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Channels\Properties\Property::class . '\'));');

		$channelsConfigurationManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__doctrine__channelsConfigurationManager');
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
