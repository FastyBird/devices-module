<?php declare(strict_types = 1);

namespace Tests\Fixtures;

use FastyBird\DevicesModule\Schemas;
use FastyBird\Metadata\Types as MetadataTypes;

final class DummyConnectorSchema extends Schemas\Connectors\Connector
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = MetadataTypes\ModuleSource::SOURCE_MODULE_DEVICES . '/connector/dummy';

	public function getEntityClass(): string
	{
		return DummyConnectorEntity::class;
	}

	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

}
