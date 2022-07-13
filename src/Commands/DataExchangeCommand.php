<?php declare(strict_types = 1);

/**
 * DataExchangeCommand.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Commands
 * @since          0.71.0
 *
 * @date           10.07.22
 */

namespace FastyBird\DevicesModule\Commands;

use FastyBird\DevicesModule\Consumers;
use FastyBird\Exchange\Consumer as ExchangeConsumer;
use Psr\Log;
use React\EventLoop;
use Symfony\Component\Console;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output;
use Symfony\Component\Console\Style;
use Throwable;

/**
 * Data exchange worker command
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Commands
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class DataExchangeCommand extends Console\Command\Command
{

	private Consumers\DataExchangeConsumer $dataExchangeConsumer;

	private ExchangeConsumer\Consumer $consumer;

	private EventLoop\LoopInterface $eventLoop;

	private Log\LoggerInterface $logger;

	public function __construct(
		Consumers\DataExchangeConsumer $dataExchangeConsumer,
		ExchangeConsumer\Consumer $consumer,
		EventLoop\LoopInterface $eventLoop,
		?Log\LoggerInterface $logger = null,
		?string $name = null
	) {
		$this->eventLoop = $eventLoop;

		$this->logger = $logger ?? new Log\NullLogger();

		$this->consumer = $consumer;
		$this->dataExchangeConsumer = $dataExchangeConsumer;

		parent::__construct($name);
	}

	protected function configure(): void
	{
		$this
			->setName('fb:devices-module:data-exchange')
			->addOption('noconfirm', null, Input\InputOption::VALUE_NONE, 'do not ask for any confirmation')
			->setDescription('Data exchange worker');
	}

	protected function execute(Input\InputInterface $input, Output\OutputInterface $output): int
	{
		$symfonyApp = $this->getApplication();

		if ($symfonyApp === null) {
			return 1;
		}

		$this->consumer->register($this->dataExchangeConsumer);

		$io = new Style\SymfonyStyle($input, $output);

		$io->title('FB devices module - data exchange worker');

		try {
			$this->eventLoop->run();

		} catch (Throwable $ex) {
			// Log caught exception
			$this->logger->error('An unhandled error occurred', [
				'source'    => 'devices-module',
				'type'      => 'data-exchange-cmd',
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			$this->eventLoop->stop();
		}

		return 0;
	}

}
