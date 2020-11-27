<?php declare(strict_types = 1);

/**
 * CreateCommand.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Commands
 * @since          0.1.0
 *
 * @date           11.05.19
 */

namespace FastyBird\DevicesModule\Commands\Devices;

use Contributte\Translation;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Models;
use Nette;
use Nette\Utils;
use Symfony\Component\Console;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output;
use Symfony\Component\Console\Style;
use Throwable;

/**
 * Physical device creation command
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Commands
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class CreateCommand extends Console\Command\Command
{

	use Nette\SmartObject;

	/** @var Models\Devices\IDevicesManager */
	private $devicesManager;

	/** @var Translation\PrefixedTranslator */
	private $translator;

	/** @var string */
	private $translationDomain = 'commands.deviceCreate';

	/**
	 * @param Models\Devices\IDevicesManager $devicesManager
	 * @param Translation\Translator $translator
	 * @param string|null $name
	 */
	public function __construct(
		Models\Devices\IDevicesManager $devicesManager,
		Translation\Translator $translator,
		?string $name = null
	) {
		// Modules models
		$this->devicesManager = $devicesManager;

		$this->translator = new Translation\PrefixedTranslator($translator, $this->translationDomain);

		parent::__construct($name);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function configure(): void
	{
		parent::configure();

		$this
			->setName('fb:devices-module:devices:create')
			->addArgument('serial_number', Input\InputArgument::OPTIONAL, $this->translator->translate('serialNumber.title'))
			->addArgument('name', Input\InputArgument::OPTIONAL, $this->translator->translate('name.title'))
			->addOption('noconfirm', null, Input\InputOption::VALUE_NONE, 'do not ask for any confirmation')
			->setDescription('Create device.');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(Input\InputInterface $input, Output\OutputInterface $output)
	{
		$io = new Style\SymfonyStyle($input, $output);

		$io->title('FB devices module - create device');

		$type = $io->choice($this->translator->translate('type.title'), ['network'], 'network');

		if ($input->hasOption('identifier') && $input->getOption('identifier') !== '') {
			$identifier = $input->getOption('identifier');

		} else {
			$identifier = $io->ask($this->translator->translate('identifier.title'));
		}

		try {
			$create = new Utils\ArrayHash();

			if ($type === 'network') {
				$create->entity = Entities\Devices\NetworkDevice::class;
			}

			$create->identifier = $identifier;

			$device = $this->devicesManager->create($create);

			$io->text(sprintf('<info>%s</info>', $this->translator->translate('success', ['name' => $device->getName()])));

		} catch (Throwable $ex) {
			$io->text(sprintf('<error>%s</error>', $this->translator->translate('validation.device.wasNotCreated', ['error' => $ex->getMessage()])));
		}

		return 0;
	}

}
