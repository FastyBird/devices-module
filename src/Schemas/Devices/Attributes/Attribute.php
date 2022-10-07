<?php declare(strict_types = 1);

/**
 * Attribute.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Schemas
 * @since          0.57.0
 *
 * @date           22.04.22
 */

namespace FastyBird\DevicesModule\Schemas\Devices\Attributes;

use FastyBird\DevicesModule;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Router;
use FastyBird\DevicesModule\Schemas;
use FastyBird\JsonApi\Schemas as JsonApiSchemas;
use FastyBird\Metadata\Types as MetadataTypes;
use IPub\SlimRouter\Routing;
use Neomerx\JsonApi;
use function assert;
use function is_scalar;

/**
 * Device attribute entity schema
 *
 * @phpstan-extends JsonApiSchemas\JsonApiSchema<Entities\Devices\Attributes\Attribute>
 *
 * @package         FastyBird:DevicesModule!
 * @subpackage      Schemas
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Attribute extends JsonApiSchemas\JsonApiSchema
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES . '/attribute/device';

	/**
	 * Define relationships names
	 */
	public const RELATIONSHIPS_DEVICE = 'device';

	public function __construct(private Routing\IRouter $router)
	{
	}

	public function getEntityClass(): string
	{
		return Entities\Devices\Attributes\Attribute::class;
	}

	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

	/**
	 * @return iterable<string, string|bool|null>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes(
		$attribute,
		JsonApi\Contracts\Schema\ContextInterface $context,
	): iterable
	{
		assert($attribute instanceof Entities\Devices\Attributes\Attribute);

		return [
			'identifier' => $attribute->getIdentifier(),
			'name' => $attribute->getName(),
			'content' => is_scalar($attribute->getContent(true)) || $attribute->getContent(
				true,
			) === null ? $attribute->getContent(
				true,
			) : (string) $attribute->getContent(),
		];
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getSelfLink(
		$attribute,
	): JsonApi\Contracts\Schema\LinkInterface
	{
		return new JsonApi\Schema\Link(
			false,
			$this->router->urlFor(
				DevicesModule\Constants::ROUTE_NAME_DEVICE_ATTRIBUTE,
				[
					Router\Routes::URL_DEVICE_ID => $attribute->getDevice()->getPlainId(),
					Router\Routes::URL_ITEM_ID => $attribute->getPlainId(),
				],
			),
			false,
		);
	}

	/**
	 * @return iterable<string, mixed>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationships(
		$attribute,
		JsonApi\Contracts\Schema\ContextInterface $context,
	): iterable
	{
		return [
			self::RELATIONSHIPS_DEVICE => [
				self::RELATIONSHIP_DATA => $attribute->getDevice(),
				self::RELATIONSHIP_LINKS_SELF => false,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
		];
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipRelatedLink(
		$attribute,
		string $name,
	): JsonApi\Contracts\Schema\LinkInterface
	{
		if ($name === self::RELATIONSHIPS_DEVICE) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_DEVICE,
					[
						Router\Routes::URL_ITEM_ID => $attribute->getDevice()->getPlainId(),
					],
				),
				false,
			);
		}

		return parent::getRelationshipRelatedLink($attribute, $name);
	}

}
