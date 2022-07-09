<?php declare(strict_types = 1);

/**
 * DataStorageCommand.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Commands
 * @since          0.71.0
 *
 * @date           08.07.22
 */

namespace FastyBird\DevicesModule\Commands;

use FastyBird\DevicesModule\DataStorage;
use Psr\Log;
use RuntimeException;
use Symfony\Component\Console;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output;
use Symfony\Component\Console\Style;
use Throwable;

/**
 * Data storage initialize command
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Commands
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class DataStorageCommand extends Console\Command\Command
{

	private DataStorage\Writer $writer;

	private Log\LoggerInterface $logger;

	public function __construct(
		DataStorage\Writer $writer,
		?Log\LoggerInterface $logger = null,
		?string $name = null
	) {
		$this->writer = $writer;

		$this->logger = $logger ?? new Log\NullLogger();

		parent::__construct($name);
	}

	protected function configure(): void
	{
		$this
			->setName('fb:devices-module:data-storage')
			->addOption('noconfirm', null, Input\InputOption::VALUE_NONE, 'do not ask for any confirmation')
			->setDescription('Initialize module data storage.');
	}

	protected function execute(Input\InputInterface $input, Output\OutputInterface $output): int
	{
		$symfonyApp = $this->getApplication();

		if ($symfonyApp === null) {
			return 1;
		}

		$io = new Style\SymfonyStyle($input, $output);

		$io->title('FB devices module - data storage initialization');

		$io->note('This action will create|update module data storage configuration.');

		/** @var bool $continue */
		$continue = $io->ask('Would you like to continue?', 'n', function ($answer): bool {
			if (!in_array($answer, ['y', 'Y', 'n', 'N'], true)) {
				throw new RuntimeException('You must type Y or N');
			}

			return in_array($answer, ['y', 'Y'], true);
		});

		if (!$continue) {
			return 0;
		}

		try {
			$this->writer->write();

			$io->success('Devices module data storage has been successfully initialized.');

			return 0;

		} catch (Throwable $ex) {
			// Log caught exception
			$this->logger->error('An unhandled error occurred', [
				'source'    => 'devices-module',
				'type'      => 'data-storage-cmd',
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			$io->error('Something went wrong, initialization could not be finished. Error was logged.');

			return 1;
		}
	}

}
