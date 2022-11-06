<?php declare(strict_types = 1);

/**
 * Database.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Utilities
 * @since          0.73.0
 *
 * @date           26.10.22
 */

namespace FastyBird\Module\Devices\Utilities;

use function preg_replace;
use function strtolower;
use function strval;

class Api
{

	public static function fieldToJsonApi(string $field): string
	{
		return strtolower(strval(preg_replace('/(?<!^)[A-Z]/', '_$0', $field)));
	}

}
