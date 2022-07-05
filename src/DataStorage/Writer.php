<?php declare(strict_types = 1);

/**
 * BaseV1Controller.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     DataStorage
 * @since          0.1.0
 *
 * @date           13.04.19
 */

namespace FastyBird\DevicesModule\DataStorage;

use FastyBird\DevicesModule;
use FastyBird\DevicesModule\Events;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use League\Flysystem;
use Nette\Utils;
use Psr\EventDispatcher;

/**
 * Data storage configuration writer
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     DataStorage
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Writer
{

	/** @var Models\Connectors\IConnectorsRepository */
	private Models\Connectors\IConnectorsRepository $connectorsRepository;

	/** @var Flysystem\Filesystem */
	private Flysystem\Filesystem $filesystem;

	/** @var EventDispatcher\EventDispatcherInterface|null */
	private ?EventDispatcher\EventDispatcherInterface $dispatcher;

	public function __construct(
		Models\Connectors\IConnectorsRepository $connectorsRepository,
		Flysystem\Filesystem $filesystem,
		?EventDispatcher\EventDispatcherInterface $dispatcher
	) {
		$this->connectorsRepository = $connectorsRepository;
		$this->filesystem = $filesystem;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * @return void
	 *
	 * @throws Flysystem\FilesystemException
	 * @throws Utils\JsonException
	 */
	public function write(): void
	{
		$data = [];

		$findConnectors = new Queries\FindConnectorsQuery();

		$connectors = $this->connectorsRepository->findAllBy($findConnectors);

		foreach ($connectors as $connector) {
			$devices = [];

			foreach ($connector->getDevices() as $device) {
				$channels = [];

				foreach ($device->getChannels() as $channel) {
					$properties = [];

					foreach ($channel->getProperties() as $property) {
						$properties[$property->getPlainId()] = $property->toArray();
					}

					$controls = [];

					foreach ($channel->getControls() as $control) {
						$controls[$control->getPlainId()] = $control->toArray();
					}

					$channels[$channel->getPlainId()] = array_merge($channel->toArray(), [
						DevicesModule\Constants::DATA_STORAGE_PROPERTIES_KEY => $properties,
						DevicesModule\Constants::DATA_STORAGE_CONTROLS_KEY   => $controls,
					]);
				}

				$properties = [];

				foreach ($device->getProperties() as $property) {
					$properties[$property->getPlainId()] = $property->toArray();
				}

				$attributes = [];

				foreach ($device->getAttributes() as $attribute) {
					$attributes[$attribute->getPlainId()] = $attribute->toArray();
				}

				$controls = [];

				foreach ($device->getControls() as $control) {
					$controls[$control->getPlainId()] = $control->toArray();
				}

				$devices[$device->getPlainId()] = array_merge($device->toArray(), [
					DevicesModule\Constants::DATA_STORAGE_PROPERTIES_KEY => $properties,
					DevicesModule\Constants::DATA_STORAGE_ATTRIBUTES_KEY => $attributes,
					DevicesModule\Constants::DATA_STORAGE_CONTROLS_KEY   => $controls,
					DevicesModule\Constants::DATA_STORAGE_CHANNELS_KEY   => $channels,
				]);
			}

			$properties = [];

			foreach ($connector->getProperties() as $property) {
				$properties[$property->getPlainId()] = $property->toArray();
			}

			$controls = [];

			foreach ($connector->getControls() as $control) {
				$controls[$control->getPlainId()] = $control->toArray();
			}

			$data[$connector->getPlainId()] = array_merge($connector->toArray(), [
				DevicesModule\Constants::DATA_STORAGE_PROPERTIES_KEY => $properties,
				DevicesModule\Constants::DATA_STORAGE_CONTROLS_KEY   => $controls,
				DevicesModule\Constants::DATA_STORAGE_DEVICES_KEY    => $devices,
			]);
		}

		$this->filesystem->write(DevicesModule\Constants::CONFIGURATION_FILE_FILENAME, Utils\Json::encode($data));

		if ($this->dispatcher !== null) {
			$this->dispatcher->dispatch(new Events\DataStorageWrittenEvent());
		}
	}

}
