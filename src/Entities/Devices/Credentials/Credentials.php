<?php declare(strict_types = 1);

/**
 * Credentials.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           10.12.20
 */

namespace FastyBird\DevicesModule\Entities\Devices\Credentials;

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
 *     name="fb_physicals_devices_credentials",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Network connected devices credentials"
 *     }
 * )
 */
class Credentials implements ICredentials
{

	use DatabaseEntities\TEntity;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="credentials_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected Uuid\UuidInterface $id;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 * @ORM\Column(type="string", name="credentials_username", length=150, nullable=false)
	 */
	private string $username;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 * @ORM\Column(type="string", name="credentials_password", length=150, nullable=false)
	 */
	private string $password;

	/**
	 * @var Entities\Devices\INetworkDevice
	 *
	 * @ORM\OneToOne(targetEntity="FastyBird\DevicesModule\Entities\Devices\NetworkDevice", inversedBy="credentials")
	 * @ORM\JoinColumn(name="device_id", referencedColumnName="device_id", unique=true, onDelete="cascade", nullable=false)
	 */
	private Entities\Devices\INetworkDevice $device;

	/**
	 * @param Entities\Devices\INetworkDevice $device
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		Entities\Devices\INetworkDevice $device,
		?Uuid\UuidInterface $id = null
	) {
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->device = $device;

		$device->setCredentials($this);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'id'       => $this->getPlainId(),
			'username' => $this->getUsername(),
			'password' => $this->getPassword(),
			'device'   => $this->getDevice()->getIdentifier(),
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUsername(): string
	{
		return $this->username;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setUsername(string $username): void
	{
		$this->username = $username;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPassword(): string
	{
		return $this->password;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setPassword(string $password): void
	{
		$this->password = $password;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDevice(): Entities\Devices\INetworkDevice
	{
		return $this->device;
	}

}
