<?php declare(strict_types = 1);

/**
 * HardwareManufacturerType.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Types
 * @since          0.1.0
 *
 * @date           30.11.17
 */

namespace FastyBird\DevicesModule\Types;

use Consistence;

/**
 * Doctrine2 DB type for machine device manufacturer column
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Types
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class HardwareManufacturerType extends Consistence\Enum\Enum
{

	/**
	 * Define data types
	 */
	public const MANUFACTURER_GENERIC = 'generic';
	public const MANUFACTURER_FASTYBIRD = 'fastybird';
	public const MANUFACTURER_ITEAD = 'itead';
	public const MANUFACTURER_AI_THINKER = 'ai_thinker';

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return (string) self::getValue();
	}

}
