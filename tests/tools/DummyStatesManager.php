<?php declare(strict_types = 1);

namespace Tests\Tools;

use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\States;
use Nette\Utils;
use Ramsey\Uuid;
use RuntimeException;

class DummyStatesManager implements Models\States\IPropertiesManager
{

	public function create(Uuid\UuidInterface $id, Utils\ArrayHash $values): States\IProperty
	{
		throw new RuntimeException('Thi is dummy service');
	}

	public function update(States\IProperty $state, Utils\ArrayHash $values): States\IProperty
	{
		throw new RuntimeException('Thi is dummy service');
	}

	public function updateState(States\IProperty $state, Utils\ArrayHash $values): States\IProperty
	{
		throw new RuntimeException('Thi is dummy service');
	}

	public function delete(States\IProperty $state): bool
	{
		throw new RuntimeException('Thi is dummy service');
	}

}
