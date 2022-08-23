<?php declare(strict_types = 1);

/**
 * ServiceCommand.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Commands
 * @since          0.60.0
 *
 * @date           31.05.22
 */

namespace FastyBird\DevicesModule\Commands;

use FastyBird\DateTimeFactory;
use FastyBird\DevicesModule\Connectors;
use FastyBird\DevicesModule\Consumers;
use FastyBird\DevicesModule\Events;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\Exchange\Consumer as ExchangeConsumer;
use FastyBird\Metadata;
use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Types as MetadataTypes;
use Psr\EventDispatcher as PsrEventDispatcher;
use Psr\Log;
use Ramsey\Uuid;
use React\EventLoop;
use Symfony\Component\Console;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output;
use Symfony\Component\Console\Style;
use Throwable;

/**
 * Module connector command
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Commands
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class ServiceCommand extends Console\Command\Command
{

	private const SHUTDOWN_WAITING_DELAY = 3;

	/** @var Connectors\ConnectorFactory */
	private Connectors\ConnectorFactory $factory;

	/** @var Models\DataStorage\IConnectorsRepository */
	private Models\DataStorage\IConnectorsRepository $connectorsRepository;

	/** @var Models\DataStorage\IDevicesRepository */
	private Models\DataStorage\IDevicesRepository $devicesRepository;

	/** @var Models\DataStorage\IDevicePropertiesRepository */
	private Models\DataStorage\IDevicePropertiesRepository $devicesPropertiesRepository;

	/** @var Models\DataStorage\IChannelsRepository */
	private Models\DataStorage\IChannelsRepository $channelsRepository;

	/** @var Models\DataStorage\IChannelPropertiesRepository */
	private Models\DataStorage\IChannelPropertiesRepository $channelsPropertiesRepository;

	/** @var Models\States\DeviceConnectionStateManager */
	private Models\States\DeviceConnectionStateManager $deviceConnectionStateManager;

	/** @var Models\States\DevicePropertyStateManager */
	private Models\States\DevicePropertyStateManager $devicePropertyStateManager;

	/** @var Models\States\ChannelPropertyStateManager */
	private Models\States\ChannelPropertyStateManager $channelPropertyStateManager;

	/** @var Consumers\ConnectorConsumer */
	private Consumers\ConnectorConsumer $connectorConsumer;

	/** @var Consumers\DataExchangeConsumer */
	private Consumers\DataExchangeConsumer $dataExchangeConsumer;

	/** @var ExchangeConsumer\Consumer */
	private ExchangeConsumer\Consumer $consumer;

	/** @var Models\States\ConnectorConnectionStateManager */
	private Models\States\ConnectorConnectionStateManager $connectorConnectionStateManager;

	/** @var DateTimeFactory\DateTimeFactory */
	private DateTimeFactory\DateTimeFactory $dateTimeFactory;

	/** @var Log\LoggerInterface */
	private Log\LoggerInterface $logger;

	/** @var PsrEventDispatcher\EventDispatcherInterface|null */
	private ?PsrEventDispatcher\EventDispatcherInterface $dispatcher;

	/** @var EventLoop\LoopInterface */
	private EventLoop\LoopInterface $eventLoop;

	/**
	 * @param Connectors\ConnectorFactory $factory
	 * @param Models\DataStorage\IConnectorsRepository $connectorsRepository
	 * @param Models\DataStorage\IDevicesRepository $devicesRepository
	 * @param Models\DataStorage\IDevicePropertiesRepository $devicesPropertiesRepository
	 * @param Models\DataStorage\IChannelsRepository $channelsRepository
	 * @param Models\DataStorage\IChannelPropertiesRepository $channelsPropertiesRepository
	 * @param Models\States\ConnectorConnectionStateManager $connectorConnectionStateManager
	 * @param Models\States\DeviceConnectionStateManager $deviceConnectionStateManager
	 * @param Models\States\DevicePropertyStateManager $devicePropertyStateManager
	 * @param Models\States\ChannelPropertyStateManager $channelPropertyStateManager
	 * @param Consumers\ConnectorConsumer $connectorConsumer
	 * @param Consumers\DataExchangeConsumer $dataExchangeConsumer
	 * @param ExchangeConsumer\Consumer $consumer
	 * @param DateTimeFactory\DateTimeFactory $dateTimeFactory
	 * @param EventLoop\LoopInterface $eventLoop
	 * @param PsrEventDispatcher\EventDispatcherInterface|null $dispatcher
	 * @param Log\LoggerInterface|null $logger
	 * @param string|null $name
	 */
	public function __construct(
		Connectors\ConnectorFactory $factory,
		Models\DataStorage\IConnectorsRepository $connectorsRepository,
		Models\DataStorage\IDevicesRepository $devicesRepository,
		Models\DataStorage\IDevicePropertiesRepository $devicesPropertiesRepository,
		Models\DataStorage\IChannelsRepository $channelsRepository,
		Models\DataStorage\IChannelPropertiesRepository $channelsPropertiesRepository,
		Models\States\ConnectorConnectionStateManager $connectorConnectionStateManager,
		Models\States\DeviceConnectionStateManager $deviceConnectionStateManager,
		Models\States\DevicePropertyStateManager $devicePropertyStateManager,
		Models\States\ChannelPropertyStateManager $channelPropertyStateManager,
		Consumers\ConnectorConsumer $connectorConsumer,
		Consumers\DataExchangeConsumer $dataExchangeConsumer,
		ExchangeConsumer\Consumer $consumer,
		DateTimeFactory\DateTimeFactory $dateTimeFactory,
		EventLoop\LoopInterface $eventLoop,
		?PsrEventDispatcher\EventDispatcherInterface $dispatcher,
		?Log\LoggerInterface $logger = null,
		?string $name = null
	) {
		$this->factory = $factory;
		$this->connectorsRepository = $connectorsRepository;
		$this->devicesRepository = $devicesRepository;
		$this->devicesPropertiesRepository = $devicesPropertiesRepository;
		$this->channelsRepository = $channelsRepository;
		$this->channelsPropertiesRepository = $channelsPropertiesRepository;

		$this->connectorConnectionStateManager = $connectorConnectionStateManager;
		$this->deviceConnectionStateManager = $deviceConnectionStateManager;

		$this->devicePropertyStateManager = $devicePropertyStateManager;
		$this->channelPropertyStateManager = $channelPropertyStateManager;

		$this->connectorConsumer = $connectorConsumer;
		$this->dataExchangeConsumer = $dataExchangeConsumer;
		$this->consumer = $consumer;

		$this->dateTimeFactory = $dateTimeFactory;

		$this->eventLoop = $eventLoop;
		$this->dispatcher = $dispatcher;

		$this->logger = $logger ?? new Log\NullLogger();

		parent::__construct($name);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function configure(): void
	{
		$this
			->setName('fb:devices-module:service')
			->setDescription('Devices module service')
			->setDefinition(
				new Input\InputDefinition([
					new Input\InputOption('connector', 'c', Input\InputOption::VALUE_OPTIONAL, 'Run devices module connector', true),
					new Input\InputOption('exchange', 'e', Input\InputOption::VALUE_NONE, 'Run devices module data exchange'),
					new Input\InputOption('no-confirm', null, Input\InputOption::VALUE_NONE, 'Do not ask for any confirmation'),
					new Input\InputOption('quiet', 'q', Input\InputOption::VALUE_NONE, 'Do not output any message'),
				])
			);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(Input\InputInterface $input, Output\OutputInterface $output): int
	{
		$io = new Style\SymfonyStyle($input, $output);

		if (!$input->getOption('quiet')) {
			$io->title('FB devices module - service');

			$io->note('This action will run module services.');
		}

		if (!$input->getOption('no-confirm')) {
			$question = new Console\Question\ConfirmationQuestion(
				'Would you like to continue?',
				false
			);

			$continue = $io->askQuestion($question);

			if (!$continue) {
				return Console\Command\Command::SUCCESS;
			}
		}

		try {
			if ($input->getOption('exchange')) {
				$this->consumer->register($this->dataExchangeConsumer);
			}

			if ($input->getOption('connector')) {
				try {
					$this->executeConnector($io, $input);

				} catch (Exceptions\TerminateException $ex) {
					$this->logger->debug('Stopping connector', [
						'source'    => Metadata\Constants::MODULE_DEVICES_SOURCE,
						'type'      => 'service-cmd',
						'exception' => [
							'message' => $ex->getMessage(),
							'code'    => $ex->getCode(),
						],
					]);

					$this->eventLoop->stop();
				}
			}

			return Console\Command\Command::SUCCESS;

		} catch (Throwable $ex) {
			// Log caught exception
			$this->logger->error('An unhandled error occurred', [
				'source'    => Metadata\Constants::MODULE_DEVICES_SOURCE,
				'type'      => 'service-cmd',
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			if (!$input->getOption('quiet')) {
				$io->error('Something went wrong, service could not be finished. Error was logged.');
			}

			return Console\Command\Command::FAILURE;
		}
	}

	/**
	 * @param Style\SymfonyStyle $io
	 * @param Input\InputInterface $input
	 *
	 * @return void
	 */
	private function executeConnector(Style\SymfonyStyle $io, Input\InputInterface $input): void
	{
		if (!$input->getOption('quiet')) {
			$io->section('Preparing connector');
		}

		$this->consumer->register($this->connectorConsumer);

		if (
			$input->hasOption('connector')
			&& is_string($input->getOption('connector'))
			&& $input->getOption('connector') !== ''
		) {
			$connectorId = $input->getOption('connector');

			if (Uuid\Uuid::isValid($connectorId)) {
				$connector = $this->connectorsRepository->findById(Uuid\Uuid::fromString($connectorId));
			} else {
				$connector = $this->connectorsRepository->findByIdentifier($connectorId);
			}

			if ($connector === null) {
				if (!$input->getOption('quiet')) {
					$io->warning('Connector was not found in system');
				}

				return;
			}
		} else {
			$connectors = [];

			foreach ($this->connectorsRepository as $connector) {
				$connectors[$connector->getIdentifier()] = $connector->getIdentifier() . $connector->getName() ? ' [' . $connector->getName() . ']' : '';
			}

			if (count($connectors) === 0) {
				if (!$input->getOption('quiet')) {
					$io->warning('No connectors registered in system');
				}

				return;
			}

			$question = new Console\Question\ChoiceQuestion(
				'Please select connector to execute',
				array_values($connectors)
			);

			$question->setErrorMessage('Selected connector: %s is not valid.');

			$connectorIdentifierKey = array_search($io->askQuestion($question), $connectors);

			if ($connectorIdentifierKey === false) {
				if (!$input->getOption('quiet')) {
					$io->error('Something went wrong, connector could not be loaded');
				}

				$this->logger->error('Connector identifier was not able to get from answer', [
					'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
					'type'   => 'service-cmd',
				]);

				return;
			}

			$connector = $this->connectorsRepository->findByIdentifier($connectorIdentifierKey);

			if ($connector === null) {
				if (!$input->getOption('quiet')) {
					$io->error('Something went wrong, connector could not be loaded');
				}

				$this->logger->error('Connector was not found', [
					'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
					'type'   => 'service-cmd',
				]);

				return;
			}
		}

		if (!$connector->isEnabled()) {
			if (!$input->getOption('quiet')) {
				$io->warning('Connector is disabled. Disabled connector could not be executed');
			}

			return;
		}

		if (!$input->getOption('quiet')) {
			$io->section('Initializing connector');
		}

		$service = $this->factory->create($connector);

		$this->eventLoop->futureTick(function () use ($connector, $service): void {
			$this->dispatcher?->dispatch(new Events\BeforeConnectorStartEvent($connector));

			$this->logger->info('Starting connector...', [
				'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
				'type'   => 'service-cmd',
			]);

			try {
				$this->resetConnectorDevices(
					$connector,
					MetadataTypes\ConnectionStateType::get(MetadataTypes\ConnectionStateType::STATE_UNKNOWN)
				);

				// Start connector service
				$service->execute();

				$this->connectorConnectionStateManager->setState(
					$connector,
					MetadataTypes\ConnectionStateType::get(MetadataTypes\ConnectionStateType::STATE_RUNNING)
				);
			} catch (Throwable $ex) {
				throw new Exceptions\TerminateException('Connector can\'t be started', $ex->getCode(), $ex);
			}

			$this->dispatcher?->dispatch(new Events\AfterConnectorStartEvent($connector));
		});

		$this->eventLoop->addSignal(SIGINT, function (int $signal) use ($connector, $service): void {
			$this->logger->info('Stopping connector...', [
				'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
				'type'   => 'service-cmd',
			]);

			try {
				$this->dispatcher?->dispatch(new Events\BeforeConnectorTerminateEvent($service));

				$service->terminate();

				$this->resetConnectorDevices(
					$connector,
					MetadataTypes\ConnectionStateType::get(MetadataTypes\ConnectionStateType::STATE_DISCONNECTED)
				);

				$now = $this->dateTimeFactory->getNow();

				$waitingForClosing = true;

				// Wait until connector is fully terminated
				while (
					$waitingForClosing
					&& (
						$this->dateTimeFactory->getNow()->getTimestamp() - $now->getTimestamp()
					) < self::SHUTDOWN_WAITING_DELAY
				) {
					if (!$service->hasUnfinishedTasks()) {
						$waitingForClosing = false;
					}
				}

				$this->dispatcher?->dispatch(new Events\AfterConnectorTerminateEvent($service));

				$this->connectorConnectionStateManager->setState(
					$connector,
					MetadataTypes\ConnectionStateType::get(MetadataTypes\ConnectionStateType::STATE_STOPPED)
				);

				$this->eventLoop->stop();
			} catch (Throwable $ex) {
				$this->logger->error('Connector couldn\'t be stopped. An unexpected error occurred', [
					'source'    => Metadata\Constants::MODULE_DEVICES_SOURCE,
					'type'      => 'service-cmd',
					'exception' => [
						'message' => $ex->getMessage(),
						'code'    => $ex->getCode(),
					],
				]);

				throw new Exceptions\TerminateException(
					'Error during connector termination process',
					$ex->getCode(),
					$ex
				);
			}
		});

		$this->eventLoop->run();
	}

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector
	 * @param MetadataTypes\ConnectionStateType $state
	 *
	 * @return void
	 */
	private function resetConnectorDevices(
		MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector,
		MetadataTypes\ConnectionStateType $state
	): void {
		foreach ($this->devicesRepository->findAllByConnector($connector->getId()) as $device) {
			$this->deviceConnectionStateManager->setState(
				$device,
				$state
			);

			/** @var MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity[] $properties */
			$properties = $this->devicesPropertiesRepository->findAllByDevice(
				$device->getId(),
				MetadataEntities\Modules\DevicesModule\DeviceDynamicPropertyEntity::class
			);

			$this->devicePropertyStateManager->setValidState($properties, false);

			foreach ($this->channelsRepository->findAllByDevice($device->getId()) as $channel) {
				/** @var MetadataEntities\Modules\DevicesModule\ChannelDynamicPropertyEntity[] $properties */
				$properties = $this->channelsPropertiesRepository->findAllByChannel(
					$channel->getId(),
					MetadataEntities\Modules\DevicesModule\ChannelDynamicPropertyEntity::class
				);

				$this->channelPropertyStateManager->setValidState($properties, false);
			}
		}
	}

}
