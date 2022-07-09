<?php declare(strict_types = 1);

/**
 * MappedProperty.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.47.0
 *
 * @date           02.04.22
 */

namespace FastyBird\DevicesModule\Entities\Devices\Properties;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Utilities;
use FastyBird\Metadata\Types as MetadataTypes;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 */
class MappedProperty extends Property implements IMappedProperty
{

	/**
	 * @param Entities\Devices\IDevice $device
	 * @param Entities\Devices\Properties\IProperty $parent
	 * @param string $identifier
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		Entities\Devices\IDevice $device,
		Entities\Devices\Properties\IProperty $parent,
		string $identifier,
		?Uuid\UuidInterface $id = null
	) {
		parent::__construct($device, $identifier, $id);

		$this->parent = $parent;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getParent(): IProperty
	{
		if ($this->parent === null) {
			throw new Exceptions\InvalidStateException('Mapped property can\'t be without parent property');
		}

		return $this->parent;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getType(): MetadataTypes\PropertyTypeType
	{
		return MetadataTypes\PropertyTypeType::get(MetadataTypes\PropertyTypeType::TYPE_MAPPED);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		if ($this->getParent() instanceof Entities\Devices\Properties\StaticProperty) {
			return array_merge(parent::toArray(), [
				'default' => Utilities\ValueHelper::flattenValue($this->getDefault()),
				'value'   => Utilities\ValueHelper::flattenValue($this->getValue()),
			]);
		}

		return parent::toArray();
	}

}
