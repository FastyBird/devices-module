<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\DevicesModule\Connectors;
use FastyBird\DevicesModule\DataStorage;
use FastyBird\DevicesModule\Models;
use Ramsey\Uuid;
use Tester\Assert;
use Tests\Tools;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../DbTestCase.php';
require_once __DIR__ . '/../../../tools/DummyConnector.php';
require_once __DIR__ . '/../../../tools/DummyConnectorEntity.php';
require_once __DIR__ . '/../../../tools/DummyConnectorFactory.php';

/**
 * @testCase
 */
final class ConnectorFactoryTest extends DbTestCase
{

	public function setUp(): void
	{
		parent::setUp();

		$writer = $this->getContainer()->getByType(DataStorage\Writer::class);
		$reader = $this->getContainer()->getByType(DataStorage\Reader::class);

		$writer->write();
		$reader->read();
	}

	public function testCreateConnector()
	{
		/** @var Connectors\ConnectorFactory $factory */
		$factory = $this->getContainer()->getByType(Connectors\ConnectorFactory::class);

		/** @var Models\DataStorage\ConnectorsRepository $connectorsRepository */
		$connectorsRepository = $this->getContainer()->getByType(Models\DataStorage\ConnectorsRepository::class);

		$connectorEntity = $connectorsRepository->findById(Uuid\Uuid::fromString('7a3dd94c-7294-46fd-8c61-1b375c313d4d'));

		Assert::notNull($connectorEntity);

		$connector = $factory->create($connectorEntity);

		Assert::type(Tools\DummyConnector::class, $connector);
	}

}

$test_case = new ConnectorFactoryTest();
$test_case->run();
