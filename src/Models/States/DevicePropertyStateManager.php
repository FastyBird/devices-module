<?php declare(strict_types = 1);

/**
 * DevicePropertyStateManager.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     States
 * @since          0.73.0
 *
 * @date           23.08.22
 */

namespace FastyBird\DevicesModule\Models\States;

use FastyBird\DevicesModule\Exceptions as DevicesModuleExceptions;
use FastyBird\DevicesModule\Models as DevicesModuleModels;
use FastyBird\Metadata;
use Nette;
use Nette\Utils;
use Psr\Log;

/**
 * Useful device dynamic property state helpers
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     States
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class DevicePropertyStateManager
{

	use Nette\SmartObject;

	/** @var DevicesModuleModels\States\DevicePropertiesRepository */
	private DevicesModuleModels\States\DevicePropertiesRepository $devicePropertyStateRepository;

	/** @var DevicesModuleModels\States\DevicePropertiesManager */
	private DevicesModuleModels\States\DevicePropertiesManager $devicePropertiesStatesManager;

	/** @var Log\LoggerInterface */
	private Log\LoggerInterface $logger;

	/**
	 * @param DevicePropertiesRepository $devicePropertyStateRepository
	 * @param DevicePropertiesManager $devicePropertiesStatesManager
	 * @param Log\LoggerInterface|null $logger
	 */
	public function __construct(
		DevicesModuleModels\States\DevicePropertiesRepository $devicePropertyStateRepository,
		DevicesModuleModels\States\DevicePropertiesManager $devicePropertiesStatesManager,
		?Log\LoggerInterface $logger
	) {
		$this->devicePropertyStateRepository = $devicePropertyStateRepository;
		$this->devicePropertiesStatesManager = $devicePropertiesStatesManager;

		$this->logger = $logger ?? new Log\NullLogger();
	}

	/**
	 * @param Metadata\Entities\Modules\DevicesModule\IDeviceDynamicPropertyEntity $property
	 * @param Utils\ArrayHash $data
	 *
	 * @return void
	 */
	public function setValue(
		Metadata\Entities\Modules\DevicesModule\IDeviceDynamicPropertyEntity $property,
		Utils\ArrayHash $data
	): void {
		try {
			$propertyState = $this->devicePropertyStateRepository->findOne($property);
		} catch (DevicesModuleExceptions\NotImplementedException) {
			$this->logger->warning(
				'States repository is not configured. State could not be fetched',
				[
					'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
					'type'   => 'device-property-state-manager',
				]
			);

			return;
		}

		try {
			// In case synchronization failed...
			if ($propertyState === null) {
				// ...create state in storage
				$propertyState = $this->devicePropertiesStatesManager->create(
					$property,
					$data
				);

				$this->logger->debug(
					'Device property state was created',
					[
						'source'   => Metadata\Constants::MODULE_DEVICES_SOURCE,
						'type'     => 'device-property-state-manager',
						'device'   => [
							'id' => $property->getDevice()->toString(),
						],
						'property' => [
							'id'    => $property->getId()->toString(),
							'state' => $propertyState->toArray(),
						],
					]
				);
			} else {
				$propertyState = $this->devicePropertiesStatesManager->update(
					$property,
					$propertyState,
					$data
				);

				$this->logger->debug(
					'Device property state was updated',
					[
						'source'   => Metadata\Constants::MODULE_DEVICES_SOURCE,
						'type'     => 'device-property-state-manager',
						'device'   => [
							'id' => $property->getDevice()->toString(),
						],
						'property' => [
							'id'    => $property->getId()->toString(),
							'state' => $propertyState->toArray(),
						],
					]
				);
			}
		} catch (DevicesModuleExceptions\NotImplementedException) {
			$this->logger->warning(
				'States manager is not configured. State could not be saved',
				[
					'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
					'type'   => 'device-property-state-manager',
				]
			);
		}
	}

	/**
	 * @param Metadata\Entities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|Metadata\Entities\Modules\DevicesModule\IDeviceDynamicPropertyEntity[] $property
	 * @param bool $state
	 *
	 * @return void
	 */
	public function setValidState(
		Metadata\Entities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|array $property,
		bool $state
	): void {
		if (is_array($property)) {
			foreach ($property as $item) {
				$this->setValue($item, Utils\ArrayHash::from([
					'valid' => $state,
				]));
			}
		} else {
			$this->setValue($property, Utils\ArrayHash::from([
				'valid' => $state,
			]));
		}
	}

}
