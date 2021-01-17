<?php declare(strict_types = 1);

/**
 * IConnectorsManager.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           17.01.21
 */

namespace FastyBird\DevicesModule\Models\Devices\Connectors;

use FastyBird\DevicesModule\Entities;
use Nette\Utils;

/**
 * Devices connectors entities manager interface
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
	 * @return Entities\Devices\Connectors\IConnector
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Devices\Connectors\IConnector;

	/**
	 * @param Entities\Devices\Connectors\IConnector $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Devices\Connectors\IConnector
	 */
	public function update(
		Entities\Devices\Connectors\IConnector $entity,
		Utils\ArrayHash $values
	): Entities\Devices\Connectors\IConnector;

}
