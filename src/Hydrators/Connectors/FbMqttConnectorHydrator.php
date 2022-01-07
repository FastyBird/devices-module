<?php declare(strict_types = 1);

/**
 * FbMqttConnectorHydrator.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 * @since          0.6.0
 *
 * @date           07.12.21
 */

namespace FastyBird\DevicesModule\Hydrators\Connectors;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Schemas;
use IPub\JsonAPIDocument;

/**
 * FB MQTT Connector entity hydrator
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends ConnectorHydrator<Entities\Connectors\IFbMqttConnector>
 */
final class FbMqttConnectorHydrator extends ConnectorHydrator
{

	/** @var string[] */
	protected array $attributes = [
		0 => 'name',
		1 => 'enabled',
		2 => 'server',
		3 => 'port',
		4 => 'username',
		5 => 'password',

		'secured_port' => 'securedPort',
	];

	/**
	 * {@inheritDoc}
	 */
	protected function getEntityName(): string
	{
		return Entities\Connectors\FbMqttConnector::class;
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return string|null
	 */
	protected function hydrateServerAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): ?string
	{
		if (
			!is_scalar($attributes->get('server'))
			|| (string) $attributes->get('server') === ''
		) {
			return null;
		}

		return (string) $attributes->get('server');
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return string|null
	 */
	protected function hydratePortAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): ?int
	{
		if (
			!is_scalar($attributes->get('port'))
			|| (string) $attributes->get('port') === ''
		) {
			return null;
		}

		return (int) $attributes->get('port');
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return string|null
	 */
	protected function hydrateSecuredPortAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): ?int
	{
		if (
			!is_scalar($attributes->get('secured_port'))
			|| (string) $attributes->get('secured_port') === ''
		) {
			return null;
		}

		return (int) $attributes->get('secured_port');
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return string|null
	 */
	protected function hydrateUsernameAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): ?string
	{
		if (
			!is_scalar($attributes->get('username'))
			|| (string) $attributes->get('username') === ''
		) {
			return null;
		}

		return (string) $attributes->get('username');
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return string|null
	 */
	protected function hydratePasswordAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): ?string
	{
		if (
			!is_scalar($attributes->get('password'))
			|| (string) $attributes->get('password') === ''
		) {
			return null;
		}

		return (string) $attributes->get('password');
	}

}
