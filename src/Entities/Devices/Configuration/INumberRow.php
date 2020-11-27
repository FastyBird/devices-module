<?php declare(strict_types = 1);

/**
 * INumberRow.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           01.11.18
 */

namespace FastyBird\DevicesModule\Entities\Devices\Configuration;

interface INumberRow extends IRow
{

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

}
