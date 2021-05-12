<?php declare(strict_types = 1);

/**
 * IConnectorsManager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           16.04.21
 */

namespace FastyBird\DevicesModule\Models\Connectors;

use FastyBird\DevicesModule\Entities;
use Nette\Utils;

/**
 * Connectors entities manager interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IConnectorsManager
{

	/**
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Connectors\IConnector
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Connectors\IConnector;

	/**
	 * @param Entities\Connectors\IConnector $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Connectors\IConnector
	 */
	public function update(
		Entities\Connectors\IConnector $entity,
		Utils\ArrayHash $values
	): Entities\Connectors\IConnector;

	/**
	 * @param Entities\Connectors\IConnector $entity
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Connectors\IConnector $entity
	): bool;

}
