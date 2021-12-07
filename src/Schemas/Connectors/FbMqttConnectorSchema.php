<?php declare(strict_types = 1);

/**
 * FbMqttConnectorSchema.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Schemas
 * @since          0.1.0
 *
 * @date           20.02.21
 */

namespace FastyBird\DevicesModule\Schemas\Connectors;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Schemas;
use Neomerx\JsonApi;

/**
 * FB MQTT connector entity schema
 *
 * @package         FastyBird:DevicesModule!
 * @subpackage      Schemas
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends ConnectorSchema<Entities\Connectors\IFbMqttConnector>
 */
final class FbMqttConnectorSchema extends ConnectorSchema
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = 'devices-module/connector-fb-mqtt';

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass(): string
	{
		return Entities\Connectors\FbMqttConnector::class;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

	/**
	 * @param Entities\Connectors\IFbMqttConnector $connector
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, mixed>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($connector, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return array_merge((array) parent::getAttributes($connector, $context), [
			'server'       => $connector->getServer(),
			'port'         => $connector->getPort(),
			'secured_port' => $connector->getSecuredPort(),
			'username'     => $connector->getUsername(),
			'password'     => $connector->getPassword(),
		]);
	}

}
