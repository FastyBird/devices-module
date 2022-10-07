<?php declare(strict_types = 1);

/**
 * Connector.php
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

use FastyBird\DevicesModule\DataStorage;
use FastyBird\Exchange\Consumer as ExchangeConsumer;
use FastyBird\Metadata\Constants as MetadataConstants;
use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Types as MetadataTypes;
use League\Flysystem;
use Nette;
use Nette\Utils;
use function strval;

/**
 * Exchange consumer for connectors
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Consumers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Connector implements ExchangeConsumer\IConsumer
{

	use Nette\SmartObject;

	public function __construct(private DataStorage\Reader $reader)
	{
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Utils\JsonException
	 * @throws Flysystem\FilesystemException
	 */
	public function consume(
		MetadataTypes\ModuleSourceType|MetadataTypes\PluginSourceType|MetadataTypes\ConnectorSourceType $source,
		MetadataTypes\RoutingKeyType $routingKey,
		MetadataEntities\IEntity|null $entity,
	): void
	{
		if (
			Utils\Strings::startsWith(strval($routingKey->getValue()), MetadataConstants::MESSAGE_BUS_ENTITY_PREFIX_KEY)
			&& (
				Utils\Strings::contains(
					strval($routingKey->getValue()),
					MetadataConstants::MESSAGE_BUS_ENTITY_CREATED_KEY,
				)
				|| Utils\Strings::contains(
					strval($routingKey->getValue()),
					MetadataConstants::MESSAGE_BUS_ENTITY_UPDATED_KEY,
				)
				|| Utils\Strings::contains(
					strval($routingKey->getValue()),
					MetadataConstants::MESSAGE_BUS_ENTITY_DELETED_KEY,
				)
			)
		) {
			$this->reader->read();
		}
	}

}
