<?php declare(strict_types = 1);

/**
 * VirtualConnectorSchema.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Schemas
 * @since          0.6.0
 *
 * @date           07.12.21
 */

namespace FastyBird\DevicesModule\Schemas\Connectors;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Schemas;

/**
 * Modbus connector entity schema
 *
 * @package         FastyBird:DevicesModule!
 * @subpackage      Schemas
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends ConnectorSchema<Entities\Connectors\IVirtualConnector>
 */
final class VirtualConnectorSchema extends ConnectorSchema
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = 'devices-module/connector-virtual';

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass(): string
	{
		return Entities\Connectors\VirtualConnector::class;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

}
