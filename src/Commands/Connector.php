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
use SplObjectStorage;
use Symfony\Component\Console;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output;
use Symfony\Component\Console\Style;
use Throwable;
use function array_search;
use function array_values;
use function assert;
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
		private Models\DataStorage\ConnectorsRepository $connectorsRepository,
		private Models\DataStorage\DevicesRepository $devicesRepository,
		private Models\DataStorage\DevicePropertiesRepository $devicesPropertiesRepository,
		private Models\DataStorage\ChannelsRepository $channelsRepository,
		private Models\DataStorage\ChannelPropertiesRepository $channelsPropertiesRepository,
		private Models\States\ConnectorConnectionStateManager $connectorConnectionStateManager,
		private Models\States\DeviceConnectionStateManager $deviceConnectionStateManager,
		private Models\States\DevicePropertyStateManager $devicePropertyStateManager,
		private Models\States\ChannelPropertyStateManager $channelPropertyStateManager,
		private Consumers\Connector $connectorConsumer,
		private ExchangeConsumer\Consumer $consumer,
		private DateTimeFactory\DateTimeFactory $dateTimeFactory,
		private EventLoop\LoopInterface $eventLoop,
		private PsrEventDispatcher\EventDispatcherInterface|null $dispatcher,
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
				false,
			);

			$continue = $io->askQuestion($question);

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

			if (!$input->getOption('quiet')) {
				$io->error('Something went wrong, service could not be finished. Error was logged.');
			}

			return Console\Command\Command::FAILURE;
		}
	}

	/**
	 * @throws Exceptions\Terminate
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

			$connector = Uuid\Uuid::isValid($connectorId)
				? $this->connectorsRepository->findById(Uuid\Uuid::fromString($connectorId))
				: $this->connectorsRepository->findByIdentifier($connectorId);

			if ($connector === null) {
				if (!$input->getOption('quiet')) {
					$io->warning('Connector was not found in system');
				}

				return;
			}
		} else {
			$connectors = [];

			foreach ($this->connectorsRepository as $connector) {
				$connectors[$connector->getIdentifier()] = $connector->getIdentifier() . $connector->getName()
					? ' [' . $connector->getName() . ']'
					: '';
			}

			if (count($connectors) === 0) {
				if (!$input->getOption('quiet')) {
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
				if (!$input->getOption('quiet')) {
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
				if (!$input->getOption('quiet')) {
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
			if (!$input->getOption('quiet')) {
				$io->warning('Connector is disabled. Disabled connector could not be executed');
			}

			return;
		}

		if (!$input->getOption('quiet')) {
			$io->section('Initializing connector');
		}

		$service = null;

		foreach ($this->factories as $factory) {
			assert($factory instanceof Connectors\ConnectorFactory);
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
					MetadataTypes\ConnectionStateType::get(MetadataTypes\ConnectionStateType::STATE_UNKNOWN),
				);

				// Start connector service
				$service->execute();

				$this->connectorConnectionStateManager->setState(
					$connector,
					MetadataTypes\ConnectionStateType::get(MetadataTypes\ConnectionStateType::STATE_RUNNING),
				);
			} catch (Throwable $ex) {
				throw new Exceptions\Terminate('Connector can\'t be started', $ex->getCode(), $ex);
			}

			$this->dispatcher?->dispatch(new Events\AfterConnectorStart($connector));
		});

		$this->eventLoop->addSignal(SIGINT, function (int $signal) use ($connector, $service): void {
			$this->logger->info('Stopping connector...', [
				'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
				'type' => 'service-cmd',
			]);

			try {
				$this->dispatcher?->dispatch(new Events\BeforeConnectorTerminate($service));

				$service->terminate();

				$this->resetConnectorDevices(
					$connector,
					MetadataTypes\ConnectionStateType::get(MetadataTypes\ConnectionStateType::STATE_DISCONNECTED),
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
					MetadataTypes\ConnectionStateType::get(MetadataTypes\ConnectionStateType::STATE_STOPPED),
				);

				$this->eventLoop->stop();
			} catch (Throwable $ex) {
				$this->logger->error('Connector couldn\'t be stopped. An unexpected error occurred', [
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

	private function resetConnectorDevices(
		MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector,
		MetadataTypes\ConnectionStateType $state,
	): void
	{
		foreach ($this->devicesRepository->findAllByConnector($connector->getId()) as $device) {
			$this->deviceConnectionStateManager->setState($device, $state);

			/** @var Array<MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity> $properties */
			$properties = $this->devicesPropertiesRepository->findAllByDevice(
				$device->getId(),
				MetadataEntities\Modules\DevicesModule\DeviceDynamicPropertyEntity::class,
			);

			$this->devicePropertyStateManager->setValidState($properties, false);

			foreach ($this->channelsRepository->findAllByDevice($device->getId()) as $channel) {
				/** @var Array<MetadataEntities\Modules\DevicesModule\ChannelDynamicPropertyEntity> $properties */
				$properties = $this->channelsPropertiesRepository->findAllByChannel(
					$channel->getId(),
					MetadataEntities\Modules\DevicesModule\ChannelDynamicPropertyEntity::class,
				);

				$this->channelPropertyStateManager->setValidState($properties, false);
			}
		}
	}

}
