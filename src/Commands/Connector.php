<?php declare(strict_types = 1);

/**
 * Connector.php
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

namespace FastyBird\Module\Devices\Commands;

use BadMethodCallException;
use FastyBird\DateTimeFactory;
use FastyBird\Library\Exchange\Consumer as ExchangeConsumer;
use FastyBird\Library\Metadata;
use FastyBird\Library\Metadata\Entities as MetadataEntities;
use FastyBird\Library\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use FastyBird\Module\Devices\Connectors;
use FastyBird\Module\Devices\Consumers;
use FastyBird\Module\Devices\Events;
use FastyBird\Module\Devices\Exceptions;
use FastyBird\Module\Devices\Models;
use IPub\DoctrineOrmQuery\Exceptions as DoctrineOrmQueryExceptions;
use Psr\EventDispatcher as PsrEventDispatcher;
use Psr\Log;
use Ramsey\Uuid;
use React\EventLoop;
use SplObjectStorage;
use Symfony\Component\Console;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output;
use Symfony\Component\Console\Style;
use Throwable;
use function array_search;
use function array_values;
use function count;
use function is_string;
use const SIGINT;

/**
 * Module connector command
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Commands
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class Connector extends Console\Command\Command
{

	public const NAME = 'fb:devices-module:connector';

	private const SHUTDOWN_WAITING_DELAY = 3;

	/** @var SplObjectStorage<Connectors\ConnectorFactory, string> */
	private SplObjectStorage $factories;

	private Log\LoggerInterface $logger;

	public function __construct(
		private readonly Models\DataStorage\ConnectorsRepository $connectorsRepository,
		private readonly Models\DataStorage\DevicesRepository $devicesRepository,
		private readonly Models\DataStorage\DevicePropertiesRepository $devicesPropertiesRepository,
		private readonly Models\DataStorage\ChannelsRepository $channelsRepository,
		private readonly Models\DataStorage\ChannelPropertiesRepository $channelsPropertiesRepository,
		private readonly Models\States\ConnectorConnectionStateManager $connectorConnectionStateManager,
		private readonly Models\States\DeviceConnectionStateManager $deviceConnectionStateManager,
		private readonly Models\States\DevicePropertyStateManager $devicePropertyStateManager,
		private readonly Models\States\ChannelPropertyStateManager $channelPropertyStateManager,
		private readonly Consumers\Connector $connectorConsumer,
		private readonly ExchangeConsumer\Container $consumer,
		private readonly DateTimeFactory\Factory $dateTimeFactory,
		private readonly EventLoop\LoopInterface $eventLoop,
		private readonly PsrEventDispatcher\EventDispatcherInterface|null $dispatcher,
		Log\LoggerInterface|null $logger = null,
		string|null $name = null,
	)
	{
		$this->logger = $logger ?? new Log\NullLogger();

		$this->factories = new SplObjectStorage();

		parent::__construct($name);
	}

	public function attach(Connectors\ConnectorFactory $factory, string $type): void
	{
		$this->factories->attach($factory, $type);
	}

	/**
	 * @throws Console\Exception\InvalidArgumentException
	 */
	protected function configure(): void
	{
		$this
			->setName(self::NAME)
			->setDescription('Devices module connector')
			->setDefinition(
				new Input\InputDefinition([
					new Input\InputOption(
						'connector',
						'c',
						Input\InputOption::VALUE_REQUIRED,
						'Run devices module connector',
					),
					new Input\InputOption(
						'no-confirm',
						null,
						Input\InputOption::VALUE_NONE,
						'Do not ask for any confirmation',
					),
					new Input\InputOption('quiet', 'q', Input\InputOption::VALUE_NONE, 'Do not output any message'),
				]),
			);
	}

	/**
	 * @throws Console\Exception\InvalidArgumentException
	 */
	protected function execute(Input\InputInterface $input, Output\OutputInterface $output): int
	{
		$io = new Style\SymfonyStyle($input, $output);

		if ($input->getOption('quiet') === false) {
			$io->title('Devices module - service');

			$io->note('This action will run module services.');
		}

		if ($input->getOption('no-confirm') === false) {
			$question = new Console\Question\ConfirmationQuestion(
				'Would you like to continue?',
				false,
			);

			$continue = (bool) $io->askQuestion($question);

			if (!$continue) {
				return Console\Command\Command::SUCCESS;
			}
		}

		try {
			$this->executeConnector($io, $input);

			$this->eventLoop->run();

			return Console\Command\Command::SUCCESS;
		} catch (Exceptions\Terminate $ex) {
			$this->logger->debug('Stopping connector', [
				'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
				'type' => 'service-cmd',
				'exception' => [
					'message' => $ex->getMessage(),
					'code' => $ex->getCode(),
				],
			]);

			$this->eventLoop->stop();

			return Console\Command\Command::SUCCESS;
		} catch (Throwable $ex) {
			// Log caught exception
			$this->logger->error('An unhandled error occurred', [
				'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
				'type' => 'service-cmd',
				'exception' => [
					'message' => $ex->getMessage(),
					'code' => $ex->getCode(),
				],
			]);

			if ($input->getOption('quiet') === false) {
				$io->error('Something went wrong, service could not be finished. Error was logged.');
			}

			return Console\Command\Command::FAILURE;
		}
	}

	/**
	 * @throws Console\Exception\InvalidArgumentException
	 * @throws BadMethodCallException
	 * @throws Exceptions\Terminate
	 * @throws MetadataExceptions\FileNotFound
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidData
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\Logic
	 * @throws MetadataExceptions\MalformedInput
	 */
	private function executeConnector(Style\SymfonyStyle $io, Input\InputInterface $input): void
	{
		if ($input->getOption('quiet') === false) {
			$io->section('Preparing connector');
		}

		$this->consumer->register($this->connectorConsumer);

		if (
			$input->hasOption('connector')
			&& is_string($input->getOption('connector'))
			&& $input->getOption('connector') !== ''
		) {
			$connectorId = $input->getOption('connector');

			$connector = Uuid\Uuid::isValid($connectorId)
				? $this->connectorsRepository->findById(Uuid\Uuid::fromString($connectorId))
				: $this->connectorsRepository->findByIdentifier($connectorId);

			if ($connector === null) {
				if ($input->getOption('quiet') === false) {
					$io->warning('Connector was not found in system');
				}

				return;
			}
		} else {
			$connectors = [];

			foreach ($this->connectorsRepository as $connector) {
				$connectors[$connector->getIdentifier()] = $connector->getIdentifier() .
					($connector->getName() !== null ? ' [' . $connector->getName() . ']' : '');
			}

			if (count($connectors) === 0) {
				if ($input->getOption('quiet') === false) {
					$io->warning('No connectors registered in system');
				}

				return;
			}

			$question = new Console\Question\ChoiceQuestion(
				'Please select connector to execute',
				array_values($connectors),
			);

			$question->setErrorMessage('Selected connector: %s is not valid.');

			$connectorIdentifierKey = array_search($io->askQuestion($question), $connectors, true);

			if ($connectorIdentifierKey === false) {
				if ($input->getOption('quiet') === false) {
					$io->error('Something went wrong, connector could not be loaded');
				}

				$this->logger->error('Connector identifier was not able to get from answer', [
					'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
					'type' => 'service-cmd',
				]);

				return;
			}

			$connector = $this->connectorsRepository->findByIdentifier($connectorIdentifierKey);

			if ($connector === null) {
				if ($input->getOption('quiet') === false) {
					$io->error('Something went wrong, connector could not be loaded');
				}

				$this->logger->error('Connector was not found', [
					'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
					'type' => 'service-cmd',
				]);

				return;
			}
		}

		if (!$connector->isEnabled()) {
			if ($input->getOption('quiet') === false) {
				$io->warning('Connector is disabled. Disabled connector could not be executed');
			}

			return;
		}

		if ($input->getOption('quiet') === false) {
			$io->section('Initializing connector');
		}

		$this->dispatcher?->dispatch(new Events\ConnectorStartup());

		$service = null;

		foreach ($this->factories as $factory) {
			if ($connector->getType() === $this->factories[$factory]) {
				$service = $factory->create($connector);
			}
		}

		if ($service === null) {
			throw new Exceptions\Terminate('Connector service could not created');
		}

		$this->eventLoop->futureTick(function () use ($connector, $service): void {
			$this->dispatcher?->dispatch(new Events\BeforeConnectorStart($connector));

			$this->logger->info('Starting connector...', [
				'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
				'type' => 'service-cmd',
			]);

			try {
				$this->resetConnectorDevices(
					$connector,
					MetadataTypes\ConnectionState::get(MetadataTypes\ConnectionState::STATE_UNKNOWN),
				);

				// Start connector service
				$service->execute();

				$this->connectorConnectionStateManager->setState(
					$connector,
					MetadataTypes\ConnectionState::get(MetadataTypes\ConnectionState::STATE_RUNNING),
				);
			} catch (Throwable $ex) {
				throw new Exceptions\Terminate('Connector can\'t be started', $ex->getCode(), $ex);
			}

			$this->dispatcher?->dispatch(new Events\AfterConnectorStart($connector));
		});

		$this->eventLoop->addSignal(SIGINT, function () use ($connector, $service): void {
			$this->logger->info('Stopping connector...', [
				'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
				'type' => 'service-cmd',
			]);

			try {
				$this->dispatcher?->dispatch(new Events\BeforeConnectorTerminate($service));

				$service->terminate();

				$this->resetConnectorDevices(
					$connector,
					MetadataTypes\ConnectionState::get(MetadataTypes\ConnectionState::STATE_DISCONNECTED),
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

				$this->dispatcher?->dispatch(new Events\AfterConnectorTerminate($service));

				$this->connectorConnectionStateManager->setState(
					$connector,
					MetadataTypes\ConnectionState::get(MetadataTypes\ConnectionState::STATE_STOPPED),
				);

				$this->eventLoop->stop();
			} catch (Throwable $ex) {
				$this->logger->error('Connector could not be stopped. An unexpected error occurred', [
					'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
					'type' => 'service-cmd',
					'exception' => [
						'message' => $ex->getMessage(),
						'code' => $ex->getCode(),
					],
				]);

				throw new Exceptions\Terminate(
					'Error during connector termination process',
					$ex->getCode(),
					$ex,
				);
			}
		});
	}

	/**
	 * @throws DoctrineOrmQueryExceptions\InvalidStateException
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\FileNotFound
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidData
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\Logic
	 * @throws MetadataExceptions\MalformedInput
	 */
	private function resetConnectorDevices(
		MetadataEntities\DevicesModule\Connector $connector,
		MetadataTypes\ConnectionState $state,
	): void
	{
		foreach ($this->devicesRepository->findAllByConnector($connector->getId()) as $device) {
			$this->deviceConnectionStateManager->setState($device, $state);

			/** @var Array<MetadataEntities\DevicesModule\DeviceDynamicProperty> $properties */
			$properties = $this->devicesPropertiesRepository->findAllByDevice(
				$device->getId(),
				MetadataEntities\DevicesModule\DeviceDynamicProperty::class,
			);

			$this->devicePropertyStateManager->setValidState($properties, false);

			foreach ($this->channelsRepository->findAllByDevice($device->getId()) as $channel) {
				/** @var Array<MetadataEntities\DevicesModule\ChannelDynamicProperty> $properties */
				$properties = $this->channelsPropertiesRepository->findAllByChannel(
					$channel->getId(),
					MetadataEntities\DevicesModule\ChannelDynamicProperty::class,
				);

				$this->channelPropertyStateManager->setValidState($properties, false);
			}
		}
	}

}