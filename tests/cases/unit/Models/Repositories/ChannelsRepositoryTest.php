<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\Models\Repositories;

use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineOrmQuery\Exceptions as DoctrineOrmQueryExceptions;
use Nette;
use RuntimeException;
use Tests\Cases\Unit\DbTestCase;

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
