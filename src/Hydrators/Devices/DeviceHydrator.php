<?php declare(strict_types = 1);

/**
 * Device.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 * @since          0.1.0
 *
 * @date           07.06.19
 */

namespace FastyBird\DevicesModule\Hydrators\Devices;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Schemas;
use FastyBird\JsonApi\Hydrators as JsonApiHydrators;
use IPub\JsonAPIDocument;

/**
 * Device entity hydrator
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends JsonApiHydrators\Hydrator<Entities\Devices\IDevice>
 */
class DeviceHydrator extends JsonApiHydrators\Hydrator
{

	/** @var string[] */
	protected array $attributes = [
		'identifier',
		'name',
		'comment',
		'enabled',
		'params',
	];

	/** @var string[] */
	protected array $relationships = [
		Schemas\Devices\DeviceSchema::RELATIONSHIPS_CONNECTOR,
	];

	/** @var string */
	protected string $translationDomain = 'devices-module.devices';

	/**
	 * {@inheritDoc}
	 */
	protected function getEntityName(): string
	{
		return Entities\Devices\Device::class;
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return string|null
	 */
	protected function hydrateNameAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): ?string
	{
		if (
			!is_scalar($attributes->get('name'))
			|| (string) $attributes->get('name') === ''
		) {
			return null;
		}

		return (string) $attributes->get('name');
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return string|null
	 */
	protected function hydrateCommentAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): ?string
	{
		if (
			!is_scalar($attributes->get('comment'))
			|| (string) $attributes->get('comment') === ''
		) {
			return null;
		}

		return (string) $attributes->get('comment');
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return bool
	 */
	protected function hydrateEnabledAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): bool
	{
		return is_scalar($attributes->get('enabled')) && (bool) $attributes->get('enabled');
	}

}
