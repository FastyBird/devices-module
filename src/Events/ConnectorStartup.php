<?php declare(strict_types = 1);

/**
 * ConnectorStartup.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Events
 * @since          0.61.0
 *
 * @date           10.10.22
 */

namespace FastyBird\DevicesModule\Events;

use Symfony\Contracts\EventDispatcher;

/**
 * When WS server started
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Events
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class ConnectorStartup extends EventDispatcher\Event
{

}