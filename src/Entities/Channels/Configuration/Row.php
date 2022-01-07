<?php declare(strict_types = 1);

/**
 * Row.php
 *
 * @license        More in LICENSE.md
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
 *       "comment"="Device channels configurations rows"
 *     },
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint(name="channel_configuration_unique", columns={"configuration_identifier", "channel_id"}),
 *       @ORM\UniqueConstraint(name="configuration_key_unique", columns={"configuration_key"})
 *     },
 *     indexes={
 *       @ORM\Index(name="configuration_identifier_idx", columns={"configuration_identifier"})
 *     }
 * )
 */
class Row extends Entities\Row implements IRow
{

	/**
	 * @var Entities\Channels\IChannel
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\ManyToOne(targetEntity="FastyBird\DevicesModule\Entities\Channels\Channel", inversedBy="configuration")
	 * @ORM\JoinColumn(name="channel_id", referencedColumnName="channel_id", onDelete="CASCADE", nullable=false)
	 */
	protected Entities\Channels\IChannel $channel;

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

		$channel->addConfigurationRow($this);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'channel' => $this->getChannel()->getPlainId(),
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
