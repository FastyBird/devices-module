<?php declare(strict_types = 1);

/**
 * IControlsRepository.php
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
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineOrmQuery;

/**
 * Connector control repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IControlsRepository
{

	/**
	 * @param Queries\FindConnectorControlsQuery $queryObject
	 *
	 * @return Entities\Connectors\Controls\IControl|null
	 */
	public function findOneBy(Queries\FindConnectorControlsQuery $queryObject): ?Entities\Connectors\Controls\IControl;

	/**
	 * @param Queries\FindConnectorControlsQuery $queryObject
	 *
	 * @return DoctrineOrmQuery\ResultSet
	 *
	 * @phpstan-return DoctrineOrmQuery\ResultSet<Entities\Connectors\Controls\IControl>
	 */
	public function getResultSet(
		Queries\FindConnectorControlsQuery $queryObject
	): DoctrineOrmQuery\ResultSet;

}
