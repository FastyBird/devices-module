<?php declare(strict_types = 1);

/**
 * StaticProperty.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.9.0
 *
 * @date           04.01.22
 */

namespace FastyBird\DevicesModule\Entities\Devices\Properties;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 */
class StaticProperty extends Property implements IStaticProperty
{

	/**
	 * @param Entities\Devices\IDevice $device
	 * @param string $identifier
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		Entities\Devices\IDevice $device,
		string $identifier,
		?Uuid\UuidInterface $id = null
	) {
		parent::__construct(
			$device,
			$identifier,
			$id
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getType(): ModulesMetadataTypes\PropertyTypeType
	{
		return ModulesMetadataTypes\PropertyTypeType::get(ModulesMetadataTypes\PropertyTypeType::TYPE_STATIC);
	}

}