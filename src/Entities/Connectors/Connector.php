<?php declare(strict_types = 1);

/**
 * Connector.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           17.01.20
 */

namespace FastyBird\DevicesModule\Entities\Connectors;

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
 *     name="fb_devices_module_connectors",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Communication connectors"
 *     },
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint(name="connector_identifier_unique", columns={"connector_identifier"})
 *     },
 *     indexes={
 *       @ORM\Index(name="connector_identifier_idx", columns={"connector_identifier"}),
 *       @ORM\Index(name="connector_name_idx", columns={"connector_name"}),
 *       @ORM\Index(name="connector_enabled_idx", columns={"connector_enabled"})
 *     }
 * )
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="connector_type", type="string", length=40)
 * @ORM\DiscriminatorMap({
 *    "connector" = "FastyBird\DevicesModule\Entities\Connectors\Connector"
 * })
 * @ORM\MappedSuperclass
 */
abstract class Connector implements IConnector, DoctrineDynamicDiscriminatorMapEntities\IDiscriminatorProvider
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
	 * @ORM\Column(type="uuid_binary", name="connector_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected Uuid\UuidInterface $id;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\Column(type="string", name="connector_identifier", length=50, nullable=false)
	 */
	protected string $identifier;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="connector_name", nullable=true, options={"default": null})
	 */
	protected ?string $name = null;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="text", name="connector_comment", nullable=true, options={"default": null})
	 */
	protected ?string $comment = null;

	/**
	 * @var bool
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="boolean", name="connector_enabled", length=1, nullable=false, options={"default": true})
	 */
	protected bool $enabled = true;

	/**
	 * @var Common\Collections\Collection<int, Entities\Devices\IDevice>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Devices\Device", mappedBy="connector", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	protected Common\Collections\Collection $devices;

	/**
	 * @var Common\Collections\Collection<int, Entities\Connectors\Properties\IProperty>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Connectors\Properties\Property", mappedBy="connector", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	private Common\Collections\Collection $properties;

	/**
	 * @var Common\Collections\Collection<int, Entities\Connectors\Controls\IControl>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Connectors\Controls\Control", mappedBy="connector", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	protected Common\Collections\Collection $controls;

	/**
	 * @param string $identifier
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		string $identifier,
		?Uuid\UuidInterface $id = null
	) {
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->identifier = $identifier;

		$this->devices = new Common\Collections\ArrayCollection();
		$this->properties = new Common\Collections\ArrayCollection();
		$this->controls = new Common\Collections\ArrayCollection();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDevices(): array
	{
		return $this->devices->toArray();
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
	public function addProperty(Entities\Connectors\Properties\IProperty $property): void
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
	public function getProperty(string $id): ?Entities\Connectors\Properties\IProperty
	{
		$found = $this->properties
			->filter(function (Entities\Connectors\Properties\IProperty $row) use ($id): bool {
				return $id === $row->getPlainId();
			});

		return $found->isEmpty() ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeProperty(Entities\Connectors\Properties\IProperty $property): void
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
	public function hasProperty(string $property): bool
	{
		return $this->findProperty($property) !== null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findProperty(string $property): ?Entities\Connectors\Properties\IProperty
	{
		$found = $this->properties
			->filter(function (Entities\Connectors\Properties\IProperty $row) use ($property): bool {
				return $property === $row->getIdentifier();
			});

		return $found->isEmpty() ? null : $found->first();
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
	public function addControl(Entities\Connectors\Controls\IControl $control): void
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
	public function getControl(string $name): ?Entities\Connectors\Controls\IControl
	{
		$found = $this->controls
			->filter(function (Entities\Connectors\Controls\IControl $row) use ($name): bool {
				return $name === $row->getName();
			});

		return $found->isEmpty() ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeControl(Entities\Connectors\Controls\IControl $control): void
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
	public function hasControl(string $name): bool
	{
		return $this->findControl($name) !== null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findControl(string $name): ?Entities\Connectors\Controls\IControl
	{
		$found = $this->controls
			->filter(function (Entities\Connectors\Controls\IControl $row) use ($name): bool {
				return $name === $row->getName();
			});

		return $found->isEmpty() ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'id'         => $this->getPlainId(),
			'type'       => $this->getType(),
			'identifier' => $this->getIdentifier(),
			'name'       => $this->getName(),
			'comment'    => $this->getComment(),
			'enabled'    => $this->isEnabled(),

			'owner' => $this->getOwnerId(),
		];
	}

	/**
	 * {@inheritDoc}
	 */
	abstract public function getType(): string;

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

}
