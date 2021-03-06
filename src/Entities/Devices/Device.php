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

use Consistence\Doctrine\Enum\EnumAnnotation as Enum;
use Doctrine\Common;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;
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
 *       @ORM\UniqueConstraint(name="device_identifier_unique", columns={"device_identifier"}),
 *       @ORM\UniqueConstraint(name="device_key_unique", columns={"device_key"})
 *     },
 *     indexes={
 *       @ORM\Index(name="device_identifier_idx", columns={"device_identifier"}),
 *       @ORM\Index(name="device_name_idx", columns={"device_name"}),
 *       @ORM\Index(name="device_state_idx", columns={"device_state"}),
 *       @ORM\Index(name="device_enabled_idx", columns={"device_enabled"})
 *     }
 * )
 */
class Device implements IDevice
{

	use Entities\TKey;
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
	private string $identifier;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", name="device_key", length=50, nullable=false)
	 */
	private string $key;

	/**
	 * @var Entities\Devices\IDevice|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\ManyToOne(targetEntity="FastyBird\DevicesModule\Entities\Devices\Device", inversedBy="children")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="device_id", nullable=true, onDelete="SET null")
	 */
	private ?Entities\Devices\IDevice $parent = null;

	/**
	 * @var Common\Collections\Collection<int, IDevice>
	 *
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Devices\Device", mappedBy="parent")
	 */
	private Common\Collections\Collection $children;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="device_name", nullable=true, options={"default": null})
	 */
	private ?string $name;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="text", name="device_comment", nullable=true, options={"default": null})
	 */
	private ?string $comment = null;

	/**
	 * @var ModulesMetadataTypes\DeviceConnectionStateType
	 *
	 * @Enum(class=ModulesMetadataTypes\DeviceConnectionStateType::class)
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string_enum", name="device_state", nullable=false, options={"default": "unknown"})
	 */
	private $state;

	/**
	 * @var bool
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="boolean", name="device_enabled", length=1, nullable=false, options={"default": true})
	 */
	private bool $enabled = true;

	/**
	 * @var ModulesMetadataTypes\HardwareManufacturerType
	 *
	 * @Enum(class=ModulesMetadataTypes\HardwareManufacturerType::class)
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="device_hardware_manufacturer", length=150, nullable=false, options={"default": "generic"})
	 */
	private $hardwareManufacturer;

	/**
	 * @var ModulesMetadataTypes\DeviceModelType
	 *
	 * @Enum(class=ModulesMetadataTypes\DeviceModelType::class)
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="device_hardware_model", length=150, nullable=false, options={"default": "custom"})
	 */
	private $hardwareModel;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="device_hardware_version", length=150, nullable=true, options={"default": null})
	 */
	private ?string $hardwareVersion = null;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="device_mac_address", length=150, nullable=true, options={"default": null})
	 */
	private ?string $macAddress = null;

	/**
	 * @var ModulesMetadataTypes\FirmwareManufacturerType
	 *
	 * @Enum(class=ModulesMetadataTypes\FirmwareManufacturerType::class)
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="device_firmware_manufacturer", length=150, nullable=false, options={"default": "generic"})
	 */
	private $firmwareManufacturer;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="device_firmware_version", length=150, nullable=true, options={"default": null})
	 */
	private ?string $firmwareVersion = null;

	/**
	 * @var Common\Collections\Collection<int, Entities\Channels\IChannel>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Channels\Channel", mappedBy="device", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	private Common\Collections\Collection $channels;

	/**
	 * @var Common\Collections\Collection<int, Entities\Devices\Controls\IControl>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Devices\Controls\Control", mappedBy="device", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	private Common\Collections\Collection $controls;

	/**
	 * @var Common\Collections\Collection<int, Entities\Devices\Properties\IProperty>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Devices\Properties\Property", mappedBy="device", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	private Common\Collections\Collection $properties;

	/**
	 * @var Common\Collections\Collection<int, Entities\Devices\Configuration\IRow>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Devices\Configuration\Row", mappedBy="device", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	private Common\Collections\Collection $configuration;

	/**
	 * @var Entities\Devices\Connectors\IConnector|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToOne(targetEntity="FastyBird\DevicesModule\Entities\Devices\Connectors\Connector", mappedBy="device", cascade={"persist", "remove"})
	 */
	private ?Entities\Devices\Connectors\IConnector $connector = null;

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

		$this->state = ModulesMetadataTypes\DeviceConnectionStateType::get(ModulesMetadataTypes\DeviceConnectionStateType::STATE_UNKNOWN);

		$this->hardwareManufacturer = ModulesMetadataTypes\HardwareManufacturerType::get(ModulesMetadataTypes\HardwareManufacturerType::MANUFACTURER_GENERIC);
		$this->hardwareModel = ModulesMetadataTypes\DeviceModelType::get(ModulesMetadataTypes\DeviceModelType::MODEL_CUSTOM);

		$this->firmwareManufacturer = ModulesMetadataTypes\FirmwareManufacturerType::get(ModulesMetadataTypes\FirmwareManufacturerType::MANUFACTURER_GENERIC);

		$this->children = new Common\Collections\ArrayCollection();
		$this->channels = new Common\Collections\ArrayCollection();
		$this->controls = new Common\Collections\ArrayCollection();
		$this->properties = new Common\Collections\ArrayCollection();
		$this->configuration = new Common\Collections\ArrayCollection();
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
	public function getControl(string $name): ?Entities\Devices\Controls\IControl
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
	public function hasControl(string $name): bool
	{
		return $this->findControl($name) !== null;
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
	public function hasProperty(string $property): bool
	{
		return $this->findProperty($property) !== null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findProperty(string $property): ?Entities\Devices\Properties\IProperty
	{
		$found = $this->properties
			->filter(function (Entities\Devices\Properties\IProperty $row) use ($property): bool {
				return $property === $row->getIdentifier();
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
	public function getConfigurationRow(string $id): ?Entities\Devices\Configuration\IRow
	{
		$found = $this->configuration
			->filter(function (Entities\Devices\Configuration\IRow $row) use ($id): bool {
				return $id === $row->getPlainId();
			});

		return $found->isEmpty() ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findConfiguration(?string $configuration): ?Entities\Devices\Configuration\IRow
	{
		$found = $this->configuration
			->filter(function (Entities\Devices\Configuration\IRow $row) use ($configuration): bool {
				return $configuration === $row->getIdentifier();
			});

		return $found->isEmpty() ? null : $found->first();
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
	public function toArray(): array
	{
		return [
			'id'         => $this->getPlainId(),
			'key'        => $this->getKey(),
			'identifier' => $this->getIdentifier(),
			'parent'     => $this->getParent() !== null ? $this->getParent()->getIdentifier() : null,
			'name'       => $this->getName(),
			'comment'    => $this->getComment(),
			'state'      => $this->getState()->getValue(),
			'enabled'    => $this->isEnabled(),

			'hardware_version'      => $this->getHardwareVersion(),
			'hardware_manufacturer' => $this->getHardwareManufacturer()->getValue(),
			'hardware_model'        => $this->getHardwareModel()->getValue(),
			'mac_address'           => $this->getMacAddress(),

			'firmware_manufacturer' => $this->getFirmwareManufacturer()->getValue(),
			'firmware_version'      => $this->getFirmwareVersion(),

			'control' => $this->getPlainControls(),

			'params' => (array) $this->getParams(),

			'owner'  => $this->getOwnerId(),
		];
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
	public function getParent(): ?IDevice
	{
		return $this->parent;
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
	public function getState(): ModulesMetadataTypes\DeviceConnectionStateType
	{
		return $this->state;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setState(string $state): void
	{
		if (!ModulesMetadataTypes\DeviceConnectionStateType::isValidValue($state)) {
			throw new Exceptions\InvalidArgumentException(sprintf('Provided device state "%s" is not valid', $state));
		}

		$this->state = ModulesMetadataTypes\DeviceConnectionStateType::get($state);
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
	public function setEnabled(bool $enabled): void
	{
		$this->enabled = $enabled;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHardwareVersion(): ?string
	{
		return $this->hardwareVersion;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setHardwareVersion(?string $version): void
	{
		$this->hardwareVersion = $version;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHardwareManufacturer(): ModulesMetadataTypes\HardwareManufacturerType
	{
		return $this->hardwareManufacturer;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setHardwareManufacturer(?string $manufacturer): void
	{
		if ($manufacturer !== null && ModulesMetadataTypes\HardwareManufacturerType::isValidValue(strtolower($manufacturer))) {
			$this->hardwareManufacturer = ModulesMetadataTypes\HardwareManufacturerType::get(strtolower($manufacturer));

		} else {
			$this->hardwareManufacturer = ModulesMetadataTypes\HardwareManufacturerType::get(ModulesMetadataTypes\HardwareManufacturerType::MANUFACTURER_GENERIC);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHardwareModel(): ModulesMetadataTypes\DeviceModelType
	{
		return $this->hardwareModel;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setHardwareModel(?string $model): void
	{
		if ($model !== null && ModulesMetadataTypes\DeviceModelType::isValidValue(strtolower($model))) {
			$this->hardwareModel = ModulesMetadataTypes\DeviceModelType::get(strtolower($model));

		} else {
			$this->hardwareModel = ModulesMetadataTypes\DeviceModelType::get(ModulesMetadataTypes\DeviceModelType::MODEL_CUSTOM);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMacAddress(string $separator = ':'): ?string
	{
		return $this->macAddress !== null ? implode($separator, str_split($this->macAddress, 2)) : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setMacAddress(?string $macAddress): void
	{
		if (
			$macAddress !== null
			&& preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $macAddress) === 0
			&& preg_match('/^([0-9A-Fa-f]{12})$/', $macAddress) === 0
		) {
			throw new Exceptions\InvalidArgumentException('Provided mac address is not in valid format.');
		}

		$this->macAddress = $macAddress !== null ? strtolower(str_replace([':', '-'], '', $macAddress)) : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFirmwareManufacturer(): ModulesMetadataTypes\FirmwareManufacturerType
	{
		return $this->firmwareManufacturer;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setFirmwareManufacturer(?string $manufacturer): void
	{
		if ($manufacturer !== null && ModulesMetadataTypes\FirmwareManufacturerType::isValidValue(strtolower($manufacturer))) {
			$this->firmwareManufacturer = ModulesMetadataTypes\FirmwareManufacturerType::get(strtolower($manufacturer));

		} else {
			$this->firmwareManufacturer = ModulesMetadataTypes\FirmwareManufacturerType::get(ModulesMetadataTypes\FirmwareManufacturerType::MANUFACTURER_GENERIC);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFirmwareVersion(): ?string
	{
		return $this->firmwareVersion;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setFirmwareVersion(?string $version): void
	{
		$this->firmwareVersion = $version;
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
	public function setConnector(?Entities\Devices\Connectors\IConnector $connector): void
	{
		$this->connector = $connector;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getConnector(): ?Entities\Devices\Connectors\IConnector
	{
		return $this->connector;
	}

}
