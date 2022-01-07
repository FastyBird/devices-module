<?php declare(strict_types = 1);

/**
 * ModbusConnector.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.6.0
 *
 * @date           07.12.21
 */

namespace FastyBird\DevicesModule\Entities\Connectors;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;

/**
 * @ORM\Entity
 */
class ModbusConnector extends Entities\Connectors\Connector implements IModbusConnector
{

	/**
	 * @var string|null
	 * @IPubDoctrine\Crud(is="writable")
	 */
	protected ?string $serialInterface = null;

	/**
	 * @var int|null
	 * @IPubDoctrine\Crud(is="writable")
	 */
	protected ?int $baudRate = null;

	/**
	 * {@inheritDoc}
	 */
	public function getType(): ModulesMetadataTypes\ConnectorTypeType
	{
		return ModulesMetadataTypes\ConnectorTypeType::get(ModulesMetadataTypes\ConnectorTypeType::TYPE_MODBUS);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSerialInterface(): ?string
	{
		return $this->getParam('serial_interface');
	}

	/**
	 * {@inheritDoc}
	 */
	public function setSerialInterface(string $serialInterface): void
	{
		$this->setParam('serial_interface', $serialInterface);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBaudRate(): ?int
	{
		return $this->getParam('baud_rate');
	}

	/**
	 * {@inheritDoc}
	 */
	public function setBaudRate(?int $baudRate): void
	{
		$this->setParam('baud_rate', $baudRate);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'serial_interface' => $this->getSerialInterface(),
			'baud_rate'        => $this->getBaudRate(),
		]);
	}

}
