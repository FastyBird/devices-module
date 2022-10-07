<?php declare(strict_types = 1);

/**
 * Channel.php
 *
 * @license        More in LICENSE.md
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
use FastyBird\DevicesModule\Entities;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use IPub\DoctrineTimestampable;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_devices_module_channels",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Device channels"
 *     },
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint(name="channel_identifier_unique", columns={"channel_identifier", "device_id"})
 *     },
 *     indexes={
 *       @ORM\Index(name="channel_identifier_idx", columns={"channel_identifier"})
 *     }
 * )
 */
class Channel implements Entities\Entity,
	Entities\EntityParams,
	DoctrineTimestampable\Entities\IEntityCreated, DoctrineTimestampable\Entities\IEntityUpdated
{

	use Entities\TEntity;
	use Entities\TEntityParams;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="channel_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected Uuid\UuidInterface $id;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\Column(type="string", name="channel_identifier", length=50, nullable=false)
	 */
	private string $identifier;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="channel_name", nullable=true, options={"default": null})
	 */
	private ?string $name = null;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="text", name="channel_comment", nullable=true, options={"default": null})
	 */
	private ?string $comment = null;

	/**
	 * @var Common\Collections\Collection<int, Entities\Channels\Properties\Property>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Channels\Properties\Property", mappedBy="channel", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	private Common\Collections\Collection $properties;

	/**
	 * @var Common\Collections\Collection<int, Entities\Channels\Controls\Control>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Channels\Controls\Control", mappedBy="channel", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	private Common\Collections\Collection $controls;

	/**
	 * @var Entities\Devices\Device
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\ManyToOne(targetEntity="FastyBird\DevicesModule\Entities\Devices\Device", inversedBy="channels")
	 * @ORM\JoinColumn(name="device_id", referencedColumnName="device_id", onDelete="CASCADE", nullable=false)
	 */
	private Entities\Devices\Device $device;

	/**
	 * @param Entities\Devices\Device $device
	 * @param string $identifier
	 * @param string|null $name
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		Entities\Devices\Device $device,
		string $identifier,
		?string $name = null,
		?Uuid\UuidInterface $id = null
	) {
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->device = $device;
		$this->identifier = $identifier;

		$this->name = $name;

		$this->properties = new Common\Collections\ArrayCollection();
		$this->controls = new Common\Collections\ArrayCollection();
	}

	/**
	 * @return Entities\Channels\Properties\Property[]
	 */
	public function getProperties(): array
	{
		return $this->properties->toArray();
	}

	/**
	 * @param Entities\Channels\Properties\Property[] $properties
	 *
	 * @return void
	 */
	public function setProperties(array $properties = []): void
	{
		$this->properties = new Common\Collections\ArrayCollection();

		// Process all passed entities...
		foreach ($properties as $entity) {
			if ($this->properties->contains($entity) === false) {
				// ...and assign them to collection
				$this->properties->add($entity);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function addProperty(Entities\Channels\Properties\Property $property): void
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
	public function getProperty(string $id): ?Entities\Channels\Properties\Property
	{
		$found = $this->properties
			->filter(function (Entities\Channels\Properties\Property $row) use ($id): bool {
				return $id === $row->getPlainId();
			});

		return $found->isEmpty() === true || $found->first() === false ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findProperty(string $identifier): ?Entities\Channels\Properties\Property
	{
		$found = $this->properties
			->filter(function (Entities\Channels\Properties\Property $row) use ($identifier): bool {
				return $identifier === $row->getIdentifier();
			});

		return $found->isEmpty() === true || $found->first() === false ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeProperty(Entities\Channels\Properties\Property $property): void
	{
		// Check if collection contain removing entity...
		if ($this->properties->contains($property)) {
			// ...and remove it from collection
			$this->properties->removeElement($property);
		}
	}

	/**
	 * @return Entities\Channels\Controls\Control[]
	 */
	public function getControls(): array
	{
		return $this->controls->toArray();
	}

	/**
	 * @param Entities\Channels\Controls\Control[] $controls
	 *
	 * @return void
	 */
	public function setControls(array $controls = []): void
	{
		$this->controls = new Common\Collections\ArrayCollection();

		// Process all passed entities...
		foreach ($controls as $entity) {
			if ($this->controls->contains($entity) === false) {
				// ...and assign them to collection
				$this->controls->add($entity);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function addControl(Entities\Channels\Controls\Control $control): void
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
	public function getControl(string $id): ?Entities\Channels\Controls\Control
	{
		$found = $this->controls
			->filter(function (Entities\Channels\Controls\Control $row) use ($id): bool {
				return $id === $row->getPlainId();
			});

		return $found->isEmpty() === true || $found->first() === false ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findControl(string $name): ?Entities\Channels\Controls\Control
	{
		$found = $this->controls
			->filter(function (Entities\Channels\Controls\Control $row) use ($name): bool {
				return $name === $row->getName();
			});

		return $found->isEmpty() === true || $found->first() === false ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeControl(Entities\Channels\Controls\Control $control): void
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
			'id'         => $this->getPlainId(),
			'identifier' => $this->getIdentifier(),
			'name'       => $this->getName(),
			'comment'    => $this->getComment(),

			'device' => $this->getDevice()->getPlainId(),

			'owner' => $this->getDevice()->getOwnerId(),
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
	public function setIdentifier(string $identifier): void
	{
		$this->identifier = $identifier;
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
	public function getDevice(): Entities\Devices\Device
	{
		return $this->device;
	}

}
