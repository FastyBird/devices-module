<?php declare(strict_types = 1);

/**
 * Variable.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Schemas
 * @since          0.1.0
 *
 * @date           13.04.19
 */

namespace FastyBird\DevicesModule\Schemas\Devices\Properties;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Schemas;
use FastyBird\DevicesModule\Utilities;
use FastyBird\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Metadata\Types as MetadataTypes;
use Neomerx\JsonApi;
use function array_merge;

/**
 * Device property entity schema
 *
 * @extends Property<Entities\Devices\Properties\Variable>
 *
 * @package         FastyBird:DevicesModule!
 * @subpackage      Schemas
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Variable extends Property
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = MetadataTypes\ModuleSource::SOURCE_MODULE_DEVICES . '/property/device/' . MetadataTypes\PropertyType::TYPE_VARIABLE;

	public function getEntityClass(): string
	{
		return Entities\Devices\Properties\Variable::class;
	}

	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

	/**
	 * @phpstan-param Entities\Devices\Properties\Variable $resource
	 *
	 * @phpstan-return iterable<string, (string|bool|int|float|Array<string>|Array<int, (int|float|Array<int, (string|int|float|null)>|null)>|Array<int, Array<int, (string|Array<int, (string|int|float|bool)>|null)>>|null)>
	 *
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes(
		$resource,
		JsonApi\Contracts\Schema\ContextInterface $context,
	): iterable
	{
		return array_merge((array) parent::getAttributes($resource, $context), [
			'value' => Utilities\ValueHelper::flattenValue($resource->getValue()),
			'default' => Utilities\ValueHelper::flattenValue($resource->getDefault()),
		]);
	}

}
