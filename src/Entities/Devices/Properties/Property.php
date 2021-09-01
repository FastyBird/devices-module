<?php declare(strict_types = 1);

/**
 * Property.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           02.11.18
 */

namespace FastyBird\DevicesModule\Entities\Devices\Properties;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_devices_properties",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Devices properties"
 *     },
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint(name="device_property_unique", columns={"property_identifier", "device_id"}),
 *       @ORM\UniqueConstraint(name="property_key_unique", columns={"property_key"})
 *     },
 *     indexes={
 *       @ORM\Index(name="property_identifier_idx", columns={"property_identifier"}),
 *       @ORM\Index(name="property_settable_idx", columns={"property_settable"}),
 *       @ORM\Index(name="property_queryable_idx", columns={"property_queryable"})
 *     }
 * )
 */
class Property extends Entities\Property implements IProperty
{

	/**
	 * @var Entities\Devices\IDevice
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\ManyToOne(targetEntity="FastyBird\DevicesModule\Entities\Devices\Device", inversedBy="properties")
	 * @ORM\JoinColumn(name="device_id", referencedColumnName="device_id", onDelete="CASCADE", nullable=false)
	 */
	private Entities\Devices\IDevice $device;

	/**
	 * @param Entities\Devices\IDevice $device
	 * @param string $property
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		Entities\Devices\IDevice $device,
		string $property,
		?Uuid\UuidInterface $id = null
	) {
		parent::__construct($property, $id);

		$this->device = $device;

		$device->addProperty($this);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'device' => $this->getDevice()->getPlainId(),
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
