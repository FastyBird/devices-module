<?php declare(strict_types = 1);

/**
 * ModbusConnectorHydrator.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 * @since          0.6.0
 *
 * @date           07.12.21
 */

namespace FastyBird\DevicesModule\Hydrators\Connectors;

use FastyBird\DevicesModule\Entities;

/**
 * Modbus Connector entity hydrator
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends ConnectorHydrator<Entities\Connectors\IModbusConnector>
 */
final class ModbusConnectorHydrator extends ConnectorHydrator
{

	/** @var string[] */
	protected array $attributes = [
		'name',
		'serial_interface',
		'baud_rate',
	];

	/**
	 * {@inheritDoc}
	 */
	protected function getEntityName(): string
	{
		return Entities\Connectors\ModbusConnector::class;
	}

}
