<?php declare(strict_types = 1);

/**
 * Generic.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Documents
 * @since          1.0.0
 *
 * @date           08.06.24
 */

namespace FastyBird\Module\Devices\Documents\Devices;

use FastyBird\Core\Application\Documents as ApplicationDocuments;
use FastyBird\Module\Devices\Documents;
use FastyBird\Module\Devices\Entities;

#[ApplicationDocuments\Mapping\Document(entity: Entities\Devices\Generic::class)]
#[ApplicationDocuments\Mapping\DiscriminatorEntry(name: Entities\Devices\Generic::TYPE)]
class Generic extends Documents\Devices\Device
{

	public static function getType(): string
	{
		return Entities\Devices\Generic::TYPE;
	}

}
