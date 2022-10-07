<?php declare(strict_types = 1);

namespace Tests\Tools;

use FastyBird\DevicesModule\Connectors;
use Ramsey\Uuid;

class DummyConnector implements Connectors\Connector
{

	public function getId(): Uuid\UuidInterface
	{
		return Uuid\Uuid::fromString('7a3dd94c-7294-46fd-8c61-1b375c313d4d');
	}

	public function execute(): void
	{
		// NOT IMPLEMENTED
	}

	public function terminate(): void
	{
		// NOT IMPLEMENTED
	}

	public function hasUnfinishedTasks(): bool
	{
		return false;
	}

}
