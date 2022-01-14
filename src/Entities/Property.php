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
use FastyBird\Metadata\Helpers as MetadataHelpers;
use FastyBird\Metadata\Types as MetadataTypes;
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
	 * @var MetadataTypes\DataTypeType|null
	 *
	 * @Enum(class=MetadataTypes\DataTypeType::class)
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string_enum", name="property_data_type", length=100, nullable=true, options={"default": null})
	 */
	protected $dataType = null;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="property_unit", length=20, nullable=true, options={"default": null})
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
	 * @var int|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="integer", name="property_number_of_decimals", nullable=true, options={"default": null})
	 */
	protected ?int $numberOfDecimals = null;

	/**
	 * @var mixed|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="property_value", nullable=true, options={"default": null})
	 */
	protected $value = null;

	/**
	 * @var mixed|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="property_default", nullable=true, options={"default": null})
	 */
	protected $default = null;

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

		// Static property can not be set or read from device/channel property
		if ($this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			$this->settable = false;
			$this->queryable = false;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	abstract public function getType(): MetadataTypes\PropertyTypeType;

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		$data = [
			'id'                 => $this->getPlainId(),
			'type'               => $this->getType()->getValue(),
			'key'                => $this->getKey(),
			'identifier'         => $this->getIdentifier(),
			'name'               => $this->getName(),
			'settable'           => $this->isSettable(),
			'queryable'          => $this->isQueryable(),
			'data_type'          => $this->getDataType() !== null ? $this->getDataType()->getValue() : null,
			'unit'               => $this->getUnit(),
			'format'             => $this->getFormat(),
			'invalid'            => $this->getInvalid(),
			'number_of_decimals' => $this->getNumberOfDecimals(),

		];

		if (!$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			return $data;
		}

		return array_merge($data, [
			'value'   => $this->getValue(),
			'default' => $this->getDefault(),
		]);
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
		if ($settable && $this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			throw new Exceptions\InvalidArgumentException('Static type property can not be settable');
		}

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
		if ($queryable && $this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			throw new Exceptions\InvalidArgumentException('Static type property can not be queryable');
		}

		$this->queryable = $queryable;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDataType(): ?MetadataTypes\DataTypeType
	{
		return $this->dataType;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDataType(?MetadataTypes\DataTypeType $dataType): void
	{
		$this->dataType = $dataType;
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
		return $this->buildFormat($this->format);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setFormat(?string $format): void
	{
		if ($format !== null && $this->buildFormat($format) === null) {
			throw new Exceptions\InvalidArgumentException('Provided property format is not valid');
		}

		$this->format = $format;
	}

	/**
	 * @param string|null $format
	 *
	 * @return Array<string>|Array<Array<string|null>>|Array<int|null>|Array<float|null>|null
	 */
	protected function buildFormat(?string $format): ?array
	{
		if ($format === null) {
			return null;
		}

		if ($this->dataType !== null) {
			if (
				$this->dataType->equalsValue(MetadataTypes\DataTypeType::DATA_TYPE_CHAR)
				|| $this->dataType->equalsValue(MetadataTypes\DataTypeType::DATA_TYPE_UCHAR)
				|| $this->dataType->equalsValue(MetadataTypes\DataTypeType::DATA_TYPE_SHORT)
				|| $this->dataType->equalsValue(MetadataTypes\DataTypeType::DATA_TYPE_USHORT)
				|| $this->dataType->equalsValue(MetadataTypes\DataTypeType::DATA_TYPE_INT)
				|| $this->dataType->equalsValue(MetadataTypes\DataTypeType::DATA_TYPE_UINT)
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
			} elseif ($this->dataType->equalsValue(MetadataTypes\DataTypeType::DATA_TYPE_FLOAT)) {
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
			} elseif (
				$this->dataType->equalsValue(MetadataTypes\DataTypeType::DATA_TYPE_ENUM)
				|| $this->dataType->equalsValue(MetadataTypes\DataTypeType::DATA_TYPE_BUTTON)
				|| $this->dataType->equalsValue(MetadataTypes\DataTypeType::DATA_TYPE_SWITCH)
			) {
				return array_map(function (string $item) {
					if (strpos($item, ':') === false) {
						return $item;
					}

					$parts = array_map(function (?string $item): ?string {
						return $item === '' ? null : $item;
					}, array_map('trim', explode(':', $item) + [null, null, null]));

					return [$parts[0], $parts[1], $parts[2]];
				}, array_filter(array_map('trim', explode(',', $format)), function ($item): bool {
					return $item !== '';
				}));
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getInvalid()
	{
		if ($this->invalid === null) {
			return null;
		}

		if ($this->dataType !== null) {
			if (
				$this->dataType->equalsValue(MetadataTypes\DataTypeType::DATA_TYPE_CHAR)
				|| $this->dataType->equalsValue(MetadataTypes\DataTypeType::DATA_TYPE_UCHAR)
				|| $this->dataType->equalsValue(MetadataTypes\DataTypeType::DATA_TYPE_SHORT)
				|| $this->dataType->equalsValue(MetadataTypes\DataTypeType::DATA_TYPE_USHORT)
				|| $this->dataType->equalsValue(MetadataTypes\DataTypeType::DATA_TYPE_INT)
				|| $this->dataType->equalsValue(MetadataTypes\DataTypeType::DATA_TYPE_UINT)
			) {
				if (is_numeric($this->invalid)) {
					return intval($this->invalid);
				}
			} elseif ($this->dataType->equalsValue(MetadataTypes\DataTypeType::DATA_TYPE_FLOAT)) {
				if (is_numeric($this->invalid)) {
					return floatval($this->invalid);
				}
			}
		}

		return $this->invalid;
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
		if (!$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			throw new Exceptions\InvalidStateException(sprintf('Value is not allowed for property type: %s', $this->getType()
				->getValue()));
		}

		if ($this->value === null) {
			return null;
		}

		if ($this->getDataType() === null) {
			return null;
		}

		return MetadataHelpers\ValueHelper::normalizeValue($this->getDataType(), $this->value, $this->getFormat());
	}

	/**
	 * {@inheritDoc}
	 */
	public function setValue(?string $value): void
	{
		if (!$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			throw new Exceptions\InvalidStateException(sprintf('Value is not allowed for property type: %s', $this->getType()
				->getValue()));
		}

		$this->value = $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefault()
	{
		if (!$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			throw new Exceptions\InvalidStateException(sprintf('Value is not allowed for property type: %s', $this->getType()
				->getValue()));
		}

		if ($this->default === null) {
			return null;
		}

		if ($this->getDataType() === null) {
			return null;
		}

		return MetadataHelpers\ValueHelper::normalizeValue($this->getDataType(), $this->default, $this->getFormat());
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDefault(?string $default): void
	{
		if (!$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			throw new Exceptions\InvalidStateException(sprintf('Default value is not allowed for property type: %s', $this->getType()
				->getValue()));
		}

		$this->default = $default;
	}

}
