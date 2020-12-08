<?php declare(strict_types = 1);

/**
 * Property.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           26.10.18
 */

namespace FastyBird\DevicesModule\Entities\Channels\Properties;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_channels_properties",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Communication channels properties"
 *     },
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint(name="channel_property_unique", columns={"property_property", "channel_id"})
 *     },
 *     indexes={
 *       @ORM\Index(name="property_property_idx", columns={"property_property"}),
 *       @ORM\Index(name="property_settable_idx", columns={"property_settable"}),
 *       @ORM\Index(name="property_queryable_idx", columns={"property_queryable"})
 *     }
 * )
 */
class Property extends Entities\Property implements IProperty
{

	/**
	 * @var Entities\Channels\IChannel
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\ManyToOne(targetEntity="FastyBird\DevicesModule\Entities\Channels\Channel", inversedBy="properties")
	 * @ORM\JoinColumn(name="channel_id", referencedColumnName="channel_id", onDelete="CASCADE", nullable=false)
	 */
	private $channel;

	/**
	 * @param Entities\Channels\IChannel $channel
	 * @param string $property
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		Entities\Channels\IChannel $channel,
		string $property,
		?Uuid\UuidInterface $id = null
	) {
		parent::__construct($property, $id);

		$this->channel = $channel;

		$channel->addProperty($this);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'device'  => $this->getChannel()->getDevice()->getIdentifier(),
			'owner'   => $this->getChannel()->getDevice()->getOwnerId(),
			'parent'  => $this->getChannel()->getDevice()->getParent() !== null ? $this->getChannel()->getDevice()->getParent()->getIdentifier() : null,
			'channel' => $this->getChannel()->getChannel(),
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getChannel(): Entities\Channels\IChannel
	{
		return $this->channel;
	}

}
