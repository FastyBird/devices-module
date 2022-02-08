<?php declare(strict_types = 1);

/**
 * IPropertiesManager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.31.0
 *
 * @date           08.02.22
 */

namespace FastyBird\DevicesModule\Models\Connectors\Properties;

use FastyBird\DevicesModule\Entities;
use Nette\Utils;

/**
 * Connectors properties entities manager interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IPropertiesManager
{

	/**
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Connectors\Properties\IProperty
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Connectors\Properties\IProperty;

	/**
	 * @param Entities\Connectors\Properties\IProperty $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Connectors\Properties\IProperty
	 */
	public function update(
		Entities\Connectors\Properties\IProperty $entity,
		Utils\ArrayHash $values
	): Entities\Connectors\Properties\IProperty;

	/**
	 * @param Entities\Connectors\Properties\IProperty $entity
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Connectors\Properties\IProperty $entity
	): bool;

}
