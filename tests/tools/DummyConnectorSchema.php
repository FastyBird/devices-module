<?php declare(strict_types = 1);

namespace Tests\Tools;

use FastyBird\DevicesModule\Schemas;
use FastyBird\Metadata\Types as MetadataTypes;

final class DummyConnectorSchema extends Schemas\Connectors\ConnectorSchema
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES . '/connector/dummy';

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass(): string
	{
		return DummyConnectorEntity::class;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

}
