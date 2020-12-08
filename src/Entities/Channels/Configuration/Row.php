<?php declare(strict_types = 1);

/**
 * Row.php
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

namespace FastyBird\DevicesModule\Entities\Channels\Configuration;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_channels_configuration",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Communication channels configurations rows"
 *     },
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint(name="channel_configuration_unique", columns={"configuration_name", "channel_id"})
 *     },
 *     indexes={
 *       @ORM\Index(name="configuration_name_idx", columns={"configuration_name"})
 *     }
 * )
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="configuration_type", type="string", length=20)
 * @ORM\DiscriminatorMap({
 *    "boolean" = "FastyBird\DevicesModule\Entities\Channels\Configuration\BooleanRow",
 *    "number"  = "FastyBird\DevicesModule\Entities\Channels\Configuration\NumberRow",
 *    "select"  = "FastyBird\DevicesModule\Entities\Channels\Configuration\SelectRow",
 *    "text"    = "FastyBird\DevicesModule\Entities\Channels\Configuration\TextRow"
 * })
 * @ORM\MappedSuperclass
 *
 * @property-read string $type
 */
abstract class Row extends Entities\Row implements IRow
{

	/**
	 * @var Entities\Channels\IChannel
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\ManyToOne(targetEntity="FastyBird\DevicesModule\Entities\Channels\Channel", inversedBy="configuration")
	 * @ORM\JoinColumn(name="channel_id", referencedColumnName="channel_id", onDelete="CASCADE", nullable=false)
	 */
	protected $channel;

	/**
	 * @param Entities\Channels\IChannel $channel
	 * @param string $configuration
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		Entities\Channels\IChannel $channel,
		string $configuration,
		?Uuid\UuidInterface $id = null
	) {
		parent::__construct($configuration, $id);

		$this->channel = $channel;

		$channel->addConfiguration($this);
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
