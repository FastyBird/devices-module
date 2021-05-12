<?php declare(strict_types = 1);

/**
 * IRow.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           26.10.18
 */

namespace FastyBird\DevicesModule\Entities\Channels\Configuration;

use FastyBird\DevicesModule\Entities;

/**
 * Channel configuration row entity interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IRow extends Entities\IRow
{

	/**
	 * @return Entities\Channels\IChannel
	 */
	public function getChannel(): Entities\Channels\IChannel;

}
