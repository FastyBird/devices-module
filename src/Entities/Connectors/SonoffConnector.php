<?php declare(strict_types = 1);

/**
 * SonoffConnector.php
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
class SonoffConnector extends Entities\Connectors\Connector implements ISonoffConnector
{

	/**
	 * {@inheritDoc}
	 */
	public function getType(): ModulesMetadataTypes\ConnectorTypeType
	{
		return ModulesMetadataTypes\ConnectorTypeType::get(ModulesMetadataTypes\ConnectorTypeType::TYPE_SONOFF);
	}

}
