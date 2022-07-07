<?php declare(strict_types = 1);

/**
 * Property.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           26.10.18
 */

namespace FastyBird\DevicesModule\Entities\Channels\Properties;

use DateTimeInterface;
use Doctrine\Common;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\Metadata\Types as MetadataTypes;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_devices_module_channels_properties",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Device channels properties"
 *     },
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint(name="property_identifier_unique", columns={"property_identifier", "channel_id"})
 *     },
 *     indexes={
 *       @ORM\Index(name="property_identifier_idx", columns={"property_identifier"}),
 *       @ORM\Index(name="property_settable_idx", columns={"property_settable"}),
 *       @ORM\Index(name="property_queryable_idx", columns={"property_queryable"})
 *     }
 * )
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="property_type", type="string", length=40)
 * @ORM\DiscriminatorMap({
 *    "static"   = "FastyBird\DevicesModule\Entities\Channels\Properties\StaticProperty",
 *    "dynamic"  = "FastyBird\DevicesModule\Entities\Channels\Properties\DynamicProperty",
 *    "mapped"   = "FastyBird\DevicesModule\Entities\Channels\Properties\MappedProperty"
 * })
 * @ORM\MappedSuperclass
 */
abstract class Property extends Entities\Property implements IProperty
{

	/**
	 * @var Entities\Channels\IChannel
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\ManyToOne(targetEntity="FastyBird\DevicesModule\Entities\Channels\Channel", inversedBy="properties")
	 * @ORM\JoinColumn(name="channel_id", referencedColumnName="channel_id", onDelete="CASCADE", nullable=false)
	 */
	protected Entities\Channels\IChannel $channel;

	/**
	 * @var IProperty|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\ManyToOne(targetEntity="FastyBird\DevicesModule\Entities\Channels\Properties\Property", inversedBy="children")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="property_id", nullable=true, onDelete="CASCADE")
	 */
	protected ?IProperty $parent = null;

	/**
	 * @var Common\Collections\Collection<int, IProperty>
	 *
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Channels\Properties\Property", mappedBy="parent", cascade={"remove"}, orphanRemoval=true)
	 */
	protected Common\Collections\Collection $children;

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
		parent::__construct($identifier, $id);

		$this->channel = $channel;

		$channel->addProperty($this);

		$this->children = new Common\Collections\ArrayCollection();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getParent(): ?IProperty
	{
		return $this->parent;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setParent(IProperty $device): void
	{
		$this->parent = $device;
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeParent(): void
	{
		$this->parent = null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getChildren(): array
	{
		return $this->children->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setChildren(array $children): void
	{
		$this->children = new Common\Collections\ArrayCollection();

		// Process all passed entities...
		/** @var IProperty $entity */
		foreach ($children as $entity) {
			if (!$this->children->contains($entity)) {
				// ...and assign them to collection
				$this->children->add($entity);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function addChild(IProperty $child): void
	{
		// Check if collection does not contain inserting entity
		if (!$this->children->contains($child)) {
			// ...and assign it to collection
			$this->children->add($child);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeChild(IProperty $child): void
	{
		// Check if collection contain removing entity...
		if ($this->children->contains($child)) {
			// ...and remove it from collection
			$this->children->removeElement($child);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getChannel(): Entities\Channels\IChannel
	{
		return $this->channel;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isSettable(): bool
	{
		if ($this->getParent() !== null && !$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_MAPPED)) {
			return $this->getParent()->isSettable();
		}

		return parent::isSettable();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setSettable(bool $settable): void
	{
		if ($this->getParent() !== null && !$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_MAPPED)) {
			throw new Exceptions\InvalidStateException('Settable setter is allowed only for parent');
		}

		parent::setSettable($settable);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isQueryable(): bool
	{
		if ($this->getParent() !== null && !$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_MAPPED)) {
			return $this->getParent()->isQueryable();
		}

		return parent::isQueryable();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setQueryable(bool $queryable): void
	{
		if ($this->getParent() !== null && !$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_MAPPED)) {
			throw new Exceptions\InvalidStateException('Queryable setter is allowed only for parent');
		}

		parent::setQueryable($queryable);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDataType(): MetadataTypes\DataTypeType
	{
		if ($this->getParent() !== null && !$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_MAPPED)) {
			return $this->getParent()->getDataType();
		}

		return parent::getDataType();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDataType(MetadataTypes\DataTypeType $dataType): void
	{
		if ($this->getParent() !== null && !$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_MAPPED)) {
			throw new Exceptions\InvalidStateException('Data type setter is allowed only for parent');
		}

		parent::setDataType($dataType);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUnit(): ?string
	{
		if ($this->getParent() !== null && !$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_MAPPED)) {
			return $this->getParent()->getUnit();
		}

		return parent::getUnit();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setUnit(?string $unit): void
	{
		if ($this->getParent() !== null && !$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_MAPPED)) {
			throw new Exceptions\InvalidStateException('Value unit setter is allowed only for parent');
		}

		parent::setUnit($unit);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFormat(): ?array
	{
		if ($this->getParent() !== null && !$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_MAPPED)) {
			return $this->getParent()->getFormat();
		}

		return parent::getFormat();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setFormat(array|string|null $format): void
	{
		if ($this->getParent() !== null && !$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_MAPPED)) {
			throw new Exceptions\InvalidStateException('Value format setter is allowed only for parent');
		}

		parent::setFormat($format);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getInvalid(): float|int|string|null
	{
		if ($this->getParent() !== null && !$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_MAPPED)) {
			return $this->getParent()->getInvalid();
		}

		return parent::getInvalid();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setInvalid(?string $invalid): void
	{
		if ($this->getParent() !== null && !$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_MAPPED)) {
			throw new Exceptions\InvalidStateException('Invalid value setter is allowed only for parent');
		}

		parent::setInvalid($invalid);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getNumberOfDecimals(): ?int
	{
		if ($this->getParent() !== null && !$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_MAPPED)) {
			return $this->getParent()->getNumberOfDecimals();
		}

		return parent::getNumberOfDecimals();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setNumberOfDecimals(?int $numberOfDecimals): void
	{
		if ($this->getParent() !== null && !$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_MAPPED)) {
			throw new Exceptions\InvalidStateException('Number of decimals setter is allowed only for parent');
		}

		parent::setNumberOfDecimals($numberOfDecimals);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefault(): bool|float|int|string|DateTimeInterface|MetadataTypes\ButtonPayloadType|MetadataTypes\SwitchPayloadType|null
	{
		if ($this->getParent() !== null && !$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_MAPPED)) {
			return $this->getParent()->getDefault();
		}

		return parent::getDefault();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDefault(?string $default): void
	{
		if ($this->getParent() !== null && !$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_MAPPED)) {
			throw new Exceptions\InvalidStateException('Default value setter is allowed only for parent');
		}

		parent::setDefault($default);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		$children = [];

		foreach ($this->getChildren() as $child) {
			$children[] = $child->getPlainId();
		}

		return array_merge(parent::toArray(), [
			'channel'  => $this->getChannel()->getPlainId(),
			'parent'   => $this->getParent() !== null ? $this->getParent()->getPlainId() : null,
			'children' => $children,

			'owner' => $this->getChannel()->getDevice()->getOwnerId(),
		]);
	}

}
