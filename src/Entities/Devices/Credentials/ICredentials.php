<?php declare(strict_types = 1);

/**
 * ICredentials.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           10.12.20
 */

namespace FastyBird\DevicesModule\Entities\Devices\Credentials;

use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\DevicesModule\Entities;
use IPub\DoctrineTimestampable;

/**
 * Credentials info entity interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface ICredentials extends DatabaseEntities\IEntity,
	DoctrineTimestampable\Entities\IEntityCreated, DoctrineTimestampable\Entities\IEntityUpdated
{

	/**
	 * @return string
	 */
	public function getUsername(): string;

	/**
	 * @param string $username
	 *
	 * @return void
	 */
	public function setUsername(string $username): void;

	/**
	 * @return string
	 */
	public function getPassword(): string;

	/**
	 * @param string $password
	 *
	 * @return void
	 */
	public function setPassword(string $password): void;

	/**
	 * @return Entities\Devices\INetworkDevice
	 *
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 * @ORM\OneToOne(targetEntity="FastyBird\DevicesModule\Devices\NetworkDevice", mappedBy="credentials", cascade={"persist", "remove"})
	 */
	public function getDevice(): Entities\Devices\INetworkDevice;

	/**
	 * @return mixed[]
	 */
	public function toArray(): array;

}
