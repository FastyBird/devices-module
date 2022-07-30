<?php declare(strict_types = 1);

/**
 * InitializeCommand.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Commands
 * @since          0.1.0
 *
 * @date           08.08.20
 */

namespace FastyBird\DevicesModule\Commands;

use Exception;
use FastyBird\DevicesModule\DataStorage;
use FastyBird\Metadata;
use Psr\Log;
use Symfony\Component\Console;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output;
use Symfony\Component\Console\Style;
use Throwable;

/**
 * Module initialize command
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Commands
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class InitializeCommand extends Console\Command\Command
{

	/** @var DataStorage\Writer */
	private DataStorage\Writer $writer;

	/** @var Log\LoggerInterface */
	private Log\LoggerInterface $logger;

	/**
	 * @param DataStorage\Writer $writer
	 * @param Log\LoggerInterface|null $logger
	 * @param string|null $name
	 */
	public function __construct(
		DataStorage\Writer $writer,
		?Log\LoggerInterface $logger = null,
		?string $name = null
	) {
		$this->writer = $writer;

		$this->logger = $logger ?? new Log\NullLogger();

		parent::__construct($name);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function configure(): void
	{
		$this
			->setName('fb:devices-module:initialize')
			->setDescription('Devices module initialization')
			->setDefinition(
				new Input\InputDefinition([
					new Input\InputOption('database', 'd', Input\InputOption::VALUE_NONE, 'Initialize module database'),
					new Input\InputOption('data-storage', 's', Input\InputOption::VALUE_NONE, 'Initialize module data storage'),
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

		$io->title('FB devices module - initialization');

		$io->note('This action will create|update module database structure.');

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
			if ($input->getOption('database')) {
				$this->initializeDatabase($io, $output);
			}

			if ($input->getOption('data-storage')) {
				$this->initializeDataStorage($io);
			}

			$io->success('Devices module has been successfully initialized and can be now used.');

			return Console\Command\Command::SUCCESS;

		} catch (Throwable $ex) {
			// Log caught exception
			$this->logger->error('An unhandled error occurred', [
				'source'    => Metadata\Constants::MODULE_DEVICES_SOURCE,
				'type'      => 'initialize-cmd',
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			$io->error('Something went wrong, initialization could not be finished. Error was logged.');

			return Console\Command\Command::FAILURE;
		}
	}

	/**
	 * @param Style\SymfonyStyle $io
	 * @param Output\OutputInterface $output
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	private function initializeDatabase(Style\SymfonyStyle $io, Output\OutputInterface $output): void
	{
		$symfonyApp = $this->getApplication();

		if ($symfonyApp === null) {
			return;
		}

		$io->section('Preparing module database');

		$databaseCmd = $symfonyApp->find('orm:schema-tool:update');

		$result = $databaseCmd->run(new Input\ArrayInput([
			'--force' => true,
		]), $output);

		if ($result !== Console\Command\Command::SUCCESS) {
			$io->error('Something went wrong, initialization could not be finished.');

			return;
		}

		$databaseProxiesCmd = $symfonyApp->find('orm:generate-proxies');

		$result = $databaseProxiesCmd->run(new Input\ArrayInput([
			'--quiet' => true,
		]), $output);

		if ($result !== 0) {
			$io->error('Something went wrong, database initialization could not be finished.');

			return;
		}

		$io->success('Devices module database has been successfully initialized.');
	}

	/**
	 * @param Style\SymfonyStyle $io
	 *
	 * @return void
	 */
	private function initializeDataStorage(Style\SymfonyStyle $io): void
	{
		$io->section('Preparing module data storage');

		try {
			$this->writer->write();

			$io->success('Devices module data storage has been successfully initialized.');

			return;

		} catch (Throwable $ex) {
			// Log caught exception
			$this->logger->error('An unhandled error occurred', [
				'source'    => Metadata\Constants::MODULE_DEVICES_SOURCE,
				'type'      => 'initialize-cmd',
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			$io->error('Something went wrong, data storage initialization could not be finished. Error was logged.');

			return;
		}
	}

}
