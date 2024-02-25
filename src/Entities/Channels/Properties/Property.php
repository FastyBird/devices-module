<?php declare(strict_types = 1);

/**
 * Property.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          1.0.0
 *
 * @date           26.10.18
 */

namespace FastyBird\Module\Devices\Entities\Channels\Properties;

use Doctrine\Common;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\Library\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Module\Devices\Entities;
use FastyBird\Module\Devices\Exceptions;
use IPub\DoctrineCrud\Mapping\Attribute as IPubDoctrine;
use Nette\Utils;
use Ramsey\Uuid;
use TypeError;
use ValueError;
use function array_merge;

#[ORM\Entity]
#[ORM\Table(
	name: 'fb_devices_module_channels_properties',
	options: [
		'collate' => 'utf8mb4_general_ci',
		'charset' => 'utf8mb4',
		'comment' => 'Device channels properties',
	],
)]
#[ORM\Index(columns: ['property_identifier'], name: 'property_identifier_idx')]
#[ORM\Index(columns: ['property_settable'], name: 'property_settable_idx')]
#[ORM\Index(columns: ['property_queryable'], name: 'property_queryable_idx')]
#[ORM\UniqueConstraint(name: 'property_identifier_unique', columns: ['property_identifier', 'channel_id'])]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'property_type', type: 'string', length: 100)]
#[ORM\DiscriminatorMap([
	Entities\Channels\Properties\Variable::TYPE => Entities\Channels\Properties\Variable::class,
	Entities\Channels\Properties\Dynamic::TYPE => Entities\Channels\Properties\Dynamic::class,
	Entities\Channels\Properties\Mapped::TYPE => Entities\Channels\Properties\Mapped::class,
])]
#[ORM\MappedSuperclass]
abstract class Property extends Entities\Property
{

	#[IPubDoctrine\Crud(required: true)]
	#[ORM\ManyToOne(
		targetEntity: Entities\Channels\Channel::class,
		cascade: ['persist'],
		inversedBy: 'properties',
	)]
	#[ORM\JoinColumn(
		name: 'channel_id',
		referencedColumnName: 'channel_id',
		nullable: false,
		onDelete: 'CASCADE',
	)]
	protected Entities\Channels\Channel $channel;

	#[IPubDoctrine\Crud(writable: true)]
	#[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
	#[ORM\JoinColumn(
		name: 'parent_id',
		referencedColumnName: 'property_id',
		nullable: true,
		onDelete: 'CASCADE',
	)]
	protected self|null $parent = null;

	/** @var Common\Collections\Collection<int, Property> */
	#[ORM\OneToMany(
		mappedBy: 'parent',
		targetEntity: self::class,
		cascade: ['remove'],
		orphanRemoval: true,
	)]
	protected Common\Collections\Collection $children;

	public function __construct(
		Entities\Channels\Channel $channel,
		string $identifier,
		Uuid\UuidInterface|null $id = null,
	)
	{
		parent::__construct($identifier, $id);

		$this->channel = $channel;

		$channel->addProperty($this);

		$this->children = new Common\Collections\ArrayCollection();
	}

	public function getParent(): self|null
	{
		return $this->parent;
	}

	public function setParent(self $property): void
	{
		$this->parent = $property;
	}

	public function removeParent(): void
	{
		$this->parent = null;
	}

	/**
	 * @return array<Property>
	 */
	public function getChildren(): array
	{
		return $this->children->toArray();
	}

	/**
	 * @param array<Property> $children
	 */
	public function setChildren(array $children): void
	{
		$this->children = new Common\Collections\ArrayCollection();

		foreach ($children as $entity) {
			// ...and assign them to collection
			$this->addChild($entity);
		}
	}

	public function addChild(self $child): void
	{
		// Check if collection does not contain inserting entity
		if (!$this->children->contains($child)) {
			// ...and assign it to collection
			$this->children->add($child);
		}
	}

	public function removeChild(self $child): void
	{
		// Check if collection contain removing entity...
		if ($this->children->contains($child)) {
			// ...and remove it from collection
			$this->children->removeElement($child);
		}
	}

	public function getChannel(): Entities\Channels\Channel
	{
		return $this->channel;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'channel' => $this->getChannel()->getId()->toString(),

			'owner' => $this->getChannel()->getDevice()->getOwnerId(),
		]);
	}

	/**
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws Utils\JsonException
	 * @throws TypeError
	 * @throws ValueError
	 */
	public function __toString(): string
	{
		return Utils\Json::encode($this->toArray());
	}

}
