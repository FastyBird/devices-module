<?php declare(strict_types = 1);

/**
 * PropertySchema.php
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

use FastyBird\DevicesModule;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use FastyBird\DevicesModule\Router;
use FastyBird\DevicesModule\Schemas;
use FastyBird\JsonApi\Schemas as JsonApiSchemas;
use IPub\SlimRouter\Routing;
use Neomerx\JsonApi;

/**
 * Device property entity schema
 *
 * @package         FastyBird:DevicesModule!
 * @subpackage      Schemas
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-template T of Entities\Devices\Properties\IProperty
 * @phpstan-extends  JsonApiSchemas\JsonApiSchema<T>
 */
abstract class PropertySchema extends JsonApiSchemas\JsonApiSchema
{

	/**
	 * Define relationships names
	 */
	public const RELATIONSHIPS_DEVICE = 'device';

	public const RELATIONSHIPS_PARENT = 'parent';
	public const RELATIONSHIPS_CHILDREN = 'children';

	/** @var Routing\IRouter */
	private Routing\IRouter $router;

	/** @var Models\Devices\Properties\IPropertiesRepository */
	private Models\Devices\Properties\IPropertiesRepository $propertiesRepository;

	public function __construct(
		Routing\IRouter $router,
		Models\Devices\Properties\IPropertiesRepository $propertiesRepository
	) {
		$this->router = $router;
		$this->propertiesRepository = $propertiesRepository;
	}

	/**
	 * @param Entities\Devices\Properties\IProperty $property
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, string|bool|int|float|Array<int|null>|Array<float|null>|Array<string>|Array<Array<string|null>>|null>
	 *
	 * @phpstan-param T $property
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($property, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return [
			'identifier'         => $property->getIdentifier(),
			'name'               => $property->getName(),
			'settable'           => $property->isSettable(),
			'queryable'          => $property->isQueryable(),
			'data_type'          => $property->getDataType()->getValue(),
			'unit'               => $property->getUnit(),
			'format'             => $property->getFormat(),
			'invalid'            => $property->getInvalid(),
			'number_of_decimals' => $property->getNumberOfDecimals(),
		];
	}

	/**
	 * @param Entities\Devices\Properties\IProperty $property
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpstan-param T $property
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getSelfLink($property): JsonApi\Contracts\Schema\LinkInterface
	{
		return new JsonApi\Schema\Link(
			false,
			$this->router->urlFor(
				DevicesModule\Constants::ROUTE_NAME_DEVICE_PROPERTY,
				[
					Router\Routes::URL_DEVICE_ID => $property->getDevice()->getPlainId(),
					Router\Routes::URL_ITEM_ID   => $property->getPlainId(),
				]
			),
			false
		);
	}

	/**
	 * @param Entities\Devices\Properties\IProperty $property
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, mixed>
	 *
	 * @phpstan-param T $property
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationships($property, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return [
			self::RELATIONSHIPS_DEVICE   => [
				self::RELATIONSHIP_DATA          => $property->getDevice(),
				self::RELATIONSHIP_LINKS_SELF    => false,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
			self::RELATIONSHIPS_PARENT   => [
				self::RELATIONSHIP_DATA          => $property->getParent(),
				self::RELATIONSHIP_LINKS_SELF    => true,
				self::RELATIONSHIP_LINKS_RELATED => $property->getParent() !== null,
			],
			self::RELATIONSHIPS_CHILDREN => [
				self::RELATIONSHIP_DATA          => $this->getChildren($property),
				self::RELATIONSHIP_LINKS_SELF    => true,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
		];
	}

	/**
	 * @param Entities\Devices\Properties\IProperty $property
	 * @param string $name
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpstan-param T $property
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipRelatedLink($property, string $name): JsonApi\Contracts\Schema\LinkInterface
	{
		if ($name === self::RELATIONSHIPS_DEVICE) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_DEVICE,
					[
						Router\Routes::URL_ITEM_ID => $property->getDevice()->getPlainId(),
					]
				),
				false
			);

		} elseif ($name === self::RELATIONSHIPS_PARENT && $property->getParent() !== null) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_DEVICE_PROPERTY,
					[
						Router\Routes::URL_DEVICE_ID => $property->getDevice()->getPlainId(),
						Router\Routes::URL_ITEM_ID   => $property->getPlainId(),
					]
				),
				false
			);

		} elseif ($name === self::RELATIONSHIPS_CHILDREN) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_DEVICE_PROPERTY_CHILDREN,
					[
						Router\Routes::URL_DEVICE_ID   => $property->getDevice()->getPlainId(),
						Router\Routes::URL_PROPERTY_ID => $property->getPlainId(),
					]
				),
				true,
				[
					'count' => count($property->getChildren()),
				]
			);
		}

		return parent::getRelationshipRelatedLink($property, $name);
	}

	/**
	 * @param Entities\Devices\Properties\IProperty $property
	 * @param string $name
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpstan-param T $property
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipSelfLink($property, string $name): JsonApi\Contracts\Schema\LinkInterface
	{
		if (
			$name === self::RELATIONSHIPS_CHILDREN
			|| $name === self::RELATIONSHIPS_PARENT
		) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_DEVICE_PROPERTY_RELATIONSHIP,
					[
						Router\Routes::URL_DEVICE_ID   => $property->getDevice()->getPlainId(),
						Router\Routes::URL_ITEM_ID     => $property->getPlainId(),
						Router\Routes::RELATION_ENTITY => $name,

					]
				),
				false
			);
		}

		return parent::getRelationshipSelfLink($property, $name);
	}

	/**
	 * @param Entities\Devices\Properties\IProperty $property
	 *
	 * @return Entities\Devices\Properties\IProperty[]
	 */
	private function getChildren(Entities\Devices\Properties\IProperty $property): array
	{
		$findQuery = new Queries\FindDevicePropertiesQuery();
		$findQuery->forParent($property);

		return $this->propertiesRepository->findAllBy($findQuery);
	}

}
