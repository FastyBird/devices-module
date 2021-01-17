<?php declare(strict_types = 1);

/**
 * Connector.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           17.01.21
 */

namespace FastyBird\DevicesModule\Entities\Devices\Connectors;

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
 *     name="fb_devices_connectors",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Devices server connectors"
 *     }
 * )
 */
class Connector implements IConnector
{

	use DatabaseEntities\TEntity;
	use DatabaseEntities\TEntityParams;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="device_connector_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected Uuid\UuidInterface $id;

	/**
	 * @var Entities\Devices\IDevice
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\OneToOne(targetEntity="FastyBird\DevicesModule\Entities\Devices\Device", inversedBy="connector")
	 * @ORM\JoinColumn(name="device_id", referencedColumnName="device_id", unique=true, onDelete="cascade", nullable=false)
	 */
	private Entities\Devices\IDevice $device;

	/**
	 * @var Entities\Connectors\IConnector
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\ManyToOne(targetEntity="FastyBird\DevicesModule\Entities\Connectors\Connector", inversedBy="devices")
	 * @ORM\JoinColumn(name="connector_id", referencedColumnName="connector_id", onDelete="cascade", nullable=false)
	 */
	private Entities\Connectors\IConnector $connector;

	/**
	 * @var string|null
	 * @IPubDoctrine\Crud(is="writable")
	 */
	private ?string $username = null;

	/**
	 * @var string|null
	 * @IPubDoctrine\Crud(is="writable")
	 */
	private ?string $password = null;

	/**
	 * @param Entities\Devices\IDevice $device
	 * @param Entities\Connectors\IConnector $connector
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		Entities\Devices\IDevice $device,
		Entities\Connectors\IConnector $connector,
		?Uuid\UuidInterface $id = null
	) {
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->device = $device;
		$this->connector = $connector;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'id'        => $this->getPlainId(),
			'device'    => $this->getDevice()->getIdentifier(),
			'connector' => $this->getConnector()->getType(),
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDevice(): Entities\Devices\IDevice
	{
		return $this->device;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getConnector(): Entities\Connectors\IConnector
	{
		return $this->connector;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setUsername(?string $username): void
	{
		$this->setParam('username', $username);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setPassword(?string $password): void
	{
		$this->setParam('password', $password);
	}

}
