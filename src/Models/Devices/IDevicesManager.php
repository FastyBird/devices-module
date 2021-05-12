<?php declare(strict_types = 1);

/**
 * IDevicesManager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           28.07.18
 */

namespace FastyBird\DevicesModule\Models\Devices;

use FastyBird\DevicesModule\Entities;
use Nette\Utils;

/**
 * Devices entities manager interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IDevicesManager
{

	/**
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Devices\IDevice
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Devices\IDevice;

	/**
	 * @param Entities\Devices\IDevice $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Devices\IDevice
	 */
	public function update(
		Entities\Devices\IDevice $entity,
		Utils\ArrayHash $values
	): Entities\Devices\IDevice;

	/**
	 * @param Entities\Devices\IDevice $entity
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Devices\IDevice $entity
	): bool;

}
