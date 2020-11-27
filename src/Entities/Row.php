<?php declare(strict_types = 1);

/**
 * Row.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           26.10.18
 */

namespace FastyBird\DevicesModule\Entities;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\Database\Entities as DatabaseEntities;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use IPub\DoctrineTimestampable;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\MappedSuperclass
 *
 * @property-read string $type
 */
abstract class Row implements IRow
{

	use DatabaseEntities\TEntity;
	use DatabaseEntities\TEntityParams;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="configuration_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected $id;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\Column(type="string", name="configuration_configuration", length=50, nullable=false)
	 */
	protected $configuration;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="configuration_name", nullable=true, options={"default": null})
	 */
	protected $name = null;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="text", name="configuration_comment", nullable=true, options={"default": null})
	 */
	protected $comment = null;

	/**
	 * @var mixed|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="configuration_default", nullable=true, options={"default": null})
	 */
	protected $default = null;

	/**
	 * @var mixed|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="configuration_value", nullable=true, options={"default": null})
	 */
	protected $value = null;

	/**
	 * @param string $configuration
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		string $configuration,
		?Uuid\UuidInterface $id = null
	) {
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->configuration = $configuration;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getConfiguration(): string
	{
		return $this->configuration;
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
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setComment(?string $comment): void
	{
		$this->comment = $comment;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getComment(): ?string
	{
		return $this->comment;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDefault(?string $default): void
	{
		$this->default = $default;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefault()
	{
		return $this->default;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setValue(?string $value): void
	{
		$this->value = $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'id'            => $this->getPlainId(),
			'type'          => $this->getType(),
			'configuration' => $this->getConfiguration(),
			'name'          => $this->getName(),
			'comment'       => $this->getComment(),
			'default'       => $this->getDefault(),
			'value'         => $this->getValue(),
		];
	}

}
