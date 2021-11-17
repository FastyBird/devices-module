<?php declare(strict_types = 1);

/**
 * PropertyHelper.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Helpers
 * @since          0.1.0
 *
 * @date           05.12.20
 */

namespace FastyBird\DevicesModule\Helpers;

use FastyBird\DevicesModule\Entities;
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;

/**
 * Property helpers
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Helpers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class PropertyHelper
{

	/**
	 * @param Entities\IProperty $property
	 * @param bool|float|int|string|null $value
	 *
	 * @return bool|float|int|string|ModulesMetadataTypes\ButtonPayloadType|ModulesMetadataTypes\SwitchPayloadType|null
	 */
	public static function normalizeValue(
		Entities\IProperty $property,
		$value
	) {
		if ($value === null) {
			return null;
		}

		if ($property->getDataType() !== null) {
			if ($property->getDataType()->isInteger()) {
				return intval($value);

			} elseif ($property->getDataType()->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_FLOAT)) {
				return floatval($value);

			} elseif ($property->getDataType()->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_STRING)) {
				return $value;

			} elseif ($property->getDataType()->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_BOOLEAN)) {
				return in_array(strtolower($value), ['true', 't', 'yes', 'y', '1', 'on'], true);

			} elseif (
				$property->getDataType()->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_BUTTON)
				&& ModulesMetadataTypes\ButtonPayloadType::isValidValue((string) $value)
			) {
				return ModulesMetadataTypes\ButtonPayloadType::get($value);

			} elseif (
				$property->getDataType()->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_SWITCH)
				&& ModulesMetadataTypes\SwitchPayloadType::isValidValue((string) $value)
			) {
				return ModulesMetadataTypes\SwitchPayloadType::get($value);

			} elseif ($property->getDataType()->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_ENUM)) {
				if (is_array($property->getFormat()) && count($property->getFormat()) > 0) {
					if (in_array($value, $property->getFormat(), true)) {
						return $value;
					}

					return null;
				}

				return $value;
			}
		}

		return $value;
	}

}
