<?php declare(strict_types = 1);

/**
 * Hardware.php
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

namespace FastyBird\DevicesModule\Entities\Devices\Hardware;

use Consistence\Doctrine\Enum\EnumAnnotation as Enum;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Types;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use IPub\DoctrineTimestampable;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_devices_hardware",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Physicals devices hardware info"
 *     }
 * )
 */
class Hardware implements IHardware
{

	use DatabaseEntities\TEntity;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="hardware_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected Uuid\UuidInterface $id;

	/**
	 * @var Types\HardwareManufacturerType
	 *
	 * @Enum(class=Types\HardwareManufacturerType::class)
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="hardware_manufacturer", length=150, nullable=true, options={"default": "generic"})
	 */
	private $manufacturer;

	/**
	 * @var Types\ModelType
	 *
	 * @Enum(class=Types\ModelType::class)
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="hardware_model", length=150, nullable=true, options={"default": "custom"})
	 */
	private $model;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="hardware_version", length=150, nullable=true, options={"default": null})
	 */
	private ?string $version = null;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="hardware_mac_address", length=150, nullable=true, options={"default": null})
	 */
	private ?string $macAddress = null;

	/**
	 * @var Entities\Devices\IDevice
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\OneToOne(targetEntity="FastyBird\DevicesModule\Entities\Devices\Device", inversedBy="hardware")
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

		$this->manufacturer = Types\HardwareManufacturerType::get(Types\HardwareManufacturerType::MANUFACTURER_GENERIC);
		$this->model = Types\ModelType::get(Types\ModelType::MODEL_CUSTOM);

		$this->device = $device;

		$device->setHardware($this);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'id'           => $this->getPlainId(),
			'version'      => $this->getVersion(),
			'manufacturer' => $this->getManufacturer()->getValue(),
			'model'        => $this->getModel()->getValue(),
			'mac_address'  => $this->getMacAddress(),
			'device'       => $this->getDevice()->getIdentifier(),
		];
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
	public function getManufacturer(): Types\HardwareManufacturerType
	{
		return $this->manufacturer;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setManufacturer(?string $manufacturer): void
	{
		if ($manufacturer !== null && Types\HardwareManufacturerType::isValidValue(strtolower($manufacturer))) {
			$this->manufacturer = Types\HardwareManufacturerType::get(strtolower($manufacturer));

		} else {
			$this->manufacturer = Types\HardwareManufacturerType::get(Types\HardwareManufacturerType::MANUFACTURER_GENERIC);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getModel(): Types\ModelType
	{
		return $this->model;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setModel(?string $model): void
	{
		if ($model !== null && Types\ModelType::isValidValue(strtolower($model))) {
			$this->model = Types\ModelType::get(strtolower($model));

		} else {
			$this->model = Types\ModelType::get(Types\ModelType::MODEL_CUSTOM);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMacAddress(string $separator = ':'): ?string
	{
		return $this->macAddress !== null ? implode($separator, str_split($this->macAddress, 2)) : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setMacAddress(?string $macAddress): void
	{
		if (
			$macAddress !== null
			&& preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $macAddress) === 0
			&& preg_match('/^([0-9A-Fa-f]{12})$/', $macAddress) === 0
		) {
			throw new Exceptions\InvalidArgumentException('Provided mac address is not in valid format.');
		}

		$this->macAddress = $macAddress !== null ? strtolower(str_replace([':', '-'], '', $macAddress)) : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDevice(): Entities\Devices\IDevice
	{
		return $this->device;
	}

}
