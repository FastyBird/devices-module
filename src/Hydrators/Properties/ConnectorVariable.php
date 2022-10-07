<?php declare(strict_types = 1);

/**
 * ConnectorVariable.php
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
 * @phpstan-extends Connector<Entities\Connectors\Properties\Variable>
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ConnectorVariable extends Connector
{

	public function getEntityName(): string
	{
		return Entities\Connectors\Properties\Variable::class;
	}

}
