<?php declare(strict_types = 1);

/**
 * IControlManager.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           09.06.19
 */

namespace FastyBird\DevicesModule\Models\Channels\Controls;

use FastyBird\DevicesModule\Entities;
use Nette\Utils;

/**
 * Channels controls entities manager interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IControlsManager
{

	/**
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Channels\Controls\IControl
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Channels\Controls\IControl;

	/**
	 * @param Entities\Channels\Controls\IControl $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Channels\Controls\IControl
	 */
	public function update(
		Entities\Channels\Controls\IControl $entity,
		Utils\ArrayHash $values
	): Entities\Channels\Controls\IControl;

	/**
	 * @param Entities\Channels\Controls\IControl $entity
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Channels\Controls\IControl $entity
	): bool;

}
