<?php declare(strict_types = 1);

/**
 * Device.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           29.01.17
 */

namespace FastyBird\DevicesModule\Entities\Devices;

use Consistence\Doctrine\Enum\EnumAnnotation as Enum;
use Doctrine\Common;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Types;
use FastyBird\SimpleAuth\Entities as SimpleAuthEntities;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use IPub\DoctrineTimestampable;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_devices",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Devices"
 *     },
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint(name="device_identifier_unique", columns={"device_identifier"})
 *     },
 *     indexes={
 *       @ORM\Index(name="device_identifier_idx", columns={"device_identifier"}),
 *       @ORM\Index(name="device_name_idx", columns={"device_name"}),
 *       @ORM\Index(name="device_state_idx", columns={"device_state"}),
 *       @ORM\Index(name="device_enabled_idx", columns={"device_enabled"})
 *     }
 * )
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="device_type", type="string", length=20)
 * @ORM\DiscriminatorMap({
 *    "device"   = "FastyBird\DevicesModule\Entities\Devices\Device",
 *    "local"    = "FastyBird\DevicesModule\Entities\Devices\LocalDevice",
 *    "network"  = "FastyBird\DevicesModule\Entities\Devices\NetworkDevice"
 * })
 * @ORM\MappedSuperclass
 */
abstract class Device implements IDevice
{

	use DatabaseEntities\TEntity;
	use DatabaseEntities\TEntityParams;
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
	protected $id;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 * @ORM\Column(type="string", name="device_identifier", length=50, nullable=false)
	 */
	protected $identifier;

	/**
	 * @var Entities\Devices\IDevice|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\ManyToOne(targetEntity="FastyBird\DevicesModule\Entities\Devices\Device", inversedBy="children")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="device_id", nullable=true, onDelete="SET null")
	 */
	protected $parent = null;

	/**
	 * @var Common\Collections\Collection<int, IDevice>
	 *
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Devices\Device", mappedBy="parent")
	 */
	protected $children;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="device_name", nullable=true, options={"default": null})
	 */
	protected $name = null;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="text", name="device_comment", nullable=true, options={"default": null})
	 */
	protected $comment = null;

	/**
	 * @var Types\DeviceConnectionState
	 *
	 * @Enum(class=Types\DeviceConnectionState::class)
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string_enum", name="device_state", nullable=false, options={"default": "unknown"})
	 */
	protected $state;

	/**
	 * @var bool
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="boolean", name="device_enabled", length=1, nullable=false, options={"default": true})
	 */
	protected $enabled = true;

	/**
	 * @var Common\Collections\Collection<int, Entities\Channels\IChannel>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Channels\Channel", mappedBy="device", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	protected $channels;

	/**
	 * @var Common\Collections\Collection<int, Entities\Devices\Controls\IControl>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Devices\Controls\Control", mappedBy="device", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	protected $controls;

	/**
	 * @var Common\Collections\Collection<int, Entities\Devices\Properties\IProperty>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Devices\Properties\Property", mappedBy="device", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	protected $properties;

	/**
	 * @var Common\Collections\Collection<int, Entities\Devices\Configuration\IRow>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Devices\Configuration\Row", mappedBy="device", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	protected $configuration;

	/**
	 * @param string $identifier
	 * @param string|null $name
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		string $identifier,
		?string $name,
		?Uuid\UuidInterface $id = null
	) {
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->identifier = $identifier;
		$this->name = $name;

		$this->state = Types\DeviceConnectionState::get(Types\DeviceConnectionState::STATE_UNKNOWN);

		$this->children = new Common\Collections\ArrayCollection();
		$this->channels = new Common\Collections\ArrayCollection();
		$this->controls = new Common\Collections\ArrayCollection();
		$this->properties = new Common\Collections\ArrayCollection();
		$this->configuration = new Common\Collections\ArrayCollection();
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
	public function setParent(IDevice $device): void
	{
		$this->parent = $device;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getParent(): ?IDevice
	{
		return $this->parent;
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
	public function getChildren(): array
	{
		return $this->children->toArray();
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
	public function setName(?string $name): void
	{
		$this->name = $name;
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
	public function setComment(?string $comment = null): void
	{
		$this->comment = $comment;
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
	public function setState(string $state): void
	{
		if (!Types\DeviceConnectionState::isValidValue($state)) {
			throw new Exceptions\InvalidArgumentException(sprintf('Provided device state "%s" is not valid', $state));
		}

		$this->state = Types\DeviceConnectionState::get($state);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getState(): Types\DeviceConnectionState
	{
		return $this->state;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setEnabled(bool $enabled): void
	{
		$this->enabled = $enabled;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isEnabled(): bool
	{
		return $this->enabled;
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
	public function getChannels(): array
	{
		return $this->channels->toArray();
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

		return $found->isEmpty() || $found->first() === false ? null : $found->first();
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
	public function getControls(): array
	{
		return $this->controls->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getControl(string $name): ?Entities\Devices\Controls\IControl
	{
		$found = $this->controls
			->filter(function (Entities\Devices\Controls\IControl $row) use ($name): bool {
				return $name === $row->getName();
			});

		return $found->isEmpty() || $found->first() === false ? null : $found->first();
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

		return $found->isEmpty() || $found->first() === false ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasControl(string $name): bool
	{
		return $this->findControl($name) !== null;
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
	public function getProperties(): array
	{
		return $this->properties->toArray();
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

		return $found->isEmpty() || $found->first() === false ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findProperty(string $property): ?Entities\Devices\Properties\IProperty
	{
		$found = $this->properties
			->filter(function (Entities\Devices\Properties\IProperty $row) use ($property): bool {
				return $property === $row->getProperty();
			});

		return $found->isEmpty() || $found->first() === false ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasProperty(string $property): bool
	{
		return $this->findProperty($property) !== null;
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
	public function setConfiguration(array $configuration = []): void
	{
		$this->configuration = new Common\Collections\ArrayCollection();

		// Process all passed entities...
		foreach ($configuration as $entity) {
			if (!$this->configuration->contains($entity)) {
				// ...and assign them to collection
				$this->configuration->add($entity);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function addConfiguration(Entities\Devices\Configuration\IRow $row): void
	{
		// Check if collection does not contain inserting entity
		if (!$this->configuration->contains($row)) {
			// ...and assign it to collection
			$this->configuration->add($row);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getConfiguration(): array
	{
		return $this->configuration->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getConfigurationRow(string $id): ?Entities\Devices\Configuration\IRow
	{
		$found = $this->configuration
			->filter(function (Entities\Devices\Configuration\IRow $row) use ($id): bool {
				return $id === $row->getPlainId();
			});

		return $found->isEmpty() || $found->first() === false ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findConfiguration(?string $configuration): ?Entities\Devices\Configuration\IRow
	{
		$found = $this->configuration
			->filter(function (Entities\Devices\Configuration\IRow $row) use ($configuration): bool {
				return $configuration === $row->getConfiguration();
			});

		return $found->isEmpty() || $found->first() === false ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasConfiguration(): bool
	{
		return $this->configuration->count() > 0;
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeConfiguration(Entities\Devices\Configuration\IRow $stat): void
	{
		// Check if collection contain removing entity...
		if ($this->configuration->contains($stat)) {
			// ...and remove it from collection
			$this->configuration->removeElement($stat);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOwnerId(): ?string
	{
		if ($this->parent !== null) {
			return $this->parent->getOwnerId();
		}

		return $this->owner;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'id'         => $this->getPlainId(),
			'identifier' => $this->getIdentifier(),
			'parent'     => $this->getParent() !== null ? $this->getParent()->getIdentifier() : null,
			'name'       => $this->getName(),
			'comment'    => $this->getComment(),
			'state'      => $this->getState()->getValue(),
			'enabled'    => $this->isEnabled(),

			'control' => $this->getPlainControls(),

			'params' => (array) $this->getParams(),

			'device' => $this->getIdentifier(),
			'owner'  => $this->getOwnerId(),
		];
	}

	/**
	 * @return string[]
	 */
	private function getPlainControls(): array
	{
		$controls = [];

		foreach ($this->getControls() as $control) {
			$controls[] = $control->getName();
		}

		return $controls;
	}

}
