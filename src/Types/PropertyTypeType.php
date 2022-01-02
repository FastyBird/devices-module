<?php declare(strict_types = 1);

/**
 * PropertyTypeType.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.9.0
 *
 * @date           02.01.22
 */

namespace FastyBird\DevicesModule\Types;

use Consistence;

/**
 * Property type
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Types
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class PropertyTypeType extends Consistence\Enum\Enum
{

	/**
	 * Define states
	 */
	public const TYPE_STATIC = 'static';
	public const TYPE_DYNAMIC = 'dynamic';

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return (string) self::getValue();
	}

}
