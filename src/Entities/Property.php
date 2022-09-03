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
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Utilities;
use FastyBird\Metadata;
use FastyBird\Metadata\Types as MetadataTypes;
use FastyBird\Metadata\ValueObjects as MetadataValueObjects;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use IPub\DoctrineTimestampable;
use Nette\Utils;
use Ramsey\Uuid;
use Throwable;

abstract class Property implements IProperty
{

	use TEntity;
	use TEntityParams;
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
	 * @var MetadataTypes\DataTypeType
	 *
	 * @Enum(class=MetadataTypes\DataTypeType::class)
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string_enum", name="property_data_type", length=100, nullable=true, options={"default": "unknown"})
	 */
	protected $dataType;

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
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="property_value", nullable=true, options={"default": null})
	 */
	protected ?string $value = null;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="property_default", nullable=true, options={"default": null})
	 */
	protected ?string $default = null;

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

		$this->dataType = MetadataTypes\DataTypeType::get(MetadataTypes\DataTypeType::DATA_TYPE_UNKNOWN);

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
	public function getDataType(): MetadataTypes\DataTypeType
	{
		return $this->dataType;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDataType(MetadataTypes\DataTypeType $dataType): void
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
	public function getFormat(): MetadataValueObjects\StringEnumFormat|MetadataValueObjects\NumberRangeFormat|MetadataValueObjects\CombinedEnumFormat|null
	{
		return $this->buildFormat($this->format);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setFormat(array|string|null $format): void
	{
		if (is_string($format)) {
			if ($this->buildFormat($format) === null) {
				throw new Exceptions\InvalidArgumentException('Provided property format is not valid');
			}

			$this->format = $format;

			return;

		} elseif (is_array($format)) {
			if (
				in_array($this->dataType->getValue(), [
					MetadataTypes\DataTypeType::DATA_TYPE_CHAR,
					MetadataTypes\DataTypeType::DATA_TYPE_UCHAR,
					MetadataTypes\DataTypeType::DATA_TYPE_SHORT,
					MetadataTypes\DataTypeType::DATA_TYPE_USHORT,
					MetadataTypes\DataTypeType::DATA_TYPE_INT,
					MetadataTypes\DataTypeType::DATA_TYPE_UINT,
					MetadataTypes\DataTypeType::DATA_TYPE_FLOAT,
				], true)
			) {
				$plainFormat = implode(':', array_map(function ($item): string {
					if (is_array($item) || $item instanceof Utils\ArrayHash) {
						return implode('|', array_map(function ($part): string|int|float {
							return is_array($part) ? strval($part) : $part;
						}, (array) $item));
					}

					return strval($item);
				}, $format));

				if (preg_match(Metadata\Constants::VALUE_FORMAT_NUMBER_RANGE, $plainFormat) === 1) {
					$this->format = $plainFormat;

					return;
				}

				throw new Exceptions\InvalidArgumentException('Provided property format is not valid');

			} elseif (
				in_array($this->dataType->getValue(), [
					MetadataTypes\DataTypeType::DATA_TYPE_ENUM,
					MetadataTypes\DataTypeType::DATA_TYPE_BUTTON,
					MetadataTypes\DataTypeType::DATA_TYPE_SWITCH,
				], true)
			) {
				$plainFormat = implode(',', array_map(function ($item): string {
					if (is_array($item) || $item instanceof Utils\ArrayHash) {
						return implode(':', array_map(function (string|array|int|float|bool|Utils\ArrayHash|null $part): string {
							return is_array($part) || $part instanceof Utils\ArrayHash ? implode('|', (array) $part) : ($part !== null ? strval($part) : '');
						}, (array) $item));
					}

					return strval($item);
				}, $format));

				if (
					preg_match(Metadata\Constants::VALUE_FORMAT_STRING_ENUM, $plainFormat) === 1
					|| preg_match(Metadata\Constants::VALUE_FORMAT_COMBINED_ENUM, $plainFormat) === 1
				) {
					$this->format = $plainFormat;

					return;
				}

				throw new Exceptions\InvalidArgumentException('Provided property format is not valid');
			}
		}

		$this->format = null;
	}


	/**
	 * @param string|null $format
	 *
	 * @return MetadataValueObjects\StringEnumFormat|MetadataValueObjects\NumberRangeFormat|MetadataValueObjects\CombinedEnumFormat|null
	 */
	private function buildFormat(
		?string $format
	): MetadataValueObjects\StringEnumFormat|MetadataValueObjects\NumberRangeFormat|MetadataValueObjects\CombinedEnumFormat|null {
		if ($format === null) {
			return null;
		}

		if (
			in_array($this->dataType->getValue(), [
				MetadataTypes\DataTypeType::DATA_TYPE_CHAR,
				MetadataTypes\DataTypeType::DATA_TYPE_UCHAR,
				MetadataTypes\DataTypeType::DATA_TYPE_SHORT,
				MetadataTypes\DataTypeType::DATA_TYPE_USHORT,
				MetadataTypes\DataTypeType::DATA_TYPE_INT,
				MetadataTypes\DataTypeType::DATA_TYPE_UINT,
				MetadataTypes\DataTypeType::DATA_TYPE_FLOAT,
			], true)
		) {
			if (preg_match(Metadata\Constants::VALUE_FORMAT_NUMBER_RANGE, $format) === 1) {
				return new MetadataValueObjects\NumberRangeFormat($format);
			}
		} elseif (
			in_array($this->dataType->getValue(), [
				MetadataTypes\DataTypeType::DATA_TYPE_ENUM,
				MetadataTypes\DataTypeType::DATA_TYPE_BUTTON,
				MetadataTypes\DataTypeType::DATA_TYPE_SWITCH,
			], true)
		) {
			if (preg_match(Metadata\Constants::VALUE_FORMAT_STRING_ENUM, $format) === 1) {
				return new MetadataValueObjects\StringEnumFormat($format);

			} elseif (preg_match(Metadata\Constants::VALUE_FORMAT_COMBINED_ENUM, $format) === 1) {
				return new MetadataValueObjects\CombinedEnumFormat($format);
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getInvalid(): float|int|string|null
	{
		if ($this->invalid === null) {
			return null;
		}

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

			return null;
		} elseif ($this->dataType->equalsValue(MetadataTypes\DataTypeType::DATA_TYPE_FLOAT)) {
			if (is_numeric($this->invalid)) {
				return floatval($this->invalid);
			}

			return null;
		}

		return strval($this->invalid);
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
	public function getValue(): bool|float|int|string|DateTimeInterface|MetadataTypes\ButtonPayloadType|MetadataTypes\SwitchPayloadType|null
	{
		if (!$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			throw new Exceptions\InvalidStateException(sprintf('Value is not allowed for property type: %s', strval($this->getType()->getValue())));
		}

		if ($this->value === null) {
			return null;
		}

		return Utilities\ValueHelper::normalizeValue($this->getDataType(), $this->value, $this->getFormat(), $this->getInvalid());
	}

	/**
	 * {@inheritDoc}
	 */
	public function setValue(?string $value): void
	{
		if (!$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			throw new Exceptions\InvalidStateException(sprintf('Value is not allowed for property type: %s', strval($this->getType()->getValue())));
		}

		$this->value = $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefault(): bool|float|int|string|DateTimeInterface|MetadataTypes\ButtonPayloadType|MetadataTypes\SwitchPayloadType|null
	{
		if (!$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			throw new Exceptions\InvalidStateException(sprintf('Value is not allowed for property type: %s', strval($this->getType()->getValue())));
		}

		if ($this->default === null) {
			return null;
		}

		return Utilities\ValueHelper::normalizeValue($this->getDataType(), $this->default, $this->getFormat(), $this->getInvalid());
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDefault(?string $default): void
	{
		if (!$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			throw new Exceptions\InvalidStateException(sprintf('Default value is not allowed for property type: %s', strval($this->getType()->getValue())));
		}

		$this->default = $default;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		$data = [
			'id'                 => $this->getPlainId(),
			'type'               => $this->getType()->getValue(),
			'identifier'         => $this->getIdentifier(),
			'name'               => $this->getName(),
			'settable'           => $this->isSettable(),
			'queryable'          => $this->isQueryable(),
			'data_type'          => $this->getDataType()->getValue(),
			'unit'               => $this->getUnit(),
			'format'             => $this->getFormat()?->toArray(),
			'invalid'            => $this->getInvalid(),
			'number_of_decimals' => $this->getNumberOfDecimals(),
		];

		if ($this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			return array_merge($data, [
				'default' => Utilities\ValueHelper::flattenValue($this->getDefault()),
				'value'   => Utilities\ValueHelper::flattenValue($this->getValue()),
			]);
		}

		return $data;
	}

}
