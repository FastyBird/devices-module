<?php declare(strict_types = 1);

/**
 * Property.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Documents
 * @since          1.0.0
 *
 * @date           23.01.24
 */

namespace FastyBird\Module\Devices\Documents\States\Connectors\Properties;

use DateTimeInterface;
use FastyBird\Core\Application\Documents as ApplicationDocuments;
use FastyBird\Core\Application\ObjectMapper as ApplicationObjectMapper;
use FastyBird\Core\Exchange\Documents as ExchangeDocuments;
use FastyBird\Module\Devices;
use FastyBird\Module\Devices\Documents;
use Ramsey\Uuid;
use function array_merge;

/**
 * Connector property state document
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Documents
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
#[ApplicationDocuments\Mapping\Document]
#[ExchangeDocuments\Mapping\RoutingMap([
	Devices\Constants::MESSAGE_BUS_CONNECTOR_PROPERTY_STATE_DOCUMENT_REPORTED_ROUTING_KEY,
	Devices\Constants::MESSAGE_BUS_CONNECTOR_PROPERTY_STATE_DOCUMENT_CREATED_ROUTING_KEY,
	Devices\Constants::MESSAGE_BUS_CONNECTOR_PROPERTY_STATE_DOCUMENT_UPDATED_ROUTING_KEY,
	Devices\Constants::MESSAGE_BUS_CONNECTOR_PROPERTY_STATE_DOCUMENT_DELETED_ROUTING_KEY,
])]
final class Property extends Devices\Documents\States\Property
{

	public function __construct(
		Uuid\UuidInterface $id,
		#[ApplicationObjectMapper\Rules\UuidValue()]
		private readonly Uuid\UuidInterface $connector,
		Documents\States\StateValues $read,
		Documents\States\StateValues $get,
		bool|DateTimeInterface $pending = false,
		bool $valid = false,
		DateTimeInterface|null $createdAt = null,
		DateTimeInterface|null $updatedAt = null,
	)
	{
		parent::__construct($id, $read, $get, $pending, $valid, $createdAt, $updatedAt);
	}

	public function getConnector(): Uuid\UuidInterface
	{
		return $this->connector;
	}

	public function toArray(): array
	{
		return array_merge(
			parent::toArray(),
			[
				'connector' => $this->getConnector()->toString(),
			],
		);
	}

}
