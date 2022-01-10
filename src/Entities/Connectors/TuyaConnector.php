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
use FastyBird\Metadata\Types as MetadataTypes;

/**
 * @ORM\Entity
 */
class TuyaConnector extends Entities\Connectors\Connector implements ITuyaConnector
{

	/**
	 * {@inheritDoc}
	 */
	public function getType(): MetadataTypes\ConnectorTypeType
	{
		return MetadataTypes\ConnectorTypeType::get(MetadataTypes\ConnectorTypeType::TYPE_TUYA);
	}

}
