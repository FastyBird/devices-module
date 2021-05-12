<?php declare(strict_types = 1);

/**
 * DevicePropertyMessageConsumer.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Consumers
 * @since          0.1.0
 *
 * @date           19.03.20
 */

namespace FastyBird\DevicesModule\Consumers;

use FastyBird\ApplicationExchange\Consumer as ApplicationExchangeConsumer;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Helpers;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use FastyBird\ModulesMetadata;
use Nette;
use Nette\Utils;
use Psr\Log;

/**
 * Device message consumer
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Consumers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class DevicePropertyMessageConsumer implements ApplicationExchangeConsumer\IConsumer
{

	use Nette\SmartObject;
	use TPropertyMessageConsumer;

	private const ROUTING_KEYS = [
		ModulesMetadata\Constants::MESSAGE_BUS_DEVICES_PROPERTIES_DATA_ROUTING_KEY,
	];

	/** @var Helpers\PropertyHelper */
	protected Helpers\PropertyHelper $propertyHelper;

	/** @var Models\Devices\IDeviceRepository */
	private Models\Devices\IDeviceRepository $deviceRepository;

	/** @var Models\Devices\Properties\IPropertyRepository */
	private Models\Devices\Properties\IPropertyRepository $propertyRepository;

	/** @var Models\States\IPropertiesManager|null */
	private ?Models\States\IPropertiesManager $propertiesStatesManager;

	/** @var Models\States\IPropertyRepository|null */
	private ?Models\States\IPropertyRepository $propertyStateRepository;

	/** @var Log\LoggerInterface */
	protected Log\LoggerInterface $logger;

	public function __construct(
		Models\Devices\IDeviceRepository $deviceRepository,
		Models\Devices\Properties\IPropertyRepository $propertyRepository,
		Helpers\PropertyHelper $propertyHelper,
		?Models\States\IPropertiesManager $propertiesStatesManager = null,
		?Models\States\IPropertyRepository $propertyStateRepository = null,
		?Log\LoggerInterface $logger = null
	) {
		$this->deviceRepository = $deviceRepository;
		$this->propertyRepository = $propertyRepository;
		$this->propertyHelper = $propertyHelper;

		$this->propertiesStatesManager = $propertiesStatesManager;
		$this->propertyStateRepository = $propertyStateRepository;

		$this->logger = $logger ?? new Log\NullLogger();
	}

	/**
	 * {@inheritDoc}
	 */
	public function consume(
		string $origin,
		string $routingKey,
		Utils\ArrayHash $message
	): void {
		if (!in_array($routingKey, self::ROUTING_KEYS, true)) {
			return;
		}

		// No state management plugin is installed
		if ($this->propertiesStatesManager === null || $this->propertyStateRepository === null) {
			return;
		}

		$findQuery = new Queries\FindDevicesQuery();
		$findQuery->byKey($message->offsetGet('device'));

		$device = $this->deviceRepository->findOneBy($findQuery);

		if ($device === null) {
			$this->logger->error(sprintf('[FB:DEVICES_MODULE:CONSUMER] Device "%s" is not registered', $message->offsetGet('device')));

			return;
		}

		$findQuery = new Queries\FindDevicePropertiesQuery();
		$findQuery->forDevice($device);
		$findQuery->byKey($message->offsetGet('property'));

		$property = $this->propertyRepository->findOneBy($findQuery);

		if ($property === null) {
			$this->logger->error(sprintf('[FB:DEVICES_MODULE:CONSUMER] Property "%s" is not registered', $message->offsetGet('property')));

			return;
		}

		try {
			switch ($routingKey) {
				case ModulesMetadata\Constants::MESSAGE_BUS_DEVICES_PROPERTIES_DATA_ROUTING_KEY:
					// Property have to be configured & have to be settable
					if ($property->isSettable()) {
						$state = $this->propertyStateRepository->findOne($property->getId());

						// In case synchronization failed...
						if ($state === null) {
							// ...create state in storage
							$state = $this->propertiesStatesManager->create(
								$property->getId(),
								Utils\ArrayHash::from($property->toArray())
							);
						}

						$toUpdate = $this->handlePropertyState($property, $state, $message);

						$this->propertiesStatesManager->updateState(
							$state,
							Utils\ArrayHash::from($toUpdate)
						);
					}
					break;

				default:
					throw new Exceptions\InvalidStateException('Unknown routing key');
			}

		} catch (Exceptions\InvalidStateException $ex) {
			return;
		}

		$this->logger->info('[FB:DEVICES_MODULE:CONSUMER] Successfully consumed entity message', [
			'message' => [
				'routingKey' => $routingKey,
				'origin'     => $origin,
			],
		]);
	}

}
