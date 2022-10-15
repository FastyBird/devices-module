<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\Entities;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineCrud\Exceptions as DoctrineCrudExceptions;
use IPub\DoctrineOrmQuery\Exceptions as DoctrineOrmQueryExceptions;
use Nette;
use Nette\Utils;
use RuntimeException;
use Tests\Cases\Unit\DbTestCase;
use function assert;

final class DevicePropertyEntityTest extends DbTestCase
{

	/**
	 * @throws DoctrineOrmQueryExceptions\InvalidStateException
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 * @throws Exceptions\InvalidArgument
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 */
	public function testAddChildProperty(): void
	{
		$manager = $this->getContainer()->getByType(Models\Devices\Properties\PropertiesManager::class);

		$repository = $this->getContainer()->getByType(Models\Devices\Properties\PropertiesRepository::class);

		$findQuery = new Queries\FindDeviceProperties();
		$findQuery->byIdentifier('status_led');

		$parent = $repository->findOneBy($findQuery);

		self::assertIsObject($parent);
		self::assertTrue($parent instanceof Entities\Devices\Properties\Variable);
		self::assertSame('status_led', $parent->getIdentifier());

		$child = $manager->create(Utils\ArrayHash::from([
			'entity' => Entities\Devices\Properties\Variable::class,
			'identifier' => 'new-child-property',
			'device' => $parent->getDevice(),
			'parent' => $parent,
		]));

		self::assertTrue($child instanceof Entities\Devices\Properties\Variable);
		self::assertSame('new-child-property', $child->getIdentifier());
		self::assertSame($parent, $child->getParent());
	}

	/**
	 * @throws DoctrineCrudExceptions\InvalidArgumentException
	 * @throws DoctrineOrmQueryExceptions\InvalidStateException
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 * @throws Exceptions\InvalidArgument
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 */
	public function testRemoveChildProperty(): void
	{
		$manager = $this->getContainer()->getByType(Models\Devices\Properties\PropertiesManager::class);

		$repository = $this->getContainer()->getByType(Models\Devices\Properties\PropertiesRepository::class);

		$findQuery = new Queries\FindDeviceProperties();
		$findQuery->byIdentifier('status_led');

		$parent = $repository->findOneBy($findQuery);

		self::assertIsObject($parent);
		self::assertTrue($parent instanceof Entities\Devices\Properties\Variable);
		self::assertSame('status_led', $parent->getIdentifier());

		$child = $manager->create(Utils\ArrayHash::from([
			'entity' => Entities\Devices\Properties\Variable::class,
			'identifier' => 'new-child-property',
			'device' => $parent->getDevice(),
			'parent' => $parent,
		]));

		self::assertTrue($child instanceof Entities\Devices\Properties\Variable);
		self::assertSame('new-child-property', $child->getIdentifier());
		self::assertSame($parent, $child->getParent());

		$findQuery = new Queries\FindDeviceProperties();
		$findQuery->byIdentifier('status_led');

		$parent = $repository->findOneBy($findQuery);

		assert($parent instanceof Entities\Devices\Properties\Property);

		self::assertCount(1, $parent->getChildren());

		$manager->delete($child);

		$findQuery = new Queries\FindDeviceProperties();
		$findQuery->byIdentifier('status_led');

		$parent = $repository->findOneBy($findQuery);

		self::assertIsObject($parent);
		self::assertTrue($parent instanceof Entities\Devices\Properties\Variable);
		self::assertSame('status_led', $parent->getIdentifier());
		self::assertCount(0, $parent->getChildren());
	}

	/**
	 * @throws DoctrineCrudExceptions\InvalidArgumentException
	 * @throws DoctrineOrmQueryExceptions\InvalidStateException
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 * @throws Exceptions\InvalidArgument
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 */
	public function testRemoveParentProperty(): void
	{
		$manager = $this->getContainer()->getByType(Models\Devices\Properties\PropertiesManager::class);

		$repository = $this->getContainer()->getByType(Models\Devices\Properties\PropertiesRepository::class);

		$findQuery = new Queries\FindDeviceProperties();
		$findQuery->byIdentifier('status_led');

		$parent = $repository->findOneBy($findQuery);

		self::assertIsObject($parent);
		self::assertTrue($parent instanceof Entities\Devices\Properties\Variable);
		self::assertSame('status_led', $parent->getIdentifier());

		$child = $manager->create(Utils\ArrayHash::from([
			'entity' => Entities\Devices\Properties\Variable::class,
			'identifier' => 'new-child-property',
			'device' => $parent->getDevice(),
			'parent' => $parent,
		]));

		self::assertTrue($child instanceof Entities\Devices\Properties\Variable);
		self::assertSame('new-child-property', $child->getIdentifier());
		self::assertSame($parent, $child->getParent());

		$findQuery = new Queries\FindDeviceProperties();
		$findQuery->byIdentifier('new-child-property');

		$child = $repository->findOneBy($findQuery);

		self::assertIsObject($child);
		self::assertTrue($child instanceof Entities\Devices\Properties\Variable);

		$findQuery = new Queries\FindDeviceProperties();
		$findQuery->byIdentifier('status_led');

		$parent = $repository->findOneBy($findQuery);

		assert($parent instanceof Entities\Devices\Properties\Property);

		self::assertCount(1, $parent->getChildren());

		$manager->delete($parent);

		$findQuery = new Queries\FindDeviceProperties();
		$findQuery->byIdentifier('new-child-property');

		$child = $repository->findOneBy($findQuery);

		self::assertIsNotObject($child);
	}

}
