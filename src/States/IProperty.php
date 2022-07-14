<?php declare(strict_types = 1);

/**
 * IProperty.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     States
 * @since          0.1.0
 *
 * @date           03.03.20
 */

namespace FastyBird\DevicesModule\States;

use Ramsey\Uuid;

/**
 * Property interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     States
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IProperty
{

	/**
	 * @return Uuid\UuidInterface
	 */
	public function getId(): Uuid\UuidInterface;

	/**
	 * @param float|bool|int|string|null $actual
	 *
	 * @return void
	 */
	public function setActualValue(float|bool|int|string|null $actual): void;

	/**
	 * @return float|bool|int|string|null
	 */
	public function getActualValue(): float|bool|int|string|null;

	/**
	 * @param float|bool|int|string|null $expected
	 *
	 * @return void
	 */
	public function setExpectedValue(float|bool|int|string|null $expected): void;

	/**
	 * @return float|bool|int|string|null
	 */
	public function getExpectedValue(): float|bool|int|string|null;

	/**
	 * @param bool|string|null $pending
	 *
	 * @return void
	 */
	public function setPending(bool|string|null $pending): void;

	/**
	 * @return bool|string|null
	 */
	public function getPending(): bool|string|null;

	/**
	 * @return bool
	 */
	public function isPending(): bool;

	/**
	 * @param bool $valid
	 *
	 * @return void
	 */
	public function setValid(bool $valid): void;

	/**
	 * @return bool
	 */
	public function isValid(): bool;

	/**
	 * @return Array<string, mixed>
	 */
	public function toArray(): array;

}
