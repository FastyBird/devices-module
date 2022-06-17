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

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\Metadata\Entities as MetadataEntities;
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

	public function execute(Entities\Connectors\IConnector $connector): void
	{
		$this->connectors->rewind();

		foreach ($this->connectors as $extension) {
			if ($connector->getType() === $extension->getType()) {
				$this->connector = $extension;

				return;
			}
		}

		throw new Exceptions\InvalidArgumentException(sprintf('Connector %s is not registered', $connector->getPlainId()));
	}

	/**
	 * @param MetadataEntities\IEntity $entity
	 *
	 * @return void
	 */
	public function handlePropertyCommand(
		MetadataEntities\IEntity $entity
	): void {
		if ($this->connector === null) {
			return;
		}

		if (
			// Connector
			$entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorMappedPropertyEntity
			// Device
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
			// Channel
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
		) {
			$this->connector->writeProperty($entity);
		}
	}

	/**
	 * @param MetadataEntities\IEntity $entity
	 *
	 * @return void
	 */
	public function handleControlCommand(
		MetadataEntities\IEntity $entity
	): void {
		if ($this->connector === null) {
			return;
		}

		if (
			$entity instanceof MetadataEntities\Modules\DevicesModule\IConnectorControlEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceControlEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelControlEntity
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

		if ($entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceEntity) {
			$this->connector->notifyDevice($entity);

		} elseif ($entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity) {
			$this->connector->notifyDeviceAttribute($entity);

		} elseif (
			$entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
		) {
			$this->connector->notifyDeviceProperty($entity);

		} elseif ($entity instanceof MetadataEntities\Modules\DevicesModule\IChannelEntity) {
			$this->connector->notifyChannel($entity);

		} elseif (
			$entity instanceof MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
		) {
			$this->connector->notifyChannelProperty($entity);
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

		if ($entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceEntity) {
			$this->connector->initializeDevice($entity);

		} elseif ($entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity) {
			$this->connector->initializeDeviceAttribute($entity);

		} elseif (
			$entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
		) {
			$this->connector->initializeDeviceProperty($entity);

		} elseif ($entity instanceof MetadataEntities\Modules\DevicesModule\IChannelEntity) {
			$this->connector->initializeChannel($entity);

		} elseif (
			$entity instanceof MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
		) {
			$this->connector->initializeChannelProperty($entity);
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

		if ($entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceEntity) {
			$this->connector->initializeDevice($entity);

		} elseif ($entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity) {
			$this->connector->initializeDeviceAttribute($entity);

		} elseif (
			$entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
		) {
			$this->connector->initializeDeviceProperty($entity);

		} elseif ($entity instanceof MetadataEntities\Modules\DevicesModule\IChannelEntity) {
			$this->connector->initializeChannel($entity);

		} elseif (
			$entity instanceof MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
		) {
			$this->connector->initializeChannelProperty($entity);
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

		if ($entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceEntity) {
			$this->connector->removeDevice($entity);

		} elseif ($entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity) {
			$this->connector->removeDeviceAttribute($entity);

		} elseif (
			$entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity
		) {
			$this->connector->removeDeviceProperty($entity);

		} elseif ($entity instanceof MetadataEntities\Modules\DevicesModule\IChannelEntity) {
			$this->connector->removeChannel($entity);

		} elseif (
			$entity instanceof MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity
			|| $entity instanceof MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity
		) {
			$this->connector->removeChannelProperty($entity);
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
