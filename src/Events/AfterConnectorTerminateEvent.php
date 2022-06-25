<?php declare(strict_types = 1);

/**
 * AfterConnectorTerminateEvent.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Events
 * @since          0.65.0
 *
 * @date           22.06.22
 */

namespace FastyBird\DevicesModule\Events;

use FastyBird\Metadata\Entities as MetadataEntities;
use Symfony\Contracts\EventDispatcher;

/**
 * Event fired after connector has been terminated
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Events
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class AfterConnectorTerminateEvent extends EventDispatcher\Event
{

	/** @var MetadataEntities\Modules\DevicesModule\IConnectorEntity */
	private MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector
	 */
	public function __construct(
		MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector
	) {
		$this->connector = $connector;
	}

	/**
	 * @return MetadataEntities\Modules\DevicesModule\IConnectorEntity
	 */
	public function getConnector(): MetadataEntities\Modules\DevicesModule\IConnectorEntity
	{
		return $this->connector;
	}

}
