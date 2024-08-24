<?php declare(strict_types = 1);

/**
 * Channel.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          1.0.0
 *
 * @date           28.07.18
 */

namespace FastyBird\Module\Devices\Entities\Channels;

use DateTimeInterface;
use Doctrine\Common;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use FastyBird\Module\Devices\Entities;
use FastyBird\Module\Devices\Types;
use IPub\DoctrineCrud\Mapping\Attribute as IPubDoctrine;
use IPub\DoctrineTimestampable;
use Nette\Utils;
use Ramsey\Uuid;
use function array_map;

#[ORM\Entity]
#[ORM\Table(
	name: 'fb_devices_module_channels',
	options: [
		'collate' => 'utf8mb4_general_ci',
		'charset' => 'utf8mb4',
		'comment' => 'Device channels',
	],
)]
#[ORM\Index(columns: ['channel_identifier'], name: 'channel_identifier_idx')]
#[ORM\UniqueConstraint(name: 'channel_identifier_unique', columns: ['channel_identifier', 'device_id'])]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'channel_type', type: 'string', length: 100)]
#[ORM\MappedSuperclass]
abstract class Channel implements Entities\Entity,
	Entities\EntityParams,
	DoctrineTimestampable\Entities\IEntityCreated, DoctrineTimestampable\Entities\IEntityUpdated
{

	use Entities\TEntity;
	use Entities\TEntityParams;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	#[ORM\Id]
	#[ORM\Column(name: 'channel_id', type: Uuid\Doctrine\UuidBinaryType::NAME)]
	#[ORM\CustomIdGenerator(class: Uuid\Doctrine\UuidGenerator::class)]
	protected Uuid\UuidInterface $id;

	#[IPubDoctrine\Crud(writable: true)]
	#[ORM\Column(
		name: 'channel_category',
		type: 'string',
		length: 100,
		nullable: false,
		enumType: Types\ChannelCategory::class,
		options: ['default' => Types\ChannelCategory::GENERIC],
	)]
	protected Types\ChannelCategory $category;

	#[IPubDoctrine\Crud(required: true)]
	#[ORM\Column(name: 'channel_identifier', type: 'string', length: 50, nullable: false)]
	protected string $identifier;

	#[IPubDoctrine\Crud(writable: true)]
	#[ORM\Column(name: 'channel_name', type: 'string', nullable: true, options: ['default' => null])]
	protected string|null $name;

	#[IPubDoctrine\Crud(writable: true)]
	#[ORM\Column(name: 'channel_comment', type: 'text', nullable: true, options: ['default' => null])]
	protected string|null $comment = null;

	/** @var Common\Collections\Collection<int, Entities\Channels\Properties\Property> */
	#[IPubDoctrine\Crud(writable: true)]
	#[ORM\OneToMany(
		mappedBy: 'channel',
		targetEntity: Entities\Channels\Properties\Property::class,
		cascade: ['persist', 'remove'],
		orphanRemoval: true,
	)]
	protected Common\Collections\Collection $properties;

	/** @var Common\Collections\Collection<int, Entities\Channels\Controls\Control> */
	#[IPubDoctrine\Crud(writable: true)]
	#[ORM\OneToMany(
		mappedBy: 'channel',
		targetEntity: Entities\Channels\Controls\Control::class,
		cascade: ['persist', 'remove'],
		orphanRemoval: true,
	)]
	protected Common\Collections\Collection $controls;

	#[IPubDoctrine\Crud(required: true)]
	#[ORM\ManyToOne(
		targetEntity: Entities\Devices\Device::class,
		cascade: ['persist'],
		inversedBy: 'channels',
	)]
	#[ORM\JoinColumn(
		name: 'device_id',
		referencedColumnName: 'device_id',
		nullable: false,
		onDelete: 'CASCADE',
	)]
	protected Entities\Devices\Device $device;

	public function __construct(
		Entities\Devices\Device $device,
		string $identifier,
		string|null $name = null,
		Uuid\UuidInterface|null $id = null,
	)
	{
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->device = $device;
		$this->identifier = $identifier;

		$this->name = $name;

		$this->category = Types\ChannelCategory::GENERIC;

		$this->properties = new Common\Collections\ArrayCollection();
		$this->controls = new Common\Collections\ArrayCollection();

		$device->addChannel($this);
	}

	abstract public static function getType(): string;

	public function getCategory(): Types\ChannelCategory
	{
		return $this->category;
	}

	public function setCategory(Types\ChannelCategory $category): void
	{
		$this->category = $category;
	}

	public function getIdentifier(): string
	{
		return $this->identifier;
	}

	public function setIdentifier(string $identifier): void
	{
		$this->identifier = $identifier;
	}

	public function getName(): string|null
	{
		return $this->name;
	}

	public function setName(string|null $name): void
	{
		$this->name = $name;
	}

	public function getComment(): string|null
	{
		return $this->comment;
	}

	public function setComment(string|null $comment = null): void
	{
		$this->comment = $comment;
	}

	public function getDevice(): Entities\Devices\Device
	{
		return $this->device;
	}

	/**
	 * @return array<Entities\Channels\Properties\Property>
	 */
	public function getProperties(): array
	{
		return $this->properties->toArray();
	}

	/**
	 * @param array<Entities\Channels\Properties\Property> $properties
	 */
	public function setProperties(array $properties = []): void
	{
		$this->properties = new Common\Collections\ArrayCollection();

		// Process all passed entities...
		foreach ($properties as $entity) {
			// ...and assign them to collection
			$this->addProperty($entity);
		}
	}

	public function addProperty(Entities\Channels\Properties\Property $property): void
	{
		// Check if collection does not contain inserting entity
		if (!$this->properties->contains($property)) {
			// ...and assign it to collection
			$this->properties->add($property);
		}
	}

	/**
	 * @return array<Entities\Channels\Controls\Control>
	 */
	public function getControls(): array
	{
		return $this->controls->toArray();
	}

	/**
	 * @param array<Entities\Channels\Controls\Control> $controls
	 */
	public function setControls(array $controls = []): void
	{
		$this->controls = new Common\Collections\ArrayCollection();

		// Process all passed entities...
		foreach ($controls as $entity) {
			// ...and assign them to collection
			$this->addControl($entity);
		}
	}

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
	public function toArray(): array
	{
		return [
			'id' => $this->getId()->toString(),
			'type' => static::getType(),
			'category' => $this->getCategory()->value,
			'identifier' => $this->getIdentifier(),
			'name' => $this->getName(),
			'comment' => $this->getComment(),

			'properties' => array_map(
				static fn (Entities\Channels\Properties\Property $property): string => $property->getId()->toString(),
				$this->getProperties(),
			),
			'controls' => array_map(
				static fn (Entities\Channels\Controls\Control $control): string => $control->getId()->toString(),
				$this->getControls(),
			),
			'device' => $this->getDevice()->getId()->toString(),
			'connector' => $this->getDevice()->getConnector()->getId()->toString(),

			'owner' => $this->getDevice()->getOwnerId(),
			'created_at' => $this->getCreatedAt()?->format(DateTimeInterface::ATOM),
			'updated_at' => $this->getUpdatedAt()?->format(DateTimeInterface::ATOM),
		];
	}

	public function getSource(): MetadataTypes\Sources\Source
	{
		return MetadataTypes\Sources\Module::DEVICES;
	}

	/**
	 * @throws Utils\JsonException
	 */
	public function __toString(): string
	{
		return Utils\Json::encode($this->toArray());
	}

}
