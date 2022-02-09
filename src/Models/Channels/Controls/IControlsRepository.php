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

namespace FastyBird\DevicesModule\Models\Channels\Controls;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineOrmQuery;

/**
 * Device channel control repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IControlsRepository
{

	/**
	 * @param Queries\FindChannelControlsQuery $queryObject
	 *
	 * @return Entities\Channels\Controls\IControl|null
	 */
	public function findOneBy(Queries\FindChannelControlsQuery $queryObject): ?Entities\Channels\Controls\IControl;

	/**
	 * @param Queries\FindChannelControlsQuery $queryObject
	 *
	 * @return DoctrineOrmQuery\ResultSet
	 *
	 * @phpstan-return DoctrineOrmQuery\ResultSet<Entities\Channels\Controls\IControl>
	 */
	public function getResultSet(
		Queries\FindChannelControlsQuery $queryObject
	): DoctrineOrmQuery\ResultSet;

}
