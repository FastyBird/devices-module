<?php declare(strict_types = 1);

/**
 * ConnectorFactory.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Connectors
 * @since          0.67.0
 *
 * @date           03.07.22
 */

namespace FastyBird\DevicesModule\Connectors;

use FastyBird\DevicesModule\Exceptions;
use FastyBird\Metadata\Entities as MetadataEntities;
use Nette;
use SplObjectStorage;

/**
 * Connector factory
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Connectors
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ConnectorFactory
{

	use Nette\SmartObject;

	/** @var SplObjectStorage<IConnectorFactory, string> */
	private SplObjectStorage $factories;

	public function __construct()
	{
		$this->factories = new SplObjectStorage();
	}

	/**
	 * @param IConnectorFactory $factory
	 * @param string $type
	 *
	 * @return void
	 */
	public function attach(IConnectorFactory $factory, string $type): void
	{
		$this->factories->attach($factory, $type);
	}

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector
	 *
	 * @return IConnector
	 */
	public function create(MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector): IConnector
	{
		/** @var IConnectorFactory $factory */
		foreach ($this->factories as $factory) {
			if ($connector->getType() === $this->factories[$factory]) {
				return $factory->create($connector);
			}
		}

		throw new Exceptions\InvalidStateException('Connector executor could not be created');
	}

}
