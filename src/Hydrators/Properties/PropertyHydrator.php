<?php declare(strict_types = 1);

/**
 * PropertyHydrator.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 * @since          0.9.0
 *
 * @date           02.01.22
 */

namespace FastyBird\DevicesModule\Hydrators\Properties;

use FastyBird\DevicesModule\Entities;
use FastyBird\JsonApi\Hydrators as JsonApiHydrators;
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;
use IPub\JsonAPIDocument;

/**
 * Property entity hydrator
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-template TEntityClass of Entities\IProperty
 * @phpstan-extends  JsonApiHydrators\Hydrator<TEntityClass>
 */
abstract class PropertyHydrator extends JsonApiHydrators\Hydrator
{

	/** @var string[] */
	protected array $attributes = [
		0 => 'type',
		1 => 'identifier',
		2 => 'name',
		3 => 'settable',
		4 => 'queryable',
		5 => 'unit',
		6 => 'format',
		7 => 'invalid',
		8 => 'value',

		'data_type'          => 'dataType',
		'number_of_decimals' => 'numberOfDecimals',
	];

	/** @var string */
	protected string $translationDomain = 'devices-module.properties';

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return string|null
	 */
	protected function hydrateNameAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): ?string
	{
		if (
			!is_scalar($attributes->get('name'))
			|| (string) $attributes->get('name') === ''
		) {
			return null;
		}

		return (string) $attributes->get('name');
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return bool
	 */
	protected function hydrateSettableAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): bool
	{
		return is_scalar($attributes->get('settable')) && (bool) $attributes->get('settable');
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return bool
	 */
	protected function hydrateQueryableAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): bool
	{
		return is_scalar($attributes->get('queryable')) && (bool) $attributes->get('queryable');
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return ModulesMetadataTypes\DataTypeType|null
	 */
	protected function hydrateDataTypeAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): ?ModulesMetadataTypes\DataTypeType
	{
		if (
			!is_scalar($attributes->get('data_type'))
			|| (string) $attributes->get('data_type') === ''
			|| !ModulesMetadataTypes\DataTypeType::isValidValue((string) $attributes->get('data_type'))
		) {
			return null;
		}

		return ModulesMetadataTypes\DataTypeType::get((string) $attributes->get('data_type'));
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return string|null
	 */
	protected function hydrateUnitAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): ?string
	{
		if (
			!is_scalar($attributes->get('unit'))
			|| (string) $attributes->get('unit') === ''
		) {
			return null;
		}

		return (string) $attributes->get('unit');
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return string|null
	 */
	protected function hydrateFormatAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): ?string
	{
		$rawFormat = $attributes->get('format');
		$rawDataType = $attributes->get('data_type');

		if (is_array($rawFormat)) {
			if (
				$rawDataType !== null
				&& is_scalar($rawDataType)
				&& ModulesMetadataTypes\DataTypeType::isValidValue((string) $rawDataType)
			) {
				$dataType = ModulesMetadataTypes\DataTypeType::get((string) $rawDataType);

				if ($dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_ENUM)) {
					return implode(',', array_map(function ($item): string {
						return is_array($item) ? implode(':', $item) : $item;
					}, $rawFormat));
				}

				if (count($rawFormat) === 2) {
					return implode(':', $rawFormat);
				}
			}

			return null;

		} elseif (!is_scalar($rawFormat) || (string) $rawFormat === '') {
			return null;
		}

		return (string) $rawFormat;
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return string|null
	 */
	protected function hydrateInvalidAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): ?string
	{
		if (
			!is_scalar($attributes->get('invalid'))
			|| (string) $attributes->get('invalid') === ''
		) {
			return null;
		}

		return (string) $attributes->get('invalid');
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return int|null
	 */
	protected function hydrateNumberOfDecimalsAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): ?int
	{
		if (
			!is_scalar($attributes->get('number_of_decimals'))
			|| (string) $attributes->get('number_of_decimals') === ''
		) {
			return null;
		}

		return (int) $attributes->get('number_of_decimals');
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return string|null
	 */
	protected function hydrateValueAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): ?string
	{
		if (
			!is_scalar($attributes->get('value'))
			|| (string) $attributes->get('value') === ''
		) {
			return null;
		}

		return (string) $attributes->get('value');
	}

}
