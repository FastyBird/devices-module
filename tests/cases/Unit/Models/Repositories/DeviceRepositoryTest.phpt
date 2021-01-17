<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineOrmQuery;
use Tester\Assert;

require_once __DIR__ . '/../../../../bootstrap.php';
require_once __DIR__ . '/../../DbTestCase.php';

/**
 * @testCase
 */
final class DeviceRepositoryTest extends DbTestCase
{

	public function testReadOne(): void
	{
		/** @var Models\Devices\DeviceRepository $repository */
		$repository = $this->getContainer()->getByType(Models\Devices\DeviceRepository::class);

		$findQuery = new Queries\FindDevicesQuery();
		$findQuery->byIdentifier('first-device');

		$parent = $repository->findOneBy($findQuery);

		Assert::true(is_object($parent));
		Assert::type(Entities\Devices\Device::class, $parent);
		Assert::same('first-device', $parent->getIdentifier());

		$findQuery = new Queries\FindDevicesQuery();
		$findQuery->forParent($parent);

		$entity = $repository->findOneBy($findQuery);

		Assert::true(is_object($entity));
		Assert::type(Entities\Devices\Device::class, $entity);
		Assert::same('child-device', $entity->getIdentifier());
	}

	public function testReadResultSet(): void
	{
		/** @var Models\Devices\DeviceRepository $repository */
		$repository = $this->getContainer()->getByType(Models\Devices\DeviceRepository::class);

		$findQuery = new Queries\FindDevicesQuery();

		$resultSet = $repository->getResultSet($findQuery);

		Assert::type(DoctrineOrmQuery\ResultSet::class, $resultSet);
		Assert::same(4, $resultSet->getTotalCount());
	}

}

$test_case = new DeviceRepositoryTest();
$test_case->run();
