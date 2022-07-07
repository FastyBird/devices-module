<?php declare(strict_types = 1);

/**
 * ExchangeSubscriber.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Subscribers
 * @since          0.62.0
 *
 * @date           19.06.22
 */

namespace FastyBird\DevicesModule\Subscribers;

use FastyBird\DevicesModule\DataStorage;
use FastyBird\Exchange\Events as ExchangeEvents;
use FastyBird\Metadata\Constants as MetadataConstants;
use League\Flysystem;
use Nette;
use Nette\Utils;
use Symfony\Component\EventDispatcher;

/**
 * Exchange events
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Subscribers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ExchangeSubscriber implements EventDispatcher\EventSubscriberInterface
{

	use Nette\SmartObject;

	/** @var DataStorage\Reader */
	private DataStorage\Reader $reader;

	public function __construct(
		DataStorage\Reader $reader
	) {
		$this->reader = $reader;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			ExchangeEvents\BeforeMessageConsumedEvent::class => 'messageConsumed',
		];
	}

	/**
	 * @param ExchangeEvents\BeforeMessageConsumedEvent $event
	 *
	 * @return void
	 *
	 * @throws Flysystem\FilesystemException
	 * @throws Utils\JsonException
	 */
	public function messageConsumed(ExchangeEvents\BeforeMessageConsumedEvent $event): void
	{
		if (
			Utils\Strings::startsWith($event->getRoutingKey()->getValue(), MetadataConstants::MESSAGE_BUS_ENTITY_PREFIX_KEY)
			&& (
				Utils\Strings::contains($event->getRoutingKey()->getValue(), MetadataConstants::MESSAGE_BUS_ENTITY_CREATED_KEY)
				|| Utils\Strings::contains($event->getRoutingKey()->getValue(), MetadataConstants::MESSAGE_BUS_ENTITY_UPDATED_KEY)
				|| Utils\Strings::contains($event->getRoutingKey()->getValue(), MetadataConstants::MESSAGE_BUS_ENTITY_DELETED_KEY)
			)
		) {
			$this->reader->read();
		}
	}

}
