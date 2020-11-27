<?php declare(strict_types = 1);

/**
 * Control.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           19.07.19
 */

namespace FastyBird\DevicesModule\Entities\Channels\Controls;

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
 *     name="fb_channels_controls",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Communication channels controls"
 *     },
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint(name="channel_control_unique", columns={"control_name", "channel_id"})
 *     },
 *     indexes={
 *       @ORM\Index(name="control_name_idx", columns={"control_name"})
 *     }
 * )
 */
class Control implements IControl
{

	use DatabaseEntities\TEntity;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="control_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected $id;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\Column(type="string", name="control_name", length=100, nullable=false)
	 */
	private $name;

	/**
	 * @var Entities\Channels\IChannel
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\ManyToOne(targetEntity="FastyBird\DevicesModule\Entities\Channels\Channel", inversedBy="controls")
	 * @ORM\JoinColumn(name="channel_id", referencedColumnName="channel_id", onDelete="CASCADE", nullable=false)
	 */
	private $channel;

	/**
	 * @param string $name
	 * @param Entities\Channels\IChannel $channel
	 *
	 * @throws Throwable
	 */
	public function __construct(string $name, Entities\Channels\IChannel $channel)
	{
		$this->id = Uuid\Uuid::uuid4();

		$this->name = $name;
		$this->channel = $channel;

		$channel->addControl($this);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getChannel(): Entities\Channels\IChannel
	{
		return $this->channel;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName(): string
	{
		return $this->name;
	}

}
