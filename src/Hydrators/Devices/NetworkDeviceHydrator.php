<?php declare(strict_types = 1);

/**
 * NetworkDeviceHydrator.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 * @since          0.9.0
 *
 * @date           07.02.22
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
 *
 * @phpstan-extends DeviceHydrator<Entities\Devices\INetworkDevice>
 */
final class NetworkDeviceHydrator extends DeviceHydrator
{

	/**
	 * {@inheritDoc}
	 */
	public function getEntityName(): string
	{
		return Entities\Devices\NetworkDevice::class;
	}

}
