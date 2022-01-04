<?php declare(strict_types = 1);

/**
 * DynamicProperty.php
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

namespace FastyBird\DevicesModule\Entities\Channels\Properties;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 */
class DynamicProperty extends Property implements IDynamicProperty
{

	/**
	 * @param Entities\Channels\IChannel $channel
	 * @param string $identifier
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		Entities\Channels\IChannel $channel,
		string $identifier,
		?Uuid\UuidInterface $id = null
	) {
		parent::__construct(
			$channel,
			$identifier,
			$id
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getType(): ModulesMetadataTypes\PropertyTypeType
	{
		return ModulesMetadataTypes\PropertyTypeType::get(ModulesMetadataTypes\PropertyTypeType::TYPE_DYNAMIC);
	}

}
