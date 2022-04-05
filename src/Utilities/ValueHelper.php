<?php declare(strict_types = 1);

/**
 * ValueHelper.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Utilities
 * @since          0.51.0
 *
 * @date           05.12.20
 */

namespace FastyBird\DevicesModule\Utilities;

use DateTime;
use FastyBird\Metadata\Types;
use Nette\Utils;

/**
 * Value helpers
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Helpers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ValueHelper
{

	/**
	 * @param Types\DataTypeType $dataType
	 * @param bool|float|int|string|DateTime|Types\ButtonPayloadType|Types\SwitchPayloadType|null $value
	 * @param Array<string>|Array<Array<string|null>>|Array<int|null>|Array<float|null>|null $format
	 * @param float|int|string|null $invalid
	 *
	 * @return bool|float|int|string|DateTime|Types\ButtonPayloadType|Types\SwitchPayloadType|null
	 */
	public static function normalizeValue(
		Types\DataTypeType $dataType,
		$value,
		?array $format = null,
		$invalid = null
	) {
		if ($value === null) {
			return null;
		}

		if (
			$dataType->equalsValue(Types\DataTypeType::DATA_TYPE_CHAR)
			|| $dataType->equalsValue(Types\DataTypeType::DATA_TYPE_UCHAR)
			|| $dataType->equalsValue(Types\DataTypeType::DATA_TYPE_SHORT)
			|| $dataType->equalsValue(Types\DataTypeType::DATA_TYPE_USHORT)
			|| $dataType->equalsValue(Types\DataTypeType::DATA_TYPE_INT)
			|| $dataType->equalsValue(Types\DataTypeType::DATA_TYPE_UINT)
		) {
			if ($invalid !== null && $invalid === intval($value)) {
				return $invalid;
			}

			if (is_array($format) && count($format) === 2) {
				if ($format[0] !== null && intval($format[0]) > intval($value)) {
					return null;
				}

				if ($format[1] !== null && intval($format[1]) < intval($value)) {
					return null;
				}
			}

			return intval($value);

		} elseif ($dataType->equalsValue(Types\DataTypeType::DATA_TYPE_FLOAT)) {
			if ($invalid !== null && $invalid === floatval($value)) {
				return $invalid;
			}

			if (is_array($format) && count($format) === 2) {
				if ($format[0] !== null && floatval($format[0]) > floatval($value)) {
					return null;
				}

				if ($format[1] !== null && floatval($format[1]) < floatval($value)) {
					return null;
				}
			}

			return floatval($value);

		} elseif ($dataType->equalsValue(Types\DataTypeType::DATA_TYPE_STRING)) {
			return $value;

		} elseif ($dataType->equalsValue(Types\DataTypeType::DATA_TYPE_BOOLEAN)) {
			return in_array(strtolower(strval($value)), ['true', 't', 'yes', 'y', '1', 'on'], true);

		} elseif ($dataType->equalsValue(Types\DataTypeType::DATA_TYPE_DATE)) {
			if ($value instanceof DateTime) {
				return $value;
			}

			$value = Utils\DateTime::createFromFormat('Y-m-d', strval($value));

			return $value === false ? null : $value;

		} elseif ($dataType->equalsValue(Types\DataTypeType::DATA_TYPE_TIME)) {
			if ($value instanceof DateTime) {
				return $value;
			}

			$value = Utils\DateTime::createFromFormat('H:i:sP', strval($value));

			return $value === false ? null : $value;

		} elseif ($dataType->equalsValue(Types\DataTypeType::DATA_TYPE_DATETIME)) {
			if ($value instanceof DateTime) {
				return $value;
			}

			$value = Utils\DateTime::createFromFormat(DateTime::ATOM, strval($value));

			return $value === false ? null : $value;

		} elseif ($dataType->equalsValue(Types\DataTypeType::DATA_TYPE_BUTTON)) {
			if ($value instanceof Types\ButtonPayloadType) {
				return $value;
			}

			if (Types\ButtonPayloadType::isValidValue(strval($value))) {
				return Types\ButtonPayloadType::get($value);
			}

			return null;

		} elseif ($dataType->equalsValue(Types\DataTypeType::DATA_TYPE_SWITCH)) {
			if ($value instanceof Types\SwitchPayloadType) {
				return $value;
			}

			if (Types\SwitchPayloadType::isValidValue(strval($value))) {
				return Types\SwitchPayloadType::get($value);
			}

			return null;

		} elseif ($dataType->equalsValue(Types\DataTypeType::DATA_TYPE_ENUM)) {
			if (is_array($format) && count($format) > 0) {
				$filtered = array_filter($format, function ($item) use ($value): bool {
					if (is_array($item)) {
						if (count($item) !== 3) {
							return false;
						}

						return strtolower(strval($value)) === $item[0]
							|| strtolower(strval($value)) === $item[1]
							|| strtolower(strval($value)) === $item[2];
					}

					return strtolower(strval($value)) === $item;
				});

				if (count($filtered) === 1) {
					$filtered = array_pop($filtered);

					return is_array($filtered) ? $filtered[0] : $filtered;
				}

				return null;
			}

			return null;
		}

		return $value;
	}

}