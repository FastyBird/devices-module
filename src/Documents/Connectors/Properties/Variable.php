<?php declare(strict_types = 1);

/**
 * Variable.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Documents
 * @since          1.0.0
 *
 * @date           02.06.22
 */

namespace FastyBird\Module\Devices\Documents\Connectors\Properties;

use DateTimeInterface;
use FastyBird\Library\Metadata\Documents\Mapping as DOC;
use FastyBird\Library\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use FastyBird\Library\Metadata\Utilities as MetadataUtilities;
use FastyBird\Module\Devices\Entities;
use FastyBird\Module\Devices\Exceptions;
use FastyBird\Module\Devices\Types;
use Orisai\ObjectMapper;
use Ramsey\Uuid;
use TypeError;
use ValueError;
use function array_merge;

/**
 * Connector variable property document
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Documents
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
#[DOC\Document(entity: Entities\Connectors\Properties\Variable::class)]
final class Variable extends Property
{

	/**
	 * @param string|array<int, string>|array<int, int>|array<int, float>|array<int, bool|string|int|float|array<int, bool|string|int|float>|null>|array<int, array<int, string|array<int, string|int|float|bool>|null>>|null $format
	 */
	public function __construct(
		Uuid\UuidInterface $id,
		Uuid\UuidInterface $connector,
		Types\PropertyCategory $category,
		string $identifier,
		string|null $name,
		MetadataTypes\DataType $dataType,
		string|null $unit = null,
		string|array|null $format = null,
		float|int|string|null $invalid = null,
		int|null $scale = null,
		int|float|null $step = null,
		bool|float|int|string|null $default = null,
		Uuid\UuidInterface|string|null $valueTransformer = null,
		#[ObjectMapper\Rules\AnyOf([
			new ObjectMapper\Rules\BoolValue(),
			new ObjectMapper\Rules\IntValue(),
			new ObjectMapper\Rules\FloatValue(),
			new ObjectMapper\Rules\StringValue(notEmpty: true),
			new ObjectMapper\Rules\NullValue(castEmptyString: true),
		])]
		private readonly bool|float|int|string|null $value = null,
		Uuid\UuidInterface|null $owner = null,
		DateTimeInterface|null $createdAt = null,
		DateTimeInterface|null $updatedAt = null,
	)
	{
		parent::__construct(
			$id,
			$connector,
			$category,
			$identifier,
			$name,
			$dataType,
			$unit,
			$format,
			$invalid,
			$scale,
			$step,
			$default,
			$valueTransformer,
			$owner,
			$createdAt,
			$updatedAt,
		);
	}

	public static function getType(): string
	{
		return Entities\Connectors\Properties\Variable::getType();
	}

	/**
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws TypeError
	 * @throws ValueError
	 */
	public function getValue(): bool|float|int|string|DateTimeInterface|MetadataTypes\Payloads\Payload|null
	{
		try {
			return MetadataUtilities\Value::normalizeValue(
				$this->value,
				$this->getDataType(),
				$this->getFormat(),
			);
		} catch (MetadataExceptions\InvalidValue) {
			return null;
		}
	}

	/**
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws TypeError
	 * @throws ValueError
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'value' => MetadataUtilities\Value::flattenValue($this->getValue()),
		]);
	}

}
