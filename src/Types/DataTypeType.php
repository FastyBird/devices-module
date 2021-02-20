<?php declare(strict_types = 1);

/**
 * DataTypeType.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Types
 * @since          0.1.0
 *
 * @date           24.09.18
 */

namespace FastyBird\DevicesModule\Types;

use Consistence;

/**
 * Device or channel property data types
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Types
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class DataTypeType extends Consistence\Enum\Enum
{

	/**
	 * Define data types
	 */
	public const DATA_TYPE_CHAR = 'char';
	public const DATA_TYPE_UCHAR = 'uchar';
	public const DATA_TYPE_SHORT = 'short';
	public const DATA_TYPE_USHORT = 'ushort';
	public const DATA_TYPE_INT = 'int';
	public const DATA_TYPE_UINT = 'uint';
	public const DATA_TYPE_FLOAT = 'float';
	public const DATA_TYPE_BOOLEAN = 'bool';
	public const DATA_TYPE_STRING = 'string';
	public const DATA_TYPE_ENUM = 'enum';
	public const DATA_TYPE_COLOR = 'color';

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return (string) self::getValue();
	}

	/**
	 * @return bool
	 */
	public function isInteger(): bool
	{
		return self::equalsValue(self::DATA_TYPE_CHAR)
			|| self::equalsValue(self::DATA_TYPE_UCHAR)
			|| self::equalsValue(self::DATA_TYPE_SHORT)
			|| self::equalsValue(self::DATA_TYPE_USHORT)
			|| self::equalsValue(self::DATA_TYPE_INT)
			|| self::equalsValue(self::DATA_TYPE_UINT);
	}

}
