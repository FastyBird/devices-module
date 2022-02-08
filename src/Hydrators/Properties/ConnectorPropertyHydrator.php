<?php declare(strict_types = 1);

/**
 * ConnectorPropertyHydrator.php
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
use FastyBird\DevicesModule\Schemas;

/**
 * Connector property entity hydrator
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-template TEntityClass of Entities\Connectors\Properties\IProperty
 * @phpstan-extends  PropertyHydrator<TEntityClass>
 */
abstract class ConnectorPropertyHydrator extends PropertyHydrator
{

	/** @var string[] */
	protected array $relationships = [
		Schemas\Connectors\Properties\PropertySchema::RELATIONSHIPS_CONNECTOR,
	];

}
