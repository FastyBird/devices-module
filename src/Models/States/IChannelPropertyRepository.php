<?php declare(strict_types = 1);

/**
 * IDevicePropertyRepository.php
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

use FastyBird\DevicesModule\States;
use Ramsey\Uuid;

/**
 * Channel property repository interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IChannelPropertyRepository extends IPropertyRepository
{

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return States\IChannelProperty|null
	 */
	public function findOne(
		Uuid\UuidInterface $id
	): ?States\IChannelProperty;

}
