<?php declare(strict_types = 1);

/**
 * IFbMqttConnector.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           20.02.21
 */

namespace FastyBird\DevicesModule\Entities\Connectors;

/**
 * FB MQTT connector entity interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IFbMqttConnector extends IConnector
{

	/**
	 * @return string|null
	 */
	public function getServer(): ?string;

	/**
	 * @param string|null $server
	 *
	 * @return void
	 */
	public function setServer(?string $server): void;

	/**
	 * @return int|null
	 */
	public function getPort(): ?int;

	/**
	 * @param int|null $port
	 *
	 * @return void
	 */
	public function setPort(?int $port): void;

	/**
	 * @return int|null
	 */
	public function getSecuredPort(): ?int;

	/**
	 * @param int|null $port
	 *
	 * @return void
	 */
	public function setSecuredPort(?int $port): void;

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

}
