<?php declare(strict_types = 1);

namespace Tests\Tools;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;

/**
 * @ORM\Entity
 */
class DummyConnectorEntity extends Entities\Connectors\Connector
{

	public const CONNECTOR_TYPE = 'blank';

	/**
	 * {@inheritDoc}
	 */
	public function getType(): string
	{
		return 'dummy';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDiscriminatorName(): string
	{
		return 'dummy';
	}

}
