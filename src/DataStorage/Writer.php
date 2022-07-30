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

	/** @var Models\Connectors\IConnectorsRepository */
	private Models\Connectors\IConnectorsRepository $connectorsRepository;

	/** @var Models\Connectors\Properties\IPropertiesRepository */
	private Models\Connectors\Properties\IPropertiesRepository $connectorsPropertiesRepository;

	/** @var Models\Connectors\Controls\IControlsRepository */
	private Models\Connectors\Controls\IControlsRepository $connectorsControlsRepository;

	/** @var Models\Devices\IDevicesRepository */
	private Models\Devices\IDevicesRepository $devicesRepository;

	/** @var Models\Devices\Properties\IPropertiesRepository */
	private Models\Devices\Properties\IPropertiesRepository $devicesPropertiesRepository;

	/** @var Models\Devices\Controls\IControlsRepository */
	private Models\Devices\Controls\IControlsRepository $devicesControlsRepository;

	/** @var Models\Devices\Attributes\IAttributesRepository */
	private Models\Devices\Attributes\IAttributesRepository $devicesAttributesRepository;

	/** @var Models\Channels\IChannelsRepository */
	private Models\Channels\IChannelsRepository $channelsRepository;

	/** @var Models\Channels\Properties\IPropertiesRepository */
	private Models\Channels\Properties\IPropertiesRepository $channelsPropertiesRepository;

	/** @var Models\Channels\Controls\IControlsRepository */
	private Models\Channels\Controls\IControlsRepository $channelsControlsRepository;

	/** @var Flysystem\Filesystem */
	private Flysystem\Filesystem $filesystem;

	/** @var EventDispatcher\EventDispatcherInterface|null */
	private ?EventDispatcher\EventDispatcherInterface $dispatcher;

	/**
	 * @param Models\Connectors\IConnectorsRepository $connectorsRepository
	 * @param Models\Connectors\Properties\IPropertiesRepository $connectorsPropertiesRepository
	 * @param Models\Connectors\Controls\IControlsRepository $connectorsControlsRepository
	 * @param Models\Devices\IDevicesRepository $devicesRepository
	 * @param Models\Devices\Properties\IPropertiesRepository $devicesPropertiesRepository
	 * @param Models\Devices\Controls\IControlsRepository $devicesControlsRepository
	 * @param Models\Devices\Attributes\IAttributesRepository $devicesAttributesRepository
	 * @param Models\Channels\IChannelsRepository $channelsRepository
	 * @param Models\Channels\Properties\IPropertiesRepository $channelsPropertiesRepository
	 * @param Models\Channels\Controls\IControlsRepository $channelsControlsRepository
	 * @param Flysystem\Filesystem $filesystem
	 * @param EventDispatcher\EventDispatcherInterface|null $dispatcher
	 */
	public function __construct(
		Models\Connectors\IConnectorsRepository $connectorsRepository,
		Models\Connectors\Properties\IPropertiesRepository $connectorsPropertiesRepository,
		Models\Connectors\Controls\IControlsRepository $connectorsControlsRepository,
		Models\Devices\IDevicesRepository $devicesRepository,
		Models\Devices\Properties\IPropertiesRepository $devicesPropertiesRepository,
		Models\Devices\Controls\IControlsRepository $devicesControlsRepository,
		Models\Devices\Attributes\IAttributesRepository $devicesAttributesRepository,
		Models\Channels\IChannelsRepository $channelsRepository,
		Models\Channels\Properties\IPropertiesRepository $channelsPropertiesRepository,
		Models\Channels\Controls\IControlsRepository $channelsControlsRepository,
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

		$findConnectorsQuery = new Queries\FindConnectorsQuery();

		$connectors = $this->connectorsRepository->findAllBy($findConnectorsQuery);

		foreach ($connectors as $connector) {
			$devices = [];

			$findDevicesQuery = new Queries\FindDevicesQuery();
			$findDevicesQuery->forConnector($connector);

			foreach ($this->devicesRepository->findAllBy($findDevicesQuery) as $device) {
				$channels = [];

				$findChannelsQuery = new Queries\FindChannelsQuery();
				$findChannelsQuery->forDevice($device);

				foreach ($this->channelsRepository->findAllBy($findChannelsQuery) as $channel) {
					$properties = [];

					$findChannelPropertiesQuery = new Queries\FindChannelPropertiesQuery();
					$findChannelPropertiesQuery->forChannel($channel);

					foreach ($this->channelsPropertiesRepository->findAllBy($findChannelPropertiesQuery) as $property) {
						$properties[$property->getPlainId()] = $property->toArray();
					}

					$controls = [];

					$findChannelControlsQuery = new Queries\FindChannelControlsQuery();
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

				$findDevicePropertiesQuery = new Queries\FindDevicePropertiesQuery();
				$findDevicePropertiesQuery->forDevice($device);

				foreach ($this->devicesPropertiesRepository->findAllBy($findDevicePropertiesQuery) as $property) {
					$properties[$property->getPlainId()] = $property->toArray();
				}

				$attributes = [];

				$findDeviceAttributesQuery = new Queries\FindDeviceAttributesQuery();
				$findDeviceAttributesQuery->forDevice($device);

				foreach ($this->devicesAttributesRepository->findAllBy($findDeviceAttributesQuery) as $attribute) {
					$attributes[$attribute->getPlainId()] = $attribute->toArray();
				}

				$controls = [];

				$findDeviceControlsQuery = new Queries\FindDeviceControlsQuery();
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

			$findConnectorPropertiesQuery = new Queries\FindConnectorPropertiesQuery();
			$findConnectorPropertiesQuery->forConnector($connector);

			foreach ($this->connectorsPropertiesRepository->findAllBy($findConnectorPropertiesQuery) as $property) {
				$properties[$property->getPlainId()] = $property->toArray();
			}

			$controls = [];

			$findConnectorControlsQuery = new Queries\FindConnectorControlsQuery();
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

		$this->dispatcher?->dispatch(new Events\DataStorageWrittenEvent());
	}

}
