<?php declare(strict_types = 1);

/**
 * IControl.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           28.07.18
 */

namespace FastyBird\DevicesModule\Entities\Devices\Controls;

use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\DevicesModule\Entities;
use IPub\DoctrineTimestampable;

/**
 * Control settings entity interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IControl extends DatabaseEntities\IEntity,
	DoctrineTimestampable\Entities\IEntityCreated, DoctrineTimestampable\Entities\IEntityUpdated
{

	/**
	 * @return Entities\Devices\IDevice
	 */
	public function getDevice(): Entities\Devices\IDevice;

	/**
	 * @return string
	 */
	public function getName(): string;

}
