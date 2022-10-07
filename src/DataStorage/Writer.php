<?php declare(strict_types = 1);

/**
 * Writer.php
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
use Nette;
use Nette\Utils;
use Psr\EventDispatcher;
use function array_merge;

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

	use Nette\SmartObject;

	public function __construct(
		private Models\Connectors\ConnectorsRepository $connectorsRepository,
		private Models\Connectors\Properties\PropertiesRepository $connectorsPropertiesRepository,
		private Models\Connectors\Controls\ControlsRepository $connectorsControlsRepository,
		private Models\Devices\DevicesRepository $devicesRepository,
		private Models\Devices\Properties\PropertiesRepository $devicesPropertiesRepository,
		private Models\Devices\Controls\ControlsRepository $devicesControlsRepository,
		private Models\Devices\Attributes\AttributesRepository $devicesAttributesRepository,
		private Models\Channels\ChannelsRepository $channelsRepository,
		private Models\Channels\Properties\PropertiesRepository $channelsPropertiesRepository,
		private Models\Channels\Controls\ControlsRepository $channelsControlsRepository,
		private Reader $reader,
		private Flysystem\Filesystem $filesystem,
		private EventDispatcher\EventDispatcherInterface|null $dispatcher,
	)
	{
	}

	/**
	 * @throws Flysystem\FilesystemException
	 * @throws Utils\JsonException
	 */
	public function write(): void
	{
		$data = [];

		$findConnectorsQuery = new Queries\FindConnectors();

		$connectors = $this->connectorsRepository->findAllBy($findConnectorsQuery);

		foreach ($connectors as $connector) {
			$devices = [];

			$findDevicesQuery = new Queries\FindDevices();
			$findDevicesQuery->forConnector($connector);

			foreach ($this->devicesRepository->findAllBy($findDevicesQuery) as $device) {
				$channels = [];

				$findChannelsQuery = new Queries\FindChannels();
				$findChannelsQuery->forDevice($device);

				foreach ($this->channelsRepository->findAllBy($findChannelsQuery) as $channel) {
					$properties = [];

					$findChannelPropertiesQuery = new Queries\FindChannelProperties();
					$findChannelPropertiesQuery->forChannel($channel);

					foreach ($this->channelsPropertiesRepository->findAllBy($findChannelPropertiesQuery) as $property) {
						$properties[$property->getPlainId()] = $property->toArray();
					}

					$controls = [];

					$findChannelControlsQuery = new Queries\FindChannelControls();
					$findChannelControlsQuery->forChannel($channel);

					foreach ($this->channelsControlsRepository->findAllBy($findChannelControlsQuery) as $control) {
						$controls[$control->getPlainId()] = $control->toArray();
					}

					$channels[$channel->getPlainId()] = array_merge($channel->toArray(), [
						DevicesModule\Constants::DATA_STORAGE_PROPERTIES_KEY => $properties,
						DevicesModule\Constants::DATA_STORAGE_CONTROLS_KEY => $controls,
					]);
				}

				$properties = [];

				$findDevicePropertiesQuery = new Queries\FindDeviceProperties();
				$findDevicePropertiesQuery->forDevice($device);

				foreach ($this->devicesPropertiesRepository->findAllBy($findDevicePropertiesQuery) as $property) {
					$properties[$property->getPlainId()] = $property->toArray();
				}

				$attributes = [];

				$findDeviceAttributesQuery = new Queries\FindDeviceAttributes();
				$findDeviceAttributesQuery->forDevice($device);

				foreach ($this->devicesAttributesRepository->findAllBy($findDeviceAttributesQuery) as $attribute) {
					$attributes[$attribute->getPlainId()] = $attribute->toArray();
				}

				$controls = [];

				$findDeviceControlsQuery = new Queries\FindDeviceControls();
				$findDeviceControlsQuery->forDevice($device);

				foreach ($this->devicesControlsRepository->findAllBy($findDeviceControlsQuery) as $control) {
					$controls[$control->getPlainId()] = $control->toArray();
				}

				$devices[$device->getPlainId()] = array_merge($device->toArray(), [
					DevicesModule\Constants::DATA_STORAGE_PROPERTIES_KEY => $properties,
					DevicesModule\Constants::DATA_STORAGE_ATTRIBUTES_KEY => $attributes,
					DevicesModule\Constants::DATA_STORAGE_CONTROLS_KEY => $controls,
					DevicesModule\Constants::DATA_STORAGE_CHANNELS_KEY => $channels,
				]);
			}

			$properties = [];

			$findConnectorPropertiesQuery = new Queries\FindConnectorProperties();
			$findConnectorPropertiesQuery->forConnector($connector);

			foreach ($this->connectorsPropertiesRepository->findAllBy($findConnectorPropertiesQuery) as $property) {
				$properties[$property->getPlainId()] = $property->toArray();
			}

			$controls = [];

			$findConnectorControlsQuery = new Queries\FindConnectorControls();
			$findConnectorControlsQuery->forConnector($connector);

			foreach ($this->connectorsControlsRepository->findAllBy($findConnectorControlsQuery) as $control) {
				$controls[$control->getPlainId()] = $control->toArray();
			}

			$data[$connector->getPlainId()] = array_merge($connector->toArray(), [
				DevicesModule\Constants::DATA_STORAGE_PROPERTIES_KEY => $properties,
				DevicesModule\Constants::DATA_STORAGE_CONTROLS_KEY => $controls,
				DevicesModule\Constants::DATA_STORAGE_DEVICES_KEY => $devices,
			]);
		}

		$this->filesystem->write(DevicesModule\Constants::CONFIGURATION_FILE_FILENAME, Utils\Json::encode($data));

		$this->reader->read();

		$this->dispatcher?->dispatch(new Events\DataStorageWritten());
	}

}
