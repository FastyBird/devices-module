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
			$this->connector->handleMessage(new Connectors\Messages\ExchangeMessage($routingKey, $entity));

		} else {
			$this->logger->warning('Received data message without data', [
				'source' => 'devices-module',
				'type'   => 'connector-consumer',
			]);
		}
	}

}
