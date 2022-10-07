<?php declare(strict_types = 1);

/**
 * DeviceVariable.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 * @since          0.9.0
 *
 * @date           04.01.22
 */

namespace FastyBird\DevicesModule\Hydrators\Properties;

use FastyBird\DevicesModule\Entities;

/**
 * Device property entity hydrator
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends Device<Entities\Devices\Properties\Variable>
 */
final class DeviceVariable extends Device
{

	/**
	 * {@inheritDoc}
	 */
	public function getEntityName(): string
	{
		return Entities\Devices\Properties\Variable::class;
	}

}
