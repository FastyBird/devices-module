<?php declare(strict_types = 1);

/**
 * Device.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           29.01.17
 */

namespace FastyBird\DevicesModule\Entities\Devices;

use Doctrine\Common;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;
use FastyBird\SimpleAuth\Entities as SimpleAuthEntities;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use IPub\DoctrineDynamicDiscriminatorMap\Entities as DoctrineDynamicDiscriminatorMapEntities;
use IPub\DoctrineTimestampable;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_devices_module_devices",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Devices"
 *     },
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint(name="device_identifier_unique", columns={"device_identifier", "connector_id"})
 *     },
 *     indexes={
 *       @ORM\Index(name="device_identifier_idx", columns={"device_identifier"}),
 *       @ORM\Index(name="device_name_idx", columns={"device_name"})
 *     }
 * )
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="device_type", type="string", length=40)
 * @ORM\DiscriminatorMap({
 *    "device" = "FastyBird\DevicesModule\Entities\Devices\Device"
 * })
 * @ORM\MappedSuperclass
 */
abstract class Device implements IDevice, DoctrineDynamicDiscriminatorMapEntities\IDiscriminatorProvider
{

	use Entities\TEntity;
	use Entities\TEntityParams;
	use SimpleAuthEntities\TEntityOwner;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="device_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected Uuid\UuidInterface $id;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\Column(type="string", name="device_identifier", length=50, nullable=false)
	 */
	protected string $identifier;

	/**
	 * @var Common\Collections\Collection<int, IDevice>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\ManyToMany(targetEntity="FastyBird\DevicesModule\Entities\Devices\Device", inversedBy="children")
	 * @ORM\JoinTable(
	 *     name="fb_devices_module_devices_children",
	 *     joinColumns={@ORM\JoinColumn(name="child_device", referencedColumnName="device_id", onDelete="CASCADE")},
	 *     inverseJoinColumns={@ORM\JoinColumn(name="parent_device", referencedColumnName="device_id", onDelete="CASCADE")}
	 * )
	 */
	protected Common\Collections\Collection $parents;

	/**
	 * @var Common\Collections\Collection<int, IDevice>
	 *
	 * @ORM\ManyToMany(targetEntity="FastyBird\DevicesModule\Entities\Devices\Device", mappedBy="parents", cascade={"remove"}, orphanRemoval=true)
	 */
	protected Common\Collections\Collection $children;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="device_name", nullable=true, options={"default": null})
	 */
	protected ?string $name;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="text", name="device_comment", nullable=true, options={"default": null})
	 */
	protected ?string $comment = null;

	/**
	 * @var Common\Collections\Collection<int, Entities\Channels\IChannel>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Channels\Channel", mappedBy="device", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	protected Common\Collections\Collection $channels;

	/**
	 * @var Common\Collections\Collection<int, Entities\Devices\Controls\IControl>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Devices\Controls\Control", mappedBy="device", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	protected Common\Collections\Collection $controls;

	/**
	 * @var Common\Collections\Collection<int, Entities\Devices\Properties\IProperty>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Devices\Properties\Property", mappedBy="device", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	protected Common\Collections\Collection $properties;

	/**
	 * @var Common\Collections\Collection<int, Entities\Devices\Attributes\IAttribute>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Devices\Attributes\Attribute", mappedBy="device", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	protected Common\Collections\Collection $attributes;

	/**
	 * @var Entities\Connectors\IConnector
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\ManyToOne(targetEntity="FastyBird\DevicesModule\Entities\Connectors\Connector", inversedBy="devices")
	 * @ORM\JoinColumn(name="connector_id", referencedColumnName="connector_id", onDelete="CASCADE", nullable=false)
	 */
	protected Entities\Connectors\IConnector $connector;

	/**
	 * @param string $identifier
	 * @param Entities\Connectors\IConnector $connector
	 * @param string|null $name
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		string $identifier,
		Entities\Connectors\IConnector $connector,
		?string $name = null,
		?Uuid\UuidInterface $id = null
	) {
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->identifier = $identifier;
		$this->name = $name;

		$this->connector = $connector;

		$this->parents = new Common\Collections\ArrayCollection();
		$this->children = new Common\Collections\ArrayCollection();
		$this->channels = new Common\Collections\ArrayCollection();
		$this->controls = new Common\Collections\ArrayCollection();
		$this->properties = new Common\Collections\ArrayCollection();
		$this->attributes = new Common\Collections\ArrayCollection();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getParents(): array
	{
		return $this->parents->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setParents(array $parents): void
	{
		$this->parents = new Common\Collections\ArrayCollection();

		// Process all passed entities...
		/** @var IDevice $entity */
		foreach ($parents as $entity) {
			if (!$this->parents->contains($entity)) {
				// ...and assign them to collection
				$this->parents->add($entity);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function addParent(IDevice $device): void
	{
		// Check if collection does not contain inserting entity
		if (!$this->parents->contains($device)) {
			// ...and assign it to collection
			$this->parents->add($device);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeParent(IDevice $parent): void
	{
		$this->parents->removeElement($parent);
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
		/** @var IDevice $entity */
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
	public function addChild(IDevice $child): void
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
	public function removeChild(IDevice $child): void
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
	public function getChannels(): array
	{
		return $this->channels->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setChannels(array $channels = []): void
	{
		$this->channels = new Common\Collections\ArrayCollection();

		// Process all passed entities...
		/** @var Entities\Channels\IChannel $entity */
		foreach ($channels as $entity) {
			if (!$this->channels->contains($entity)) {
				// ...and assign them to collection
				$this->channels->add($entity);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function addChannel(Entities\Channels\IChannel $channel): void
	{
		// Check if collection does not contain inserting entity
		if (!$this->channels->contains($channel)) {
			// ...and assign it to collection
			$this->channels->add($channel);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getChannel(string $id): ?Entities\Channels\IChannel
	{
		$found = $this->channels
			->filter(function (Entities\Channels\IChannel $row) use ($id): bool {
				return $id === $row->getPlainId();
			});

		return $found->isEmpty() ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findChannel(string $identifier): ?Entities\Channels\IChannel
	{
		$found = $this->channels
			->filter(function (Entities\Channels\IChannel $row) use ($identifier): bool {
				return $identifier === $row->getIdentifier();
			});

		return $found->isEmpty() ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeChannel(Entities\Channels\IChannel $channel): void
	{
		// Check if collection contain removing entity...
		if ($this->channels->contains($channel)) {
			// ...and remove it from collection
			$this->channels->removeElement($channel);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getControls(): array
	{
		return $this->controls->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setControls(array $controls = []): void
	{
		$this->controls = new Common\Collections\ArrayCollection();

		// Process all passed entities...
		foreach ($controls as $entity) {
			if (!$this->controls->contains($entity)) {
				// ...and assign them to collection
				$this->controls->add($entity);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function addControl(Entities\Devices\Controls\IControl $control): void
	{
		// Check if collection does not contain inserting entity
		if (!$this->controls->contains($control)) {
			// ...and assign it to collection
			$this->controls->add($control);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getControl(string $id): ?Entities\Devices\Controls\IControl
	{
		$found = $this->controls
			->filter(function (Entities\Devices\Controls\IControl $row) use ($id): bool {
				return $id === $row->getPlainId();
			});

		return $found->isEmpty() ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findControl(string $name): ?Entities\Devices\Controls\IControl
	{
		$found = $this->controls
			->filter(function (Entities\Devices\Controls\IControl $row) use ($name): bool {
				return $name === $row->getName();
			});

		return $found->isEmpty() ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeControl(Entities\Devices\Controls\IControl $control): void
	{
		// Check if collection contain removing entity...
		if ($this->controls->contains($control)) {
			// ...and remove it from collection
			$this->controls->removeElement($control);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getProperties(): array
	{
		return $this->properties->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setProperties(array $properties = []): void
	{
		$this->properties = new Common\Collections\ArrayCollection();

		// Process all passed entities...
		foreach ($properties as $entity) {
			if (!$this->properties->contains($entity)) {
				// ...and assign them to collection
				$this->properties->add($entity);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function addProperty(Entities\Devices\Properties\IProperty $property): void
	{
		// Check if collection does not contain inserting entity
		if (!$this->properties->contains($property)) {
			// ...and assign it to collection
			$this->properties->add($property);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getProperty(string $id): ?Entities\Devices\Properties\IProperty
	{
		$found = $this->properties
			->filter(function (Entities\Devices\Properties\IProperty $row) use ($id): bool {
				return $id === $row->getPlainId();
			});

		return $found->isEmpty() ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findProperty(string $identifier): ?Entities\Devices\Properties\IProperty
	{
		$found = $this->properties
			->filter(function (Entities\Devices\Properties\IProperty $row) use ($identifier): bool {
				return $identifier === $row->getIdentifier();
			});

		return $found->isEmpty() ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeProperty(Entities\Devices\Properties\IProperty $property): void
	{
		// Check if collection contain removing entity...
		if ($this->properties->contains($property)) {
			// ...and remove it from collection
			$this->properties->removeElement($property);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAttributes(): array
	{
		return $this->attributes->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setAttributes(array $attributes = []): void
	{
		$this->attributes = new Common\Collections\ArrayCollection();

		// Process all passed entities...
		foreach ($attributes as $entity) {
			if (!$this->attributes->contains($entity)) {
				// ...and assign them to collection
				$this->attributes->add($entity);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function addAttribute(Entities\Devices\Attributes\IAttribute $attribute): void
	{
		// Check if collection does not contain inserting entity
		if (!$this->attributes->contains($attribute)) {
			// ...and assign it to collection
			$this->attributes->add($attribute);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAttribute(string $id): ?Entities\Devices\Attributes\IAttribute
	{
		$found = $this->attributes
			->filter(function (Entities\Devices\Attributes\IAttribute $row) use ($id): bool {
				return $id === $row->getPlainId();
			});

		return $found->isEmpty() ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findAttribute(string $identifier): ?Entities\Devices\Attributes\IAttribute
	{
		$found = $this->attributes
			->filter(function (Entities\Devices\Attributes\IAttribute $row) use ($identifier): bool {
				return $identifier === $row->getIdentifier();
			});

		return $found->isEmpty() ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeAttribute(Entities\Devices\Attributes\IAttribute $attribute): void
	{
		// Check if collection contain removing entity...
		if ($this->attributes->contains($attribute)) {
			// ...and remove it from collection
			$this->attributes->removeElement($attribute);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIdentifier(): string
	{
		return $this->identifier;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setName(?string $name): void
	{
		$this->name = $name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getComment(): ?string
	{
		return $this->comment;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setComment(?string $comment = null): void
	{
		$this->comment = $comment;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getConnector(): Entities\Connectors\IConnector
	{
		return $this->connector;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setConnector(Entities\Connectors\IConnector $connector): void
	{
		$this->connector = $connector;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOwnerId(): ?string
	{
		return $this->owner;
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

		$parents = [];

		foreach ($this->getParents() as $parent) {
			$parents[] = $parent->getPlainId();
		}

		return [
			'id'         => $this->getPlainId(),
			'type'       => $this->getType(),
			'identifier' => $this->getIdentifier(),
			'name'       => $this->getName(),
			'comment'    => $this->getComment(),

			'connector' => $this->getConnector()->getPlainId(),

			'parents'  => $parents,
			'children' => $children,

			'owner' => $this->getOwnerId(),
		];
	}

}
