<?php declare(strict_types = 1);

/**
 * RowSchema.php
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

namespace FastyBird\DevicesModule\Schemas\Channels\Configuration;

use FastyBird\DevicesModule;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Router;
use FastyBird\DevicesModule\Schemas;
use FastyBird\JsonApi\Schemas as JsonApiSchemas;
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;
use IPub\SlimRouter\Routing;
use Neomerx\JsonApi;

/**
 * Channel configuration row entity schema
 *
 * @package          FastyBird:DevicesModule!
 * @subpackage       Schemas
 *
 * @author           Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends  JsonApiSchemas\JsonApiSchema<Entities\Channels\Configuration\IRow>
 */
final class RowSchema extends JsonApiSchemas\JsonApiSchema
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = 'devices-module/channel-configuration';

	/**
	 * Define relationships names
	 */
	public const RELATIONSHIPS_CHANNEL = 'channel';

	/** @var Routing\IRouter */
	protected Routing\IRouter $router;

	public function __construct(Routing\IRouter $router)
	{
		$this->router = $router;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass(): string
	{
		return Entities\Channels\Configuration\Row::class;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

	/**
	 * @param Entities\Channels\Configuration\IRow $row
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, mixed>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($row, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		$attributes = [
			'key'        => $row->getKey(),
			'identifier' => $row->getIdentifier(),
			'name'       => $row->getName(),
			'comment'    => $row->getComment(),
			'data_type'  => $row->getDataType()->getValue(),
			'default'    => $row->getDefault(),
		];

		if (
			$row->getDataType()->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_FLOAT)
			|| $row->getDataType()->isInteger()
		) {
			return array_merge($attributes, [
				'min'  => $row->getMin(),
				'max'  => $row->getMax(),
				'step' => $row->getStep(),
			]);

		} elseif ($row->getDataType()->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_ENUM)) {
			return array_merge($attributes, [
				'values' => $row->getValues(),
			]);
		}

		return $attributes;
	}

	/**
	 * @param Entities\Channels\Configuration\IRow $row
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getSelfLink($row): JsonApi\Contracts\Schema\LinkInterface
	{
		return new JsonApi\Schema\Link(
			false,
			$this->router->urlFor(
				DevicesModule\Constants::ROUTE_NAME_CHANNEL_CONFIGURATION_ROW,
				[
					Router\Routes::URL_DEVICE_ID  => $row->getChannel()->getDevice()->getPlainId(),
					Router\Routes::URL_CHANNEL_ID => $row->getChannel()->getPlainId(),
					Router\Routes::URL_ITEM_ID    => $row->getPlainId(),
				]
			),
			false
		);
	}

	/**
	 * @param Entities\Channels\Configuration\IRow $row
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, mixed>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationships($row, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return [
			self::RELATIONSHIPS_CHANNEL => [
				self::RELATIONSHIP_DATA          => $row->getChannel(),
				self::RELATIONSHIP_LINKS_SELF    => false,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
		];
	}

	/**
	 * @param Entities\Channels\Configuration\IRow $row
	 * @param string $name
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipRelatedLink($row, string $name): JsonApi\Contracts\Schema\LinkInterface
	{
		if ($name === self::RELATIONSHIPS_CHANNEL) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_CHANNEL,
					[
						Router\Routes::URL_DEVICE_ID => $row->getChannel()->getDevice()->getPlainId(),
						Router\Routes::URL_ITEM_ID   => $row->getChannel()->getPlainId(),
					]
				),
				false
			);
		}

		return parent::getRelationshipRelatedLink($row, $name);
	}

}
