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

namespace FastyBird\DevicesModule\Models\Devices\Configuration;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineOrmQuery;

/**
 * Device configuration row repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IRowRepository
{

	/**
	 * @param Queries\FindDeviceConfigurationQuery $queryObject
	 *
	 * @return Entities\Devices\Configuration\IRow|null
	 */
	public function findOneBy(
		Queries\FindDeviceConfigurationQuery $queryObject
	): ?Entities\Devices\Configuration\IRow;

	/**
	 * @param Queries\FindDeviceConfigurationQuery $queryObject
	 *
	 * @return DoctrineOrmQuery\ResultSet
	 *
	 * @phpstan-return  DoctrineOrmQuery\ResultSet<Entities\Devices\Configuration\IRow>
	 */
	public function getResultSet(
		Queries\FindDeviceConfigurationQuery $queryObject
	): DoctrineOrmQuery\ResultSet;

}
