<?php declare(strict_types = 1);

/**
 * IControlManager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.4.0
 *
 * @date           29.09.21
 */

namespace FastyBird\DevicesModule\Models\Connectors\Controls;

use FastyBird\DevicesModule\Entities;
use Nette\Utils;

/**
 * Connectors controls entities manager interface
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
	 * @return Entities\Connectors\Controls\IControl
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Connectors\Controls\IControl;

	/**
	 * @param Entities\Connectors\Controls\IControl $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Connectors\Controls\IControl
	 */
	public function update(
		Entities\Connectors\Controls\IControl $entity,
		Utils\ArrayHash $values
	): Entities\Connectors\Controls\IControl;

	/**
	 * @param Entities\Connectors\Controls\IControl $entity
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Connectors\Controls\IControl $entity
	): bool;

}
