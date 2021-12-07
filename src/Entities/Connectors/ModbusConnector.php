<?php declare(strict_types = 1);

/**
 * ModbusConnector.php
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
class ModbusConnector extends Entities\Connectors\Connector implements IModbusConnector
{

	/** @var string */
	protected string $type = 'modbus';

	/**
	 * {@inheritDoc}
	 */
	public function getType(): string
	{
		return $this->type;
	}

}
