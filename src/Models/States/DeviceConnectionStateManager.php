<?php declare(strict_types = 1);

/**
 * ConnectorConnectionStateManager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     States
 * @since          0.73.0
 *
 * @date           19.07.22
 */

namespace FastyBird\DevicesModule\Models\States;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Types as MetadataTypes;
use Nette;
use Nette\Utils;
use Psr\Log;

/**
 * Device connection states manager
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     States
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class DeviceConnectionStateManager
{

	use Nette\SmartObject;

	/** @var Models\DataStorage\IDevicePropertiesRepository */
	private Models\DataStorage\IDevicePropertiesRepository $repository;

	/** @var Models\Devices\Properties\IPropertiesManager */
	private Models\Devices\Properties\IPropertiesManager $manager;

	/** @var Models\States\DevicePropertiesRepository */
	private Models\States\DevicePropertiesRepository $statesRepository;

	/** @var Models\States\DevicePropertiesManager */
	private Models\States\DevicePropertiesManager $statesManager;

	/** @var Log\LoggerInterface */
	private Log\LoggerInterface $logger;

	/**
	 * @param Models\DataStorage\IDevicePropertiesRepository $repository
	 * @param Models\Devices\Properties\IPropertiesManager $manager
	 * @param Models\States\DevicePropertiesRepository $statesRepository
	 * @param Models\States\DevicePropertiesManager $statesManager
	 * @param Log\LoggerInterface|null $logger
	 */
	public function __construct(
		Models\DataStorage\IDevicePropertiesRepository $repository,
		Models\Devices\Properties\IPropertiesManager $manager,
		Models\States\DevicePropertiesRepository $statesRepository,
		Models\States\DevicePropertiesManager $statesManager,
		?Log\LoggerInterface $logger = null
	) {
		$this->repository = $repository;
		$this->manager = $manager;
		$this->statesRepository = $statesRepository;
		$this->statesManager = $statesManager;

		$this->logger = $logger ?? new Log\NullLogger();
	}

	/**
	 * @param Entities\Devices\IDevice|MetadataEntities\Modules\DevicesModule\IDeviceEntity $device
	 * @param MetadataTypes\ConnectionStateType $state
	 *
	 * @return bool
	 */
	public function setState(
		Entities\Devices\IDevice|MetadataEntities\Modules\DevicesModule\IDeviceEntity $device,
		MetadataTypes\ConnectionStateType $state
	): bool {
		$stateProperty = $this->repository->findByIdentifier(
			$device->getId(),
			MetadataTypes\DevicePropertyNameType::NAME_STATE
		);

		if ($stateProperty === null) {
			$stateProperty = $this->manager->create(Utils\ArrayHash::from([
				'connector'  => $device->getId(),
				'entity'     => Entities\Connectors\Properties\DynamicProperty::class,
				'identifier' => MetadataTypes\ConnectorPropertyNameType::NAME_STATE,
				'dataType'   => MetadataTypes\DataTypeType::get(MetadataTypes\DataTypeType::DATA_TYPE_ENUM),
				'unit'       => null,
				'format'     => [
					MetadataTypes\ConnectionStateType::STATE_RUNNING,
					MetadataTypes\ConnectionStateType::STATE_STOPPED,
					MetadataTypes\ConnectionStateType::STATE_UNKNOWN,
					MetadataTypes\ConnectionStateType::STATE_SLEEPING,
					MetadataTypes\ConnectionStateType::STATE_ALERT,
				],
				'settable'   => false,
				'queryable'  => false,
			]));
		}

		if (
			$stateProperty instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
			|| $stateProperty instanceof Entities\Devices\Properties\IDynamicProperty
		) {
			try {
				$statePropertyState = $this->statesRepository->findOne($stateProperty);

			} catch (Exceptions\NotImplementedException $ex) {
				$this->logger->warning(
					'States repository is not configured. State could not be fetched',
					[
						'source' => 'devices-module',
						'type'   => 'connection-state-manager',
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

				} catch (Exceptions\NotImplementedException $ex) {
					$this->logger->warning(
						'States manager is not configured. State could not be saved',
						[
							'source' => 'devices-module',
							'type'   => 'connection-state-manager',
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

				} catch (Exceptions\NotImplementedException $ex) {
					$this->logger->warning(
						'States manager is not configured. State could not be saved',
						[
							'source' => 'devices-module',
							'type'   => 'connection-state-manager',
						]
					);
				}
			}
		}

		return false;
	}

	/**
	 * @param Entities\Devices\IDevice|MetadataEntities\Modules\DevicesModule\IDeviceEntity $device
	 *
	 * @return MetadataTypes\ConnectionStateType
	 */
	public function getState(
		Entities\Devices\IDevice|MetadataEntities\Modules\DevicesModule\IDeviceEntity $device
	): MetadataTypes\ConnectionStateType {
		$stateProperty = $this->repository->findByIdentifier(
			$device->getId(),
			MetadataTypes\DevicePropertyNameType::NAME_STATE
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
