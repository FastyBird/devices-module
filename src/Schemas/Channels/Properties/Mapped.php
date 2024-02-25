<?php declare(strict_types = 1);

/**
 * Mapped.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Schemas
 * @since          1.0.0
 *
 * @date           02.04.22
 */

namespace FastyBird\Module\Devices\Schemas\Channels\Properties;

use Exception;
use FastyBird\Library\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use FastyBird\Library\Metadata\Utilities as MetadataUtilities;
use FastyBird\Library\Tools\Exceptions as ToolsExceptions;
use FastyBird\Module\Devices;
use FastyBird\Module\Devices\Documents;
use FastyBird\Module\Devices\Entities;
use FastyBird\Module\Devices\Exceptions;
use FastyBird\Module\Devices\Models;
use FastyBird\Module\Devices\Router;
use FastyBird\Module\Devices\Schemas;
use FastyBird\Module\Devices\Types;
use IPub\DoctrineOrmQuery\Exceptions as DoctrineOrmQueryExceptions;
use IPub\SlimRouter\Routing;
use Neomerx\JsonApi;
use TypeError;
use ValueError;
use function array_merge;
use function assert;

/**
 * Channel property entity schema
 *
 * @template T of Entities\Channels\Properties\Mapped
 * @extends Property<T>
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Schemas
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Mapped extends Property
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = MetadataTypes\Sources\Module::DEVICES->value . '/property/channel/' . Types\PropertyType::MAPPED->value;

	public function __construct(
		Routing\IRouter $router,
		Models\Entities\Channels\Properties\PropertiesRepository $channelsPropertiesRepository,
		private readonly Models\Configuration\Channels\Properties\Repository $channelsPropertiesConfigurationRepository,
		private readonly Models\States\ChannelPropertiesManager $channelPropertiesStatesManager,
	)
	{
		parent::__construct($router, $channelsPropertiesRepository);
	}

	public function getEntityClass(): string
	{
		return Entities\Channels\Properties\Mapped::class;
	}

	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

	/**
	 * @param T $resource
	 *
	 * @return iterable<string, (string|bool|int|float|array<string>|array<int, (int|float|array<int, (string|int|float|null)>|null)>|array<int, array<int, (string|array<int, (string|int|float|bool)>|null)>>|null)>
	 *
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws TypeError
	 * @throws ValueError
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes(
		$resource,
		JsonApi\Contracts\Schema\ContextInterface $context,
	): iterable
	{
		return $resource->getParent() instanceof Entities\Channels\Properties\Dynamic ? array_merge(
			(array) parent::getAttributes($resource, $context),
			[
				'settable' => $resource->isSettable(),
				'queryable' => $resource->isQueryable(),
			],
		) : array_merge((array) parent::getAttributes($resource, $context), [
			'value' => MetadataUtilities\Value::flattenValue($resource->getValue()),
		]);
	}

	/**
	 * @param T $resource
	 *
	 * @return iterable<string, mixed>
	 *
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 * @throws Exception
	 * @throws Exceptions\InvalidState
	 * @throws Exceptions\InvalidArgument
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\Mapping
	 * @throws MetadataExceptions\MalformedInput
	 * @throws ToolsExceptions\InvalidArgument
	 * @throws TypeError
	 * @throws ValueError
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationships(
		$resource,
		JsonApi\Contracts\Schema\ContextInterface $context,
	): iterable
	{
		return array_merge((array) parent::getRelationships($resource, $context), [
			self::RELATIONSHIPS_PARENT => [
				self::RELATIONSHIP_DATA => $resource->getParent(),
				self::RELATIONSHIP_LINKS_SELF => true,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
			self::RELATIONSHIPS_STATE => [
				self::RELATIONSHIP_DATA => $this->getState($resource),
				self::RELATIONSHIP_LINKS_SELF => true,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
		]);
	}

	/**
	 * @param T $resource
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipRelatedLink(
		$resource,
		string $name,
	): JsonApi\Contracts\Schema\LinkInterface
	{
		if ($name === self::RELATIONSHIPS_PARENT) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					Devices\Constants::ROUTE_NAME_CHANNEL_PROPERTY,
					[
						Router\ApiRoutes::URL_DEVICE_ID => $resource->getChannel()->getDevice()->getId()->toString(),
						Router\ApiRoutes::URL_CHANNEL_ID => $resource->getChannel()->getId()->toString(),
						Router\ApiRoutes::URL_ITEM_ID => $resource->getId()->toString(),
					],
				),
				false,
			);
		} elseif ($name === self::RELATIONSHIPS_STATE) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					Devices\Constants::ROUTE_NAME_CHANNEL_PROPERTY_STATE,
					[
						Router\ApiRoutes::URL_DEVICE_ID => $resource->getChannel()->getDevice()->getId()->toString(),
						Router\ApiRoutes::URL_CHANNEL_ID => $resource->getChannel()->getId()->toString(),
						Router\ApiRoutes::URL_PROPERTY_ID => $resource->getId()->toString(),
					],
				),
				false,
			);
		}

		return parent::getRelationshipRelatedLink($resource, $name);
	}

	/**
	 * @param T $resource
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipSelfLink(
		$resource,
		string $name,
	): JsonApi\Contracts\Schema\LinkInterface
	{
		if (
			$name === self::RELATIONSHIPS_PARENT
			|| $name === self::RELATIONSHIPS_STATE
		) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					Devices\Constants::ROUTE_NAME_CHANNEL_PROPERTY_RELATIONSHIP,
					[
						Router\ApiRoutes::URL_DEVICE_ID => $resource->getChannel()->getDevice()->getId()->toString(),
						Router\ApiRoutes::URL_CHANNEL_ID => $resource->getChannel()->getId()->toString(),
						Router\ApiRoutes::URL_ITEM_ID => $resource->getId()->toString(),
						Router\ApiRoutes::RELATION_ENTITY => $name,

					],
				),
				false,
			);
		}

		return parent::getRelationshipSelfLink($resource, $name);
	}

	/**
	 * @throws Exceptions\InvalidState
	 * @throws Exceptions\InvalidArgument
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\Mapping
	 * @throws MetadataExceptions\MalformedInput
	 * @throws ToolsExceptions\InvalidArgument
	 * @throws TypeError
	 * @throws ValueError
	 */
	protected function getState(
		Entities\Channels\Properties\Mapped $property,
	): Documents\States\Channels\Properties\Property|null
	{
		$configuration = $this->channelsPropertiesConfigurationRepository->find($property->getId());
		assert($configuration instanceof Documents\Channels\Properties\Mapped);

		return $this->channelPropertiesStatesManager->readState($configuration);
	}

}
