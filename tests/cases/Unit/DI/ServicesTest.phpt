<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\DevicesModule\Commands;
use FastyBird\DevicesModule\Controllers;
use FastyBird\DevicesModule\DI;
use FastyBird\DevicesModule\Helpers;
use FastyBird\DevicesModule\Hydrators;
use FastyBird\DevicesModule\Middleware;
use FastyBird\DevicesModule\Models;
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

		Assert::notNull($container->getByType(Commands\Devices\CreateCommand::class));
		Assert::notNull($container->getByType(Commands\InitializeCommand::class));

		Assert::notNull($container->getByType(Middleware\AccessMiddleware::class));

		Assert::notNull($container->getByType(Models\Devices\DeviceRepository::class));
		Assert::notNull($container->getByType(Models\Devices\Properties\PropertyRepository::class));
		Assert::notNull($container->getByType(Models\Devices\Configuration\RowRepository::class));
		Assert::notNull($container->getByType(Models\Channels\ChannelRepository::class));
		Assert::notNull($container->getByType(Models\Channels\Properties\PropertyRepository::class));
		Assert::notNull($container->getByType(Models\Channels\Configuration\RowRepository::class));

		Assert::notNull($container->getByType(Models\Devices\DevicesManager::class));
		Assert::notNull($container->getByType(Models\Devices\Controls\ControlsManager::class));
		Assert::notNull($container->getByType(Models\Devices\Properties\PropertiesManager::class));
		Assert::notNull($container->getByType(Models\Devices\PhysicalDevice\HardwareManager::class));
		Assert::notNull($container->getByType(Models\Devices\PhysicalDevice\FirmwareManager::class));
		Assert::notNull($container->getByType(Models\Channels\ChannelsManager::class));
		Assert::notNull($container->getByType(Models\Channels\Controls\ControlsManager::class));
		Assert::notNull($container->getByType(Models\Channels\Properties\PropertiesManager::class));
		Assert::notNull($container->getByType(Models\Channels\Configuration\RowsManager::class));

		Assert::notNull($container->getByType(Controllers\DevicesV1Controller::class));
		Assert::notNull($container->getByType(Controllers\DeviceChildrenV1Controller::class));
		Assert::notNull($container->getByType(Controllers\DevicePropertiesV1Controller::class));
		Assert::notNull($container->getByType(Controllers\DeviceConfigurationV1Controller::class));
		Assert::notNull($container->getByType(Controllers\DeviceHardwareV1Controller::class));
		Assert::notNull($container->getByType(Controllers\DeviceFirmwareV1Controller::class));
		Assert::notNull($container->getByType(Controllers\ChannelsV1Controller::class));
		Assert::notNull($container->getByType(Controllers\ChannelPropertiesV1Controller::class));
		Assert::notNull($container->getByType(Controllers\ChannelConfigurationV1Controller::class));

		Assert::notNull($container->getByType(Schemas\Devices\NetworkDeviceSchema::class));
		Assert::notNull($container->getByType(Schemas\Devices\LocalDeviceSchema::class));
		Assert::notNull($container->getByType(Schemas\Devices\Properties\PropertySchema::class));
		Assert::notNull($container->getByType(Schemas\Devices\Hardware\HardwareSchema::class));
		Assert::notNull($container->getByType(Schemas\Devices\Firmware\FirmwareSchema::class));
		Assert::notNull($container->getByType(Schemas\Devices\Configuration\BooleanRowSchema::class));
		Assert::notNull($container->getByType(Schemas\Devices\Configuration\NumberRowSchema::class));
		Assert::notNull($container->getByType(Schemas\Devices\Configuration\SelectRowSchema::class));
		Assert::notNull($container->getByType(Schemas\Devices\Configuration\TextRowSchema::class));
		Assert::notNull($container->getByType(Schemas\Channels\ChannelSchema::class));
		Assert::notNull($container->getByType(Schemas\Channels\Properties\PropertySchema::class));
		Assert::notNull($container->getByType(Schemas\Channels\Configuration\BooleanRowSchema::class));
		Assert::notNull($container->getByType(Schemas\Channels\Configuration\NumberRowSchema::class));
		Assert::notNull($container->getByType(Schemas\Channels\Configuration\SelectRowSchema::class));
		Assert::notNull($container->getByType(Schemas\Channels\Configuration\TextRowSchema::class));

		Assert::notNull($container->getByType(Hydrators\Devices\NetworkDeviceHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Devices\LocalDeviceHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Channels\ChannelHydrator::class));

		Assert::notNull($container->getByType(Helpers\PropertyHelper::class));

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
