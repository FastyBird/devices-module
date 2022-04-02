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
use FastyBird\DevicesModule\Exceptions;
use FastyBird\Metadata\Types as MetadataTypes;

/**
 * @ORM\Entity
 */
class StaticProperty extends Property implements IStaticProperty
{

	/**
	 * {@inheritDoc}
	 */
	public function getType(): MetadataTypes\PropertyTypeType
	{
		return MetadataTypes\PropertyTypeType::get(MetadataTypes\PropertyTypeType::TYPE_STATIC);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getValue()
	{
		if ($this->getParent() !== null) {
			return $this->getParent()->getValue();
		}

		return parent::getValue();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setValue(?string $value): void
	{
		if ($this->getParent() !== null) {
			throw new Exceptions\InvalidStateException('Value setter is allowed only for parent');
		}

		parent::setValue($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'value'   => $this->getValue(),
			'default' => $this->getDefault(),
		]);
	}

}
