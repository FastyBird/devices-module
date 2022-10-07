<?php declare(strict_types = 1);

/**
 * Device.php
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
 * Device property entity hydrator
 *
 * @phpstan-template TEntityClass of Entities\Devices\Properties\Property
 * @phpstan-extends  Property<TEntityClass>
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
abstract class Device extends Property
{

	/** @var Array<string> */
	protected array $relationships = [
		Schemas\Devices\Properties\Property::RELATIONSHIPS_DEVICE,
		Schemas\Devices\Properties\Property::RELATIONSHIPS_PARENT,
	];

}
