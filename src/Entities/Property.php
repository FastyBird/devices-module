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
use function array_map;
use function array_merge;
use function floatval;
use function implode;
use function in_array;
use function intval;
use function is_array;
use function is_numeric;
use function is_string;
use function preg_match;
use function sprintf;
use function strval;

abstract class Property implements Entity,
	EntityParams,
	DoctrineTimestampable\Entities\IEntityCreated, DoctrineTimestampable\Entities\IEntityUpdated
{

	use TEntity;
	use TEntityParams;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="property_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected Uuid\UuidInterface $id;

	/**
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\Column(type="string", name="property_identifier", length=50, nullable=false)
	 */
	protected string $identifier;

	/**
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="property_name", nullable=true, options={"default": null})
	 */
	protected string|null $name = null;

	/**
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="boolean", name="property_settable", nullable=false, options={"default": false})
	 */
	protected bool $settable = false;

	/**
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="boolean", name="property_queryable", nullable=false, options={"default": false})
	 */
	protected bool $queryable = false;

	/**
	 * @var MetadataTypes\DataTypeType
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 *
	 * @Enum(class=MetadataTypes\DataTypeType::class)
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string_enum", name="property_data_type", length=100, nullable=true, options={"default": "unknown"})
	 */
	protected $dataType;

	/**
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="property_unit", length=20, nullable=true, options={"default": null})
	 */
	protected string|null $unit = null;

	/**
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="property_format", nullable=true, options={"default": null})
	 */
	protected string|null $format = null;

	/**
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="property_invalid", nullable=true, options={"default": null})
	 */
	protected string|null $invalid = null;

	/**
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="integer", name="property_number_of_decimals", nullable=true, options={"default": null})
	 */
	protected int|null $numberOfDecimals = null;

	/**
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="property_value", nullable=true, options={"default": null})
	 */
	protected string|null $value = null;

	/**
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="property_default", nullable=true, options={"default": null})
	 */
	protected string|null $default = null;

	/**
	 * @throws Throwable
	 */
	public function __construct(
		string $identifier,
		Uuid\UuidInterface|null $id = null,
	)
	{
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->identifier = $identifier;

		$this->dataType = MetadataTypes\DataTypeType::get(MetadataTypes\DataTypeType::DATA_TYPE_UNKNOWN);

		// Static property can not be set or read from device/channel property
		if ($this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			$this->settable = false;
			$this->queryable = false;
		}
	}

	abstract public function getType(): MetadataTypes\PropertyTypeType;

	public function getIdentifier(): string
	{
		return $this->identifier;
	}

	public function getName(): string|null
	{
		return $this->name;
	}

	public function setName(string|null $name): void
	{
		$this->name = $name;
	}

	public function isSettable(): bool
	{
		return $this->settable;
	}

	public function setSettable(bool $settable): void
	{
		if ($settable && $this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			throw new Exceptions\InvalidArgument('Static type property can not be settable');
		}

		$this->settable = $settable;
	}

	public function isQueryable(): bool
	{
		return $this->queryable;
	}

	public function setQueryable(bool $queryable): void
	{
		if ($queryable && $this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			throw new Exceptions\InvalidArgument('Static type property can not be queryable');
		}

		$this->queryable = $queryable;
	}

	public function getDataType(): MetadataTypes\DataTypeType
	{
		return $this->dataType;
	}

	public function setDataType(MetadataTypes\DataTypeType $dataType): void
	{
		$this->dataType = $dataType;
	}

	public function getUnit(): string|null
	{
		return $this->unit;
	}

	public function setUnit(string|null $unit): void
	{
		$this->unit = $unit;
	}

	public function getFormat(): MetadataValueObjects\StringEnumFormat|MetadataValueObjects\NumberRangeFormat|MetadataValueObjects\CombinedEnumFormat|null
	{
		return $this->buildFormat($this->format);
	}

	/**
	 * @param string|Array<int, string>|Array<int, string|int|float|Array<int, string|int|float>|Utils\ArrayHash|null>|Array<int, Array<int, string|Array<int, string|int|float|bool>|Utils\ArrayHash|null>>|null $format
	 */
	public function setFormat(array|string|null $format): void
	{
		if (is_string($format)) {
			if ($this->buildFormat($format) === null) {
				throw new Exceptions\InvalidArgument('Provided property format is not valid');
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
				$plainFormat = implode(':', array_map(static function ($item): string {
					if (is_array($item) || $item instanceof Utils\ArrayHash) {
						return implode(
							'|',
							array_map(
								static fn ($part): string|int|float => is_array($part) ? strval($part) : $part,
								(array) $item,
							),
						);
					}

					return strval($item);
				}, $format));

				if (preg_match(Metadata\Constants::VALUE_FORMAT_NUMBER_RANGE, $plainFormat) === 1) {
					$this->format = $plainFormat;

					return;
				}

				throw new Exceptions\InvalidArgument('Provided property format is not valid');
			} elseif (
				in_array($this->dataType->getValue(), [
					MetadataTypes\DataTypeType::DATA_TYPE_ENUM,
					MetadataTypes\DataTypeType::DATA_TYPE_BUTTON,
					MetadataTypes\DataTypeType::DATA_TYPE_SWITCH,
				], true)
			) {
				$plainFormat = implode(',', array_map(static function ($item): string {
					if (is_array($item) || $item instanceof Utils\ArrayHash) {
						return implode(
							':',
							array_map(
								static fn (string|array|int|float|bool|Utils\ArrayHash|null $part): string => is_array(
									$part,
								) || $part instanceof Utils\ArrayHash
									? implode('|', (array) $part)
									: ($part !== null ? strval(
										$part,
									) : ''),
								(array) $item,
							),
						);
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

				throw new Exceptions\InvalidArgument('Provided property format is not valid');
			}
		}

		$this->format = null;
	}

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

	public function setInvalid(string|null $invalid): void
	{
		$this->invalid = $invalid;
	}

	public function getNumberOfDecimals(): int|null
	{
		return $this->numberOfDecimals;
	}

	public function setNumberOfDecimals(int|null $numberOfDecimals): void
	{
		$this->numberOfDecimals = $numberOfDecimals;
	}

	public function getValue(): bool|float|int|string|DateTimeInterface|MetadataTypes\ButtonPayloadType|MetadataTypes\SwitchPayloadType|null
	{
		if (!$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			throw new Exceptions\InvalidState(
				sprintf('Value is not allowed for property type: %s', strval($this->getType()->getValue())),
			);
		}

		if ($this->value === null) {
			return null;
		}

		return Utilities\ValueHelper::normalizeValue(
			$this->getDataType(),
			$this->value,
			$this->getFormat(),
			$this->getInvalid(),
		);
	}

	public function setValue(string|null $value): void
	{
		if (!$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			throw new Exceptions\InvalidState(
				sprintf('Value is not allowed for property type: %s', strval($this->getType()->getValue())),
			);
		}

		$this->value = $value;
	}

	public function getDefault(): bool|float|int|string|DateTimeInterface|MetadataTypes\ButtonPayloadType|MetadataTypes\SwitchPayloadType|null
	{
		if (!$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			throw new Exceptions\InvalidState(
				sprintf('Value is not allowed for property type: %s', strval($this->getType()->getValue())),
			);
		}

		if ($this->default === null) {
			return null;
		}

		return Utilities\ValueHelper::normalizeValue(
			$this->getDataType(),
			$this->default,
			$this->getFormat(),
			$this->getInvalid(),
		);
	}

	public function setDefault(string|null $default): void
	{
		if (!$this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			throw new Exceptions\InvalidState(
				sprintf('Default value is not allowed for property type: %s', strval($this->getType()->getValue())),
			);
		}

		$this->default = $default;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		$data = [
			'id' => $this->getPlainId(),
			'type' => $this->getType()->getValue(),
			'identifier' => $this->getIdentifier(),
			'name' => $this->getName(),
			'settable' => $this->isSettable(),
			'queryable' => $this->isQueryable(),
			'data_type' => $this->getDataType()->getValue(),
			'unit' => $this->getUnit(),
			'format' => $this->getFormat()?->toArray(),
			'invalid' => $this->getInvalid(),
			'number_of_decimals' => $this->getNumberOfDecimals(),
		];

		if ($this->getType()->equalsValue(MetadataTypes\PropertyTypeType::TYPE_STATIC)) {
			return array_merge($data, [
				'default' => Utilities\ValueHelper::flattenValue($this->getDefault()),
				'value' => Utilities\ValueHelper::flattenValue($this->getValue()),
			]);
		}

		return $data;
	}

	private function buildFormat(
		string|null $format,
	): MetadataValueObjects\StringEnumFormat|MetadataValueObjects\NumberRangeFormat|MetadataValueObjects\CombinedEnumFormat|null
	{
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

}
