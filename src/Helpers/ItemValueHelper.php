<?php declare(strict_types = 1);

/**
 * ItemValueHelper.php
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

use DateTime;
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;
use Nette\Utils;

/**
 * Property helpers
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Helpers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ItemValueHelper
{

	/**
	 * @param ModulesMetadataTypes\DataTypeType $dataType
	 * @param bool|float|int|string|DateTime|ModulesMetadataTypes\ButtonPayloadType|ModulesMetadataTypes\SwitchPayloadType|null $value
	 * @param Array<string|int|float>|null  $format
	 *
	 * @return bool|float|int|string|DateTime|ModulesMetadataTypes\ButtonPayloadType|ModulesMetadataTypes\SwitchPayloadType|null
	 */
	public static function normalizeValue(
		ModulesMetadataTypes\DataTypeType $dataType,
		$value,
		$format = null
	) {
		if ($value === null) {
			return null;
		}

		if ($dataType->isInteger()) {
			if (is_array($format) && count($format) === 2) {
				if ($format[0] !== null && intval($format[0]) > intval($value)) {
					return null;
				}

				if ($format[1] !== null && intval($format[1]) < intval($value)) {
					return null;
				}
			}

			return intval($value);

		} elseif ($dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_FLOAT)) {
			if (is_array($format) && count($format) === 2) {
				if ($format[0] !== null && floatval($format[0]) > floatval($value)) {
					return null;
				}

				if ($format[1] !== null && floatval($format[1]) < floatval($value)) {
					return null;
				}
			}

			return floatval($value);

		} elseif ($dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_STRING)) {
			return $value;

		} elseif ($dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_BOOLEAN)) {
			return in_array(strtolower(strval($value)), ['true', 't', 'yes', 'y', '1', 'on'], true);

		} elseif ($dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_DATE)) {
			if ($value instanceof DateTime) {
				return $value;
			}

			$value = Utils\DateTime::createFromFormat('Y-m-d', strval($value));

			return $value === false ? null : $value;

		} elseif ($dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_TIME)) {
			if ($value instanceof DateTime) {
				return $value;
			}

			$value = Utils\DateTime::createFromFormat('H:i:sP', strval($value));

			return $value === false ? null : $value;

		} elseif ($dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_DATETIME)) {
			if ($value instanceof DateTime) {
				return $value;
			}

			$value = Utils\DateTime::createFromFormat(DateTime::ATOM, strval($value));

			return $value === false ? null : $value;

		} elseif ($dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_BUTTON)) {
			if ($value instanceof ModulesMetadataTypes\ButtonPayloadType) {
				return $value;
			}

			if (ModulesMetadataTypes\ButtonPayloadType::isValidValue(strval($value))) {
				return ModulesMetadataTypes\ButtonPayloadType::get($value);
			}

			return null;

		} elseif ($dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_SWITCH)) {
			if ($value instanceof ModulesMetadataTypes\SwitchPayloadType) {
				return $value;
			}

			if (ModulesMetadataTypes\SwitchPayloadType::isValidValue(strval($value))) {
				return ModulesMetadataTypes\SwitchPayloadType::get($value);
			}

			return null;

		} elseif ($dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_ENUM)) {
			if (
				is_array($format) && count($format) > 0
				&& in_array($value, $format, true)
			) {
				return $value;
			}

			return null;
		}

		return $value;
	}

}
