<?php declare(strict_types = 1);

/**
 * DeviceSchema.php
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

namespace FastyBird\DevicesModule\Schemas\Devices;

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
 * Device entity schema
 *
 * @package          FastyBird:DevicesModule!
 * @subpackage       Schemas
 *
 * @author           Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends JsonApiSchemas\JsonApiSchema<Entities\Devices\IDevice>
 */
class DeviceSchema extends JsonApiSchemas\JsonApiSchema
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = 'devices-module/device';

	/**
	 * Define relationships names
	 */
	public const RELATIONSHIPS_CHANNELS = 'channels';

	public const RELATIONSHIPS_PROPERTIES = 'properties';
	public const RELATIONSHIPS_CONFIGURATION = 'configuration';

	public const RELATIONSHIPS_PARENT = 'parent';
	public const RELATIONSHIPS_CHILDREN = 'children';

	public const RELATIONSHIPS_CONNECTOR = 'connector';

	/** @var Models\Devices\IDeviceRepository */
	protected Models\Devices\IDeviceRepository $deviceRepository;

	/** @var Models\Channels\IChannelRepository */
	protected Models\Channels\IChannelRepository $channelRepository;

	/** @var Routing\IRouter */
	protected Routing\IRouter $router;

	public function __construct(
		Models\Devices\IDeviceRepository $deviceRepository,
		Models\Channels\IChannelRepository $channelRepository,
		Routing\IRouter $router
	) {
		$this->deviceRepository = $deviceRepository;
		$this->channelRepository = $channelRepository;

		$this->router = $router;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass(): string
	{
		return Entities\Devices\Device::class;
	}

	/**
	 * @param Entities\Devices\IDevice $device
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, string>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($device, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return [
			'key'        => $device->getKey(),
			'identifier' => $device->getIdentifier(),
			'name'       => $device->getName(),
			'comment'    => $device->getComment(),

			'enabled' => $device->isEnabled(),

			'hardware_model'        => $device->getHardwareModel()->getValue(),
			'hardware_manufacturer' => $device->getHardwareManufacturer()->getValue(),
			'hardware_version'      => $device->getHardwareVersion(),
			'mac_address'           => $device->getMacAddress(),

			'firmware_manufacturer' => $device->getFirmwareManufacturer()->getValue(),
			'firmware_version'      => $device->getFirmwareVersion(),

			'control' => $this->formatControls($device->getControls()),

			'owner' => $device->getOwnerId(),
		];
	}

	/**
	 * @param Entities\Devices\Controls\IControl[] $controls
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

	/**
	 * @param Entities\Devices\IDevice $device
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getSelfLink($device): JsonApi\Contracts\Schema\LinkInterface
	{
		return new JsonApi\Schema\Link(
			false,
			$this->router->urlFor(
				DevicesModule\Constants::ROUTE_NAME_DEVICE,
				[
					Router\Routes::URL_ITEM_ID => $device->getPlainId(),
				]
			),
			false
		);
	}

	/**
	 * @param Entities\Devices\IDevice $device
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, mixed>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationships($device, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		$relationships = [
			self::RELATIONSHIPS_PROPERTIES    => [
				self::RELATIONSHIP_DATA          => $device->getProperties(),
				self::RELATIONSHIP_LINKS_SELF    => true,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
			self::RELATIONSHIPS_CONFIGURATION => [
				self::RELATIONSHIP_DATA          => $device->getConfiguration(),
				self::RELATIONSHIP_LINKS_SELF    => true,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
			self::RELATIONSHIPS_CHANNELS      => [
				self::RELATIONSHIP_DATA          => $this->getChannels($device),
				self::RELATIONSHIP_LINKS_SELF    => true,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
			self::RELATIONSHIPS_CHILDREN      => [
				self::RELATIONSHIP_DATA          => $this->getChildren($device),
				self::RELATIONSHIP_LINKS_SELF    => true,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
			self::RELATIONSHIPS_CONNECTOR     => [
				self::RELATIONSHIP_DATA          => $device->getConnector(),
				self::RELATIONSHIP_LINKS_SELF    => true,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
		];

		if ($device->getParent() !== null) {
			$relationships[self::RELATIONSHIPS_PARENT] = [
				self::RELATIONSHIP_DATA          => $device->getParent(),
				self::RELATIONSHIP_LINKS_SELF    => true,
				self::RELATIONSHIP_LINKS_RELATED => true,
			];
		}

		return $relationships;
	}

	/**
	 * @param Entities\Devices\IDevice $device
	 *
	 * @return Entities\Channels\IChannel[]
	 */
	private function getChannels(Entities\Devices\IDevice $device): array
	{
		$findQuery = new Queries\FindChannelsQuery();
		$findQuery->forDevice($device);

		return $this->channelRepository->findAllBy($findQuery);
	}

	/**
	 * @param Entities\Devices\IDevice $device
	 *
	 * @return Entities\Devices\IDevice[]
	 */
	private function getChildren(Entities\Devices\IDevice $device): array
	{
		$findQuery = new Queries\FindDevicesQuery();
		$findQuery->forParent($device);

		return $this->deviceRepository->findAllBy($findQuery);
	}

	/**
	 * @param Entities\Devices\IDevice $device
	 * @param string $name
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipRelatedLink($device, string $name): JsonApi\Contracts\Schema\LinkInterface
	{
		if ($name === self::RELATIONSHIPS_PROPERTIES) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_DEVICE_PROPERTIES,
					[
						Router\Routes::URL_DEVICE_ID => $device->getPlainId(),
					]
				),
				true,
				[
					'count' => count($device->getProperties()),
				]
			);

		} elseif ($name === self::RELATIONSHIPS_CONFIGURATION) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_DEVICE_CONFIGURATION_ROWS,
					[
						Router\Routes::URL_DEVICE_ID => $device->getPlainId(),
					]
				),
				true,
				[
					'count' => count($device->getConfiguration()),
				]
			);

		} elseif ($name === self::RELATIONSHIPS_CHANNELS) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_CHANNELS,
					[
						Router\Routes::URL_DEVICE_ID => $device->getPlainId(),
					]
				),
				true,
				[
					'count' => count($device->getChannels()),
				]
			);

		} elseif ($name === self::RELATIONSHIPS_PARENT && $device->getParent() !== null) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_DEVICE,
					[
						Router\Routes::URL_ITEM_ID => $device->getPlainId(),
					]
				),
				false
			);

		} elseif ($name === self::RELATIONSHIPS_CHILDREN) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_DEVICE_CHILDREN,
					[
						Router\Routes::URL_DEVICE_ID => $device->getPlainId(),
					]
				),
				true,
				[
					'count' => count($device->getChildren()),
				]
			);

		} elseif ($name === self::RELATIONSHIPS_CONNECTOR) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_DEVICE_CONNECTOR,
					[
						Router\Routes::URL_DEVICE_ID => $device->getPlainId(),
					]
				),
				false
			);
		}

		return parent::getRelationshipRelatedLink($device, $name);
	}

	/**
	 * @param Entities\Devices\IDevice $device
	 * @param string $name
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipSelfLink($device, string $name): JsonApi\Contracts\Schema\LinkInterface
	{
		if (
			$name === self::RELATIONSHIPS_PROPERTIES
			|| $name === self::RELATIONSHIPS_CONFIGURATION
			|| $name === self::RELATIONSHIPS_CHANNELS
			|| $name === self::RELATIONSHIPS_CHILDREN
			|| ($name === self::RELATIONSHIPS_PARENT && $device->getParent() !== null)
			|| $name === self::RELATIONSHIPS_CONNECTOR
		) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_DEVICE_RELATIONSHIP,
					[
						Router\Routes::URL_ITEM_ID     => $device->getPlainId(),
						Router\Routes::RELATION_ENTITY => $name,

					]
				),
				false
			);
		}

		return parent::getRelationshipSelfLink($device, $name);
	}

}
