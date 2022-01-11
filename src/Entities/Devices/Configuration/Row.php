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
 * @date           01.11.18
 */

namespace FastyBird\DevicesModule\Entities\Devices\Configuration;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_devices_configuration",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Devices configurations rows"
 *     },
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint(name="configuration_identifier_unique", columns={"configuration_identifier", "device_id"}),
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
	 * @var Entities\Devices\IDevice
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\ManyToOne(targetEntity="FastyBird\DevicesModule\Entities\Devices\Device", inversedBy="configuration")
	 * @ORM\JoinColumn(name="device_id", referencedColumnName="device_id", onDelete="CASCADE", nullable=false)
	 */
	protected Entities\Devices\IDevice $device;

	/**
	 * @param Entities\Devices\IDevice $device
	 * @param string $configuration
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		Entities\Devices\IDevice $device,
		string $configuration,
		?Uuid\UuidInterface $id = null
	) {
		parent::__construct($configuration, $id);

		$this->device = $device;

		$device->addConfigurationRow($this);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'device' => $this->getDevice()->getPlainId(),

			'owner' => $this->getDevice()->getOwnerId(),
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDevice(): Entities\Devices\IDevice
	{
		return $this->device;
	}

}
