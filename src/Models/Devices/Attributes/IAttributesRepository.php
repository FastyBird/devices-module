<?php declare(strict_types = 1);

/**
 * IAttributesRepository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.57.0
 *
 * @date           22.04.22
 */

namespace FastyBird\DevicesModule\Models\Devices\Attributes;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineOrmQuery;

/**
 * Device attribute repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IAttributesRepository
{

	/**
	 * @param Queries\FindDeviceAttributesQuery $queryObject
	 *
	 * @return Entities\Devices\Attributes\IAttribute|null
	 */
	public function findOneBy(Queries\FindDeviceAttributesQuery $queryObject): ?Entities\Devices\Attributes\IAttribute;

	/**
	 * @param Queries\FindDeviceAttributesQuery $queryObject
	 *
	 * @return Entities\Devices\Attributes\IAttribute[]
	 */
	public function findAllBy(Queries\FindDeviceAttributesQuery $queryObject): array;

	/**
	 * @param Queries\FindDeviceAttributesQuery $queryObject
	 *
	 * @return DoctrineOrmQuery\ResultSet
	 *
	 * @phpstan-return DoctrineOrmQuery\ResultSet<Entities\Devices\Attributes\IAttribute>
	 */
	public function getResultSet(
		Queries\FindDeviceAttributesQuery $queryObject
	): DoctrineOrmQuery\ResultSet;

}
