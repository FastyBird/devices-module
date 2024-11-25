<?php declare(strict_types = 1);

/**
 * ActionValues.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Documents
 * @since          1.0.0
 *
 * @date           23.01.24
 */

namespace FastyBird\Module\Devices\Documents\States;

use DateTimeInterface;
use FastyBird\Core\Application\Documents as ApplicationDocuments;
use FastyBird\Core\Tools\Utilities as ToolsUtilities;
use FastyBird\Library\Metadata\Constants as MetadataConstants;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use Orisai\ObjectMapper;
use function array_merge;

/**
 * Property set action value document
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Documents
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
#[ApplicationDocuments\Mapping\Document]
final readonly class ActionValues implements ApplicationDocuments\Document
{

	public function __construct(
		#[ObjectMapper\Rules\AnyOf([
			new ObjectMapper\Rules\DateTimeValue(format: DateTimeInterface::ATOM),
			new ObjectMapper\Rules\BackedEnumValue(class: MetadataTypes\Payloads\Button::class),
			new ObjectMapper\Rules\BackedEnumValue(class: MetadataTypes\Payloads\Switcher::class),
			new ObjectMapper\Rules\BackedEnumValue(class: MetadataTypes\Payloads\Cover::class),
			new ObjectMapper\Rules\BoolValue(),
			new ObjectMapper\Rules\IntValue(),
			new ObjectMapper\Rules\FloatValue(),
			new ObjectMapper\Rules\StringValue(notEmpty: true),
			new ObjectMapper\Rules\NullValue(castEmptyString: true),
		])]
		#[ObjectMapper\Modifiers\FieldName('actual_value')]
		private bool|float|int|string|DateTimeInterface|MetadataTypes\Payloads\Payload|null $actualValue = MetadataConstants::VALUE_NOT_SET,
		#[ObjectMapper\Rules\AnyOf([
			new ObjectMapper\Rules\DateTimeValue(format: DateTimeInterface::ATOM),
			new ObjectMapper\Rules\BackedEnumValue(class: MetadataTypes\Payloads\Button::class),
			new ObjectMapper\Rules\BackedEnumValue(class: MetadataTypes\Payloads\Switcher::class),
			new ObjectMapper\Rules\BackedEnumValue(class: MetadataTypes\Payloads\Cover::class),
			new ObjectMapper\Rules\BoolValue(),
			new ObjectMapper\Rules\IntValue(),
			new ObjectMapper\Rules\FloatValue(),
			new ObjectMapper\Rules\StringValue(notEmpty: true),
			new ObjectMapper\Rules\NullValue(castEmptyString: true),
		])]
		#[ObjectMapper\Modifiers\FieldName('expected_value')]
		private bool|float|int|string|DateTimeInterface|MetadataTypes\Payloads\Payload|null $expectedValue = MetadataConstants::VALUE_NOT_SET,
	)
	{
	}

	public function getActualValue(): bool|float|int|string|DateTimeInterface|MetadataTypes\Payloads\Payload|null
	{
		return $this->actualValue;
	}

	public function getExpectedValue(): bool|float|int|string|DateTimeInterface|MetadataTypes\Payloads\Payload|null
	{
		return $this->expectedValue;
	}

	public function toArray(): array
	{
		$data = [];

		if ($this->getActualValue() !== MetadataConstants::VALUE_NOT_SET) {
			$data = array_merge($data, [
				'actual_value' => ToolsUtilities\Value::flattenValue($this->getActualValue()),
			]);
		}

		if ($this->getExpectedValue() !== MetadataConstants::VALUE_NOT_SET) {
			$data = array_merge($data, [
				'expected_value' => ToolsUtilities\Value::flattenValue($this->getExpectedValue()),
			]);
		}

		return $data;
	}

}
