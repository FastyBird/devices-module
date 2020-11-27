<?php declare(strict_types = 1);

/**
 * RowSchema.php
 *
 * @license        More in license.md
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
 * @phpstan-template T of Entities\Channels\Configuration\IRow
 * @phpstan-extends  JsonApiSchemas\JsonApiSchema<T>
 */
abstract class RowSchema extends JsonApiSchemas\JsonApiSchema
{

	/**
	 * Define relationships names
	 */
	public const RELATIONSHIPS_CHANNEL = 'channel';

	/** @var Routing\IRouter */
	protected $router;

	public function __construct(Routing\IRouter $router)
	{
		$this->router = $router;
	}

	/**
	 * @param Entities\Channels\Configuration\IRow $row
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, mixed>
	 *
	 * @phpstan-param T $row
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($row, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return [
			'configuration' => $row->getConfiguration(),
			'name'          => $row->getName(),
			'comment'       => $row->getComment(),
			'default'       => $row->getDefault(),
		];
	}

	/**
	 * @param Entities\Channels\Configuration\IRow $row
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpstan-param T $row
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
					Router\Router::URL_DEVICE_ID  => $row->getChannel()->getDevice()->getPlainId(),
					Router\Router::URL_CHANNEL_ID => $row->getChannel()->getPlainId(),
					Router\Router::URL_ITEM_ID    => $row->getPlainId(),
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
	 * @phpstan-param T $row
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
	 * @phpstan-param T $row
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
						Router\Router::URL_DEVICE_ID => $row->getChannel()->getDevice()->getPlainId(),
						Router\Router::URL_ITEM_ID   => $row->getChannel()->getPlainId(),
					]
				),
				false
			);
		}

		return parent::getRelationshipRelatedLink($row, $name);
	}

}
