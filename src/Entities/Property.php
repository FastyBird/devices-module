<?php declare(strict_types = 1);

/**
 * Property.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           07.08.20
 */

namespace FastyBird\DevicesModule\Entities;

use Consistence\Doctrine\Enum\EnumAnnotation as Enum;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use IPub\DoctrineTimestampable;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\MappedSuperclass
 */
class Property implements IProperty
{

	use TKey;
	use DatabaseEntities\TEntity;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="property_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected Uuid\UuidInterface $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", name="property_key", length=50, nullable=false)
	 */
	protected string $key;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\Column(type="string", name="property_identifier", length=50, nullable=false)
	 */
	protected string $identifier;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="property_name", nullable=true, options={"default": null})
	 */
	protected ?string $name = null;

	/**
	 * @var bool
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="boolean", name="property_settable", nullable=false, options={"default": false})
	 */
	protected bool $settable = false;

	/**
	 * @var bool
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="boolean", name="property_queryable", nullable=false, options={"default": false})
	 */
	protected bool $queryable = false;

	/**
	 * @var ModulesMetadataTypes\DataTypeType|null
	 *
	 * @Enum(class=ModulesMetadataTypes\DataTypeType::class)
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string_enum", name="property_data_type", nullable=true, options={"default": null})
	 */
	protected $dataType = null;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="property_unit", nullable=true, options={"default": null})
	 */
	protected ?string $unit = null;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="property_format", nullable=true, options={"default": null})
	 */
	protected ?string $format = null;

	/**
	 * @param string $identifier
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		string $identifier,
		?Uuid\UuidInterface $id = null
	) {
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->identifier = $identifier;
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
	public function isSettable(): bool
	{
		return $this->settable;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setSettable(bool $settable): void
	{
		$this->settable = $settable;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isQueryable(): bool
	{
		return $this->queryable;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setQueryable(bool $queryable): void
	{
		$this->queryable = $queryable;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDataType(): ?ModulesMetadataTypes\DataTypeType
	{
		return $this->dataType;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDataType(?string $dataType): void
	{
		if ($dataType !== null && !ModulesMetadataTypes\DataTypeType::isValidValue($dataType)) {
			throw new Exceptions\InvalidArgumentException(sprintf('Provided data type "%s" is not valid', $dataType));
		}

		$this->dataType = $dataType !== null ? ModulesMetadataTypes\DataTypeType::get($dataType) : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUnit(): ?string
	{
		return $this->unit;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setUnit(?string $unit): void
	{
		$this->unit = $unit;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFormat()
	{
		$format = $this->format;

		if ($this->dataType !== null) {
			if ($this->dataType->isInteger()) {
				if ($format !== null) {
					[$min, $max] = explode(':', $format) + [null, null];

					if ($min !== null && $max !== null && intval($min) <= intval($max)) {
						return [intval($min), intval($max)];
					}
				}

			} elseif ($this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_FLOAT)) {
				if ($format !== null) {
					[$min, $max] = explode(':', $format) + [null, null];

					if ($min !== null && $max !== null && floatval($min) <= floatval($max)) {
						return [floatval($min), floatval($max)];
					}
				}

			} elseif ($this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_ENUM)) {
				if ($format !== null) {
					$format = array_filter(array_map('trim', explode(',', $format)), function ($item): bool {
						return $item !== '';
					});

					return $format;
				}
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setFormat(?string $format): void
	{
		$this->format = $format;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'id'         => $this->getPlainId(),
			'key'        => $this->getKey(),
			'identifier' => $this->getIdentifier(),
			'name'       => $this->getName(),
			'settable'   => $this->isSettable(),
			'queryable'  => $this->isQueryable(),
			'data_type'  => $this->getDataType() !== null ? $this->getDataType()->getValue() : null,
			'unit'       => $this->getUnit(),
			'format'     => $this->getFormat(),
		];
	}

}
