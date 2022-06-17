<?php declare(strict_types = 1);

/**
 * ConnectorConsumer.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Consumers
 * @since          0.60.0
 *
 * @date           31.05.22
 */

namespace FastyBird\DevicesModule\Consumers;

use FastyBird\DevicesModule\Connectors;
use FastyBird\Exchange\Consumer as ExchangeConsumer;
use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Types as MetadataTypes;
use Nette;
use Nette\Utils;
use Psr\Log;

/**
 * Data exchange consumer for connectors
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Consumers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ConnectorConsumer implements ExchangeConsumer\IConsumer
{

	use Nette\SmartObject;

	private const ENTITY_PREFIX_KEY = 'fb.exchange.module.entity';

	private const ENTITY_REPORTED_KEY = 'reported';
	private const ENTITY_CREATED_KEY = 'created';
	private const ENTITY_UPDATED_KEY = 'updated';
	private const ENTITY_DELETED_KEY = 'deleted';

	private const PROPERTIES_ACTIONS_ROUTING_KEYS = [
		MetadataTypes\RoutingKeyType::ROUTE_DEVICE_PROPERTY_ACTION,
		MetadataTypes\RoutingKeyType::ROUTE_CHANNEL_PROPERTY_ACTION,
	];

	private const CONTROLS_ACTIONS_ROUTING_KEYS = [
		MetadataTypes\RoutingKeyType::ROUTE_CONNECTOR_ACTION,
		MetadataTypes\RoutingKeyType::ROUTE_DEVICE_ACTION,
		MetadataTypes\RoutingKeyType::ROUTE_CHANNEL_ACTION,
	];

	/** @var Connectors\Connector */
	private Connectors\Connector $connector;

	/** @var Log\LoggerInterface */
	private Log\LoggerInterface $logger;

	public function __construct(
		Connectors\Connector $connector,
		?Log\LoggerInterface $logger = null
	) {
		$this->connector = $connector;

		$this->logger = $logger ?? new Log\NullLogger();
	}

	/**
	 * {@inheritDoc}
	 */
	public function consume(
		$source,
		MetadataTypes\RoutingKeyType $routingKey,
		?MetadataEntities\IEntity $entity
	): void {
		if ($entity !== null) {
			if (in_array($routingKey->getValue(), self::PROPERTIES_ACTIONS_ROUTING_KEYS, true)) {
				$this->connector->handlePropertyCommand($entity);

			} elseif (in_array($routingKey->getValue(), self::CONTROLS_ACTIONS_ROUTING_KEYS, true)) {
				$this->connector->handleControlCommand($entity);

			} elseif (Utils\Strings::startsWith($routingKey->getValue(), self::ENTITY_PREFIX_KEY)) {
				if (Utils\Strings::contains($routingKey->getValue(), self::ENTITY_REPORTED_KEY)) {
					$this->connector->handleEntityReported($entity);

				} elseif (Utils\Strings::contains($routingKey->getValue(), self::ENTITY_CREATED_KEY)) {
					$this->connector->handleEntityCreated($entity);

				} elseif (Utils\Strings::contains($routingKey->getValue(), self::ENTITY_UPDATED_KEY)) {
					$this->connector->handleEntityUpdated($entity);

				} elseif (Utils\Strings::contains($routingKey->getValue(), self::ENTITY_DELETED_KEY)) {
					$this->connector->handleEntityDeleted($entity);
				}
			} else {
				$this->logger->debug('Received unknown exchange message');
			}
		} else {
			$this->logger->warning('Received data message without data');
		}
	}

}
