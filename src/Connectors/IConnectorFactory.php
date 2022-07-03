<?php declare(strict_types = 1);

/**
 * IConnectorFactory.php
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

use FastyBird\Metadata\Entities as MetadataEntities;

/**
 * Connector factory interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Connectors
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IConnectorFactory
{

	/**
	 * @return string
	 */
	public function getType(): string;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector
	 *
	 * @return IConnector
	 */
	public function create(MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector): IConnector;

}