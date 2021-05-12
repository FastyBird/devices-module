<?php declare(strict_types = 1);

/**
 * IChannelsManager.php
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
use Nette\Utils;

/**
 * Channels entities manager interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IChannelsManager
{

	/**
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Channels\IChannel
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Channels\IChannel;

	/**
	 * @param Entities\Channels\IChannel $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Channels\IChannel
	 */
	public function update(
		Entities\Channels\IChannel $entity,
		Utils\ArrayHash $values
	): Entities\Channels\IChannel;

	/**
	 * @param Entities\Channels\IChannel $entity
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Channels\IChannel $entity
	): bool;

}
