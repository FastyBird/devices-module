<?php declare(strict_types = 1);

/**
 * Connector.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Connectors
 * @since          0.60.0
 *
 * @date           31.05.22
 */

namespace FastyBird\DevicesModule\Connectors;

use FastyBird\DateTimeFactory;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Events;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Types\ConnectionStateType;
use FastyBird\Metadata\Types\ConnectorPropertyNameType;
use FastyBird\Metadata\Types\DataTypeType;
use Nette;
use Nette\Utils;
use Psr\EventDispatcher as PsrEventDispatcher;
use Psr\Log;
use SplObjectStorage;
use Throwable;

/**
 * Devices connector
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Connectors
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Connector
{

	use Nette\SmartObject;

	private const SHUTDOWN_WAITING_DELAY = 3;

	/** @var bool */
	private bool $stopped = true;

	/** @var IConnector|null */
	private ?IConnector $service = null;

	/** @var MetadataEntities\Modules\DevicesModule\IConnectorEntity|null */
	private ?MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector = null;

	/** @var SplObjectStorage<IConnector, null> */
	private SplObjectStorage $connectors;

	/** @var Models\Connectors\Properties\IPropertiesRepository */
	private Models\Connectors\Properties\IPropertiesRepository $connectorPropertiesRepository;

	/** @var Models\Connectors\Properties\IPropertiesManager */
	private Models\Connectors\Properties\IPropertiesManager $connectorPropertiesManager;

	/** @var Models\States\ConnectorPropertiesRepository */
	private Models\States\ConnectorPropertiesRepository $connectorPropertiesStateRepository;

	/** @var Models\States\ConnectorPropertiesManager */
	private Models\States\ConnectorPropertiesManager $connectorPropertiesStateManager;

	/** @var DateTimeFactory\DateTimeFactory */
	private DateTimeFactory\DateTimeFactory $dateTimeFactory;

	/** @var PsrEventDispatcher\EventDispatcherInterface|null */
	private ?PsrEventDispatcher\EventDispatcherInterface $dispatcher;

	/** @var Log\LoggerInterface */
	private Log\LoggerInterface $logger;

	public function __construct(
		Models\Connectors\Properties\IPropertiesRepository $connectorPropertiesRepository,
		Models\Connectors\Properties\IPropertiesManager $connectorPropertiesManager,
		Models\States\ConnectorPropertiesRepository $connectorPropertiesStateRepository,
		Models\States\ConnectorPropertiesManager $connectorPropertiesStateManager,
		DateTimeFactory\DateTimeFactory $dateTimeFactory,
		?PsrEventDispatcher\EventDispatcherInterface $dispatcher,
		?Log\LoggerInterface $logger = null
	) {
		$this->connectorPropertiesRepository = $connectorPropertiesRepository;
		$this->connectorPropertiesManager = $connectorPropertiesManager;
		$this->connectorPropertiesStateRepository = $connectorPropertiesStateRepository;
		$this->connectorPropertiesStateManager = $connectorPropertiesStateManager;

		$this->dateTimeFactory = $dateTimeFactory;
		$this->dispatcher = $dispatcher;

		$this->logger = $logger ?? new Log\NullLogger();

		$this->connectors = new SplObjectStorage();
	}

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector
	 *
	 * @return void
	 *
	 * @throws Exceptions\TerminateException
	 */
	public function execute(MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector): void
	{
		$this->connectors->rewind();

		if ($this->dispatcher !== null) {
			$this->dispatcher->dispatch(new Events\BeforeConnectorStartEvent($connector));
		}

		$this->connector = $connector;

		foreach ($this->connectors as $service) {
			if ($connector->getType() === $service->getType()) {
				$this->logger->debug('Starting connector...', [
					'source' => 'devices-module',
					'type'   => 'connector',
				]);

				try {
					$this->service = $service;
					$this->service->initialize($connector);

					// Start connector service
					$this->service->execute();

					$this->stopped = false;

					$this->setConnectorState(ConnectionStateType::get(ConnectionStateType::STATE_RUNNING));
				} catch (Throwable $ex) {
					throw new Exceptions\TerminateException('Connector can\'t be started');
				}

				if ($this->dispatcher !== null) {
					$this->dispatcher->dispatch(new Events\AfterConnectorStartEvent($connector));
				}

				return;
			}
		}

		throw new Exceptions\InvalidArgumentException(sprintf('Connector %s is not registered', $connector->getId()
			->toString()));
	}

	/**
	 * @return void
	 */
	public function terminate(): void
	{
		$this->stopped = true;

		$this->logger->debug('Stopping connector...', [
			'source' => 'devices-module',
			'type'   => 'connector',
		]);

		try {
			if ($this->service !== null) {
				if ($this->dispatcher !== null && $this->connector !== null) {
					$this->dispatcher->dispatch(new Events\BeforeConnectorTerminateEvent($this->connector));
				}

				$this->service->terminate();

				$now = $this->dateTimeFactory->getNow();

				$waitingForClosing = true;

				// Wait until connector is fully terminated
				while (
					$waitingForClosing
					&& (
						$this->dateTimeFactory->getNow()->getTimestamp() - $now->getTimestamp()
					) < self::SHUTDOWN_WAITING_DELAY
				) {
					if (!$this->service->hasUnfinishedTasks()) {
						$waitingForClosing = false;
					}
				}
			}

			$this->setConnectorState(ConnectionStateType::get(ConnectionStateType::STATE_STOPPED));

			if ($this->dispatcher !== null && $this->connector !== null) {
				$this->dispatcher->dispatch(new Events\AfterConnectorTerminateEvent($this->connector));
			}
		} catch (Throwable $ex) {
			$this->logger->error('Connector couldn\'t be stopped. An unexpected error occurred', [
				'source'    => 'devices-module',
				'type'      => 'connector',
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);
		}
	}

	/**
	 * @param IConnector $connector
	 *
	 * @return void
	 */
	public function registerConnector(IConnector $connector): void
	{
		if ($this->connectors->contains($connector) === false) {
			$this->connectors->attach($connector);
		}
	}

	/**
	 * @param ConnectionStateType $state
	 *
	 * @return void
	 */
	private function setConnectorState(ConnectionStateType $state): void
	{
		if ($this->connector === null) {
			return;
		}

		$findProperty = new Queries\FindConnectorPropertiesQuery();
		$findProperty->byConnectorId($this->connector->getId());
		$findProperty->byIdentifier(ConnectorPropertyNameType::NAME_STATE);

		$property = $this->connectorPropertiesRepository->findOneBy($findProperty);

		if ($property === null) {
			$property = $this->connectorPropertiesManager->create(Utils\ArrayHash::from([
				'connector'  => $this->connector,
				'entity'     => Entities\Connectors\Properties\DynamicProperty::class,
				'identifier' => ConnectorPropertyNameType::NAME_STATE,
				'data_type'  => DataTypeType::get(DataTypeType::DATA_TYPE_ENUM),
				'unit'       => null,
				'format'     => [
					ConnectionStateType::STATE_RUNNING,
					ConnectionStateType::STATE_STOPPED,
					ConnectionStateType::STATE_UNKNOWN,
					ConnectionStateType::STATE_SLEEPING,
					ConnectionStateType::STATE_ALERT,
				],
				'settable'   => false,
				'queryable'  => false,
			]));
		}

		$propertyState = $this->connectorPropertiesStateRepository->findOne($property);

		if ($propertyState === null) {
			$this->connectorPropertiesStateManager->create($property, Utils\ArrayHash::from([
				'actualValue'   => $state->getValue(),
				'expectedValue' => null,
				'pending'       => false,
				'valid'         => true,
			]));

		} else {
			$this->connectorPropertiesStateManager->update($property, $propertyState, Utils\ArrayHash::from([
				'actualValue'   => $state->getValue(),
				'expectedValue' => null,
				'pending'       => false,
				'valid'         => true,
			]));
		}
	}

}
