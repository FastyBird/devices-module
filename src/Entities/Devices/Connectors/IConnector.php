<?php declare(strict_types = 1);

/**
 * IConnector.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           17.01.21
 */

namespace FastyBird\DevicesModule\Entities\Devices\Connectors;

use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\DevicesModule\Entities;
use IPub\DoctrineTimestampable;

/**
 * Device connector info entity interface
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
	 * @return Entities\Devices\IDevice
	 */
	public function getDevice(): Entities\Devices\IDevice;

	/**
	 * @return Entities\Connectors\IConnector
	 */
	public function getConnector(): Entities\Connectors\IConnector;

	/**
	 * @return string|null
	 */
	public function getUsername(): ?string;

	/**
	 * @param string|null $username
	 *
	 * @return void
	 */
	public function setUsername(?string $username): void;

	/**
	 * @return string|null
	 */
	public function getPassword(): ?string;

	/**
	 * @param string|null $password
	 *
	 * @return void
	 */
	public function setPassword(?string $password): void;

	/**
	 * @return int|null
	 */
	public function getAddress(): ?int;

	/**
	 * @param int|null $address
	 *
	 * @return void
	 */
	public function setAddress(?int $address): void;

	/**
	 * @return int|null
	 */
	public function getMaxPacketLength(): ?int;

	/**
	 * @param int|null $maxPacketLength
	 *
	 * @return void
	 */
	public function setMaxPacketLength(?int $maxPacketLength): void;

	/**
	 * @return bool
	 */
	public function hasDescriptionSupport(): bool;

	/**
	 * @param bool $descriptionSupport
	 *
	 * @return void
	 */
	public function setDescriptionSupport(bool $descriptionSupport): void;

	/**
	 * @return bool
	 */
	public function hasSettingsSupport(): bool;

	/**
	 * @param bool $settingsSupport
	 *
	 * @return void
	 */
	public function setSettingsSupport(bool $settingsSupport): void;

	/**
	 * @return int|null
	 */
	public function getConfiguredKeyLength(): ?int;

	/**
	 * @param int|null $configuredKeyLength
	 *
	 * @return void
	 */
	public function setConfiguredKeyLength(?int $configuredKeyLength): void;

	/**
	 * @return mixed[]
	 */
	public function toArray(): array;

}
