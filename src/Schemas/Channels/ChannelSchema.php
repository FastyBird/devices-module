<?php declare(strict_types = 1);

/**
 * ChannelSchema.php
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

namespace FastyBird\DevicesModule\Schemas\Channels;

use FastyBird\DevicesModule;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Router;
use FastyBird\DevicesModule\Schemas;
use FastyBird\JsonApi\Schemas as JsonApiSchemas;
use IPub\SlimRouter\Routing;
use Neomerx\JsonApi;

/**
 * Channel entity schema
 *
 * @package         FastyBird:DevicesModule!
 * @subpackage      Schemas
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends JsonApiSchemas\JsonApiSchema<Entities\Channels\IChannel>
 */
final class ChannelSchema extends JsonApiSchemas\JsonApiSchema
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = 'devices-module/channel';

	/**
	 * Define relationships names
	 */
	public const RELATIONSHIPS_DEVICE = 'device';

	public const RELATIONSHIPS_PROPERTIES = 'properties';
	public const RELATIONSHIPS_CONFIGURATION = 'configuration';

	/** @var Routing\IRouter */
	private $router;

	public function __construct(Routing\IRouter $router)
	{
		$this->router = $router;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass(): string
	{
		return Entities\Channels\Channel::class;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

	/**
	 * @param Entities\Channels\IChannel $channel
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, string|string[]|null>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($channel, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return [
			'name'    => $channel->getName(),
			'comment' => $channel->getComment(),
			'channel' => $channel->getChannel(),

			'control' => $this->formatControls($channel->getControls()),
		];
	}

	/**
	 * @param Entities\Channels\IChannel $channel
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getSelfLink($channel): JsonApi\Contracts\Schema\LinkInterface
	{
		return new JsonApi\Schema\Link(
			false,
			$this->router->urlFor(
				DevicesModule\Constants::ROUTE_NAME_CHANNEL,
				[
					Router\Router::URL_DEVICE_ID => $channel->getDevice()->getPlainId(),
					Router\Router::URL_ITEM_ID   => $channel->getPlainId(),
				]
			),
			false
		);
	}

	/**
	 * @param Entities\Channels\IChannel $channel
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, mixed>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationships($channel, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return [
			self::RELATIONSHIPS_DEVICE        => [
				self::RELATIONSHIP_DATA          => $channel->getDevice(),
				self::RELATIONSHIP_LINKS_SELF    => false,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
			self::RELATIONSHIPS_PROPERTIES    => [
				self::RELATIONSHIP_DATA          => $channel->getProperties(),
				self::RELATIONSHIP_LINKS_SELF    => true,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
			self::RELATIONSHIPS_CONFIGURATION => [
				self::RELATIONSHIP_DATA          => $channel->getConfiguration(),
				self::RELATIONSHIP_LINKS_SELF    => true,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
		];
	}

	/**
	 * @param Entities\Channels\IChannel $channel
	 * @param string $name
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipRelatedLink($channel, string $name): JsonApi\Contracts\Schema\LinkInterface
	{
		if ($name === self::RELATIONSHIPS_PROPERTIES) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_CHANNEL_PROPERTIES,
					[
						Router\Router::URL_DEVICE_ID  => $channel->getDevice()->getPlainId(),
						Router\Router::URL_CHANNEL_ID => $channel->getPlainId(),
					]
				),
				true,
				[
					'count' => count($channel->getProperties()),
				]
			);

		} elseif ($name === self::RELATIONSHIPS_CONFIGURATION) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_CHANNEL_CONFIGURATION_ROWS,
					[
						Router\Router::URL_DEVICE_ID  => $channel->getDevice()->getPlainId(),
						Router\Router::URL_CHANNEL_ID => $channel->getPlainId(),
					]
				),
				true,
				[
					'count' => count($channel->getConfiguration()),
				]
			);

		} elseif ($name === self::RELATIONSHIPS_DEVICE) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_DEVICE,
					[
						Router\Router::URL_ITEM_ID => $channel->getDevice()->getPlainId(),
					]
				),
				false
			);
		}

		return parent::getRelationshipRelatedLink($channel, $name);
	}

	/**
	 * @param Entities\Channels\IChannel $channel
	 * @param string $name
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipSelfLink($channel, string $name): JsonApi\Contracts\Schema\LinkInterface
	{
		if (
			$name === self::RELATIONSHIPS_PROPERTIES
			|| $name === self::RELATIONSHIPS_CONFIGURATION
		) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_CHANNEL_RELATIONSHIP,
					[
						Router\Router::URL_DEVICE_ID   => $channel->getDevice()->getPlainId(),
						Router\Router::URL_ITEM_ID     => $channel->getPlainId(),
						Router\Router::RELATION_ENTITY => $name,
					]
				),
				false
			);
		}

		return parent::getRelationshipSelfLink($channel, $name);
	}

	/**
	 * @param Entities\Channels\Controls\IControl[] $controls
	 *
	 * @return string[]
	 */
	private function formatControls(array $controls): array
	{
		$return = [];

		foreach ($controls as $control) {
			$return[] = $control->getName();
		}

		return $return;
	}

}
