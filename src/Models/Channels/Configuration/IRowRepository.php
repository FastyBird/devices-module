<?php declare(strict_types = 1);

/**
 * IRowRepository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           24.03.20
 */

namespace FastyBird\DevicesModule\Models\Channels\Configuration;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineOrmQuery;

/**
 * Channel configuration row repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IRowRepository
{

	/**
	 * @param Queries\FindChannelConfigurationQuery $queryObject
	 * @param string $type
	 *
	 * @return Entities\Channels\Configuration\IRow|null
	 *
	 * @phpstan-template T of Entities\Channels\Configuration\Row
	 * @phpstan-param    Queries\FindChannelConfigurationQuery<T> $queryObject
	 * @phpstan-param    class-string<T> $type
	 */
	public function findOneBy(
		Queries\FindChannelConfigurationQuery $queryObject,
		string $type = Entities\Channels\Configuration\Row::class
	): ?Entities\Channels\Configuration\IRow;

	/**
	 * @param Queries\FindChannelConfigurationQuery $queryObject
	 * @param string $type
	 *
	 * @return DoctrineOrmQuery\ResultSet
	 *
	 * @phpstan-template T of Entities\Channels\Configuration\Row
	 * @phpstan-param    Queries\FindChannelConfigurationQuery<T> $queryObject
	 * @phpstan-param    class-string<T> $type
	 * @phpstan-return   DoctrineOrmQuery\ResultSet<T>
	 */
	public function getResultSet(
		Queries\FindChannelConfigurationQuery $queryObject,
		string $type = Entities\Channels\Configuration\Row::class
	): DoctrineOrmQuery\ResultSet;

}
