<?php declare(strict_types = 1);

/**
 * StateEntityDeletedEvent.php
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

use Ramsey\Uuid;
use Symfony\Contracts\EventDispatcher;

/**
 * State entity was deleted event
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Events
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class StateEntityDeletedEvent extends EventDispatcher\Event
{

	/** @var Uuid\UuidInterface */
	private Uuid\UuidInterface $id;

	public function __construct(
		 Uuid\UuidInterface $id
	) {
		$this->id = $id;
	}

	/**
	 * @return Uuid\UuidInterface
	 */
	public function getId(): Uuid\UuidInterface
	{
		return $this->id;
	}

}
