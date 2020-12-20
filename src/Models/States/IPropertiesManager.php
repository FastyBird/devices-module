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
 * @date           03.03.20
 */

namespace FastyBird\DevicesModule\Models\States;

use FastyBird\DevicesModule\States;
use Nette\Utils;
use Ramsey\Uuid;

/**
 * Base properties manager interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IPropertiesManager
{

	/**
	 * @param Uuid\UuidInterface $id
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IProperty
	 */
	public function create(
		Uuid\UuidInterface $id,
		Utils\ArrayHash $values
	): States\IProperty;

	/**
	 * @param States\IProperty $state
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IProperty
	 */
	public function update(
		States\IProperty $state,
		Utils\ArrayHash $values
	): States\IProperty;

	/**
	 * @param States\IProperty $state
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IProperty
	 */
	public function updateState(
		States\IProperty $state,
		Utils\ArrayHash $values
	): States\IProperty;

	/**
	 * @param States\IProperty $state
	 *
	 * @return bool
	 */
	public function delete(
		States\IProperty $state
	): bool;

}
