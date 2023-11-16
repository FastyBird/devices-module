<?php declare(strict_types = 1);

namespace FastyBird\Module\Devices\Tests\Cases\Unit\Models\Configuration\Repositories;

use Error;
use FastyBird\Library\Bootstrap\Exceptions as BootstrapExceptions;
use FastyBird\Library\Metadata\Documents as MetadataDocuments;
use FastyBird\Library\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Module\Devices\Exceptions;
use FastyBird\Module\Devices\Models;
use FastyBird\Module\Devices\Queries;
use FastyBird\Module\Devices\Tests\Cases\Unit\DbTestCase;
use Nette;
use Ramsey\Uuid;
use RuntimeException;

final class DevicesRepositoryTest extends DbTestCase
{

	/**
	 * @throws BootstrapExceptions\InvalidArgument
	 * @throws Exceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 * @throws Error
	 */
	public function testReadOne(): void
	{
		$repository = $this->getContainer()->getByType(Models\Configuration\Devices\Repository::class);

		$findQuery = new Queries\Configuration\FindDevices();
		$findQuery->byIdentifier('first-device');

		$entity = $repository->findOneBy($findQuery);

		self::assertIsObject($entity);
		self::assertSame('first-device', $entity->getIdentifier());

		$findQuery = new Queries\Configuration\FindDevices();
		$findQuery->startWithIdentifier('first-');

		$entity = $repository->findOneBy($findQuery);

		self::assertIsObject($entity);
		self::assertSame('first-device', $entity->getIdentifier());

		$findQuery = new Queries\Configuration\FindDevices();
		$findQuery->endWithIdentifier('st-device');

		$entity = $repository->findOneBy($findQuery);

		self::assertIsObject($entity);
		self::assertSame('first-device', $entity->getIdentifier());

		$findQuery = new Queries\Configuration\FindDevices();
		$findQuery->byIdentifier('invalid');

		$entity = $repository->findOneBy($findQuery);

		self::assertNull($entity);

		$findQuery = new Queries\Configuration\FindDevices();
		$findQuery->byId(Uuid\Uuid::fromString('69786d15-fd0c-4d9f-9378-33287c2009fa'));

		$entity = $repository->findOneBy($findQuery);

		self::assertIsObject($entity);
		self::assertSame('first-device', $entity->getIdentifier());

		$findQuery = new Queries\Configuration\FindDevices();
		$findQuery->byConnectorId(Uuid\Uuid::fromString('17c59dfa-2edd-438e-8c49-faa4e38e5a5e'));

		$entity = $repository->findOneBy($findQuery);

		self::assertIsObject($entity);
		self::assertSame('first-device', $entity->getIdentifier());
	}

	/**
	 * @throws BootstrapExceptions\InvalidArgument
	 * @throws Exceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 * @throws Error
	 */
	public function testReadAll(): void
	{
		$repository = $this->getContainer()->getByType(Models\Configuration\Devices\Repository::class);

		$findQuery = new Queries\Configuration\FindDevices();

		$entities = $repository->findAllBy($findQuery);

		self::assertCount(4, $entities);
	}

	/**
	 * @throws BootstrapExceptions\InvalidArgument
	 * @throws Exceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 * @throws Error
	 */
	public function testReadAllByParent(): void
	{
		$repository = $this->getContainer()->getByType(Models\Configuration\Devices\Repository::class);

		$findQuery = new Queries\Configuration\FindDevices();
		$findQuery->byIdentifier('first-device');

		$parent = $repository->findOneBy($findQuery);

		self::assertInstanceOf(MetadataDocuments\DevicesModule\Device::class, $parent);
		self::assertSame('69786d15-fd0c-4d9f-9378-33287c2009fa', $parent->getId()->toString());

		$findQuery = new Queries\Configuration\FindDevices();
		$findQuery->forParent($parent);

		$entities = $repository->findAllBy($findQuery);

		self::assertCount(1, $entities);
	}

	/**
	 * @throws BootstrapExceptions\InvalidArgument
	 * @throws Exceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 * @throws Error
	 */
	public function testReadAllByChild(): void
	{
		$repository = $this->getContainer()->getByType(Models\Configuration\Devices\Repository::class);

		$findQuery = new Queries\Configuration\FindDevices();
		$findQuery->byIdentifier('child-device');

		$child = $repository->findOneBy($findQuery);

		self::assertInstanceOf(MetadataDocuments\DevicesModule\Device::class, $child);
		self::assertSame('a1036ff8-6ee8-4405-aaed-58bae0814596', $child->getId()->toString());

		$findQuery = new Queries\Configuration\FindDevices();
		$findQuery->forChild($child);

		$entities = $repository->findAllBy($findQuery);

		self::assertCount(1, $entities);
	}

	/**
	 * @throws BootstrapExceptions\InvalidArgument
	 * @throws Exceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 * @throws Error
	 */
	public function testReadAllWithChannels(): void
	{
		$repository = $this->getContainer()->getByType(Models\Configuration\Devices\Repository::class);

		$findQuery = new Queries\Configuration\FindDevices();
		$findQuery->withChannels();

		$entities = $repository->findAllBy($findQuery);

		self::assertCount(2, $entities);
	}

}
