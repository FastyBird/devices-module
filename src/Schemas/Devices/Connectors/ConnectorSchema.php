<?php declare(strict_types = 1);

/**
 * ConnectorSchema.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Schemas
 * @since          0.1.0
 *
 * @date           17.01.21
 */

namespace FastyBird\DevicesModule\Schemas\Devices\Connectors;

use FastyBird\DevicesModule;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Router;
use FastyBird\DevicesModule\Schemas;
use FastyBird\JsonApi\Schemas as JsonApiSchemas;
use IPub\SlimRouter\Routing;
use Neomerx\JsonApi;

/**
 * Device connector entity schema
 *
 * @package         FastyBird:DevicesModule!
 * @subpackage      Schemas
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends JsonApiSchemas\JsonApiSchema<Entities\Devices\Connectors\IConnector>
 */
final class ConnectorSchema extends JsonApiSchemas\JsonApiSchema
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = 'devices-module/device-connector';

	/**
	 * Define relationships names
	 */
	public const RELATIONSHIPS_DEVICE = 'device';
	public const RELATIONSHIPS_CONNECTOR = 'connector';

	/** @var Routing\IRouter */
	private Routing\IRouter $router;

	public function __construct(
		Routing\IRouter $router
	) {
		$this->router = $router;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass(): string
	{
		return Entities\Devices\Connectors\Connector::class;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

	/**
	 * @param Entities\Devices\Connectors\IConnector $connector
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, string|int|bool|null>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($connector, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		if ($connector->getConnector() instanceof Entities\Connectors\FbMqttConnector) {
			return [
				'username' => $connector->getUsername(),
				'password' => $connector->getPassword(),
			];

		} elseif ($connector->getConnector() instanceof Entities\Connectors\FbBusConnector) {
			return [
				'address'               => $connector->getAddress(),
				'max_packet_length'     => $connector->getMaxPacketLength(),
				'description_support'   => $connector->hasDescriptionSupport(),
				'settings_support'      => $connector->hasSettingsSupport(),
				'configured_key_length' => $connector->getConfiguredKeyLength(),
			];
		}

		return [];
	}

	/**
	 * @param Entities\Devices\Connectors\IConnector $connector
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getSelfLink($connector): JsonApi\Contracts\Schema\LinkInterface
	{
		return new JsonApi\Schema\Link(
			false,
			$this->router->urlFor(
				DevicesModule\Constants::ROUTE_NAME_DEVICE_CONNECTOR,
				[
					Router\Routes::URL_DEVICE_ID => $connector->getDevice()->getId()->toString(),
				]
			),
			false
		);
	}

	/**
	 * @param Entities\Devices\Connectors\IConnector $connector
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, mixed>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationships($connector, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return [
			self::RELATIONSHIPS_DEVICE    => [
				self::RELATIONSHIP_DATA          => $connector->getDevice(),
				self::RELATIONSHIP_LINKS_SELF    => false,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
			self::RELATIONSHIPS_CONNECTOR => [
				self::RELATIONSHIP_DATA          => $connector->getConnector(),
				self::RELATIONSHIP_LINKS_SELF    => false,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
		];
	}

	/**
	 * @param Entities\Devices\Connectors\IConnector $connector
	 * @param string $name
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipRelatedLink($connector, string $name): JsonApi\Contracts\Schema\LinkInterface
	{
		if ($name === self::RELATIONSHIPS_DEVICE) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_DEVICE,
					[
						Router\Routes::URL_ITEM_ID => $connector->getDevice()->getId()->toString(),
					]
				),
				false
			);

		} elseif ($name === self::RELATIONSHIPS_CONNECTOR) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_CONNECTOR,
					[
						Router\Routes::URL_ITEM_ID => $connector->getConnector()->getId()->toString(),
					]
				),
				false
			);
		}

		return parent::getRelationshipRelatedLink($connector, $name);
	}

}
