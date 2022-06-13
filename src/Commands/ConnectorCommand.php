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

use Nette\Localization;
use Psr\Log;
use Ramsey\Uuid;
use RuntimeException;
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

	/** @var Localization\Translator */
	private Localization\Translator $translator;

	/** @var Log\LoggerInterface */
	private Log\LoggerInterface $logger;

	/** @var EventLoop\LoopInterface */
	private EventLoop\LoopInterface $eventLoop;

	public function __construct(
		Localization\Translator $translator,
		EventLoop\LoopInterface $eventLoop,
		?Log\LoggerInterface $logger = null,
		?string $name = null
	) {
		parent::__construct($name);

		$this->translator = $translator;
		$this->eventLoop = $eventLoop;

		$this->logger = $logger ?? new Log\NullLogger();
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
			->setDescription('Run connector service.');
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

		$this->eventLoop->run();

		return 0;
	}

}
