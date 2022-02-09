<?php declare(strict_types = 1);

/**
 * IChannelsRepository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           23.04.17
 */

namespace FastyBird\DevicesModule\Models\Channels;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineOrmQuery;

/**
 * Channel repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IChannelsRepository
{

	/**
	 * @param Queries\FindChannelsQuery $queryObject
	 *
	 * @return Entities\Channels\IChannel|null
	 */
	public function findOneBy(Queries\FindChannelsQuery $queryObject): ?Entities\Channels\IChannel;

	/**
	 * @param Queries\FindChannelsQuery $queryObject
	 *
	 * @return Entities\Channels\IChannel[]
	 */
	public function findAllBy(Queries\FindChannelsQuery $queryObject): array;

	/**
	 * @param Queries\FindChannelsQuery $queryObject
	 *
	 * @return DoctrineOrmQuery\ResultSet
	 *
	 * @phpstan-return DoctrineOrmQuery\ResultSet<Entities\Channels\IChannel>
	 */
	public function getResultSet(
		Queries\FindChannelsQuery $queryObject
	): DoctrineOrmQuery\ResultSet;

}
