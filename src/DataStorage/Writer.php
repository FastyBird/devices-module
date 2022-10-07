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

	/** @var Models\Connectors\ConnectorsRepository */
	private Models\Connectors\ConnectorsRepository $connectorsRepository;

	/** @var Models\Connectors\Properties\PropertiesRepository */
	private Models\Connectors\Properties\PropertiesRepository $connectorsPropertiesRepository;

	/** @var Models\Connectors\Controls\ControlsRepository */
	private Models\Connectors\Controls\ControlsRepository $connectorsControlsRepository;

	/** @var Models\Devices\DevicesRepository */
	private Models\Devices\DevicesRepository $devicesRepository;

	/** @var Models\Devices\Properties\PropertiesRepository */
	private Models\Devices\Properties\PropertiesRepository $devicesPropertiesRepository;

	/** @var Models\Devices\Controls\ControlsRepository */
	private Models\Devices\Controls\ControlsRepository $devicesControlsRepository;

	/** @var Models\Devices\Attributes\AttributesRepository */
	private Models\Devices\Attributes\AttributesRepository $devicesAttributesRepository;

	/** @var Models\Channels\ChannelsRepository */
	private Models\Channels\ChannelsRepository $channelsRepository;

	/** @var Models\Channels\Properties\PropertiesRepository */
	private Models\Channels\Properties\PropertiesRepository $channelsPropertiesRepository;

	/** @var Models\Channels\Controls\ControlsRepository */
	private Models\Channels\Controls\ControlsRepository $channelsControlsRepository;

	/** @var Flysystem\Filesystem */
	private Flysystem\Filesystem $filesystem;

	/** @var EventDispatcher\EventDispatcherInterface|null */
	private ?EventDispatcher\EventDispatcherInterface $dispatcher;

	/**
	 * @param Models\Connectors\ConnectorsRepository $connectorsRepository
	 * @param Models\Connectors\Properties\PropertiesRepository $connectorsPropertiesRepository
	 * @param Models\Connectors\Controls\ControlsRepository $connectorsControlsRepository
	 * @param Models\Devices\DevicesRepository $devicesRepository
	 * @param Models\Devices\Properties\PropertiesRepository $devicesPropertiesRepository
	 * @param Models\Devices\Controls\ControlsRepository $devicesControlsRepository
	 * @param Models\Devices\Attributes\AttributesRepository $devicesAttributesRepository
	 * @param Models\Channels\ChannelsRepository $channelsRepository
	 * @param Models\Channels\Properties\PropertiesRepository $channelsPropertiesRepository
	 * @param Models\Channels\Controls\ControlsRepository $channelsControlsRepository
	 * @param Flysystem\Filesystem $filesystem
	 * @param EventDispatcher\EventDispatcherInterface|null $dispatcher
	 */
	public function __construct(
		Models\Connectors\ConnectorsRepository $connectorsRepository,
		Models\Connectors\Properties\PropertiesRepository $connectorsPropertiesRepository,
		Models\Connectors\Controls\ControlsRepository $connectorsControlsRepository,
		Models\Devices\DevicesRepository $devicesRepository,
		Models\Devices\Properties\PropertiesRepository $devicesPropertiesRepository,
		Models\Devices\Controls\ControlsRepository $devicesControlsRepository,
		Models\Devices\Attributes\AttributesRepository $devicesAttributesRepository,
		Models\Channels\ChannelsRepository $channelsRepository,
		Models\Channels\Properties\PropertiesRepository $channelsPropertiesRepository,
		Models\Channels\Controls\ControlsRepository $channelsControlsRepository,
		Flysystem\Filesystem $filesystem,
		?EventDispatcher\EventDispatcherInterface $dispatcher
	) {
		$this->connectorsRepository = $connectorsRepository;
		$this->connectorsPropertiesRepository = $connectorsPropertiesRepository;
		$this->connectorsControlsRepository = $connectorsControlsRepository;
		$this->devicesRepository = $devicesRepository;
		$this->devicesPropertiesRepository = $devicesPropertiesRepository;
		$this->devicesControlsRepository = $devicesControlsRepository;
		$this->devicesAttributesRepository = $devicesAttributesRepository;
		$this->channelsRepository = $channelsRepository;
		$this->channelsPropertiesRepository = $channelsPropertiesRepository;
		$this->channelsControlsRepository = $channelsControlsRepository;

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
						DevicesModule\Constants::DATA_STORAGE_CONTROLS_KEY   => $controls,
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
					DevicesModule\Constants::DATA_STORAGE_CONTROLS_KEY   => $controls,
					DevicesModule\Constants::DATA_STORAGE_CHANNELS_KEY   => $channels,
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
				DevicesModule\Constants::DATA_STORAGE_CONTROLS_KEY   => $controls,
				DevicesModule\Constants::DATA_STORAGE_DEVICES_KEY    => $devices,
			]);
		}

		$this->filesystem->write(DevicesModule\Constants::CONFIGURATION_FILE_FILENAME, Utils\Json::encode($data));

		$this->dispatcher?->dispatch(new Events\DataStorageWritten());
	}

}
