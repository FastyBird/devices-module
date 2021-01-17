<?php declare(strict_types = 1);

/**
 * IFirmwareManager.php
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

namespace FastyBird\DevicesModule\Models\Devices\Firmware;

use FastyBird\DevicesModule\Entities;
use Nette\Utils;

/**
 * Device firmware entities manager interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IFirmwareManager
{

	/**
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Devices\Firmware\IFirmware
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Devices\Firmware\IFirmware;

	/**
	 * @param Entities\Devices\Firmware\IFirmware $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Devices\Firmware\IFirmware
	 */
	public function update(
		Entities\Devices\Firmware\IFirmware $entity,
		Utils\ArrayHash $values
	): Entities\Devices\Firmware\IFirmware;

}
