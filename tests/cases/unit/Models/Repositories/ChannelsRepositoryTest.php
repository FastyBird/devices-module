<?php declare(strict_types = 1);

namespace FastyBird\Module\Devices\Tests\Cases\Unit\Models\Repositories;

use FastyBird\Module\Devices\Exceptions;
use FastyBird\Module\Devices\Models;
use FastyBird\Module\Devices\Queries;
use FastyBird\Module\Devices\Tests\Cases\Unit\DbTestCase;
use IPub\DoctrineOrmQuery\Exceptions as DoctrineOrmQueryExceptions;
use Nette;
use RuntimeException;

final class ChannelsRepositoryTest extends DbTestCase
{

	/**
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 * @throws Exceptions\InvalidArgument
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 */
	public function testReadOne(): void
	{
		$repository = $this->getContainer()->getByType(Models\Channels\ChannelsRepository::class);

		$findQuery = new Queries\FindChannels();
		$findQuery->byIdentifier('channel-one');

		$entity = $repository->findOneBy($findQuery);

		self::assertIsObject($entity);
		self::assertSame('channel-one', $entity->getIdentifier());
	}

	/**
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 * @throws Exceptions\InvalidArgument
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 */
	public function testReadResultSet(): void
	{
		$repository = $this->getContainer()->getByType(Models\Channels\ChannelsRepository::class);

		$findQuery = new Queries\FindChannels();

		$resultSet = $repository->getResultSet($findQuery);

		self::assertSame(3, $resultSet->getTotalCount());
	}

}