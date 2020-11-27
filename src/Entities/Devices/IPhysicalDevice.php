<?php declare(strict_types = 1);

/**
 * IPhysicalDevice.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           11.05.19
 */

namespace FastyBird\DevicesModule\Entities\Devices;

use FastyBird\DevicesModule\Entities;

/**
 * Machine device entity interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IPhysicalDevice extends IDevice
{

	/**
	 * @param Entities\Devices\PhysicalDevice\IHardware|null $hardware
	 *
	 * @return void
	 */
	public function setHardware(?Entities\Devices\PhysicalDevice\IHardware $hardware): void;

	/**
	 * @return Entities\Devices\PhysicalDevice\IHardware|null
	 */
	public function getHardware(): ?Entities\Devices\PhysicalDevice\IHardware;

	/**
	 * @param Entities\Devices\PhysicalDevice\IFirmware|null $firmware
	 *
	 * @return void
	 */
	public function setFirmware(?Entities\Devices\PhysicalDevice\IFirmware $firmware): void;

	/**
	 * @return Entities\Devices\PhysicalDevice\IFirmware|null
	 */
	public function getFirmware(): ?Entities\Devices\PhysicalDevice\IFirmware;

}
