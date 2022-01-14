<?php declare(strict_types = 1);

/**
 * IProperty.php
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

use DateTime;
use FastyBird\Metadata\Types as MetadataTypes;
use IPub\DoctrineTimestampable;

/**
 * Device or channel property entity interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IProperty extends IEntity,
	IKey, IEntityParams,
	DoctrineTimestampable\Entities\IEntityCreated, DoctrineTimestampable\Entities\IEntityUpdated
{

	/**
	 * @return MetadataTypes\PropertyTypeType
	 */
	public function getType(): MetadataTypes\PropertyTypeType;

	/**
	 * @return string
	 */
	public function getIdentifier(): string;

	/**
	 * @return string|null
	 */
	public function getName(): ?string;

	/**
	 * @param string|null $name
	 *
	 * @return void
	 */
	public function setName(?string $name): void;

	/**
	 * @return bool
	 */
	public function isSettable(): bool;

	/**
	 * @param bool $settable
	 *
	 * @return void
	 */
	public function setSettable(bool $settable): void;

	/**
	 * @return bool
	 */
	public function isQueryable(): bool;

	/**
	 * @param bool $queryable
	 *
	 * @return void
	 */
	public function setQueryable(bool $queryable): void;

	/**
	 * @return MetadataTypes\DataTypeType|null
	 */
	public function getDataType(): ?MetadataTypes\DataTypeType;

	/**
	 * @param MetadataTypes\DataTypeType|null $dataType
	 *
	 * @return void
	 */
	public function setDataType(?MetadataTypes\DataTypeType $dataType): void;

	/**
	 * @return string|null
	 */
	public function getUnit(): ?string;

	/**
	 * @param string|null $units
	 *
	 * @return void
	 */
	public function setUnit(?string $units): void;

	/**
	 * @return Array<string>|Array<Array<string|null>>|Array<int|null>|Array<float|null>|null
	 */
	public function getFormat(): ?array;

	/**
	 * @param string|null $format
	 *
	 * @return void
	 */
	public function setFormat(?string $format): void;

	/**
	 * @return string|int|float|null
	 */
	public function getInvalid();

	/**
	 * @param string|null $invalid
	 *
	 * @return void
	 */
	public function setInvalid(?string $invalid): void;

	/**
	 * @return int|null
	 */
	public function getNumberOfDecimals(): ?int;

	/**
	 * @param int|null $numberOfDecimals
	 *
	 * @return void
	 */
	public function setNumberOfDecimals(?int $numberOfDecimals): void;

	/**
	 * @return bool|float|int|string|DateTime|MetadataTypes\ButtonPayloadType|MetadataTypes\SwitchPayloadType|null
	 */
	public function getValue();

	/**
	 * @param string|null $value
	 *
	 * @return void
	 */
	public function setValue(?string $value): void;

	/**
	 * @return bool|float|int|string|DateTime|MetadataTypes\ButtonPayloadType|MetadataTypes\SwitchPayloadType|null
	 */
	public function getDefault();

	/**
	 * @param string|null $default
	 *
	 * @return void
	 */
	public function setDefault(?string $default): void;

}
