<?php declare(strict_types = 1);

/**
 * AfterConnectorTerminate.php
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

use FastyBird\DevicesModule\Connectors;
use Symfony\Contracts\EventDispatcher;

/**
 * Event fired after connector has been terminated
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Events
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class AfterConnectorTerminate extends EventDispatcher\Event
{

	/** @var Connectors\Connector */
	private Connectors\Connector $connector;

	/**
	 * @param Connectors\Connector $connector
	 */
	public function __construct(
		Connectors\Connector $connector
	) {
		$this->connector = $connector;
	}

	/**
	 * @return Connectors\Connector
	 */
	public function getConnector(): Connectors\Connector
	{
		return $this->connector;
	}

}
