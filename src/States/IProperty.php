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
	 * @param string|null $actual
	 *
	 * @return void
	 */
	public function setActualValue(?string $actual): void;

	/**
	 * @return string|null
	 */
	public function getActualValue(): ?string;

	/**
	 * @param string|null $expected
	 *
	 * @return void
	 */
	public function setExpectedValue(?string $expected): void;

	/**
	 * @return string|null
	 */
	public function getExpectedValue(): ?string;

	/**
	 * @param bool $pending
	 *
	 * @return void
	 */
	public function setPending(bool $pending): void;

	/**
	 * @return bool
	 */
	public function isPending(): bool;

	/**
	 * @return mixed[]
	 */
	public function toArray(): array;

}
