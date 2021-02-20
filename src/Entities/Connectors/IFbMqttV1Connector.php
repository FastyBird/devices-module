<?php declare(strict_types = 1);

/**
 * IFbMqttV1Connector.php
 *
 * @license        More in license.md
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
 * FB MQTT v1 connector entity interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IFbMqttV1Connector extends IConnector
{

	/**
	 * @return string|null
	 */
	public function getServer(): ?string;

	/**
	 * @param int $server
	 *
	 * @return void
	 */
	public function setServer(int $server): void;

	/**
	 * @return int|null
	 */
	public function getPort(): ?int;

	/**
	 * @param string $port
	 *
	 * @return void
	 */
	public function setPort(string $port): void;

	/**
	 * @return string|null
	 */
	public function getUsername(): ?string;

	/**
	 * @param int $username
	 *
	 * @return void
	 */
	public function setUsername(int $username): void;

	/**
	 * @return string|null
	 */
	public function getPassword(): ?string;

	/**
	 * @param int $password
	 *
	 * @return void
	 */
	public function setPassword(int $password): void;

}
