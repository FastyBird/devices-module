<?php declare(strict_types = 1);

/**
 * Connector.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Consumers
 * @since          0.60.0
 *
 * @date           31.05.22
 */

namespace FastyBird\Module\Devices\Consumers;

use FastyBird\Library\Exchange\Consumer as ExchangeConsumer;
use FastyBird\Library\Metadata;
use FastyBird\Library\Metadata\Entities as MetadataEntities;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use FastyBird\Module\Devices\DataStorage;
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
final class Connector implements ExchangeConsumer\Consumer
{

	use Nette\SmartObject;

	public function __construct(private readonly DataStorage\Reader $reader)
	{
	}

	/**
	 * @throws Utils\JsonException
	 * @throws Flysystem\FilesystemException
	 */
	public function consume(
		MetadataTypes\ModuleSource|MetadataTypes\PluginSource|MetadataTypes\ConnectorSource|MetadataTypes\TriggerSource $source,
		MetadataTypes\RoutingKey $routingKey,
		MetadataEntities\Entity|null $entity,
	): void
	{
		if (
			Utils\Strings::startsWith(
				strval($routingKey->getValue()),
				Metadata\Constants::MESSAGE_BUS_ENTITY_PREFIX_KEY,
			)
			&& (
				Utils\Strings::contains(
					strval($routingKey->getValue()),
					Metadata\Constants::MESSAGE_BUS_ENTITY_CREATED_KEY,
				)
				|| Utils\Strings::contains(
					strval($routingKey->getValue()),
					Metadata\Constants::MESSAGE_BUS_ENTITY_UPDATED_KEY,
				)
				|| Utils\Strings::contains(
					strval($routingKey->getValue()),
					Metadata\Constants::MESSAGE_BUS_ENTITY_DELETED_KEY,
				)
			)
		) {
			$this->reader->read();
		}
	}

}