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
use FastyBird\Metadata\Types as MetadataTypes;
use Psr\EventDispatcher as PsrEventDispatcher;
use Psr\Log;
use Ramsey\Uuid;
use React\EventLoop;
use RuntimeException;
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
	 * @param Models\States\ConnectorConnectionStateManager $connectorConnectionStateManager
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
		Models\States\ConnectorConnectionStateManager $connectorConnectionStateManager,
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

		$this->connectorConnectionStateManager = $connectorConnectionStateManager;

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
				])
			);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(Input\InputInterface $input, Output\OutputInterface $output): int
	{
		$symfonyApp = $this->getApplication();

		if ($symfonyApp === null) {
			return Console\Command\Command::FAILURE;
		}

		$io = new Style\SymfonyStyle($input, $output);

		$io->title('FB devices module - service');

		$io->note('This action will run module services.');

		if (!$input->getOption('no-confirm')) {
			/** @var bool $continue */
			$continue = $io->ask('Would you like to continue?', 'n', function ($answer): bool {
				if (!in_array($answer, ['y', 'Y', 'n', 'N'], true)) {
					throw new RuntimeException('You must type Y or N');
				}

				return in_array($answer, ['y', 'Y'], true);
			});

			if (!$continue) {
				return Console\Command\Command::SUCCESS;
			}
		}

		try {
			if ($input->getOption('exchange')) {
				$this->consumer->register($this->dataExchangeConsumer);
			}

			if ($input->getOption('connector')) {
				$this->executeConnector($io, $input);
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

			$io->error('Something went wrong, service could not be finished. Error was logged.');

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
		$io->newLine();

		$io->section('Preparing module connector');

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
				$io->warning('Connector was not found in system');

				return;
			}
		} else {
			$connectors = [];

			foreach ($this->connectorsRepository->findAll() as $connector) {
				$connectors[] = $connector->getIdentifier() . $connector->getName() ? ' [' . $connector->getName() . ']' : '';
			}

			if (count($connectors) === 0) {
				$io->warning('No connectors registered in system');

				return;
			}

			$question = new Console\Question\ChoiceQuestion(
				'Please select connector to execute',
				$connectors
			);

			$question->setErrorMessage('Selected connector: %s is not valid.');

			$connectorIdentifier = $io->askQuestion($question);

			$connector = $this->connectorsRepository->findByIdentifier($connectorIdentifier);

			if ($connector === null) {
				$io->error('Something went wrong, connector could not be loaded');

				$this->logger->alert('Connector was not found', [
					'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
					'type'   => 'service-cmd',
				]);

				return;
			}
		}

		$io->newLine();

		$io->section('Initializing module connector');

		$service = $this->factory->create($connector);

		$this->eventLoop->futureTick(function () use ($connector, $service): void {
			$this->dispatcher?->dispatch(new Events\BeforeConnectorStartEvent($connector));

			$this->logger->info('Starting connector...', [
				'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
				'type'   => 'service-cmd',
			]);

			try {
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

}
