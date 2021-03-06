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
 * @date           17.01.20
 */

namespace FastyBird\DevicesModule\Entities\Connectors;

use Doctrine\Common;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use IPub\DoctrineTimestampable;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_connectors",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Communication connectors"
 *     },
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint(name="connector_key_unique", columns={"connector_key"})
 *     }
 * )
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="connector_type", type="string", length=40)
 * @ORM\DiscriminatorMap({
 *    "fb_bus"      = "FastyBird\DevicesModule\Entities\Connectors\FbBusConnector",
 *    "fb_mqtt_v1"  = "FastyBird\DevicesModule\Entities\Connectors\FbMqttV1Connector"
 * })
 * @ORM\MappedSuperclass
 */
abstract class Connector implements IConnector
{

	use Entities\TKey;
	use Entities\TEntity;
	use Entities\TEntityParams;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="connector_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected Uuid\UuidInterface $id;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\Column(type="string", name="connector_name", length=40, nullable=false)
	 */
	private string $name;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", name="connector_key", length=50, nullable=false)
	 */
	private string $key;

	/**
	 * @var bool
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="boolean", name="connector_enabled", length=1, nullable=false, options={"default": true})
	 */
	private bool $enabled = true;

	/**
	 * @var Common\Collections\Collection<int, Entities\Devices\Connectors\IConnector>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Devices\Connectors\Connector", mappedBy="connector", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	private Common\Collections\Collection $devices;

	/**
	 * @var Common\Collections\Collection<int, Entities\Connectors\Controls\IControl>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\DevicesModule\Entities\Connectors\Controls\Control", mappedBy="connector", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	private Common\Collections\Collection $controls;

	/**
	 * @param string $name
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		string $name,
		?Uuid\UuidInterface $id = null
	) {
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->name = $name;

		$this->devices = new Common\Collections\ArrayCollection();
		$this->controls = new Common\Collections\ArrayCollection();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isEnabled(): bool
	{
		return $this->enabled;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setEnabled(bool $enabled): void
	{
		$this->enabled = $enabled;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDevices(): array
	{
		return $this->devices->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getControls(): array
	{
		return $this->controls->toArray();
	}

	/**
	 * @return string[]
	 */
	private function getPlainControls(): array
	{
		$controls = [];

		foreach ($this->getControls() as $control) {
			$controls[] = $control->getName();
		}

		return $controls;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getControl(string $name): ?Entities\Connectors\Controls\IControl
	{
		$found = $this->controls
			->filter(function (Entities\Connectors\Controls\IControl $row) use ($name): bool {
				return $name === $row->getName();
			});

		return $found->isEmpty() ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findControl(string $name): ?Entities\Connectors\Controls\IControl
	{
		$found = $this->controls
			->filter(function (Entities\Connectors\Controls\IControl $row) use ($name): bool {
				return $name === $row->getName();
			});

		return $found->isEmpty() ? null : $found->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasControl(string $name): bool
	{
		return $this->findControl($name) !== null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setControls(array $controls = []): void
	{
		$this->controls = new Common\Collections\ArrayCollection();

		// Process all passed entities...
		foreach ($controls as $entity) {
			if (!$this->controls->contains($entity)) {
				// ...and assign them to collection
				$this->controls->add($entity);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function addControl(Entities\Connectors\Controls\IControl $control): void
	{
		// Check if collection does not contain inserting entity
		if (!$this->controls->contains($control)) {
			// ...and assign it to collection
			$this->controls->add($control);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeControl(Entities\Connectors\Controls\IControl $control): void
	{
		// Check if collection contain removing entity...
		if ($this->controls->contains($control)) {
			// ...and remove it from collection
			$this->controls->removeElement($control);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'id'      => $this->getPlainId(),
			'type'    => $this->getType(),
			'name'    => $this->getName(),
			'key'     => $this->getKey(),
			'enabled' => $this->isEnabled(),

			'control' => $this->getPlainControls(),
		];
	}

}
