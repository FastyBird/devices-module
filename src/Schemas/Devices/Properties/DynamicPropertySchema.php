<?php declare(strict_types = 1);

/**
 * DynamicPropertySchema.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Schemas
 * @since          0.1.0
 *
 * @date           13.04.19
 */

namespace FastyBird\DevicesModule\Schemas\Devices\Properties;

use Consistence;
use DateTime;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Schemas;
use FastyBird\Metadata\Helpers as MetadataHelpers;
use FastyBird\Metadata\Types as MetadataTypes;
use IPub\SlimRouter\Routing;
use Neomerx\JsonApi;

/**
 * Device property entity schema
 *
 * @package         FastyBird:DevicesModule!
 * @subpackage      Schemas
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends PropertySchema<Entities\Devices\Properties\IDynamicProperty>
 */
final class DynamicPropertySchema extends PropertySchema
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES . '/property/device/' . MetadataTypes\PropertyTypeType::TYPE_DYNAMIC;

	/** @var Models\States\IDevicePropertiesRepository|null */
	private ?Models\States\IDevicePropertiesRepository $propertiesStatesRepository;

	public function __construct(
		Routing\IRouter                                 $router,
		Models\Devices\Properties\IPropertiesRepository $propertiesRepository,
		?Models\States\IDevicePropertiesRepository $propertiesStatesRepository
	) {
		parent::__construct($router, $propertiesRepository);

		$this->propertiesStatesRepository = $propertiesStatesRepository;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass(): string
	{
		return Entities\Devices\Properties\DynamicProperty::class;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

	/**
	 * @param Entities\Devices\Properties\IDynamicProperty $property
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, string|bool|int|float|MetadataTypes\SwitchPayloadType|MetadataTypes\ButtonPayloadType|DateTime|Array<int|null>|Array<float|null>|Array<string>|Array<Array<string|null>>|null>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($property, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		$state = $this->propertiesStatesRepository === null ? null : $this->propertiesStatesRepository->findOne($property);

		$actualValue = $state !== null ? MetadataHelpers\ValueHelper::normalizeValue($property->getDataType(), $state->getActualValue(), $property->getFormat()) : null;
		$expectedValue = $state !== null ? MetadataHelpers\ValueHelper::normalizeValue($property->getDataType(), $state->getExpectedValue(), $property->getFormat()) : null;

		return array_merge((array) parent::getAttributes($property, $context), [
			'actual_value'   => $actualValue instanceof Consistence\Enum\Enum ? (string) $actualValue : $actualValue,
			'expected_value' => $expectedValue instanceof Consistence\Enum\Enum ? (string) $expectedValue : $expectedValue,
			'pending'        => $state !== null && $state->isPending(),
		]);
	}

}
