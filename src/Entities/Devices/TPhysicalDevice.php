<?php declare(strict_types = 1);

/**
 * PhysicalDevice.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           11.05.19
 */

namespace FastyBird\DevicesModule\Entities\Devices;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;

trait TPhysicalDevice
{

	/**
	 * @var Entities\Devices\PhysicalDevice\IHardware|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToOne(targetEntity="FastyBird\DevicesModule\Entities\Devices\PhysicalDevice\Hardware")
	 * @ORM\JoinColumn(name="hardware_id", referencedColumnName="hardware_id", onDelete="CASCADE")
	 */
	protected $hardware;

	/**
	 * @var Entities\Devices\PhysicalDevice\IFirmware|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToOne(targetEntity="FastyBird\DevicesModule\Entities\Devices\PhysicalDevice\Firmware")
	 * @ORM\JoinColumn(name="firmware_id", referencedColumnName="firmware_id", onDelete="CASCADE")
	 */
	protected $firmware;

	/**
	 * {@inheritDoc}
	 */
	public function getHardware(): ?Entities\Devices\PhysicalDevice\IHardware
	{
		return $this->hardware;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setHardware(?Entities\Devices\PhysicalDevice\IHardware $hardware): void
	{
		$this->hardware = $hardware;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFirmware(): ?Entities\Devices\PhysicalDevice\IFirmware
	{
		return $this->firmware;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setFirmware(?Entities\Devices\PhysicalDevice\IFirmware $firmware): void
	{
		$this->firmware = $firmware;
	}

}
