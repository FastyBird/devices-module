<?php declare(strict_types = 1);

/**
 * TuyaConnectorHydrator.php
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
 * Tuya Connector entity hydrator
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends ConnectorHydrator<Entities\Connectors\ITuyaConnector>
 */
final class TuyaConnectorHydrator extends ConnectorHydrator
{

	/** @var string[] */
	protected array $attributes = [
		'name',
		'enabled',
	];

	/**
	 * {@inheritDoc}
	 */
	public function getEntityName(): string
	{
		return Entities\Connectors\TuyaConnector::class;
	}

}
