<?php declare(strict_types = 1);

/**
 * ConnectorDynamic.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 * @since          0.31.0
 *
 * @date           08.02.22
 */

namespace FastyBird\DevicesModule\Hydrators\Properties;

use FastyBird\DevicesModule\Entities;

/**
 * Connector property entity hydrator
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends Connector<Entities\Connectors\Properties\Dynamic>
 */
final class ConnectorDynamic extends Connector
{

	/**
	 * {@inheritDoc}
	 */
	public function getEntityName(): string
	{
		return Entities\Connectors\Properties\Dynamic::class;
	}

}
