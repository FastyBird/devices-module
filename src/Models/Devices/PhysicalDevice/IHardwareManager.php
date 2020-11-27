<?php declare(strict_types = 1);

/**
 * IHardwareManager.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           19.03.20
 */

namespace FastyBird\DevicesModule\Models\Devices\PhysicalDevice;

use FastyBird\DevicesModule\Entities;
use Nette\Utils;

/**
 * Device hardware entities manager interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IHardwareManager
{

	/**
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Devices\PhysicalDevice\IHardware
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Devices\PhysicalDevice\IHardware;

	/**
	 * @param Entities\Devices\PhysicalDevice\IHardware $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Devices\PhysicalDevice\IHardware
	 */
	public function update(
		Entities\Devices\PhysicalDevice\IHardware $entity,
		Utils\ArrayHash $values
	): Entities\Devices\PhysicalDevice\IHardware;

}
