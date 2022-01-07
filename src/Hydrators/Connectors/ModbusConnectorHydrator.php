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
use FastyBird\DevicesModule\Schemas;
use IPub\JsonAPIDocument;

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
		0 => 'name',
		1 => 'enabled',

		'serial_interface' => 'serialInterface',
		'baud_rate'        => 'baudRate',
	];

	/**
	 * {@inheritDoc}
	 */
	protected function getEntityName(): string
	{
		return Entities\Connectors\ModbusConnector::class;
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return string|null
	 */
	protected function hydrateSerialInterfaceAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): ?string
	{
		if (
			!is_scalar($attributes->get('serial_interface'))
			|| (string) $attributes->get('serial_interface') === ''
		) {
			return null;
		}

		return (string) $attributes->get('serial_interface');
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return string|null
	 */
	protected function hydrateBaudRateAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): ?int
	{
		if (
			!is_scalar($attributes->get('baud_rate'))
			|| (string) $attributes->get('baud_rate') === ''
		) {
			return null;
		}

		return (int) $attributes->get('baud_rate');
	}

}
