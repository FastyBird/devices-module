<?php declare(strict_types = 1);

/**
 * Channel.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           28.07.18
 */

namespace FastyBird\DevicesModule\Entities\Channels;

use Doctrine\Common;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\DevicesModule\Entities;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use IPub\DoctrineTimestampable;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_channels",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Communication channels"
 *     },
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint(name="channel_unique", columns={"channel_channel", "device_id"})
 *     },
 *     indexes={
 *       @ORM\Index(name="channel_channel_idx", columns={"channel_channel"})
 *     }
 * )
 */
class Channel implements IChannel
{

	use DatabaseEntities\TEntity;
	use DatabaseEntities\TEntityParams;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="channel_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected $id;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\Column(type="string", name="channel_channel", length=40, nullable=false)
	 */
	private $channel;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="channel_name", nullable=true, options={"default": null})
	 */
	private $name;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="text", name="channel_comment", nullable=true, options={"default": null})
	 */
	private $comment = null;

	/**
	 * @var Common\Collections\Collection<int, Entities\Channels\Properties\IProperty>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Channels\Properties\Property", mappedBy="channel", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	private $properties;

	/**
	 * @var Common\Collections\Collection<int, Entities\Channels\Configuration\IRow>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Channels\Configuration\Row", mappedBy="channel", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	private $configuration;

	/**
	 * @var Common\Collections\Collection<int, Entities\Channels\Controls\IControl>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Channels\Controls\Control", mappedBy="channel", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	private $controls;

	/**
	 * @var Entities\Devices\IDevice
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\ManyToOne(targetEntity="FastyBird\DevicesModule\Entities\Devices\Device", inversedBy="channels")
	 * @ORM\JoinColumn(name="device_id", referencedColumnName="device_id", onDelete="CASCADE", nullable=false)
	 */
	private $device;

	/**
	 * @param Entities\Devices\IDevice $device
	 * @param string $channel
	 * @param string|null $name
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		Entities\Devices\IDevice $device,
		string $channel,
		?string $name = null,
		?Uuid\UuidInterface $id = null
	) {
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->device = $device;
		$this->channel = $channel;

		$this->name = $name;

		$this->properties = new Common\Collections\ArrayCollection();
		$this->configuration = new Common\Collections\ArrayCollection();
		$this->controls = new Common\Collections\ArrayCollection();
	}

	/**
	 * {@inheritDoc}
	 */
	public function addProperty(Entities\Channels\Properties\IProperty $property): void
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
	public function getProperty(string $id): ?Entities\Channels\Properties\IProperty
	{
		$found = $this->properties
			->filter(function (Entities\Channels\Properties\IProperty $row) use ($id): bool {
				return $id === $row->getPlainId();
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
	public function findProperty(string $property): ?Entities\Channels\Properties\IProperty
	{
		$found = $this->properties
			->filter(function (Entities\Channels\Properties\IProperty $row) use ($property): bool {
				return $property === $row->getProperty();
			});

		return $found->isEmpty() || $found->first() === false ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasSettableProperty(): bool
	{
		$found = $this->properties
			->filter(function (Entities\Channels\Properties\IProperty $row): bool {
				return $row->isSettable();
			});

		return !$found->isEmpty();
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeProperty(Entities\Channels\Properties\IProperty $property): void
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
	public function addConfiguration(Entities\Channels\Configuration\IRow $row): void
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
	public function getConfigurationRow(string $id): ?Entities\Channels\Configuration\IRow
	{
		if ($this->configuration !== null) {
			$found = $this->configuration
				->filter(function (Entities\Channels\Configuration\IRow $row) use ($id): bool {
					return $id === $row->getPlainId();
				});

			return $found->isEmpty() || $found->first() === false ? null : $found->first();
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findConfiguration(?string $configuration): ?Entities\Channels\Configuration\IRow
	{
		$found = $this->configuration
			->filter(function (Entities\Channels\Configuration\IRow $row) use ($configuration): bool {
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
	public function removeConfiguration(Entities\Channels\Configuration\IRow $property): void
	{
		// Check if collection contain removing entity...
		if ($this->configuration->contains($property)) {
			// ...and remove it from collection
			$this->configuration->removeElement($property);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function addControl(Entities\Channels\Controls\IControl $control): void
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
	public function getControl(string $name): ?Entities\Channels\Controls\IControl
	{
		if ($this->controls !== null) {
			$found = $this->controls
				->filter(function (Entities\Channels\Controls\IControl $row) use ($name): bool {
					return $name === $row->getName();
				});

			return $found->isEmpty() || $found->first() === false ? null : $found->first();
		}

		return null;
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
	public function findControl(string $name): ?Entities\Channels\Controls\IControl
	{
		$found = $this->controls
			->filter(function (Entities\Channels\Controls\IControl $row) use ($name): bool {
				return $name === $row->getName();
			});

		return $found->isEmpty() || $found->first() === false ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeControl(Entities\Channels\Controls\IControl $control): void
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
	public function toArray(): array
	{
		return [
			'id'      => $this->getPlainId(),
			'name'    => $this->getName(),
			'comment' => $this->getComment(),
			'channel' => $this->getChannel(),

			'control' => $this->getPlainControls(),

			'params' => (array) $this->getParams(),

			'device' => $this->getDevice()->getIdentifier(),
			'owner'  => $this->getDevice()->getOwnerId(),
			'parent' => $this->getDevice()->getParent() !== null ? $this->getDevice()->getParent()->getIdentifier() : null,
		];
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
	public function getChannel(): string
	{
		return $this->channel;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setChannel(string $channel): void
	{
		$this->channel = $channel;
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
	public function getDevice(): Entities\Devices\IDevice
	{
		return $this->device;
	}

}
