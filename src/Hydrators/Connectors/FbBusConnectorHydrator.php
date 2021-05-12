<?php declare(strict_types = 1);

/**
 * FbBusConnectorHydrator.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 * @since          0.1.0
 *
 * @date           16.04.21
 */

namespace FastyBird\DevicesModule\Hydrators\Connectors;

use FastyBird\DevicesModule\Entities;

/**
 * FB bus Connector entity hydrator
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class FbBusConnectorHydrator extends ConnectorHydrator
{

	/** @var string[] */
	protected array $attributes = [
		'name',
		'address',
		'serial_interface',
		'baud_rate',
	];

	/**
	 * {@inheritDoc}
	 */
	protected function getEntityName(): string
	{
		return Entities\Connectors\FbBusConnector::class;
	}

}
