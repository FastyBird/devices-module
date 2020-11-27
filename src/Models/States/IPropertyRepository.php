<?php declare(strict_types = 1);

/**
 * IPropertyRepository.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           02.03.20
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
interface IPropertyRepository
{

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return States\IProperty|null
	 */
	public function findOne(
		Uuid\UuidInterface $id
	): ?States\IProperty;

}
