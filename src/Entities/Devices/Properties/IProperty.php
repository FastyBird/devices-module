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
 * @date           02.11.18
 */

namespace FastyBird\DevicesModule\Entities\Devices\Properties;

use FastyBird\DevicesModule\Entities;

/**
 * Device property entity interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IProperty extends Entities\IProperty
{

	/**
	 * @return IProperty|null
	 */
	public function getParent(): ?IProperty;

	/**
	 * @param IProperty $device
	 *
	 * @return void
	 */
	public function setParent(IProperty $device): void;

	/**
	 * @return void
	 */
	public function removeParent(): void;

	/**
	 * @return IProperty[]
	 */
	public function getChildren(): array;

	/**
	 * @param IProperty[] $children
	 *
	 * @return void
	 */
	public function setChildren(array $children): void;

	/**
	 * @param IProperty $child
	 *
	 * @return void
	 */
	public function addChild(IProperty $child): void;

	/**
	 * @param IProperty $child
	 *
	 * @return void
	 */
	public function removeChild(IProperty $child): void;

	/**
	 * @return Entities\Devices\IDevice
	 */
	public function getDevice(): Entities\Devices\IDevice;

}
