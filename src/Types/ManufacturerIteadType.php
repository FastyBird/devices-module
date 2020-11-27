<?php declare(strict_types = 1);

/**
 * ManufacturerIteadType.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Types
 * @since          0.1.0
 *
 * @date           06.07.18
 */

namespace FastyBird\DevicesModule\Types;

use Consistence;

/**
 * ITEAD manufacturer models types
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Types
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class ManufacturerIteadType extends Consistence\Enum\Enum
{

	/**
	 * Define itead models
	 */
	public const MODEL_SONOFF_BASIC = ModelType::MODEL_SONOFF_BASIC;
	public const MODEL_SONOFF_RF = ModelType::MODEL_SONOFF_RF;
	public const MODEL_SONOFF_TH = ModelType::MODEL_SONOFF_TH;
	public const MODEL_SONOFF_SV = ModelType::MODEL_SONOFF_SV;
	public const MODEL_SONOFF_SLAMPHER = ModelType::MODEL_SONOFF_SLAMPHER;
	public const MODEL_SONOFF_S20 = ModelType::MODEL_SONOFF_S20;
	public const MODEL_SONOFF_TOUCH = ModelType::MODEL_SONOFF_TOUCH;
	public const MODEL_SONOFF_POW = ModelType::MODEL_SONOFF_POW;
	public const MODEL_SONOFF_POW_R2 = ModelType::MODEL_SONOFF_POW_R2;
	public const MODEL_SONOFF_DUAL = ModelType::MODEL_SONOFF_DUAL;
	public const MODEL_SONOFF_DUAL_R2 = ModelType::MODEL_SONOFF_DUAL_R2;
	public const MODEL_SONOFF_4CH = ModelType::MODEL_SONOFF_4CH;
	public const MODEL_SONOFF_4CH_PRO = ModelType::MODEL_SONOFF_4CH_PRO;
	public const MODEL_SONOFF_RF_BRIDGE = ModelType::MODEL_SONOFF_RF_BRIDGE;
	public const MODEL_SONOFF_B1 = ModelType::MODEL_SONOFF_B1;
	public const MODEL_SONOFF_LED = ModelType::MODEL_SONOFF_LED;
	public const MODEL_SONOFF_T1_1CH = ModelType::MODEL_SONOFF_T1_1CH;
	public const MODEL_SONOFF_T1_2CH = ModelType::MODEL_SONOFF_T1_2CH;
	public const MODEL_SONOFF_T1_3CH = ModelType::MODEL_SONOFF_T1_3CH;
	public const MODEL_SONOFF_S31 = ModelType::MODEL_SONOFF_S31;
	public const MODEL_SONOFF_SC = ModelType::MODEL_SONOFF_SC;
	public const MODEL_SONOFF_SC_PRO = ModelType::MODEL_SONOFF_SC_PRO;
	public const MODEL_SONOFF_PS_15 = ModelType::MODEL_SONOFF_PS_15;

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return (string) self::getValue();
	}

}
