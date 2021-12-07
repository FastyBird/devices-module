<?php declare(strict_types = 1);

/**
 * TuyaConnector.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.6.0
 *
 * @date           07.12.21
 */

namespace FastyBird\DevicesModule\Entities\Connectors;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;

/**
 * @ORM\Entity
 */
class TuyaConnector extends Entities\Connectors\Connector implements ITuyaConnector
{

	/** @var string */
	protected string $type = 'tuya';

	/**
	 * {@inheritDoc}
	 */
	public function getType(): string
	{
		return $this->type;
	}

}
