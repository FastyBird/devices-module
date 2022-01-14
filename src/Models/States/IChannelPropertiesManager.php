<?php declare(strict_types = 1);

/**
 * IChannelPropertiesManager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.9.0
 *
 * @date           09.01.22
 */

namespace FastyBird\DevicesModule\Models\States;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\States;
use Nette\Utils;

/**
 * Channel properties manager interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IChannelPropertiesManager extends IPropertiesManager
{

	/**
	 * @param Entities\Channels\Properties\IProperty $property
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IChannelProperty
	 */
	public function create(
		Entities\Channels\Properties\IProperty $property,
		Utils\ArrayHash $values
	): States\IChannelProperty;

	/**
	 * @param States\IChannelProperty $state
	 * @param Utils\ArrayHash $values
	 *
	 * @return States\IChannelProperty
	 */
	public function update(
		Entities\Channels\Properties\IProperty $property,
		States\IChannelProperty $state,
		Utils\ArrayHash $values
	): States\IChannelProperty;

	/**
	 * @param States\IChannelProperty $state
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Channels\Properties\IProperty $property,
		States\IChannelProperty $state
	): bool;

}
