<?php declare(strict_types = 1);

/**
 * LocalDevice.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           26.11.20
 */

namespace FastyBird\DevicesModule\Entities\Devices;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_local_physicals_devices",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Local connected devices"
 *     }
 * )
 */
class LocalDevice extends Device implements ILocalDevice
{

	use TPhysicalDevice;

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'type' => 'local',
		]);
	}

}
