<?php declare(strict_types = 1);

/**
 * IConnector.php
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

/**
 * Devices connector interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Connectors
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IConnector
{

	/**
	 * @return string
	 */
	public function getType(): string;

	/**
	 * @return void
	 */
	public function execute(): void;

	/**
	 * @return void
	 */
	public function terminate(): void;

	/**
	 * @return bool
	 */
	public function hasUnfinishedTasks(): bool;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector
	 *
	 * @return void
	 */
	public function initialize(
		MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector
	): void;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDeviceEntity $device
	 *
	 * @return void
	 */
	public function initializeDevice(
		MetadataEntities\Modules\DevicesModule\IDeviceEntity $device
	): void;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDeviceEntity $device
	 *
	 * @return void
	 */
	public function notifyDevice(
		MetadataEntities\Modules\DevicesModule\IDeviceEntity $device
	): void;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDeviceEntity $device
	 *
	 * @return void
	 */
	public function removeDevice(
		MetadataEntities\Modules\DevicesModule\IDeviceEntity $device
	): void;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity $property
	 *
	 * @return void
	 */
	public function initializeDeviceProperty(
		$property
	): void;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity $property
	 *
	 * @return void
	 */
	public function notifyDeviceProperty(
		$property
	): void;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDeviceStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IDeviceMappedPropertyEntity $property
	 *
	 * @return void
	 */
	public function removeDeviceProperty(
		$property
	): void;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity $attribute
	 *
	 * @return void
	 */
	public function initializeDeviceAttribute(
		MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity $attribute
	): void;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity $attribute
	 *
	 * @return void
	 */
	public function notifyDeviceAttribute(
		MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity $attribute
	): void;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity $attribute
	 *
	 * @return void
	 */
	public function removeDeviceAttribute(
		MetadataEntities\Modules\DevicesModule\IDeviceAttributeEntity $attribute
	): void;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IChannelEntity $channel
	 *
	 * @return void
	 */
	public function initializeChannel(
		MetadataEntities\Modules\DevicesModule\IChannelEntity $channel
	): void;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IChannelEntity $channel
	 *
	 * @return void
	 */
	public function notifyChannel(
		MetadataEntities\Modules\DevicesModule\IChannelEntity $channel
	): void;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IChannelEntity $channel
	 *
	 * @return void
	 */
	public function removeChannel(
		MetadataEntities\Modules\DevicesModule\IChannelEntity $channel
	): void;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity $property
	 *
	 * @return void
	 */
	public function initializeChannelProperty(
		$property
	): void;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity $property
	 *
	 * @return void
	 */
	public function notifyChannelProperty(
		$property
	): void;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IChannelStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity $property
	 *
	 * @return void
	 */
	public function removeChannelProperty(
		$property
	): void;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity $entity
	 *
	 * @return void
	 */
	public function writeProperty($entity): void;

	/**
	 * @param MetadataEntities\Modules\DevicesModule\IConnectorControlEntity|MetadataEntities\Modules\DevicesModule\IDeviceControlEntity|MetadataEntities\Modules\DevicesModule\IChannelControlEntity $entity
	 *
	 * @return void
	 */
	public function writeControl($entity): void;

}
