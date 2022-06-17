<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\DevicesModule;
use FastyBird\DevicesModule\DataStorage;
use FastyBird\DevicesModule\Models;
use League\Flysystem;
use Mockery;
use Nette\Utils;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../DbTestCase.php';

/**
 * @testCase
 */
final class ReaderTest extends DbTestCase
{

	public function testReadConfiguration(): void
	{
		$filesystem = Mockery::mock(Flysystem\Filesystem::class);
		$filesystem
			->shouldReceive('read')
			->withArgs([DevicesModule\Constants::CONFIGURATION_FILE_FILENAME])
			->andReturn(Utils\FileSystem::read('./../../../fixtures/DataStorage/devices-module-data.json'));

		$this->mockContainerService(
			Flysystem\Filesystem::class,
			$filesystem
		);

		$reader = $this->getContainer()->getByType(DataStorage\Reader::class);

		$connectorsRepository = $this->getContainer()->getByType(Models\DataStorage\IConnectorsRepository::class);
		$connectorPropertiesRepository = $this->getContainer()
			->getByType(Models\DataStorage\IConnectorPropertiesRepository::class);
		$connectorControlsRepository = $this->getContainer()
			->getByType(Models\DataStorage\IConnectorControlsRepository::class);

		$devicesRepository = $this->getContainer()->getByType(Models\DataStorage\IDevicesRepository::class);
		$devicePropertiesRepository = $this->getContainer()
			->getByType(Models\DataStorage\IDevicePropertiesRepository::class);
		$deviceControlsRepository = $this->getContainer()
			->getByType(Models\DataStorage\IDeviceControlsRepository::class);
		$deviceAttributesRepository = $this->getContainer()
			->getByType(Models\DataStorage\IDeviceAttributesRepository::class);

		$channelsRepository = $this->getContainer()->getByType(Models\DataStorage\IChannelsRepository::class);
		$channelPropertiesRepository = $this->getContainer()
			->getByType(Models\DataStorage\IChannelPropertiesRepository::class);
		$channelControlsRepository = $this->getContainer()
			->getByType(Models\DataStorage\IChannelControlsRepository::class);

		$reader->read();

		Assert::count(1, $connectorsRepository);
		Assert::count(0, $connectorPropertiesRepository);
		Assert::count(1, $connectorControlsRepository);

		Assert::count(4, $devicesRepository);
		Assert::count(5, $devicePropertiesRepository);
		Assert::count(1, $deviceControlsRepository);
		Assert::count(10, $deviceAttributesRepository);

		Assert::count(3, $channelsRepository);
		Assert::count(3, $channelPropertiesRepository);
		Assert::count(2, $channelControlsRepository);
	}

}

$test_case = new ReaderTest();
$test_case->run();
