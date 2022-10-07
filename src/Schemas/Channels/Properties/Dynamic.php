<?php declare(strict_types = 1);

/**
 * Dynamic.php
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

namespace FastyBird\DevicesModule\Schemas\Channels\Properties;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Schemas;
use FastyBird\DevicesModule\Utilities;
use FastyBird\Metadata\Types as MetadataTypes;
use IPub\SlimRouter\Routing;
use Neomerx\JsonApi;

/**
 * Channel property entity schema
 *
 * @package         FastyBird:DevicesModule!
 * @subpackage      Schemas
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends Property<Entities\Channels\Properties\Dynamic>
 */
final class Dynamic extends Property
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = MetadataTypes\ModuleSourceType::SOURCE_MODULE_DEVICES . '/property/channel/' . MetadataTypes\PropertyTypeType::TYPE_DYNAMIC;

	/** @var Models\States\ChannelPropertiesRepository */
	private Models\States\ChannelPropertiesRepository $propertiesStatesRepository;

	/**
	 * @param Routing\IRouter $router
	 * @param Models\Channels\Properties\PropertiesRepository $propertiesRepository
	 * @param Models\States\ChannelPropertiesRepository $propertiesStatesRepository
	 */
	public function __construct(
		Routing\IRouter $router,
		Models\Channels\Properties\PropertiesRepository $propertiesRepository,
		Models\States\ChannelPropertiesRepository $propertiesStatesRepository
	) {
		parent::__construct($router, $propertiesRepository);

		$this->propertiesStatesRepository = $propertiesStatesRepository;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass(): string
	{
		return Entities\Channels\Properties\Dynamic::class;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

	/**
	 * @param Entities\Channels\Properties\Dynamic $property
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, string|bool|int|float|string[]|Array<int, int|float|Array<int, string|int|float|null>|null>|Array<int, Array<int, string|Array<int, string|int|float|bool>|null>>|null>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($property, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		try {
			$state = $this->propertiesStatesRepository->findOne($property);

		} catch (Exceptions\NotImplemented) {
			$state = null;
		}

		$actualValue = $state !== null ? Utilities\ValueHelper::normalizeValue($property->getDataType(), $state->getActualValue(), $property->getFormat(), $property->getInvalid()) : null;
		$expectedValue = $state !== null ? Utilities\ValueHelper::normalizeValue($property->getDataType(), $state->getExpectedValue(), $property->getFormat(), $property->getInvalid()) : null;

		return array_merge((array) parent::getAttributes($property, $context), [
			'actual_value'   => Utilities\ValueHelper::flattenValue($actualValue),
			'expected_value' => Utilities\ValueHelper::flattenValue($expectedValue),
			'pending'        => $state !== null && $state->isPending(),
		]);
	}

}
