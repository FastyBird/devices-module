<?php declare(strict_types = 1);

/**
 * IConnectorPropertiesManager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          1.0.0
 *
 * @date           08.02.22
 */

namespace FastyBird\Module\Devices\Models\States;

use FastyBird\Module\Devices\States;
use Nette\Utils;
use Ramsey\Uuid;

/**
 * Connector properties manager interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IConnectorPropertiesManager
{

	public function create(Uuid\UuidInterface $id, Utils\ArrayHash $values): States\ConnectorProperty;

	public function update(States\ConnectorProperty $state, Utils\ArrayHash $values): States\ConnectorProperty;

	public function delete(States\ConnectorProperty $state): bool;

}
