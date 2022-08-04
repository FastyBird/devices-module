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

namespace FastyBird\DevicesModule\Models\Devices\Controls;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Queries;
use IPub\DoctrineOrmQuery;

/**
 * Device control repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IControlsRepository
{

	/**
	 * @param Queries\FindDeviceControlsQuery $queryObject
	 *
	 * @return Entities\Devices\Controls\IControl|null
	 */
	public function findOneBy(Queries\FindDeviceControlsQuery $queryObject): ?Entities\Devices\Controls\IControl;

	/**
	 * @param Queries\FindDeviceControlsQuery $queryObject
	 *
	 * @return Entities\Devices\Controls\IControl[]
	 */
	public function findAllBy(Queries\FindDeviceControlsQuery $queryObject): array;

	/**
	 * @param Queries\FindDeviceControlsQuery $queryObject
	 *
	 * @return DoctrineOrmQuery\ResultSet<Entities\Devices\Controls\IControl>
	 */
	public function getResultSet(
		Queries\FindDeviceControlsQuery $queryObject
	): DoctrineOrmQuery\ResultSet;

}
