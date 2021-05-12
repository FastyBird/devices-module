<?php declare(strict_types = 1);

/**
 * IConnector.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           17.01.20
 */

namespace FastyBird\DevicesModule\Entities\Connectors;

use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\DevicesModule\Entities;
use IPub\DoctrineTimestampable;

/**
 * Device communication connector entity interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IConnector extends DatabaseEntities\IEntity,
	DatabaseEntities\IEntityParams,
	DoctrineTimestampable\Entities\IEntityCreated, DoctrineTimestampable\Entities\IEntityUpdated
{

	/**
	 * @return string
	 */
	public function getType(): string;

	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return bool
	 */
	public function isEnabled(): bool;

	/**
	 * @param bool $enabled
	 */
	public function setEnabled(bool $enabled): void;

	/**
	 * @return Entities\Devices\Connectors\IConnector[]
	 */
	public function getDevices(): array;

	/**
	 * @return Entities\Connectors\Controls\IControl[]
	 */
	public function getControls(): array;

	/**
	 * @param string $name
	 *
	 * @return Entities\Connectors\Controls\IControl|null
	 */
	public function getControl(string $name): ?Entities\Connectors\Controls\IControl;

	/**
	 * @param string $name
	 *
	 * @return Entities\Connectors\Controls\IControl|null
	 */
	public function findControl(string $name): ?Entities\Connectors\Controls\IControl;

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasControl(string $name): bool;

	/**
	 * @param Entities\Connectors\Controls\IControl[] $control
	 *
	 * @return void
	 */
	public function setControls(array $control): void;

	/**
	 * @param Entities\Connectors\Controls\IControl $control
	 *
	 * @return void
	 */
	public function addControl(Entities\Connectors\Controls\IControl $control): void;

	/**
	 * @param Entities\Connectors\Controls\IControl $control
	 *
	 * @return void
	 */
	public function removeControl(Entities\Connectors\Controls\IControl $control): void;

	/**
	 * @return mixed[]
	 */
	public function toArray(): array;

}
