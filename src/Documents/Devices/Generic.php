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

use FastyBird\Library\Metadata\Documents\Mapping as DOC;
use FastyBird\Module\Devices\Documents;
use FastyBird\Module\Devices\Entities;

#[DOC\Document(entity: Entities\Devices\Generic::class)]
#[DOC\DiscriminatorEntry(name: Entities\Devices\Generic::TYPE)]
class Generic extends Documents\Devices\Device
{

	public static function getType(): string
	{
		return Entities\Devices\Generic::TYPE;
	}

}
