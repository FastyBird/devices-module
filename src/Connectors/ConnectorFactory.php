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

	/** @var SplObjectStorage<IConnectorFactory, null> */
	private SplObjectStorage $factories;

	/**
	 * @param IConnectorFactory[] $factories
	 */
	public function __construct(
		array $factories
	) {
		$this->factories = new SplObjectStorage();

		foreach ($factories as $factory) {
			$this->factories->attach($factory);
		}
	}

	public function create(MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector): IConnector
	{
		/** @var IConnectorFactory $factory */
		foreach ($this->factories as $factory) {
			if ($connector->getType() === $factory->getType()) {
				return $factory->create($connector);
			}
		}

		throw new Exceptions\InvalidStateException('Connector could not be created');
	}

}
