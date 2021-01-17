<?php declare(strict_types = 1);

/**
 * Firmware.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           28.07.18
 */

namespace FastyBird\DevicesModule\Entities\Devices\Firmware;

use Consistence\Doctrine\Enum\EnumAnnotation as Enum;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Types;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use IPub\DoctrineTimestampable;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_devices_firmware",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Physicals devices firmware info"
 *     }
 * )
 */
class Firmware implements IFirmware
{

	use DatabaseEntities\TEntity;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="firmware_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected Uuid\UuidInterface $id;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="firmware_name", length=150, nullable=true, options={"default": null})
	 */
	private ?string $name = null;

	/**
	 * @var Types\FirmwareManufacturerType
	 *
	 * @Enum(class=Types\FirmwareManufacturerType::class)
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="firmware_manufacturer", length=150, nullable=true, options={"default": "generic"})
	 */
	private $manufacturer;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="firmware_version", length=150, nullable=true, options={"default": null})
	 */
	private ?string $version = null;

	/**
	 * @var Entities\Devices\IDevice
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\OneToOne(targetEntity="FastyBird\DevicesModule\Entities\Devices\Device", inversedBy="firmware")
	 * @ORM\JoinColumn(name="device_id", referencedColumnName="device_id", unique=true, onDelete="cascade", nullable=false)
	 */
	private Entities\Devices\IDevice $device;

	/**
	 * @param Entities\Devices\IDevice $device
	 *
	 * @throws Throwable
	 */
	public function __construct(Entities\Devices\IDevice $device)
	{
		$this->id = Uuid\Uuid::uuid4();

		$this->manufacturer = Types\FirmwareManufacturerType::get(Types\FirmwareManufacturerType::MANUFACTURER_GENERIC);

		$this->device = $device;

		$device->setFirmware($this);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'id'           => $this->getPlainId(),
			'name'         => $this->getName(),
			'manufacturer' => $this->getManufacturer()->getValue(),
			'version'      => $this->getVersion(),
			'device'       => $this->getDevice()->getIdentifier(),
		];
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
	public function getManufacturer(): Types\FirmwareManufacturerType
	{
		return $this->manufacturer;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setManufacturer(?string $manufacturer): void
	{
		if ($manufacturer !== null && Types\FirmwareManufacturerType::isValidValue(strtolower($manufacturer))) {
			$this->manufacturer = Types\FirmwareManufacturerType::get(strtolower($manufacturer));

		} else {
			$this->manufacturer = Types\FirmwareManufacturerType::get(Types\FirmwareManufacturerType::MANUFACTURER_GENERIC);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getVersion(): ?string
	{
		return $this->version;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setVersion(?string $version): void
	{
		$this->version = $version;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDevice(): Entities\Devices\IDevice
	{
		return $this->device;
	}

}
