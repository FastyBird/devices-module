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
use FastyBird\ModulesMetadata\Helpers as ModulesMetadataHelpers;
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;
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
	public const SCHEMA_TYPE = 'devices-module/device-property-dynamic';

	/** @var Models\States\IPropertyRepository|null */
	private ?Models\States\IPropertyRepository $propertyRepository;

	public function __construct(
		Routing\IRouter $router,
		?Models\States\IPropertyRepository $propertyRepository
	) {
		parent::__construct($router);

		$this->propertyRepository = $propertyRepository;
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
	 * @return iterable<string, string|bool|int|float|ModulesMetadataTypes\SwitchPayloadType|ModulesMetadataTypes\ButtonPayloadType|DateTime|Array<int|null>|Array<float|null>|Array<string>|Array<Array<string|null>>|null>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($property, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		$dataType = $property->getDataType();

		$state = $this->propertyRepository === null ? null : $this->propertyRepository->findOne($property->getId());

		$actualValue = $state !== null ? ($dataType !== null ? ModulesMetadataHelpers\ValueHelper::normalizeValue($dataType, $state->getActualValue(), $property->getFormat()) : $state->getActualValue()) : null;
		$expectedValue = $state !== null ? ($dataType !== null ? ModulesMetadataHelpers\ValueHelper::normalizeValue($dataType, $state->getExpectedValue(), $property->getFormat()) : $state->getExpectedValue()) : null;

		return array_merge((array) parent::getAttributes($property, $context), [
			'actual_value'   => $actualValue instanceof Consistence\Enum\Enum ? (string) $actualValue : $actualValue,
			'expected_value' => $expectedValue instanceof Consistence\Enum\Enum ? (string) $expectedValue : $expectedValue,
			'pending'        => $state !== null && $state->isPending(),
		]);
	}

}
