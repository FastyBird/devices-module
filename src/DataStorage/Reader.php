<?php declare(strict_types = 1);

/**
 * BaseV1Controller.php
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
use FastyBird\DevicesModule\Models;
use FastyBird\Metadata\Entities as MetadataEntities;
use League\Flysystem;
use Nette\Utils;
use Ramsey\Uuid\Uuid;
use Throwable;

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

	/** @var Models\DataStorage\IConnectorsRepository */
	private Models\DataStorage\IConnectorsRepository $connectorsRepository;

	/** @var Models\DataStorage\IConnectorPropertiesRepository */
	private Models\DataStorage\IConnectorPropertiesRepository $connectorPropertiesRepository;

	/** @var Models\DataStorage\IConnectorControlsRepository */
	private Models\DataStorage\IConnectorControlsRepository $connectorControlsRepository;

	/** @var Models\DataStorage\IDevicesRepository */
	private Models\DataStorage\IDevicesRepository $devicesRepository;

	/** @var Models\DataStorage\IDevicePropertiesRepository */
	private Models\DataStorage\IDevicePropertiesRepository $devicePropertiesRepository;

	/** @var Models\DataStorage\IDeviceControlsRepository */
	private Models\DataStorage\IDeviceControlsRepository $deviceControlsRepository;

	/** @var Models\DataStorage\IDeviceAttributesRepository */
	private Models\DataStorage\IDeviceAttributesRepository $deviceAttributesRepository;

	/** @var Models\DataStorage\IChannelsRepository */
	private Models\DataStorage\IChannelsRepository $channelsRepository;

	/** @var Models\DataStorage\IChannelPropertiesRepository */
	private Models\DataStorage\IChannelPropertiesRepository $channelPropertiesRepository;

	/** @var Models\DataStorage\IChannelControlsRepository */
	private Models\DataStorage\IChannelControlsRepository $channelControlsRepository;

	/** @var MetadataEntities\Modules\DevicesModule\ConnectorEntityFactory */
	private MetadataEntities\Modules\DevicesModule\ConnectorEntityFactory $connectorEntityFactory;

	/** @var MetadataEntities\Modules\DevicesModule\ConnectorPropertyEntityFactory */
	private MetadataEntities\Modules\DevicesModule\ConnectorPropertyEntityFactory $connectorPropertyEntityFactory;

	/** @var MetadataEntities\Modules\DevicesModule\ConnectorControlEntityFactory */
	private MetadataEntities\Modules\DevicesModule\ConnectorControlEntityFactory $connectorControlEntityFactory;

	/** @var MetadataEntities\Modules\DevicesModule\DeviceEntityFactory */
	private MetadataEntities\Modules\DevicesModule\DeviceEntityFactory $deviceEntityFactory;

	/** @var MetadataEntities\Modules\DevicesModule\DevicePropertyEntityFactory */
	private MetadataEntities\Modules\DevicesModule\DevicePropertyEntityFactory $devicePropertyEntityFactory;

	/** @var MetadataEntities\Modules\DevicesModule\DeviceControlEntityFactory */
	private MetadataEntities\Modules\DevicesModule\DeviceControlEntityFactory $deviceControlEntityFactory;

	/** @var MetadataEntities\Modules\DevicesModule\DeviceAttributeEntityFactory */
	private MetadataEntities\Modules\DevicesModule\DeviceAttributeEntityFactory $deviceAttributeEntityFactory;

	/** @var MetadataEntities\Modules\DevicesModule\ChannelEntityFactory */
	private MetadataEntities\Modules\DevicesModule\ChannelEntityFactory $channelEntityFactory;

	/** @var MetadataEntities\Modules\DevicesModule\ChannelPropertyEntityFactory */
	private MetadataEntities\Modules\DevicesModule\ChannelPropertyEntityFactory $channelPropertyEntityFactory;

	/** @var MetadataEntities\Modules\DevicesModule\ChannelControlEntityFactory */
	private MetadataEntities\Modules\DevicesModule\ChannelControlEntityFactory $channelControlEntityFactory;

	/** @var Flysystem\Filesystem */
	private Flysystem\Filesystem $filesystem;

	public function __construct(
		Models\DataStorage\IConnectorsRepository $connectorsRepository,
		Models\DataStorage\IConnectorPropertiesRepository $connectorPropertiesRepository,
		Models\DataStorage\IConnectorControlsRepository $connectorControlsRepository,
		Models\DataStorage\IDevicesRepository $devicesRepository,
		Models\DataStorage\IDevicePropertiesRepository $devicePropertiesRepository,
		Models\DataStorage\IDeviceControlsRepository $deviceControlsRepository,
		Models\DataStorage\IDeviceAttributesRepository $deviceAttributesRepository,
		Models\DataStorage\IChannelsRepository $channelsRepository,
		Models\DataStorage\IChannelPropertiesRepository $channelPropertiesRepository,
		Models\DataStorage\IChannelControlsRepository $channelControlsRepository,
		MetadataEntities\Modules\DevicesModule\ConnectorEntityFactory $connectorEntityFactory,
		MetadataEntities\Modules\DevicesModule\ConnectorPropertyEntityFactory $connectorPropertyEntityFactory,
		MetadataEntities\Modules\DevicesModule\ConnectorControlEntityFactory $connectorControlEntityFactory,
		MetadataEntities\Modules\DevicesModule\DeviceEntityFactory $deviceEntityFactory,
		MetadataEntities\Modules\DevicesModule\DevicePropertyEntityFactory $devicePropertyEntityFactory,
		MetadataEntities\Modules\DevicesModule\DeviceControlEntityFactory $deviceControlEntityFactory,
		MetadataEntities\Modules\DevicesModule\DeviceAttributeEntityFactory $deviceAttributeEntityFactory,
		MetadataEntities\Modules\DevicesModule\ChannelEntityFactory $channelEntityFactory,
		MetadataEntities\Modules\DevicesModule\ChannelPropertyEntityFactory $channelPropertyEntityFactory,
		MetadataEntities\Modules\DevicesModule\ChannelControlEntityFactory $channelControlEntityFactory,
		Flysystem\Filesystem $filesystem
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

		$this->connectorEntityFactory = $connectorEntityFactory;
		$this->connectorPropertyEntityFactory = $connectorPropertyEntityFactory;
		$this->connectorControlEntityFactory = $connectorControlEntityFactory;
		$this->deviceEntityFactory = $deviceEntityFactory;
		$this->devicePropertyEntityFactory = $devicePropertyEntityFactory;
		$this->deviceControlEntityFactory = $deviceControlEntityFactory;
		$this->deviceAttributeEntityFactory = $deviceAttributeEntityFactory;
		$this->channelEntityFactory = $channelEntityFactory;
		$this->channelPropertyEntityFactory = $channelPropertyEntityFactory;
		$this->channelControlEntityFactory = $channelControlEntityFactory;

		$this->filesystem = $filesystem;
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

		$this->connectorsRepository->reset();
		$this->connectorPropertiesRepository->reset();
		$this->connectorControlsRepository->reset();
		$this->devicesRepository->reset();
		$this->devicePropertiesRepository->reset();
		$this->deviceControlsRepository->reset();
		$this->deviceAttributesRepository->reset();
		$this->channelsRepository->reset();
		$this->channelPropertiesRepository->reset();
		$this->channelControlsRepository->reset();

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

						try {
							$entity = $this->channelPropertyEntityFactory->create(Utils\Json::encode($propertyData));

							if (
								$entity instanceof MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity
								|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity
								|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
							) {
								$this->channelPropertiesRepository->append($entity);
							}
						} catch (Throwable $ex) {
							continue;
						}
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

						try {
							$this->channelControlsRepository->append($this->channelControlEntityFactory->create(Utils\Json::encode($controlData)));
						} catch (Throwable $ex) {
							continue;
						}
					}

					try {
						$this->channelsRepository->append($this->channelEntityFactory->create(Utils\Json::encode($channelData)));
					} catch (Throwable $ex) {
						continue;
					}
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

					try {
						$entity = $this->devicePropertyEntityFactory->create(Utils\Json::encode($propertyData));

						if (
							$entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity
							|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
							|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
						) {
							$this->devicePropertiesRepository->append($entity);
						}
					} catch (Throwable $ex) {
						var_dump($ex->getMessage());
						continue;
					}
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

					try {
						$this->deviceControlsRepository->append($this->deviceControlEntityFactory->create(Utils\Json::encode($controlData)));
					} catch (Throwable $ex) {
						continue;
					}
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

					try {
						$this->deviceAttributesRepository->append($this->deviceAttributeEntityFactory->create(Utils\Json::encode($attributeData)));
					} catch (Throwable $ex) {
						continue;
					}
				}

				try {
					$this->devicesRepository->append($this->deviceEntityFactory->create(Utils\Json::encode($deviceData)));
				} catch (Throwable $ex) {
					continue;
				}
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

				try {
					$entity = $this->connectorPropertyEntityFactory->create(Utils\Json::encode($propertyData));

					if (
						$entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity
						|| $entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity
						|| $entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity
					) {
						$this->connectorPropertiesRepository->append($entity);
					}
				} catch (Throwable $ex) {
					continue;
				}
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

				try {
					$this->connectorControlsRepository->append($this->connectorControlEntityFactory->create(Utils\Json::encode($controlData)));
				} catch (Throwable $ex) {
					continue;
				}
			}

			try {
				$this->connectorsRepository->append($this->connectorEntityFactory->create(Utils\Json::encode($connectorData)));
			} catch (Throwable $ex) {
				continue;
			}
		}
	}

}
