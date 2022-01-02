<?php declare(strict_types = 1);

/**
 * DevicePropertyHydrator.php
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
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends PropertyHydrator<Entities\Devices\Properties\IProperty>
 */
final class DevicePropertyHydrator extends PropertyHydrator
{

	/** @var string[] */
	protected array $relationships = [
		Schemas\Devices\Properties\PropertySchema::RELATIONSHIPS_DEVICE,
	];

	/**
	 * {@inheritDoc}
	 */
	protected function getEntityName(): string
	{
		return Entities\Devices\Properties\Property::class;
	}

}
