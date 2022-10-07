<?php declare(strict_types = 1);

/**
 * Reader.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 * @since          0.1.0
 *
 * @date           13.04.19
 */

namespace FastyBird\DevicesModule\DataStorage;

use FastyBird\DevicesModule;
use FastyBird\DevicesModule\Events;
use FastyBird\DevicesModule\Models;
use League\Flysystem;
use Nette;
use Nette\Utils;
use Psr\EventDispatcher;
use Ramsey\Uuid\Uuid;

/**
 * Data storage configuration reader
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     DataStorage
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Reader
{

	use Nette\SmartObject;

	/** @var Models\DataStorage\ConnectorsRepository */
	private Models\DataStorage\ConnectorsRepository $connectorsRepository;

	/** @var Models\DataStorage\ConnectorPropertiesRepository */
	private Models\DataStorage\ConnectorPropertiesRepository $connectorPropertiesRepository;

	/** @var Models\DataStorage\ConnectorControlsRepository */
	private Models\DataStorage\ConnectorControlsRepository $connectorControlsRepository;

	/** @var Models\DataStorage\DevicesRepository */
	private Models\DataStorage\DevicesRepository $devicesRepository;

	/** @var Models\DataStorage\DevicePropertiesRepository */
	private Models\DataStorage\DevicePropertiesRepository $devicePropertiesRepository;

	/** @var Models\DataStorage\DeviceControlsRepository */
	private Models\DataStorage\DeviceControlsRepository $deviceControlsRepository;

	/** @var Models\DataStorage\DeviceAttributesRepository */
	private Models\DataStorage\DeviceAttributesRepository $deviceAttributesRepository;

	/** @var Models\DataStorage\ChannelsRepository */
	private Models\DataStorage\ChannelsRepository $channelsRepository;

	/** @var Models\DataStorage\ChannelPropertiesRepository */
	private Models\DataStorage\ChannelPropertiesRepository $channelPropertiesRepository;

	/** @var Models\DataStorage\ChannelControlsRepository */
	private Models\DataStorage\ChannelControlsRepository $channelControlsRepository;

	/** @var Flysystem\Filesystem */
	private Flysystem\Filesystem $filesystem;

	/** @var EventDispatcher\EventDispatcherInterface|null */
	private ?EventDispatcher\EventDispatcherInterface $dispatcher;

	/**
	 * @param Models\DataStorage\ConnectorsRepository $connectorsRepository
	 * @param Models\DataStorage\ConnectorPropertiesRepository $connectorPropertiesRepository
	 * @param Models\DataStorage\ConnectorControlsRepository $connectorControlsRepository
	 * @param Models\DataStorage\DevicesRepository $devicesRepository
	 * @param Models\DataStorage\DevicePropertiesRepository $devicePropertiesRepository
	 * @param Models\DataStorage\DeviceControlsRepository $deviceControlsRepository
	 * @param Models\DataStorage\DeviceAttributesRepository $deviceAttributesRepository
	 * @param Models\DataStorage\ChannelsRepository $channelsRepository
	 * @param Models\DataStorage\ChannelPropertiesRepository $channelPropertiesRepository
	 * @param Models\DataStorage\ChannelControlsRepository $channelControlsRepository
	 * @param Flysystem\Filesystem $filesystem
	 * @param EventDispatcher\EventDispatcherInterface|null $dispatcher
	 */
	public function __construct(
		Models\DataStorage\ConnectorsRepository $connectorsRepository,
		Models\DataStorage\ConnectorPropertiesRepository $connectorPropertiesRepository,
		Models\DataStorage\ConnectorControlsRepository $connectorControlsRepository,
		Models\DataStorage\DevicesRepository $devicesRepository,
		Models\DataStorage\DevicePropertiesRepository $devicePropertiesRepository,
		Models\DataStorage\DeviceControlsRepository $deviceControlsRepository,
		Models\DataStorage\DeviceAttributesRepository $deviceAttributesRepository,
		Models\DataStorage\ChannelsRepository $channelsRepository,
		Models\DataStorage\ChannelPropertiesRepository $channelPropertiesRepository,
		Models\DataStorage\ChannelControlsRepository $channelControlsRepository,
		Flysystem\Filesystem $filesystem,
		?EventDispatcher\EventDispatcherInterface $dispatcher
	) {
		$this->connectorsRepository = $connectorsRepository;
		$this->connectorPropertiesRepository = $connectorPropertiesRepository;
		$this->connectorControlsRepository = $connectorControlsRepository;
		$this->devicesRepository = $devicesRepository;
		$this->devicePropertiesRepository = $devicePropertiesRepository;
		$this->deviceControlsRepository = $deviceControlsRepository;
		$this->deviceAttributesRepository = $deviceAttributesRepository;
		$this->channelsRepository = $channelsRepository;
		$this->channelPropertiesRepository = $channelPropertiesRepository;
		$this->channelControlsRepository = $channelControlsRepository;

		$this->filesystem = $filesystem;

		$this->dispatcher = $dispatcher;
	}

	/**
	 * @return void
	 *
	 * @throws Flysystem\FilesystemException
	 * @throws Utils\JsonException
	 */
	public function read(): void
	{
		try {
			$dataConfiguration = $this->filesystem->read(DevicesModule\Constants::CONFIGURATION_FILE_FILENAME);

		} catch (Flysystem\UnableToReadFile $ex) {
			return;
		}

		$dataConfiguration = Utils\Json::decode($dataConfiguration, Utils\Json::FORCE_ARRAY);

		$this->connectorsRepository->clear();
		$this->connectorPropertiesRepository->clear();
		$this->connectorControlsRepository->clear();
		$this->devicesRepository->clear();
		$this->devicePropertiesRepository->clear();
		$this->deviceControlsRepository->clear();
		$this->deviceAttributesRepository->clear();
		$this->channelsRepository->clear();
		$this->channelPropertiesRepository->clear();
		$this->channelControlsRepository->clear();

		if (!is_array($dataConfiguration)) {
			return;
		}

		foreach ($dataConfiguration as $connectorId => $connectorData) {
			// Validate connector identifier
			if (!is_string($connectorId) || !Uuid::isValid($connectorId)) {
				continue;
			}

			// Validate connector data
			if (
				!is_array($connectorData)
				|| !array_key_exists(DevicesModule\Constants::DATA_STORAGE_PROPERTIES_KEY, $connectorData)
				|| !is_array($connectorData[DevicesModule\Constants::DATA_STORAGE_PROPERTIES_KEY])
				|| !array_key_exists(DevicesModule\Constants::DATA_STORAGE_CONTROLS_KEY, $connectorData)
				|| !is_array($connectorData[DevicesModule\Constants::DATA_STORAGE_CONTROLS_KEY])
				|| !array_key_exists(DevicesModule\Constants::DATA_STORAGE_DEVICES_KEY, $connectorData)
				|| !is_array($connectorData[DevicesModule\Constants::DATA_STORAGE_DEVICES_KEY])
			) {
				continue;
			}

			foreach ($connectorData[DevicesModule\Constants::DATA_STORAGE_DEVICES_KEY] as $deviceId => $deviceData) {
				// Validate device identifier
				if (!is_string($deviceId) || !Uuid::isValid($deviceId)) {
					continue;
				}

				// Validate device data
				if (
					!is_array($deviceData)
					|| !array_key_exists(DevicesModule\Constants::DATA_STORAGE_PROPERTIES_KEY, $deviceData)
					|| !is_array($deviceData[DevicesModule\Constants::DATA_STORAGE_PROPERTIES_KEY])
					|| !array_key_exists(DevicesModule\Constants::DATA_STORAGE_CONTROLS_KEY, $deviceData)
					|| !is_array($deviceData[DevicesModule\Constants::DATA_STORAGE_CONTROLS_KEY])
					|| !array_key_exists(DevicesModule\Constants::DATA_STORAGE_ATTRIBUTES_KEY, $deviceData)
					|| !is_array($deviceData[DevicesModule\Constants::DATA_STORAGE_ATTRIBUTES_KEY])
					|| !array_key_exists(DevicesModule\Constants::DATA_STORAGE_CHANNELS_KEY, $deviceData)
					|| !is_array($deviceData[DevicesModule\Constants::DATA_STORAGE_CHANNELS_KEY])
				) {
					continue;
				}

				foreach ($deviceData[DevicesModule\Constants::DATA_STORAGE_CHANNELS_KEY] as $channelId => $channelData) {
					// Validate channel identifier
					if (!is_string($channelId) || !Uuid::isValid($channelId)) {
						continue;
					}

					// Validate channel data
					if (
						!is_array($channelData)
						|| !array_key_exists(DevicesModule\Constants::DATA_STORAGE_PROPERTIES_KEY, $channelData)
						|| !is_array($channelData[DevicesModule\Constants::DATA_STORAGE_PROPERTIES_KEY])
						|| !array_key_exists(DevicesModule\Constants::DATA_STORAGE_CONTROLS_KEY, $channelData)
						|| !is_array($channelData[DevicesModule\Constants::DATA_STORAGE_CONTROLS_KEY])
					) {
						continue;
					}

					foreach ($channelData[DevicesModule\Constants::DATA_STORAGE_PROPERTIES_KEY] as $propertyId => $propertyData) {
						// Validate channel property identifier
						if (!is_string($propertyId) || !Uuid::isValid($propertyId)) {
							continue;
						}

						// Validate channel property data
						if (!is_array($propertyData)) {
							continue;
						}

						$this->channelPropertiesRepository->append(Uuid::fromString($propertyId), $propertyData);
					}

					foreach ($channelData[DevicesModule\Constants::DATA_STORAGE_CONTROLS_KEY] as $controlId => $controlData) {
						// Validate channel control identifier
						if (!is_string($controlId) || !Uuid::isValid($controlId)) {
							continue;
						}

						// Validate channel control data
						if (!is_array($controlData)) {
							continue;
						}

						$this->channelControlsRepository->append(Uuid::fromString($controlId), $controlData);
					}

					$this->channelsRepository->append(Uuid::fromString($channelId), $channelData);
				}

				foreach ($deviceData[DevicesModule\Constants::DATA_STORAGE_PROPERTIES_KEY] as $propertyId => $propertyData) {
					// Validate device property identifier
					if (!is_string($propertyId) || !Uuid::isValid($propertyId)) {
						continue;
					}

					// Validate device property data
					if (!is_array($propertyData)) {
						continue;
					}

					$this->devicePropertiesRepository->append(Uuid::fromString($propertyId), $propertyData);
				}

				foreach ($deviceData[DevicesModule\Constants::DATA_STORAGE_CONTROLS_KEY] as $controlId => $controlData) {
					// Validate device control identifier
					if (!is_string($controlId) || !Uuid::isValid($controlId)) {
						continue;
					}

					// Validate device control data
					if (!is_array($controlData)) {
						continue;
					}

					$this->deviceControlsRepository->append(Uuid::fromString($controlId), $controlData);
				}

				foreach ($deviceData[DevicesModule\Constants::DATA_STORAGE_ATTRIBUTES_KEY] as $attributeId => $attributeData) {
					// Validate device attribute identifier
					if (!is_string($attributeId) || !Uuid::isValid($attributeId)) {
						continue;
					}

					// Validate device attribute data
					if (!is_array($attributeData)) {
						continue;
					}

					$this->deviceAttributesRepository->append(Uuid::fromString($attributeId), $attributeData);
				}

				$this->devicesRepository->append(Uuid::fromString($deviceId), $deviceData);
			}

			foreach ($connectorData[DevicesModule\Constants::DATA_STORAGE_PROPERTIES_KEY] as $propertyId => $propertyData) {
				// Validate connector property identifier
				if (!is_string($propertyId) || !Uuid::isValid($propertyId)) {
					continue;
				}

				// Validate connector property data
				if (!is_array($propertyData)) {
					continue;
				}

				$this->connectorPropertiesRepository->append(Uuid::fromString($propertyId), $propertyData);
			}

			foreach ($connectorData[DevicesModule\Constants::DATA_STORAGE_CONTROLS_KEY] as $controlId => $controlData) {
				// Validate connector control identifier
				if (!is_string($controlId) || !Uuid::isValid($controlId)) {
					continue;
				}

				// Validate connector control data
				if (!is_array($controlData)) {
					continue;
				}

				$this->connectorControlsRepository->append(Uuid::fromString($controlId), $controlData);
			}

			$this->connectorsRepository->append(Uuid::fromString($connectorId), $connectorData);
		}

		$this->dispatcher?->dispatch(new Events\DataStorageReaded());
	}

}
