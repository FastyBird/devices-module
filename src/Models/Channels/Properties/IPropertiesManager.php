<?php declare(strict_types = 1);

/**
 * IPropertiesManager.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           02.11.18
 */

namespace FastyBird\DevicesModule\Models\Channels\Properties;

use FastyBird\DevicesModule\Entities;
use Nette\Utils;

/**
 * Channels properties entities manager interface
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
	 * @return Entities\Channels\Properties\IProperty
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Channels\Properties\IProperty;

	/**
	 * @param Entities\Channels\Properties\IProperty $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Channels\Properties\IProperty
	 */
	public function update(
		Entities\Channels\Properties\IProperty $entity,
		Utils\ArrayHash $values
	): Entities\Channels\Properties\IProperty;

	/**
	 * @param Entities\Channels\Properties\IProperty $entity
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Channels\Properties\IProperty $entity
	): bool;

}
