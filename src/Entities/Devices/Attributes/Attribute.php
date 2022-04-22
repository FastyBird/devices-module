<?php declare(strict_types = 1);

/**
 * Attribute.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.57.0
 *
 * @date           22.04.22
 */

namespace FastyBird\DevicesModule\Entities\Devices\Attributes;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\Metadata\Types as MetadataTypes;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use IPub\DoctrineTimestampable;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_devices_module_devices_attributes",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Devices attributes"
 *     },
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint(name="attribute_identifier_unique", columns={"attribute_identifier", "device_id"})
 *     },
 *     indexes={
 *       @ORM\Index(name="attribute_identifier_idx", columns={"attribute_identifier"}),
 *       @ORM\Index(name="attribute_name_idx", columns={"attribute_name"}),
 *       @ORM\Index(name="attribute_content_idx", columns={"attribute_content"})
 *     }
 * )
 */
class Attribute implements IAttribute
{

	use Entities\TEntity;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="attribute_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected Uuid\UuidInterface $id;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\Column(type="string", name="attribute_identifier", length=50, nullable=false)
	 */
	protected string $identifier;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="attribute_name", nullable=true, options={"default": null})
	 */
	protected ?string $name = null;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="attribute_content", nullable=true, options={"default": null})
	 */
	protected ?string $content = null;

	/**
	 * @var Entities\Devices\IDevice
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\ManyToOne(targetEntity="FastyBird\DevicesModule\Entities\Devices\Device", inversedBy="attributes")
	 * @ORM\JoinColumn(name="device_id", referencedColumnName="device_id", onDelete="CASCADE", nullable=false)
	 */
	private Entities\Devices\IDevice $device;

	/**
	 * @param string $identifier
	 * @param Entities\Devices\IDevice $device
	 *
	 * @throws Throwable
	 */
	public function __construct(string $identifier, Entities\Devices\IDevice $device)
	{
		$this->id = Uuid\Uuid::uuid4();

		$this->identifier = $identifier;
		$this->device = $device;

		$device->addAttribute($this);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'id'   			=> $this->getPlainId(),
			'identifier'	=> $this->getIdentifier(),
			'name'			=> $this->getName(),
			'content'		=> is_scalar($this->getContent()) || $this->getContent() === null ? $this->getContent() : (string) $this->getContent(),

			'device' => $this->getDevice()->getPlainId(),

			'owner' => $this->getDevice()->getOwnerId(),
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIdentifier(): string
	{
		return $this->identifier;
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
	public function getContent()
	{
		if ($this->getIdentifier() === MetadataTypes\DeviceAttributeNameType::ATTRIBUTE_HARDWARE_MANUFACTURER) {
			if ($this->content !== null && MetadataTypes\HardwareManufacturerType::isValidValue($this->content)) {
				return MetadataTypes\HardwareManufacturerType::get($this->content);
			}

			return MetadataTypes\HardwareManufacturerType::get(MetadataTypes\HardwareManufacturerType::MANUFACTURER_GENERIC);
		}

		if ($this->getIdentifier() === MetadataTypes\DeviceAttributeNameType::ATTRIBUTE_HARDWARE_MODEL) {
			if ($this->content !== null && MetadataTypes\DeviceModelType::isValidValue($this->content)) {
				return MetadataTypes\DeviceModelType::get($this->content);
			}

			return MetadataTypes\DeviceModelType::get(MetadataTypes\DeviceModelType::MODEL_CUSTOM);
		}

		if ($this->getIdentifier() === MetadataTypes\DeviceAttributeNameType::ATTRIBUTE_HARDWARE_MAC_ADDRESS) {
			if (
				$this->content !== null
				&& (
					preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', (string) $this->content) !== 0
					|| preg_match('/^([0-9A-Fa-f]{12})$/', (string) $this->content) !== 0
				)
			) {
				return implode(':', str_split(strtolower(str_replace([':', '-'], '', (string) $this->content)), 2));
			}

			return null;
		}

		if ($this->getIdentifier() === MetadataTypes\DeviceAttributeNameType::ATTRIBUTE_FIRMWARE_MANUFACTURER) {
			if ($this->content !== null && MetadataTypes\FirmwareManufacturerType::isValidValue($this->content)) {
				return MetadataTypes\FirmwareManufacturerType::get($this->content);
			}

			return MetadataTypes\FirmwareManufacturerType::get(MetadataTypes\FirmwareManufacturerType::MANUFACTURER_GENERIC);
		}

		return $this->content;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setContent($content): void
	{
		if ($this->getIdentifier() === MetadataTypes\DeviceAttributeNameType::ATTRIBUTE_HARDWARE_MANUFACTURER) {
			if ($content instanceof MetadataTypes\HardwareManufacturerType) {
				$this->content = $content->getValue();
			} else {
				$this->content = $content !== null ? strtolower((string) $content) : null;
			}
		} elseif ($this->getIdentifier() === MetadataTypes\DeviceAttributeNameType::ATTRIBUTE_HARDWARE_MODEL) {
			if ($content instanceof MetadataTypes\DeviceModelType) {
				$this->content = $content->getValue();
			} else {
				$this->content = $content !== null ? strtolower((string) $content) : null;
			}
		} elseif ($this->getIdentifier() === MetadataTypes\DeviceAttributeNameType::ATTRIBUTE_HARDWARE_MAC_ADDRESS) {
			if (
				$content !== null
				&& preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $content) === 0
				&& preg_match('/^([0-9A-Fa-f]{12})$/', $content) === 0
			) {
				throw new Exceptions\InvalidArgumentException('Provided mac address is not in valid format.');
			}

			$this->content = $content !== null ? strtolower(str_replace([
				':',
				'-',
			], '', (string) $content)) : null;
		} elseif ($this->getIdentifier() === MetadataTypes\DeviceAttributeNameType::ATTRIBUTE_FIRMWARE_MANUFACTURER) {
			if ($content instanceof MetadataTypes\FirmwareManufacturerType) {
				$this->content = $content->getValue();
			} else {
				$this->content = $content !== null ? strtolower((string) $content) : null;
			}
		} elseif (is_string($content) || $content === null) {
			$this->content = $content;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDevice(): Entities\Devices\IDevice
	{
		return $this->device;
	}

}
