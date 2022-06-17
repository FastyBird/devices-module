<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\DevicesModule;
use FastyBird\DevicesModule\DataStorage;
use FastyBird\DevicesModule\Models;
use FastyBird\Metadata\Entities as MetadataEntities;
use League\Flysystem;
use Mockery;
use Nette\Utils;
use Ramsey\Uuid;
use Tester\Assert;

require_once __DIR__ . '/../../../../bootstrap.php';
require_once __DIR__ . '/../../DbTestCase.php';

/**
 * @testCase
 */
final class ConnectorsRepositoryTest extends DbTestCase
{

	public function testReadConfiguration(): void
	{
		$filesystem = Mockery::mock(Flysystem\Filesystem::class);
		$filesystem
			->shouldReceive('read')
			->withArgs([DevicesModule\Constants::CONFIGURATION_FILE_FILENAME])
			->andReturn(Utils\FileSystem::read('./../../../../fixtures/DataStorage/devices-module-data.json'));

		$this->mockContainerService(
			Flysystem\Filesystem::class,
			$filesystem
		);

		$reader = $this->getContainer()->getByType(DataStorage\Reader::class);

		$connectorsRepository = $this->getContainer()->getByType(Models\DataStorage\IConnectorsRepository::class);

		$reader->read();

		Assert::count(1, $connectorsRepository);

		$connector = $connectorsRepository->findById(Uuid\Uuid::fromString('17c59dfa-2edd-438e-8c49-faa4e38e5a5e'));

		Assert::type(MetadataEntities\Modules\DevicesModule\IConnectorEntity::class, $connector);
		Assert::same('Blank', $connector->getName());
		Assert::same('blank', $connector->getType());
	}

}

$test_case = new ConnectorsRepositoryTest();
$test_case->run();
