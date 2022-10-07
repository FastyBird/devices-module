<?php declare(strict_types = 1);

/**
 * Channel.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 * @since          0.9.0
 *
 * @date           02.01.22
 */

namespace FastyBird\DevicesModule\Hydrators\Properties;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Schemas;

/**
 * Channel property entity hydrator
 *
 * @phpstan-template TEntityClass of Entities\Channels\Properties\Property
 * @phpstan-extends  Property<TEntityClass>
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
abstract class Channel extends Property
{

	/** @var Array<string> */
	protected array $relationships = [
		Schemas\Channels\Properties\Property::RELATIONSHIPS_CHANNEL,
		Schemas\Channels\Properties\Property::RELATIONSHIPS_PARENT,
	];

}
