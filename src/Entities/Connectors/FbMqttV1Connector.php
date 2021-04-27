<?php declare(strict_types = 1);

/**
 * FbMqttV1Connector.php
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

use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;

/**
 * @ORM\Entity
 */
class FbMqttV1Connector extends Entities\Connectors\Connector implements IFbMqttV1Connector
{

	/** @var string */
	protected string $type = 'fb-mqtt-v1';

	/**
	 * @var string|null
	 * @IPubDoctrine\Crud(is="writable")
	 */
	protected ?string $server = null;

	/**
	 * @var int|null
	 * @IPubDoctrine\Crud(is="writable")
	 */
	protected ?int $port = null;

	/**
	 * @var string|null
	 * @IPubDoctrine\Crud(is="writable")
	 */
	protected ?string $username = null;

	/**
	 * @var string|null
	 * @IPubDoctrine\Crud(is="writable")
	 */
	protected ?string $password = null;

	/**
	 * {@inheritDoc}
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getServer(): ?string
	{
		return $this->getParam('server');
	}

	/**
	 * {@inheritDoc}
	 */
	public function setServer(?string $server): void
	{
		$this->setParam('server', $server);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPort(): ?int
	{
		return $this->getParam('port');
	}

	/**
	 * {@inheritDoc}
	 */
	public function setPort(?int $port): void
	{
		$this->setParam('port', $port);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSecuredPort(): ?int
	{
		return $this->getParam('secured_port');
	}

	/**
	 * {@inheritDoc}
	 */
	public function setSecuredPort(?int $port): void
	{
		$this->setParam('secured_port', $port);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUsername(): ?string
	{
		return $this->getParam('username');
	}

	/**
	 * {@inheritDoc}
	 */
	public function setUsername(?string $username): void
	{
		$this->setParam('username', $username);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPassword(): ?string
	{
		return $this->getParam('password');
	}

	/**
	 * {@inheritDoc}
	 */
	public function setPassword(?string $password): void
	{
		$this->setParam('password', $password);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'server'       => $this->getServer(),
			'port'         => $this->getPort(),
			'secured_port' => $this->getSecuredPort(),
			'username'     => $this->getUsername(),
		]);
	}

}
