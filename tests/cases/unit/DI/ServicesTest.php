<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use FastyBird\DevicesModule\Commands;
use FastyBird\DevicesModule\Controllers;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Hydrators;
use FastyBird\DevicesModule\Middleware;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Router;
use FastyBird\DevicesModule\Schemas;
use FastyBird\DevicesModule\Subscribers;
use Nette;
use RuntimeException;
use Tests\Cases\Unit\DbTestCase;

final class ServicesTest extends DbTestCase
{

	/**
	 * @throws Exceptions\InvalidArgument
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 */
	public function testServicesRegistration(): void
	{
		self::assertNotNull($this->getContainer()->getByType(Commands\Initialize::class, false));

		self::assertNotNull($this->getContainer()->getByType(Middleware\Access::class, false));

		self::assertNotNull($this->getContainer()->getByType(Models\Devices\DevicesRepository::class, false));
		self::assertNotNull(
			$this->getContainer()->getByType(Models\Devices\Properties\PropertiesRepository::class, false),
		);
		self::assertNotNull($this->getContainer()->getByType(Models\Devices\Controls\ControlsRepository::class, false));
		self::assertNotNull($this->getContainer()->getByType(Models\Channels\ChannelsRepository::class, false));
		self::assertNotNull(
			$this->getContainer()->getByType(Models\Channels\Properties\PropertiesRepository::class, false),
		);
		self::assertNotNull(
			$this->getContainer()->getByType(Models\Channels\Controls\ControlsRepository::class, false),
		);
		self::assertNotNull($this->getContainer()->getByType(Models\Connectors\ConnectorsRepository::class, false));
		self::assertNotNull(
			$this->getContainer()->getByType(Models\Connectors\Controls\ControlsRepository::class, false),
		);

		self::assertNotNull($this->getContainer()->getByType(Models\Devices\DevicesManager::class, false));
		self::assertNotNull(
			$this->getContainer()->getByType(Models\Devices\Properties\PropertiesManager::class, false),
		);
		self::assertNotNull($this->getContainer()->getByType(Models\Devices\Controls\ControlsManager::class, false));
		self::assertNotNull($this->getContainer()->getByType(Models\Channels\ChannelsManager::class, false));
		self::assertNotNull(
			$this->getContainer()->getByType(Models\Channels\Properties\PropertiesManager::class, false),
		);
		self::assertNotNull($this->getContainer()->getByType(Models\Channels\Controls\ControlsManager::class, false));
		self::assertNotNull($this->getContainer()->getByType(Models\Connectors\ConnectorsManager::class, false));
		self::assertNotNull($this->getContainer()->getByType(Models\Connectors\Controls\ControlsManager::class, false));

		self::assertNotNull(
			$this->getContainer()->getByType(Models\States\ConnectorPropertiesRepository::class, false),
		);
		self::assertNotNull($this->getContainer()->getByType(Models\States\DevicePropertiesRepository::class, false));
		self::assertNotNull($this->getContainer()->getByType(Models\States\ChannelPropertiesRepository::class, false));

		self::assertNotNull($this->getContainer()->getByType(Models\States\ConnectorPropertiesManager::class, false));
		self::assertNotNull($this->getContainer()->getByType(Models\States\DevicePropertiesManager::class, false));
		self::assertNotNull($this->getContainer()->getByType(Models\States\ChannelPropertiesManager::class, false));

		self::assertNotNull($this->getContainer()->getByType(Controllers\DevicesV1::class, false));
		self::assertNotNull($this->getContainer()->getByType(Controllers\DeviceChildrenV1::class, false));
		self::assertNotNull($this->getContainer()->getByType(Controllers\DevicePropertiesV1::class, false));
		self::assertNotNull($this->getContainer()->getByType(Controllers\DevicePropertyChildrenV1::class, false));
		self::assertNotNull($this->getContainer()->getByType(Controllers\DeviceControlsV1::class, false));
		self::assertNotNull($this->getContainer()->getByType(Controllers\ChannelsV1::class, false));
		self::assertNotNull($this->getContainer()->getByType(Controllers\ChannelPropertiesV1::class, false));
		self::assertNotNull($this->getContainer()->getByType(Controllers\ChannelPropertyChildrenV1::class, false));
		self::assertNotNull($this->getContainer()->getByType(Controllers\ChannelControlsV1::class, false));
		self::assertNotNull($this->getContainer()->getByType(Controllers\ConnectorsV1::class, false));
		self::assertNotNull($this->getContainer()->getByType(Controllers\ConnectorControlsV1::class, false));

		self::assertNotNull($this->getContainer()->getByType(Schemas\Devices\Blank::class, false));
		self::assertNotNull($this->getContainer()->getByType(Schemas\Devices\Properties\Dynamic::class, false));
		self::assertNotNull($this->getContainer()->getByType(Schemas\Devices\Properties\Variable::class, false));
		self::assertNotNull($this->getContainer()->getByType(Schemas\Devices\Controls\Control::class, false));
		self::assertNotNull($this->getContainer()->getByType(Schemas\Channels\Channel::class, false));
		self::assertNotNull($this->getContainer()->getByType(Schemas\Channels\Properties\Dynamic::class, false));
		self::assertNotNull($this->getContainer()->getByType(Schemas\Channels\Properties\Variable::class, false));
		self::assertNotNull($this->getContainer()->getByType(Schemas\Channels\Controls\Control::class, false));
		self::assertNotNull($this->getContainer()->getByType(Schemas\Connectors\Blank::class, false));
		self::assertNotNull($this->getContainer()->getByType(Schemas\Connectors\Controls\Control::class, false));

		self::assertNotNull($this->getContainer()->getByType(Hydrators\Devices\Blank::class, false));
		self::assertNotNull($this->getContainer()->getByType(Hydrators\Channels\Channel::class, false));
		self::assertNotNull($this->getContainer()->getByType(Hydrators\Properties\DeviceDynamic::class, false));
		self::assertNotNull($this->getContainer()->getByType(Hydrators\Properties\DeviceVariable::class, false));
		self::assertNotNull($this->getContainer()->getByType(Hydrators\Properties\ChannelDynamic::class, false));
		self::assertNotNull($this->getContainer()->getByType(Hydrators\Properties\ChannelVariable::class, false));
		self::assertNotNull($this->getContainer()->getByType(Hydrators\Connectors\Blank::class, false));

		self::assertNotNull($this->getContainer()->getByType(Router\Validator::class, false));
		self::assertNotNull($this->getContainer()->getByType(Router\Routes::class, false));

		self::assertNotNull($this->getContainer()->getByType(Subscribers\ModuleEntities::class, false));
	}

}
