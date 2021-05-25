<?php declare(strict_types = 1);

/**
 * Connector.php
 *
 * @license        More in LICENSE.md
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

	use Entities\TEntity;
	use Entities\TEntityParams;
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
	public function getUsername(): ?string
	{
		return $this->getParam('username');
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
	public function getPassword(): ?string
	{
		return $this->getParam('password');
	}

	/**
	 * {@inheritDoc}
	 */
	public function setPassword(?string $password): void
	{
		$this->setParam('password', $password);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAddress(): ?int
	{
		return $this->getParam('address');
	}

	/**
	 * {@inheritDoc}
	 */
	public function setAddress(?int $address): void
	{
		$this->setParam('address', $address);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMaxPacketLength(): ?int
	{
		return $this->getParam('max_packet_length');
	}

	/**
	 * {@inheritDoc}
	 */
	public function setMaxPacketLength(?int $maxPacketLength): void
	{
		$this->setParam('max_packet_length', $maxPacketLength);
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasDescriptionSupport(): bool
	{
		return $this->getParam('description_support', false);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDescriptionSupport(bool $descriptionSupport): void
	{
		$this->setParam('description_support', $descriptionSupport);
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasSettingsSupport(): bool
	{
		return $this->getParam('settings_support', false);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setSettingsSupport(bool $settingsSupport): void
	{
		$this->setParam('settings_support', $settingsSupport);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getConfiguredKeyLength(): ?int
	{
		return $this->getParam('configured_key_length', 0);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setConfiguredKeyLength(?int $configuredKeyLength): void
	{
		$this->setParam('configured_key_length', $configuredKeyLength);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		$baseProperties = [
			'id'        => $this->getPlainId(),
			'device'    => $this->getDevice()->getKey(),
			'connector' => $this->getConnector()->getType(),
		];

		if ($this->getConnector() instanceof Entities\Connectors\FbBusConnector) {
			return array_merge($baseProperties, [
				'address'               => $this->getAddress(),
				'max_packet_length'     => $this->getMaxPacketLength(),
				'description_support'   => $this->hasDescriptionSupport(),
				'settings_support'      => $this->hasSettingsSupport(),
				'configured_key_length' => $this->getConfiguredKeyLength(),
			]);

		} elseif ($this->getConnector() instanceof Entities\Connectors\FbMqttV1Connector) {
			return array_merge($baseProperties, [
				'username' => $this->getUsername(),
			]);

		} else {
			return $baseProperties;
		}
	}

}
