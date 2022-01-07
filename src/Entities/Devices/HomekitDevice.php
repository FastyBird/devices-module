<?php declare(strict_types = 1);

/**
 * HomekitDevice.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.9.0
 *
 * @date           07.01.22
 */

namespace FastyBird\DevicesModule\Entities\Devices;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;

/**
 * @ORM\Entity
 */
class HomekitDevice extends Entities\Devices\Device implements IHomekitDevice
{

	/**
	 * {@inheritDoc}
	 */
	public function getType(): ModulesMetadataTypes\DeviceTypeType
	{
		return ModulesMetadataTypes\DeviceTypeType::get(ModulesMetadataTypes\DeviceTypeType::TYPE_HOMEKIT);
	}

}