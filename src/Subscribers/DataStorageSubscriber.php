<?php declare(strict_types = 1);

/**
 * DataStorageSubscriber.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Subscribers
 * @since          0.65.0
 *
 * @date           29.06.22
 */

namespace FastyBird\DevicesModule\Subscribers;

use FastyBird\DevicesModule\DataStorage;
use FastyBird\DevicesModule\Events;
use League\Flysystem;
use Nette;
use Nette\Utils;
use Symfony\Component\EventDispatcher;

/**
 * Data storage subscriber
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Subscribers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class DataStorageSubscriber implements EventDispatcher\EventSubscriberInterface
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
			Events\DataStorageWrittenEvent::class  => 'storageWritten',
		];
	}

	/**
	 * @param Events\DataStorageWrittenEvent $event
	 *
	 * @return void
	 *
	 * @throws Flysystem\FilesystemException
	 * @throws Utils\JsonException
	 */
	public function storageWritten(Events\DataStorageWrittenEvent $event): void
	{
		$this->reader->read();
	}

}
