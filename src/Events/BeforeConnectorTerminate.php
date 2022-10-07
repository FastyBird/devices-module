<?php declare(strict_types = 1);

/**
 * BeforeConnectorTerminate.php
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
 * Event fired before connector has been terminated
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Events
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class BeforeConnectorTerminate extends EventDispatcher\Event
{

	public function __construct(private Connectors\Connector $connector)
	{
	}

	public function getConnector(): Connectors\Connector
	{
		return $this->connector;
	}

}
