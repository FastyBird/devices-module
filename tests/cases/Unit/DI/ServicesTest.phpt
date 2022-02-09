<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\DevicesModule\Commands;
use FastyBird\DevicesModule\Controllers;
use FastyBird\DevicesModule\DI;
use FastyBird\DevicesModule\Hydrators;
use FastyBird\DevicesModule\Middleware;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Router;
use FastyBird\DevicesModule\Schemas;
use FastyBird\DevicesModule\Subscribers;
use Nette;
use Ninjify\Nunjuck\TestCase\BaseTestCase;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class ServicesTest extends BaseTestCase
{

	public function testServicesRegistration(): void
	{
		$container = $this->createContainer();

		Assert::notNull($container->getByType(Commands\InitializeCommand::class));

		Assert::notNull($container->getByType(Middleware\AccessMiddleware::class));

		Assert::notNull($container->getByType(Models\Devices\DevicesRepository::class));
		Assert::notNull($container->getByType(Models\Devices\Properties\PropertiesRepository::class));
		Assert::notNull($container->getByType(Models\Devices\Controls\ControlsRepository::class));
		Assert::notNull($container->getByType(Models\Channels\ChannelsRepository::class));
		Assert::notNull($container->getByType(Models\Channels\Properties\PropertiesRepository::class));
		Assert::notNull($container->getByType(Models\Channels\Controls\ControlsRepository::class));
		Assert::notNull($container->getByType(Models\Connectors\ConnectorsRepository::class));
		Assert::notNull($container->getByType(Models\Connectors\Controls\ControlsRepository::class));

		Assert::notNull($container->getByType(Models\Devices\DevicesManager::class));
		Assert::notNull($container->getByType(Models\Devices\Properties\PropertiesManager::class));
		Assert::notNull($container->getByType(Models\Devices\Controls\ControlsManager::class));
		Assert::notNull($container->getByType(Models\Channels\ChannelsManager::class));
		Assert::notNull($container->getByType(Models\Channels\Properties\PropertiesManager::class));
		Assert::notNull($container->getByType(Models\Channels\Controls\ControlsManager::class));
		Assert::notNull($container->getByType(Models\Connectors\ConnectorsManager::class));
		Assert::notNull($container->getByType(Models\Connectors\Controls\ControlsManager::class));

		Assert::notNull($container->getByType(Models\States\ConnectorPropertiesManager::class));
		Assert::notNull($container->getByType(Models\States\DevicePropertiesManager::class));
		Assert::notNull($container->getByType(Models\States\ChannelPropertiesManager::class));

		Assert::notNull($container->getByType(Controllers\DevicesV1Controller::class));
		Assert::notNull($container->getByType(Controllers\DeviceChildrenV1Controller::class));
		Assert::notNull($container->getByType(Controllers\DevicePropertiesV1Controller::class));
		Assert::notNull($container->getByType(Controllers\DevicePropertyChildrenV1Controller::class));
		Assert::notNull($container->getByType(Controllers\DeviceControlsV1Controller::class));
		Assert::notNull($container->getByType(Controllers\ChannelsV1Controller::class));
		Assert::notNull($container->getByType(Controllers\ChannelPropertiesV1Controller::class));
		Assert::notNull($container->getByType(Controllers\ChannelPropertyChildrenV1Controller::class));
		Assert::notNull($container->getByType(Controllers\ChannelControlsV1Controller::class));
		Assert::notNull($container->getByType(Controllers\ConnectorsV1Controller::class));
		Assert::notNull($container->getByType(Controllers\ConnectorControlsV1Controller::class));

		Assert::notNull($container->getByType(Schemas\Devices\VirtualDeviceSchema::class));
		Assert::notNull($container->getByType(Schemas\Devices\Properties\DynamicPropertySchema::class));
		Assert::notNull($container->getByType(Schemas\Devices\Properties\StaticPropertySchema::class));
		Assert::notNull($container->getByType(Schemas\Devices\Controls\ControlSchema::class));
		Assert::notNull($container->getByType(Schemas\Channels\ChannelSchema::class));
		Assert::notNull($container->getByType(Schemas\Channels\Properties\DynamicPropertySchema::class));
		Assert::notNull($container->getByType(Schemas\Channels\Properties\StaticPropertySchema::class));
		Assert::notNull($container->getByType(Schemas\Channels\Controls\ControlSchema::class));
		Assert::notNull($container->getByType(Schemas\Connectors\VirtualConnectorSchema::class));
		Assert::notNull($container->getByType(Schemas\Connectors\Controls\ControlSchema::class));

		Assert::notNull($container->getByType(Hydrators\Devices\VirtualDeviceHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Channels\ChannelHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Properties\DeviceDynamicPropertyHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Properties\DeviceStaticPropertyHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Properties\ChannelDynamicPropertyHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Properties\ChannelStaticPropertyHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Connectors\VirtualConnectorHydrator::class));

		Assert::notNull($container->getByType(Router\Validator::class));
		Assert::notNull($container->getByType(Router\Routes::class));

		Assert::notNull($container->getByType(Subscribers\EntitiesSubscriber::class));
	}

	/**
	 * @return Nette\DI\Container
	 */
	protected function createContainer(): Nette\DI\Container
	{
		$rootDir = __DIR__ . '/../../../';

		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		$config->addParameters(['container' => ['class' => 'SystemContainer_' . md5((string) time())]]);
		$config->addParameters(['appDir' => $rootDir, 'wwwDir' => $rootDir]);

		$config->addConfig(__DIR__ . '/../../../common.neon');

		DI\DevicesModuleExtension::register($config);

		return $config->createContainer();
	}

}

$test_case = new ServicesTest();
$test_case->run();
