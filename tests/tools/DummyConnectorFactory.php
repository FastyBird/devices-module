<?php declare(strict_types = 1);

namespace Tests\Tools;

use FastyBird\DevicesModule\Connectors;
use FastyBird\DevicesModule\Connectors\IConnector;
use FastyBird\Metadata\Entities as MetadataEntities;

class DummyConnectorFactory implements Connectors\IConnectorFactory
{

	public function getType(): string
	{
		return 'dummy';
	}

	public function create(MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector): IConnector
	{
		return new DummyConnector();
	}

}
