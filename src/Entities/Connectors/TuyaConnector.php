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
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;

/**
 * @ORM\Entity
 */
class TuyaConnector extends Entities\Connectors\Connector implements ITuyaConnector
{

	/**
	 * {@inheritDoc}
	 */
	public function getType(): ModulesMetadataTypes\ConnectorTypeType
	{
		return ModulesMetadataTypes\ConnectorTypeType::get(ModulesMetadataTypes\ConnectorTypeType::TYPE_TUYA);
	}

}
