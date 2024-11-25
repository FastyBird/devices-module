<?php declare(strict_types = 1);

/**
 * Property.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Schemas
 * @since          1.0.0
 *
 * @date           13.04.19
 */

namespace FastyBird\Module\Devices\Schemas\Channels\Properties;

use DateTimeInterface;
use Exception;
use FastyBird\Core\Tools\Exceptions as ToolsExceptions;
use FastyBird\Core\Tools\Utilities as ToolsUtilities;
use FastyBird\JsonApi\Schemas as JsonApiSchemas;
use FastyBird\Module\Devices;
use FastyBird\Module\Devices\Entities;
use FastyBird\Module\Devices\Exceptions;
use FastyBird\Module\Devices\Models;
use FastyBird\Module\Devices\Queries;
use FastyBird\Module\Devices\Router;
use FastyBird\Module\Devices\Schemas;
use IPub\DoctrineOrmQuery\Exceptions as DoctrineOrmQueryExceptions;
use IPub\SlimRouter\Routing;
use Neomerx\JsonApi;
use TypeError;
use ValueError;
use function strval;

/**
 * Channel property entity schema
 *
 * @template T of Entities\Channels\Properties\Property
 * @extends  JsonApiSchemas\JsonApi<T>
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Schemas
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
abstract class Property extends JsonApiSchemas\JsonApi
{

	/**
	 * Define relationships names
	 */
	public const RELATIONSHIPS_CHANNEL = 'channel';

	public const RELATIONSHIPS_PARENT = 'parent';

	public const RELATIONSHIPS_CHILDREN = 'children';

	public const RELATIONSHIPS_STATE = 'state';

	public function __construct(
		protected readonly Routing\IRouter $router,
		protected readonly Models\Entities\Channels\Properties\PropertiesRepository $channelsPropertiesRepository,
	)
	{
	}

	/**
	 * @param T $resource
	 *
	 * @return iterable<string, (string|bool|int|float|array<string>|array<int, (int|float|array<int, (string|int|float|null)>|null)>|array<int, array<int, (string|array<int, (string|int|float|bool)>|null)>>|null)>
	 *
	 * @throws Exceptions\InvalidState
	 * @throws ToolsExceptions\InvalidArgument
	 * @throws ToolsExceptions\InvalidState
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
		return [
			'category' => $resource->getCategory()->value,
			'identifier' => $resource->getIdentifier(),
			'name' => $resource->getName(),
			'data_type' => $resource->getDataType()->value,
			'unit' => $resource->getUnit(),
			'format' => $resource->getFormat()?->getValue(),
			'invalid' => $resource->getInvalid(),
			'scale' => $resource->getScale(),
			'step' => $resource->getStep(),
			'default' => ToolsUtilities\Value::flattenValue($resource->getDefault()),
			'value_transformer' => $resource->getValueTransformer() !== null
				? strval($resource->getValueTransformer())
				: null,
			'owner' => $resource->getChannel()->getDevice()->getOwnerId(),
			'created_at' => $resource->getCreatedAt()?->format(DateTimeInterface::ATOM),
			'updated_at' => $resource->getUpdatedAt()?->format(DateTimeInterface::ATOM),
		];
	}

	/**
	 * @param T $resource
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getSelfLink($resource): JsonApi\Contracts\Schema\LinkInterface
	{
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
	}

	/**
	 * @param T $resource
	 *
	 * @return iterable<string, mixed>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationships(
		$resource,
		JsonApi\Contracts\Schema\ContextInterface $context,
	): iterable
	{
		return [
			self::RELATIONSHIPS_CHANNEL => [
				self::RELATIONSHIP_DATA => $resource->getChannel(),
				self::RELATIONSHIP_LINKS_SELF => false,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
		];
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
		if ($name === self::RELATIONSHIPS_CHANNEL) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					Devices\Constants::ROUTE_NAME_CHANNEL,
					[
						Router\ApiRoutes::URL_DEVICE_ID => $resource->getChannel()->getDevice()->getId()->toString(),
						Router\ApiRoutes::URL_ITEM_ID => $resource->getChannel()->getId()->toString(),
					],
				),
				false,
			);
		}

		return parent::getRelationshipRelatedLink($resource, $name);
	}

	/**
	 * @return array<Entities\Channels\Properties\Property>
	 *
	 * @throws Exception
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 */
	protected function getChildren(Entities\Channels\Properties\Property $property): array
	{
		$findQuery = new Queries\Entities\FindChannelProperties();
		$findQuery->forParent($property);

		return $this->channelsPropertiesRepository->findAllBy($findQuery);
	}

}
