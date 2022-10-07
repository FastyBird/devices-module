<?php declare(strict_types = 1);

/**
 * Variable.php
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

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Schemas;
use FastyBird\DevicesModule\Utilities;
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
 * @phpstan-extends Property<Entities\Connectors\Properties\Variable>
 */
final class Variable extends Property
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES . '/property/connector/' . MetadataTypes\PropertyTypeType::TYPE_STATIC;

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass(): string
	{
		return Entities\Connectors\Properties\Variable::class;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

	/**
	 * @param Entities\Connectors\Properties\Variable $property
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, string|bool|int|float|string[]|Array<int, int|float|Array<int, string|int|float|null>|null>|Array<int, Array<int, string|Array<int, string|int|float|bool>|null>>|null>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($property, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return array_merge((array) parent::getAttributes($property, $context), [
			'value'   => Utilities\ValueHelper::flattenValue($property->getValue()),
			'default' => Utilities\ValueHelper::flattenValue($property->getDefault()),
		]);
	}

}
