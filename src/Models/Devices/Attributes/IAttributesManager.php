<?php declare(strict_types = 1);

/**
 * IAttributeManager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.57.0
 *
 * @date           22.04.22
 */

namespace FastyBird\DevicesModule\Models\Devices\Attributes;

use FastyBird\DevicesModule\Entities;
use Nette\Utils;

/**
 * Devices attributes entities manager interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IAttributesManager
{

	/**
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Devices\Attributes\IAttribute
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Devices\Attributes\IAttribute;

	/**
	 * @param Entities\Devices\Attributes\IAttribute $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Devices\Attributes\IAttribute
	 */
	public function update(
		Entities\Devices\Attributes\IAttribute $entity,
		Utils\ArrayHash $values
	): Entities\Devices\Attributes\IAttribute;

	/**
	 * @param Entities\Devices\Attributes\IAttribute $entity
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Devices\Attributes\IAttribute $entity
	): bool;

}
