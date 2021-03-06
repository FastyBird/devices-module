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

namespace FastyBird\DevicesModule\Entities\Connectors\Controls;

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
interface IControl extends Entities\IEntity,
	DoctrineTimestampable\Entities\IEntityCreated, DoctrineTimestampable\Entities\IEntityUpdated
{

	/**
	 * @return Entities\Connectors\IConnector
	 */
	public function getConnector(): Entities\Connectors\IConnector;

	/**
	 * @return string
	 */
	public function getName(): string;

}
