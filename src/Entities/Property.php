<?php declare(strict_types = 1);

/**
 * Property.php
 *
 * @license        More in LICENSE.md
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
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Helpers;
use FastyBird\DevicesModule\Types;
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use IPub\DoctrineTimestampable;
use Ramsey\Uuid;
use Throwable;

abstract class Property implements IProperty
{

	use TKey;
	use TEntity;
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
	 * @var Types\PropertyTypeType
	 *
	 * @Enum(class=Types\PropertyTypeType::class)
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\Column(type="string_enum", name="property_type", nullable=false, options={"default": "dynamic"})
	 */
	protected $type;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", name="property_key", length=50)
	 */
	protected ?string $key = null;

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
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="property_invalid", nullable=true, options={"default": null})
	 */
	protected ?string $invalid = null;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="integer", name="property_number_of_decimals", nullable=true, options={"default": null})
	 */
	protected ?string $numberOfDecimals = null;

	/**
	 * @var mixed|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="property_value", nullable=true, options={"default": null})
	 */
	protected $value = null;

	/**
	 * @param Types\PropertyTypeType $type
	 * @param string $identifier
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		Types\PropertyTypeType $type,
		string $identifier,
		?Uuid\UuidInterface $id = null
	) {
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->identifier = $identifier;
		$this->type = $type;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getType(): Types\PropertyTypeType
	{
		return $this->type;
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
	public function getFormat(): ?array
	{
		$format = $this->format;

		if ($format === null) {
			return null;
		}

		if ($this->dataType !== null) {
			if (
				$this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_CHAR)
				|| $this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_UCHAR)
				|| $this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_SHORT)
				|| $this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_USHORT)
				|| $this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_INT)
				|| $this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_UINT)
			) {
				[$min, $max] = explode(':', $format) + [null, null];

				if ($min !== null && $max !== null && intval($min) <= intval($max)) {
					return [intval($min), intval($max)];
				}

				if ($min !== null && $max === null) {
					return [intval($min), null];
				}

				if ($min === null && $max !== null) {
					return [null, intval($max)];
				}
			} elseif ($this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_FLOAT)) {
				[$min, $max] = explode(':', $format) + [null, null];

				if ($min !== null && $max !== null && floatval($min) <= floatval($max)) {
					return [floatval($min), floatval($max)];
				}

				if ($min !== null && $max === null) {
					return [floatval($min), null];
				}

				if ($min === null && $max !== null) {
					return [null, floatval($max)];
				}
			} elseif ($this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_ENUM)) {
				return array_filter(array_map('trim', explode(',', $format)), function ($item): bool {
					return $item !== '';
				});
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
	public function getInvalid()
	{
		$invalid = $this->invalid;

		if ($invalid === null) {
			return null;
		}

		if ($this->dataType !== null) {
			if (
				$this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_CHAR)
				|| $this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_UCHAR)
				|| $this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_SHORT)
				|| $this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_USHORT)
				|| $this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_INT)
				|| $this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_UINT)
			) {
				if (is_numeric($invalid)) {
					return intval($invalid);
				}
			} elseif ($this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_FLOAT)) {
				if (is_numeric($invalid)) {
					return floatval($invalid);
				}
			}
		}

		return $invalid;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setInvalid(?string $invalid): void
	{
		$this->invalid = $invalid;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getNumberOfDecimals(): ?int
	{
		return $this->numberOfDecimals;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setNumberOfDecimals(?int $numberOfDecimals): void
	{
		$this->numberOfDecimals = $numberOfDecimals;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getValue()
	{
		if (!$this->type->equalsValue(Types\PropertyTypeType::TYPE_STATIC)) {
			throw new Exceptions\InvalidStateException(sprintf('Value is not allowed for property type: %s', $this->getType()->getValue()));
		}

		if ($this->value === null) {
			return null;
		}

		if ($this->getDataType() === null) {
			return null;
		}

		return Helpers\ItemValueHelper::normalizeValue($this->getDataType(), $this->value, $this->getFormat());
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
	public function toArray(): array
	{
		$data = [
			'id'         => $this->getPlainId(),
			'type'       => $this->getType()->getValue(),
			'key'        => $this->getKey(),
			'identifier' => $this->getIdentifier(),
			'name'       => $this->getName(),
			'settable'   => $this->isSettable(),
			'queryable'  => $this->isQueryable(),
			'data_type'  => $this->getDataType() !== null ? $this->getDataType()->getValue() : null,
			'unit'       => $this->getUnit(),
			'format'     => $this->getFormat(),
			'invalid'    => $this->getInvalid(),

		];

		if (!$this->getType()->equalsValue(Types\PropertyTypeType::TYPE_STATIC)) {
			return $data;
		}

		return array_merge($data, [
			'value' => $this->getValue(),
		]);
	}

}
