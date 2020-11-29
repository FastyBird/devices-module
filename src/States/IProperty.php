<?php declare(strict_types = 1);

/**
 * IProperty.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     States
 * @since          0.1.0
 *
 * @date           03.03.20
 */

namespace FastyBird\DevicesModule\States;

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
	 * @return float|int|bool|string|null
	 */
	public function getValue();

	/**
	 * @param float|int|string|null $expected
	 *
	 * @return void
	 */
	public function setExpected($expected): void;

	/**
	 * @return float|int|bool|string|null
	 */
	public function getExpected();

	/**
	 * @return bool
	 */
	public function isPending(): bool;

}
