<?php declare(strict_types = 1);

namespace Tests\Tools;

use FastyBird\DevicesModule\States;
use Ramsey\Uuid;
use RuntimeException;

class DummyStateRepository
{

	public function findOne(Uuid\UuidInterface $id): ?States\Property
	{
		throw new RuntimeException('Thi is dummy service');
	}

}
