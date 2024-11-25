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
 * @date           27.11.23
 */

namespace FastyBird\Module\Devices\Documents\Connectors\Properties;

use DateTimeInterface;
use FastyBird\Core\Application\Documents as ApplicationDocuments;
use FastyBird\Core\Application\ObjectMapper as ApplicationObjectMapper;
use FastyBird\Core\Exchange\Documents as ExchangeDocuments;
use FastyBird\Core\Tools\Exceptions as ToolsExceptions;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use FastyBird\Module\Devices;
use FastyBird\Module\Devices\Documents;
use FastyBird\Module\Devices\Entities;
use FastyBird\Module\Devices\Exceptions;
use FastyBird\Module\Devices\Types;
use Ramsey\Uuid;
use TypeError;
use ValueError;
use function array_merge;

/**
 * Connector property document
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Documents
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
#[ApplicationDocuments\Mapping\Document(entity: Entities\Connectors\Properties\Property::class)]
#[ApplicationDocuments\Mapping\InheritanceType('SINGLE_TABLE')]
#[ApplicationDocuments\Mapping\DiscriminatorColumn(name: 'type', type: 'string')]
#[ApplicationDocuments\Mapping\DiscriminatorMap([
	Entities\Connectors\Properties\Dynamic::TYPE => Dynamic::class,
	Entities\Connectors\Properties\Variable::TYPE => Variable::class,
])]
#[ApplicationDocuments\Mapping\MappedSuperclass]
#[ExchangeDocuments\Mapping\RoutingMap([
	Devices\Constants::MESSAGE_BUS_CONNECTOR_PROPERTY_DOCUMENT_REPORTED_ROUTING_KEY,
	Devices\Constants::MESSAGE_BUS_CONNECTOR_PROPERTY_DOCUMENT_CREATED_ROUTING_KEY,
	Devices\Constants::MESSAGE_BUS_CONNECTOR_PROPERTY_DOCUMENT_UPDATED_ROUTING_KEY,
	Devices\Constants::MESSAGE_BUS_CONNECTOR_PROPERTY_DOCUMENT_DELETED_ROUTING_KEY,
])]
abstract class Property extends Documents\Property
{

	/**
	 * @param string|array<int, string>|array<int, bool|string|int|float|array<int, bool|string|int|float>|null>|array<int, array<int, string|array<int, string|int|float|bool>|null>>|null $format
	 */
	public function __construct(
		Uuid\UuidInterface $id,
		#[ApplicationObjectMapper\Rules\UuidValue()]
		private readonly Uuid\UuidInterface $connector,
		Types\PropertyCategory $category,
		string $identifier,
		string|null $name,
		MetadataTypes\DataType $dataType,
		string|null $unit = null,
		string|array|null $format = null,
		float|int|string|null $invalid = null,
		int|null $scale = null,
		int|float|null $step = null,
		bool|float|int|string|null $default = null,
		Uuid\UuidInterface|string|null $valueTransformer = null,
		Uuid\UuidInterface|null $owner = null,
		DateTimeInterface|null $createdAt = null,
		DateTimeInterface|null $updatedAt = null,
	)
	{
		parent::__construct(
			$id,
			$category,
			$identifier,
			$name,
			$dataType,
			$unit,
			$format,
			$invalid,
			$scale,
			$step,
			$default,
			$valueTransformer,
			$owner,
			$createdAt,
			$updatedAt,
		);
	}

	public function getConnector(): Uuid\UuidInterface
	{
		return $this->connector;
	}

	/**
	 * @throws Exceptions\InvalidState
	 * @throws ToolsExceptions\InvalidArgument
	 * @throws ToolsExceptions\InvalidState
	 * @throws TypeError
	 * @throws ValueError
	 */
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
