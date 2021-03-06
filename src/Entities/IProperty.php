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

use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;
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
	IKey,
	DoctrineTimestampable\Entities\IEntityCreated, DoctrineTimestampable\Entities\IEntityUpdated
{

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
	 * @return ModulesMetadataTypes\DataTypeType|null
	 */
	public function getDataType(): ?ModulesMetadataTypes\DataTypeType;

	/**
	 * @param string|null $dataType
	 *
	 * @return void
	 */
	public function setDataType(?string $dataType): void;

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
	 * @return string[]|string|int[]|float[]|null
	 */
	public function getFormat();

	/**
	 * @param string|null $format
	 *
	 * @return void
	 */
	public function setFormat(?string $format): void;

	/**
	 * @return mixed[]
	 */
	public function toArray(): array;

}
