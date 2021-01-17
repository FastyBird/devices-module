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

namespace FastyBird\DevicesModule\Models\Devices\Hardware;

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
	 * @return Entities\Devices\Hardware\IHardware
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Devices\Hardware\IHardware;

	/**
	 * @param Entities\Devices\Hardware\IHardware $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Devices\Hardware\IHardware
	 */
	public function update(
		Entities\Devices\Hardware\IHardware $entity,
		Utils\ArrayHash $values
	): Entities\Devices\Hardware\IHardware;

}
