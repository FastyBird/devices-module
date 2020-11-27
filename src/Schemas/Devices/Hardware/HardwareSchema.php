<?php declare(strict_types = 1);

/**
 * HardwareSchema.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Schemas
 * @since          0.1.0
 *
 * @date           22.04.19
 */

namespace FastyBird\DevicesModule\Schemas\Devices\Hardware;

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
 * Device hardware entity schema
 *
 * @package         FastyBird:DevicesModule!
 * @subpackage      Schemas
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends JsonApiSchemas\JsonApiSchema<Entities\Devices\PhysicalDevice\IHardware>
 */
final class HardwareSchema extends JsonApiSchemas\JsonApiSchema
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = 'devices-module/hardware';

	/**
	 * Define relationships names
	 */
	public const RELATIONSHIPS_DEVICE = 'device';

	/** @var Models\Devices\IDeviceRepository */
	private $deviceRepository;

	/** @var Routing\IRouter */
	private $router;

	public function __construct(
		Models\Devices\IDeviceRepository $deviceRepository,
		Routing\IRouter $router
	) {
		$this->deviceRepository = $deviceRepository;

		$this->router = $router;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass(): string
	{
		return Entities\Devices\PhysicalDevice\Hardware::class;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

	/**
	 * @param Entities\Devices\PhysicalDevice\IHardware $hardware
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, string|null>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($hardware, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return [
			'model'        => $hardware->getModel()->getValue(),
			'manufacturer' => $hardware->getManufacturer()->getValue(),
			'version'      => $hardware->getVersion(),
			'mac_address'  => $hardware->getMacAddress(),
		];
	}

	/**
	 * @param Entities\Devices\PhysicalDevice\IHardware $hardware
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getSelfLink($hardware): JsonApi\Contracts\Schema\LinkInterface
	{
		return new JsonApi\Schema\Link(
			false,
			$this->router->urlFor(
				DevicesModule\Constants::ROUTE_NAME_DEVICE_HARDWARE,
				[
					Router\Router::URL_DEVICE_ID => $hardware->getDevice()->toString(),
				]
			),
			false
		);
	}

	/**
	 * @param Entities\Devices\PhysicalDevice\IHardware $hardware
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, mixed>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationships($hardware, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		$findDeviceQuery = new Queries\FindDevicesQuery();
		$findDeviceQuery->byId($hardware->getDevice());

		return [
			self::RELATIONSHIPS_DEVICE => [
				self::RELATIONSHIP_DATA          => $this->deviceRepository->findOneBy($findDeviceQuery),
				self::RELATIONSHIP_LINKS_SELF    => false,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
		];
	}

	/**
	 * @param Entities\Devices\PhysicalDevice\IHardware $hardware
	 * @param string $name
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipRelatedLink($hardware, string $name): JsonApi\Contracts\Schema\LinkInterface
	{
		if ($name === self::RELATIONSHIPS_DEVICE) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_DEVICE,
					[
						Router\Router::URL_ITEM_ID => $hardware->getDevice()->toString(),
					]
				),
				false
			);
		}

		return parent::getRelationshipRelatedLink($hardware, $name);
	}

}
