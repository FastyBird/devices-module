<?php declare(strict_types = 1);

/**
 * IRow.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           07.08.20
 */

namespace FastyBird\DevicesModule\Entities;

use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\DevicesModule\Types;
use IPub\DoctrineTimestampable;

/**
 * Device or channel configuration row entity interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IRow extends DatabaseEntities\IEntity,
	IKey,
	DatabaseEntities\IEntityParams,
	DoctrineTimestampable\Entities\IEntityCreated, DoctrineTimestampable\Entities\IEntityUpdated
{

	/**
	 * @return string
	 */
	public function getIdentifier(): string;

	/**
	 * @param string|null $name
	 *
	 * @return void
	 */
	public function setName(?string $name): void;

	/**
	 * @return string|null
	 */
	public function getName(): ?string;

	/**
	 * @param string|null $comment
	 *
	 * @return void
	 */
	public function setComment(?string $comment): void;

	/**
	 * @return Types\DataTypeType
	 */
	public function getDataType(): Types\DataTypeType;

	/**
	 * @param string $dataType
	 *
	 * @return void
	 */
	public function setDataType(string $dataType): void;

	/**
	 * @return string|null
	 */
	public function getComment(): ?string;

	/**
	 * @param string|null $default
	 *
	 * @return void
	 */
	public function setDefault(?string $default): void;

	/**
	 * @return mixed|null
	 */
	public function getDefault();

	/**
	 * @param string|null $value
	 *
	 * @return void
	 */
	public function setValue(?string $value): void;

	/**
	 * @return mixed|null
	 */
	public function getValue();

	/**
	 * @param float|null $min
	 *
	 * @return void
	 */
	public function setMin(?float $min): void;

	/**
	 * @return float|null
	 */
	public function getMin(): ?float;

	/**
	 * @return bool
	 */
	public function hasMin(): bool;

	/**
	 * @param float|null $max
	 *
	 * @return void
	 */
	public function setMax(?float $max): void;

	/**
	 * @return float|null
	 */
	public function getMax(): ?float;

	/**
	 * @return bool
	 */
	public function hasMax(): bool;

	/**
	 * @param float|null $step
	 *
	 * @return void
	 */
	public function setStep(?float $step): void;

	/**
	 * @return float|null
	 */
	public function getStep(): ?float;

	/**
	 * @return bool
	 */
	public function hasStep(): bool;

	/**
	 * @param mixed[] $values
	 *
	 * @return void
	 */
	public function setValues(array $values): void;

	/**
	 * @return mixed[]
	 */
	public function getValues(): array;

	/**
	 * @return mixed[]
	 */
	public function toArray(): array;

}
