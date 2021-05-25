<?php declare(strict_types = 1);

/**
 * ConnectorHydrator.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 * @since          0.1.0
 *
 * @date           17.01.21
 */

namespace FastyBird\DevicesModule\Hydrators\Devices\Connectors;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Schemas;
use FastyBird\JsonApi\Hydrators as JsonApiHydrators;
use IPub\JsonAPIDocument;

/**
 * Device connector entity hydrator
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends JsonApiHydrators\Hydrator<Entities\Devices\Connectors\IConnector>
 */
final class ConnectorHydrator extends JsonApiHydrators\Hydrator
{

	/** @var string[] */
	protected array $attributes = [
		'username',
		'password',
		'address',
		'max_packet_size',
	];

	/** @var string[] */
	protected array $relationships = [
		Schemas\Devices\Connectors\ConnectorSchema::RELATIONSHIPS_CONNECTOR,
	];

	/** @var string */
	protected string $translationDomain = 'devices-module.connectors';

	/**
	 * {@inheritDoc}
	 */
	protected function getEntityName(): string
	{
		return Entities\Devices\Connectors\Connector::class;
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 * @param Entities\Devices\Connectors\IConnector|null $connector
	 *
	 * @return string|null
	 */
	protected function hydrateUsernameAttribute(
		JsonAPIDocument\Objects\IStandardObject $attributes,
		?Entities\Devices\Connectors\IConnector $connector
	): ?string {
		if (
			!is_scalar($attributes->get('username'))
			|| (string) $attributes->get('username') === ''
		) {
			return null;
		}

		if ($connector !== null) {
			$connector->setParam('username', (string) $attributes->get('username'));
		}

		return (string) $attributes->get('username');
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 * @param Entities\Devices\Connectors\IConnector|null $connector
	 *
	 * @return string|null
	 */
	protected function hydratePasswordAttribute(
		JsonAPIDocument\Objects\IStandardObject $attributes,
		?Entities\Devices\Connectors\IConnector $connector
	): ?string {
		if (
			!is_scalar($attributes->get('password'))
			|| (string) $attributes->get('password') === ''
		) {
			return null;
		}

		if ($connector !== null) {
			$connector->setParam('password', (string) $attributes->get('password'));
		}

		return (string) $attributes->get('password');
	}

}
