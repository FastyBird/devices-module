<?php declare(strict_types = 1);

namespace Tests\Tools;

use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\States;
use Ramsey\Uuid;
use RuntimeException;

class DummyStateRepository implements Models\States\IPropertyRepository
{

	public function findOne(Uuid\UuidInterface $id): ?States\IProperty
	{
		throw new RuntimeException('Thi is dummy service');
	}

}
