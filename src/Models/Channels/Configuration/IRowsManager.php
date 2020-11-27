<?php declare(strict_types = 1);

/**
 * IRowsManager.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           01.11.18
 */

namespace FastyBird\DevicesModule\Models\Channels\Configuration;

use FastyBird\DevicesModule\Entities;
use Nette\Utils;

/**
 * Device channel configuration entities manager interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IRowsManager
{

	/**
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Channels\Configuration\IRow
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Channels\Configuration\IRow;

	/**
	 * @param Entities\Channels\Configuration\IRow $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Channels\Configuration\IRow
	 */
	public function update(
		Entities\Channels\Configuration\IRow $entity,
		Utils\ArrayHash $values
	): Entities\Channels\Configuration\IRow;

	/**
	 * @param Entities\Channels\Configuration\IRow $entity
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Channels\Configuration\IRow $entity
	): bool;

}
