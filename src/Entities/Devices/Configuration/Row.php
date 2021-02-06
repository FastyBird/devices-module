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
 *       @ORM\UniqueConstraint(name="device_configuration_unique", columns={"configuration_name", "device_id"}),
 *       @ORM\UniqueConstraint(name="configuration_key_unique", columns={"configuration_key"})
 *     },
 *     indexes={
 *       @ORM\Index(name="configuration_name_idx", columns={"configuration_name"})
 *     }
 * )
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="configuration_type", type="string", length=20)
 * @ORM\DiscriminatorMap({
 *    "boolean" = "FastyBird\DevicesModule\Entities\Devices\Configuration\BooleanRow",
 *    "number"  = "FastyBird\DevicesModule\Entities\Devices\Configuration\NumberRow",
 *    "select"  = "FastyBird\DevicesModule\Entities\Devices\Configuration\SelectRow",
 *    "text"    = "FastyBird\DevicesModule\Entities\Devices\Configuration\TextRow"
 * })
 * @ORM\MappedSuperclass
 *
 * @property-read string $type
 */
abstract class Row extends Entities\Row implements IRow
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

		$device->addConfiguration($this);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'device' => $this->getDevice()->getIdentifier(),
			'owner'  => $this->getDevice()->getOwnerId(),
			'parent' => $this->getDevice()->getParent() !== null ? $this->getDevice()->getParent()->getIdentifier() : null,
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
