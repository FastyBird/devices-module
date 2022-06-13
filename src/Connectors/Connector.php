<?php declare(strict_types = 1);

/**
 * Connector.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Connectors
 * @since          0.60.0
 *
 * @date           31.05.22
 */

namespace FastyBird\DevicesModule\Connectors;

use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Types as MetadataTypes;
use Nette;
use SplObjectStorage;

/**
 * Devices connector
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Connectors
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Connector
{

	use Nette\SmartObject;

	/** @var IConnector|null */
	private ?IConnector $connector;

	/** @var SplObjectStorage<IConnector, null> */
	private SplObjectStorage $connectors;

	public function execute() : void
	{
	}

	/**
	 * @param MetadataTypes\RoutingKeyType $routingKey
	 * @param MetadataEntities\IEntity $entity
	 *
	 * @return void
	 */
	public function handlePropertyCommand(MetadataTypes\RoutingKeyType $routingKey, MetadataEntities\IEntity $entity): void
	{
		if ($this->connector === null) {
			return;
		}

		if (
			$routingKey->equalsValue(MetadataTypes\RoutingKeyType::ROUTE_DEVICE_PROPERTY_ACTION)
			&& (
				$entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity
				|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
				|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
			)
		) {
			$this->connector->writeProperty($entity);

		} elseif (
			$routingKey->equalsValue(MetadataTypes\RoutingKeyType::ROUTE_CHANNEL_PROPERTY_ACTION)
			&& (
				$entity instanceof MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity
				|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity
				|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
			)
		) {
			$this->connector->writeProperty($entity);

		} elseif (
			$routingKey->equalsValue(MetadataTypes\RoutingKeyType::ROUTE_CONNECTOR_PROPERTY_ACTION)
			&& (
				$entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity
				|| $entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity
				|| $entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity
			)
		) {
			$this->connector->writeProperty($entity);
		}
	}

	/**
	 * @param MetadataTypes\RoutingKeyType $routingKey
	 * @param MetadataEntities\IEntity $entity
	 *
	 * @return void
	 */
	public function handleControlCommand(MetadataTypes\RoutingKeyType $routingKey, MetadataEntities\IEntity $entity): void
	{
		if ($this->connector === null) {
			return;
		}

		if (
			$routingKey->equalsValue(MetadataTypes\RoutingKeyType::ROUTE_DEVICE_ACTION)
			&& $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceControlEntity
		) {
			$this->connector->writeControl($entity);

		} elseif (
			$routingKey->equalsValue(MetadataTypes\RoutingKeyType::ROUTE_CHANNEL_ACTION)
			&& $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelControlEntity
		) {
			$this->connector->writeControl($entity);

		} elseif (
			$routingKey->equalsValue(MetadataTypes\RoutingKeyType::ROUTE_CONNECTOR_ACTION)
			&& $entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorControlEntity
		) {
			$this->connector->writeControl($entity);
		}
	}

	/**
	 * @param MetadataEntities\IEntity $entity
	 *
	 * @return void
	 */
	public function handleEntityReported(MetadataEntities\IEntity $entity): void
	{
		if ($this->connector === null) {
			return;
		}
	}

	/**
	 * @param MetadataEntities\IEntity $entity
	 *
	 * @return void
	 */
	public function handleEntityCreated(MetadataEntities\IEntity $entity): void
	{
		if ($this->connector === null) {
			return;
		}
	}

	/**
	 * @param MetadataEntities\IEntity $entity
	 *
	 * @return void
	 */
	public function handleEntityUpdated(MetadataEntities\IEntity $entity): void
	{
		if ($this->connector === null) {
			return;
		}
	}

	/**
	 * @param MetadataEntities\IEntity $entity
	 *
	 * @return void
	 */
	public function handleEntityDeleted(MetadataEntities\IEntity $entity): void
	{
		if ($this->connector === null) {
			return;
		}
	}

	/**
	 * @param IConnector $connector
	 *
	 * @return void
	 */
	public function registerConnector(IConnector $connector): void
	{
		if ($this->connectors->contains($connector) === false) {
			$this->connectors->attach($connector);
		}
	}

}
