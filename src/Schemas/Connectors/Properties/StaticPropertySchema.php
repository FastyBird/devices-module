<?php declare(strict_types = 1);

/**
 * StaticPropertySchema.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Schemas
 * @since          0.31.0
 *
 * @date           08.02.22
 */

namespace FastyBird\DevicesModule\Schemas\Connectors\Properties;

use DateTime;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Schemas;
use FastyBird\Metadata\Types as MetadataTypes;
use Neomerx\JsonApi;

/**
 * Connector property entity schema
 *
 * @package         FastyBird:DevicesModule!
 * @subpackage      Schemas
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends PropertySchema<Entities\Connectors\Properties\IStaticProperty>
 */
final class StaticPropertySchema extends PropertySchema
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = MetadataTypes\ModuleOriginType::ORIGIN_MODULE_DEVICES . '/property/connector/' . MetadataTypes\PropertyTypeType::TYPE_STATIC;

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass(): string
	{
		return Entities\Connectors\Properties\StaticProperty::class;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

	/**
	 * @param Entities\Connectors\Properties\IStaticProperty $property
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, string|bool|int|float|MetadataTypes\SwitchPayloadType|MetadataTypes\ButtonPayloadType|DateTime|Array<int|null>|Array<float|null>|Array<string>|Array<Array<string|null>>|null>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($property, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return array_merge((array) parent::getAttributes($property, $context), [
			'value'   => $property->getValue(),
			'default' => $property->getDefault(),
		]);
	}

}
