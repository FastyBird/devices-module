<?php declare(strict_types = 1);

/**
 * DynamicPropertySchema.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Schemas
 * @since          0.31.0
 *
 * @date           08.02.22
 */

namespace FastyBird\DevicesModule\Schemas\Connectors\Properties;

use Consistence;
use DateTime;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Schemas;
use FastyBird\Metadata\Helpers as MetadataHelpers;
use FastyBird\Metadata\Types as MetadataTypes;
use IPub\SlimRouter\Routing;
use Neomerx\JsonApi;

/**
 * Connector property entity schema
 *
 * @package         FastyBird:DevicesModule!
 * @subpackage      Schemas
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends PropertySchema<Entities\Connectors\Properties\IDynamicProperty>
 */
final class DynamicPropertySchema extends PropertySchema
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES . '/property/connector/' . MetadataTypes\PropertyTypeType::TYPE_DYNAMIC;

	/** @var Models\States\ConnectorPropertiesRepository */
	private Models\States\ConnectorPropertiesRepository $propertiesStatesRepository;

	public function __construct(
		Routing\IRouter $router,
		Models\States\ConnectorPropertiesRepository $propertiesStatesRepository
	) {
		parent::__construct($router);

		$this->propertiesStatesRepository = $propertiesStatesRepository;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass(): string
	{
		return Entities\Connectors\Properties\DynamicProperty::class;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

	/**
	 * @param Entities\Connectors\Properties\IDynamicProperty $property
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, string|bool|int|float|MetadataTypes\SwitchPayloadType|MetadataTypes\ButtonPayloadType|DateTime|Array<int|null>|Array<float|null>|Array<string>|Array<Array<string|null>>|null>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($property, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		try {
			$state = $this->propertiesStatesRepository->findOne($property);

		} catch (Exceptions\NotImplementedException $ex) {
			$state = null;
		}

		$actualValue = $state !== null ? MetadataHelpers\ValueHelper::normalizeValue($property->getDataType(), $state->getActualValue(), $property->getFormat()) : null;
		$expectedValue = $state !== null ? MetadataHelpers\ValueHelper::normalizeValue($property->getDataType(), $state->getExpectedValue(), $property->getFormat()) : null;

		return array_merge((array) parent::getAttributes($property, $context), [
			'actual_value'   => $actualValue instanceof Consistence\Enum\Enum ? (string) $actualValue : $actualValue,
			'expected_value' => $expectedValue instanceof Consistence\Enum\Enum ? (string) $expectedValue : $expectedValue,
			'pending'        => $state !== null && $state->isPending(),
		]);
	}

}
