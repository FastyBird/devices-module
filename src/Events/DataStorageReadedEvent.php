<?php declare(strict_types = 1);

/**
 * DataStorageReadedEvent.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Events
 * @since          0.65.0
 *
 * @date           29.06.22
 */

namespace FastyBird\DevicesModule\Events;

use Symfony\Contracts\EventDispatcher;

/**
 * Event fired after data storage configuration has been readed
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Events
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class DataStorageReadedEvent extends EventDispatcher\Event
{

}
