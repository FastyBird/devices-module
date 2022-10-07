<?php declare(strict_types = 1);

/**
 * Dynamic.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.31.0
 *
 * @date           08.02.22
 */

namespace FastyBird\DevicesModule\Entities\Connectors\Properties;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\Metadata\Types as MetadataTypes;

/**
 * @ORM\Entity
 */
class Dynamic extends Property
{

	/**
	 * {@inheritDoc}
	 */
	public function getType(): MetadataTypes\PropertyTypeType
	{
		return MetadataTypes\PropertyTypeType::get(MetadataTypes\PropertyTypeType::TYPE_DYNAMIC);
	}

}
