<?php declare(strict_types = 1);

/**
 * RuntimeException.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Exceptions
 * @since          0.1.0
 *
 * @date           17.03.20
 */

namespace FastyBird\DevicesModule\Exceptions;

use RuntimeException as PHPRuntimeException;

class RuntimeException extends PHPRuntimeException implements IException
{

}
