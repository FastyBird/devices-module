<?php declare(strict_types = 1);

/**
 * ConnectorCommand.php
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
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Events;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use FastyBird\Exchange\Consumer as ExchangeConsumer;
use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Types as MetadataTypes;
use Nette\Localization;
use Nette\Utils;
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
class ConnectorCommand extends Console\Command\Command
{

	private const SHUTDOWN_WAITING_DELAY = 3;

	/** @var Connectors\ConnectorFactory */
	private Connectors\ConnectorFactory $factory;

	/** @var Models\DataStorage\IConnectorsRepository */
	private Models\DataStorage\IConnectorsRepository $connectorsRepository;

	/** @var Models\Connectors\Properties\IPropertiesRepository */
	private Models\Connectors\Properties\IPropertiesRepository $connectorPropertiesRepository;

	/** @var Models\Connectors\Properties\IPropertiesManager */
	private Models\Connectors\Properties\IPropertiesManager $connectorPropertiesManager;

	/** @var Models\States\ConnectorPropertiesRepository */
	private Models\States\ConnectorPropertiesRepository $connectorPropertiesStateRepository;

	/** @var Models\States\ConnectorPropertiesManager */
	private Models\States\ConnectorPropertiesManager $connectorPropertiesStateManager;

	/** @var Consumers\ConnectorConsumer */
	private Consumers\ConnectorConsumer $connectorConsumer;

	/** @var ExchangeConsumer\Consumer */
	private ExchangeConsumer\Consumer $consumer;

	/** @var DateTimeFactory\DateTimeFactory */
	private DateTimeFactory\DateTimeFactory $dateTimeFactory;

	/** @var Localization\Translator */
	private Localization\Translator $translator;

	/** @var Log\LoggerInterface */
	private Log\LoggerInterface $logger;

	/** @var PsrEventDispatcher\EventDispatcherInterface|null */
	private ?PsrEventDispatcher\EventDispatcherInterface $dispatcher;

	/** @var EventLoop\LoopInterface */
	private EventLoop\LoopInterface $eventLoop;

	/**
	 * @param Connectors\ConnectorFactory $factory
	 * @param Models\DataStorage\IConnectorsRepository $connectorsRepository
	 * @param Models\Connectors\Properties\IPropertiesRepository $connectorPropertiesRepository
	 * @param Models\Connectors\Properties\IPropertiesManager $connectorPropertiesManager
	 * @param Models\States\ConnectorPropertiesRepository $connectorPropertiesStateRepository
	 * @param Models\States\ConnectorPropertiesManager $connectorPropertiesStateManager
	 * @param Consumers\ConnectorConsumer $connectorConsumer
	 * @param ExchangeConsumer\Consumer $consumer
	 * @param DateTimeFactory\DateTimeFactory $dateTimeFactory
	 * @param Localization\Translator $translator
	 * @param EventLoop\LoopInterface $eventLoop
	 * @param PsrEventDispatcher\EventDispatcherInterface|null $dispatcher
	 * @param Log\LoggerInterface|null $logger
	 * @param string|null $name
	 */
	public function __construct(
		Connectors\ConnectorFactory $factory,
		Models\DataStorage\IConnectorsRepository $connectorsRepository,
		Models\Connectors\Properties\IPropertiesRepository $connectorPropertiesRepository,
		Models\Connectors\Properties\IPropertiesManager $connectorPropertiesManager,
		Models\States\ConnectorPropertiesRepository $connectorPropertiesStateRepository,
		Models\States\ConnectorPropertiesManager $connectorPropertiesStateManager,
		Consumers\ConnectorConsumer $connectorConsumer,
		ExchangeConsumer\Consumer $consumer,
		DateTimeFactory\DateTimeFactory $dateTimeFactory,
		Localization\Translator $translator,
		EventLoop\LoopInterface $eventLoop,
		?PsrEventDispatcher\EventDispatcherInterface $dispatcher,
		?Log\LoggerInterface $logger = null,
		?string $name = null
	) {
		$this->factory = $factory;
		$this->connectorsRepository = $connectorsRepository;
		$this->connectorPropertiesRepository = $connectorPropertiesRepository;
		$this->connectorPropertiesManager = $connectorPropertiesManager;
		$this->connectorPropertiesStateRepository = $connectorPropertiesStateRepository;
		$this->connectorPropertiesStateManager = $connectorPropertiesStateManager;

		$this->connectorConsumer = $connectorConsumer;
		$this->consumer = $consumer;

		$this->dateTimeFactory = $dateTimeFactory;

		$this->translator = $translator;
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
			->setName('fb:devices-module:connector')
			->addArgument('connector', Input\InputArgument::OPTIONAL, $this->translator->translate('//commands.connector.inputs.connector.title'))
			->addOption('noconfirm', null, Input\InputOption::VALUE_NONE, 'do not ask for any confirmation')
			->setDescription('Connector communication service');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(Input\InputInterface $input, Output\OutputInterface $output): int
	{
		$symfonyApp = $this->getApplication();

		if ($symfonyApp === null) {
			return 1;
		}

		$this->consumer->register($this->connectorConsumer);

		$io = new Style\SymfonyStyle($input, $output);

		$io->title('FB devices module - connector');

		if (
			$input->hasArgument('connector')
			&& is_string($input->getArgument('connector'))
			&& $input->getArgument('connector') !== ''
		) {
			$connectorId = $input->getArgument('connector');
		} else {
			$connectorId = $io->ask($this->translator->translate('//commands.connector.inputs.connector.title'));
		}

		if (!Uuid\Uuid::isValid($connectorId)) {
			$io->error($this->translator->translate('//commands.connector.validation.identifierNotValid'));

			return 1;
		}

		$connector = $this->connectorsRepository->findById(Uuid\Uuid::fromString($connectorId));

		if ($connector === null) {
			$this->logger->alert('Connector was not found', [
				'source' => 'devices-module',
				'type'   => 'connector',
			]);

			return 0;
		}

		$service = $this->factory->create($connector);

		try {
			$this->eventLoop->futureTick(function () use ($connector, $service): void {
				$this->dispatcher?->dispatch(new Events\BeforeConnectorStartEvent($connector));

				$this->logger->info('Starting connector...', [
					'source' => 'devices-module',
					'type'   => 'connector',
				]);

				try {
					// Start connector service
					$service->execute();

					$this->setConnectorState(
						$connector,
						MetadataTypes\ConnectionStateType::get(MetadataTypes\ConnectionStateType::STATE_RUNNING)
					);
				} catch (Throwable $ex) {
					throw new Exceptions\TerminateException('Connector can\'t be started');
				}

				$this->dispatcher?->dispatch(new Events\AfterConnectorStartEvent($connector));
			});

			$this->eventLoop->addSignal(SIGINT, function (int $signal) use ($connector, $service): void {
				$this->logger->info('Stopping connector...', [
					'source' => 'devices-module',
					'type'   => 'connector',
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

					$this->setConnectorState(
						$connector,
						MetadataTypes\ConnectionStateType::get(MetadataTypes\ConnectionStateType::STATE_STOPPED)
					);
				} catch (Throwable $ex) {
					$this->logger->error('Connector couldn\'t be stopped. An unexpected error occurred', [
						'source'    => 'devices-module',
						'type'      => 'connector',
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
		} catch (Throwable $ex) {
			if (!$ex instanceof Exceptions\TerminateException) {
				$this->logger->error('An unhandled error occurred', [
					'source'    => 'devices-module',
					'type'      => 'connector',
					'exception' => [
						'message' => $ex->getMessage(),
						'code'    => $ex->getCode(),
					],
				]);
			}

			$this->eventLoop->stop();
		}

		return 0;
	}

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector
	 * @param MetadataTypes\ConnectionStateType $state
	 *
	 * @return void
	 */
	private function setConnectorState(
		MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector,
		MetadataTypes\ConnectionStateType $state
	): void {
		$findProperty = new Queries\FindConnectorPropertiesQuery();
		$findProperty->byConnectorId($connector->getId());
		$findProperty->byIdentifier(MetadataTypes\ConnectorPropertyNameType::NAME_STATE);

		$property = $this->connectorPropertiesRepository->findOneBy($findProperty);

		if ($property === null) {
			$property = $this->connectorPropertiesManager->create(Utils\ArrayHash::from([
				'connector'  => $connector->getId(),
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

		if (!$property instanceof Entities\Connectors\Properties\IDynamicProperty) {
			throw new Exceptions\InvalidStateException('Connector property entity is not valid type');
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
