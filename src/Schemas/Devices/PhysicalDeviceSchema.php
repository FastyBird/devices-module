<?php declare(strict_types = 1);

/**
 * PhysicalDeviceSchema.php
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

namespace FastyBird\DevicesModule\Schemas\Devices;

use FastyBird\DevicesModule;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Router;
use FastyBird\DevicesModule\Schemas;
use Neomerx\JsonApi;

/**
 * Physical device entity schema
 *
 * @package         FastyBird:DevicesModule!
 * @subpackage      Schemas
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-template T of Entities\Devices\IPhysicalDevice
 * @phpstan-extends  DeviceSchema<T>
 */
abstract class PhysicalDeviceSchema extends DeviceSchema
{

	/**
	 * Define relationships names
	 */
	public const RELATIONSHIPS_HARDWARE = 'hardware';
	public const RELATIONSHIPS_FIRMWARE = 'firmware';

	/**
	 * @param Entities\Devices\IPhysicalDevice $device
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, mixed>
	 *
	 * @phpstan-param T $device
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationships($device, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return array_merge([
			self::RELATIONSHIPS_HARDWARE => [
				self::RELATIONSHIP_DATA          => $device->getHardware(),
				self::RELATIONSHIP_LINKS_SELF    => true,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
			self::RELATIONSHIPS_FIRMWARE => [
				self::RELATIONSHIP_DATA          => $device->getFirmware(),
				self::RELATIONSHIP_LINKS_SELF    => true,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
		], (array) parent::getRelationships($device, $context));
	}

	/**
	 * @param Entities\Devices\IPhysicalDevice $device
	 * @param string $name
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpstan-param T $device
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipRelatedLink($device, string $name): JsonApi\Contracts\Schema\LinkInterface
	{
		if ($name === self::RELATIONSHIPS_HARDWARE) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_DEVICE_HARDWARE,
					[
						Router\Routes::URL_DEVICE_ID => $device->getPlainId(),
					]
				),
				false
			);

		} elseif ($name === self::RELATIONSHIPS_FIRMWARE) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					DevicesModule\Constants::ROUTE_NAME_DEVICE_FIRMWARE,
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
	 * @param Entities\Devices\IPhysicalDevice $device
	 * @param string $name
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpstan-param T $device
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipSelfLink($device, string $name): JsonApi\Contracts\Schema\LinkInterface
	{
		if (
			$name === self::RELATIONSHIPS_HARDWARE
			|| $name === self::RELATIONSHIPS_FIRMWARE
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
