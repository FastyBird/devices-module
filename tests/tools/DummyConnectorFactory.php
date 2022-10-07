<?php declare(strict_types = 1);

namespace Tests\Tools;

use FastyBird\DevicesModule\Connectors;
use FastyBird\DevicesModule\Connectors\Connector;
use FastyBird\Metadata\Entities as MetadataEntities;

class DummyConnectorFactory implements Connectors\ConnectorFactory
{

	public function getType(): string
	{
		return 'dummy';
	}

	public function create(MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector): Connector
	{
		return new DummyConnector();
	}

}
