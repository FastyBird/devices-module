<?php declare(strict_types = 1);

/**
 * NetworkDeviceHydrator.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 * @since          0.1.0
 *
 * @date           07.06.19
 */

namespace FastyBird\DevicesModule\Hydrators\Devices;

use FastyBird\DevicesModule\Entities;

/**
 * Network device entity hydrator
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class NetworkDeviceHydrator extends DeviceHydrator
{

	/**
	 * {@inheritDoc}
	 */
	protected function getEntityName(): string
	{
		return Entities\Devices\NetworkDevice::class;
	}

}
