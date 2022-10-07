<?php declare(strict_types = 1);

/**
 * ConnectorConnectionStateManager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     DynamicProperties
 * @since          0.73.0
 *
 * @date           19.07.22
 */

namespace FastyBird\DevicesModule\Models\States;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use FastyBird\Metadata;
use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Types as MetadataTypes;
use Nette;
use Nette\Utils;
use Psr\Log;

/**
 * Device connection states manager
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     DynamicProperties
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class DeviceConnectionStateManager
{

	use Nette\SmartObject;

	/** @var Models\Devices\DevicesRepository */
	private Models\Devices\DevicesRepository $devicesRepository;

	/** @var Models\Devices\Properties\PropertiesManager */
	private Models\Devices\Properties\PropertiesManager $manager;

	/** @var Models\DataStorage\DevicePropertiesRepository */
	private Models\DataStorage\DevicePropertiesRepository $repository;

	/** @var Models\States\DevicePropertiesRepository */
	private Models\States\DevicePropertiesRepository $statesRepository;

	/** @var Models\States\DevicePropertiesManager */
	private Models\States\DevicePropertiesManager $statesManager;

	/** @var Log\LoggerInterface */
	private Log\LoggerInterface $logger;

	/**
	 * @param Models\Devices\DevicesRepository $devicesRepository
	 * @param Models\DataStorage\DevicePropertiesRepository $repository
	 * @param Models\Devices\Properties\PropertiesManager $manager
	 * @param DevicePropertiesRepository $statesRepository
	 * @param DevicePropertiesManager $statesManager
	 * @param Log\LoggerInterface|null $logger
	 */
	public function __construct(
		Models\Devices\DevicesRepository $devicesRepository,
		Models\DataStorage\DevicePropertiesRepository $repository,
		Models\Devices\Properties\PropertiesManager $manager,
		Models\States\DevicePropertiesRepository $statesRepository,
		Models\States\DevicePropertiesManager $statesManager,
		?Log\LoggerInterface $logger = null
	) {
		$this->devicesRepository = $devicesRepository;
		$this->repository = $repository;
		$this->manager = $manager;
		$this->statesRepository = $statesRepository;
		$this->statesManager = $statesManager;

		$this->logger = $logger ?? new Log\NullLogger();
	}

	/**
	 * @param Entities\Devices\Device|MetadataEntities\Modules\DevicesModule\IDeviceEntity $device
	 * @param MetadataTypes\ConnectionStateType $state
	 *
	 * @return bool
	 */
	public function setState(
		Entities\Devices\Device|MetadataEntities\Modules\DevicesModule\IDeviceEntity $device,
		MetadataTypes\ConnectionStateType $state
	): bool {
		$stateProperty = $this->repository->findByIdentifier(
			$device->getId(),
			MetadataTypes\DevicePropertyIdentifierType::IDENTIFIER_STATE
		);

		if ($stateProperty === null) {
			if (!$device instanceof Entities\Devices\Device) {
				$findDeviceQuery = new Queries\FindDevices();
				$findDeviceQuery->byId($device->getId());

				$device = $this->devicesRepository->findOneBy($findDeviceQuery);

				if ($device === null) {
					throw new Exceptions\InvalidState('Connector could not be loaded');
				}
			}

			$stateProperty = $this->manager->create(Utils\ArrayHash::from([
				'device'     => $device,
				'entity'     => Entities\Devices\Properties\Dynamic::class,
				'identifier' => MetadataTypes\ConnectorPropertyIdentifierType::IDENTIFIER_STATE,
				'dataType'   => MetadataTypes\DataTypeType::get(MetadataTypes\DataTypeType::DATA_TYPE_ENUM),
				'unit'       => null,
				'format'     => [
					MetadataTypes\ConnectionStateType::STATE_CONNECTED,
					MetadataTypes\ConnectionStateType::STATE_DISCONNECTED,
					MetadataTypes\ConnectionStateType::STATE_INIT,
					MetadataTypes\ConnectionStateType::STATE_READY,
					MetadataTypes\ConnectionStateType::STATE_RUNNING,
					MetadataTypes\ConnectionStateType::STATE_SLEEPING,
					MetadataTypes\ConnectionStateType::STATE_STOPPED,
					MetadataTypes\ConnectionStateType::STATE_LOST,
					MetadataTypes\ConnectionStateType::STATE_ALERT,
					MetadataTypes\ConnectionStateType::STATE_UNKNOWN,
				],
				'settable'   => false,
				'queryable'  => false,
			]));
		}

		if (
			$stateProperty instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
			|| $stateProperty instanceof Entities\Devices\Properties\Dynamic
		) {
			try {
				$statePropertyState = $this->statesRepository->findOne($stateProperty);

			} catch (Exceptions\NotImplemented $ex) {
				$this->logger->warning(
					'DynamicProperties repository is not configured. State could not be fetched',
					[
						'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
						'type'   => 'device-connection-state-manager',
					]
				);

				MetadataTypes\ConnectionStateType::get(MetadataTypes\ConnectionStateType::STATE_UNKNOWN);

				return false;
			}

			if ($statePropertyState === null) {
				try {
					$this->statesManager->create($stateProperty, Utils\ArrayHash::from([
						'actualValue'   => $state->getValue(),
						'expectedValue' => null,
						'pending'       => false,
						'valid'         => true,
					]));

					return true;

				} catch (Exceptions\NotImplemented $ex) {
					$this->logger->warning(
						'DynamicProperties manager is not configured. State could not be saved',
						[
							'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
							'type'   => 'device-connection-state-manager',
						]
					);
				}
			} else {
				try {
					$this->statesManager->update(
						$stateProperty,
						$statePropertyState,
						Utils\ArrayHash::from([
							'actualValue'   => $state->getValue(),
							'expectedValue' => null,
							'pending'       => false,
							'valid'         => true,
						])
					);

					return true;

				} catch (Exceptions\NotImplemented $ex) {
					$this->logger->warning(
						'DynamicProperties manager is not configured. State could not be saved',
						[
							'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
							'type'   => 'device-connection-state-manager',
						]
					);
				}
			}
		}

		return false;
	}

	/**
	 * @param Entities\Devices\Device|MetadataEntities\Modules\DevicesModule\IDeviceEntity $device
	 *
	 * @return MetadataTypes\ConnectionStateType
	 */
	public function getState(
		Entities\Devices\Device|MetadataEntities\Modules\DevicesModule\IDeviceEntity $device
	): MetadataTypes\ConnectionStateType {
		$stateProperty = $this->repository->findByIdentifier(
			$device->getId(),
			MetadataTypes\DevicePropertyIdentifierType::IDENTIFIER_STATE
		);

		if (
			$stateProperty instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
			&& $stateProperty->getActualValue() !== null
			&& MetadataTypes\ConnectionStateType::isValidValue($stateProperty->getActualValue())
		) {
			return MetadataTypes\ConnectionStateType::get($stateProperty->getActualValue());
		}

		return MetadataTypes\ConnectionStateType::get(MetadataTypes\ConnectionStateType::STATE_UNKNOWN);
	}

}
