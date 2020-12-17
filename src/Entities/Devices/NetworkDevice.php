<?php declare(strict_types = 1);

/**
 * NetworkDevice.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           26.11.20
 */

namespace FastyBird\DevicesModule\Entities\Devices;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_network_physicals_devices",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Network connected devices"
 *     }
 * )
 */
class NetworkDevice extends Device implements INetworkDevice
{

	use TPhysicalDevice;

	/**
	 * @var Entities\Devices\Credentials\ICredentials|null
	 *
	 * @IPubDoctrine\Crud(is={"writable"})
	 * @ORM\OneToOne(targetEntity="FastyBird\DevicesModule\Entities\Devices\Credentials\Credentials", mappedBy="device", cascade={"persist", "remove"})
	 */
	private ?Credentials\ICredentials $credentials = null;

	/**
	 * {@inheritDoc}
	 */
	public function setCredentials(?Credentials\ICredentials $credentials): void
	{
		$this->credentials = $credentials;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCredentials(): ?Credentials\ICredentials
	{
		return $this->credentials;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'type' => 'network',
		]);
	}

}
