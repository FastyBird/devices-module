<?php declare(strict_types = 1);

/**
 * ChannelPropertyStateManager.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     States
 * @since          0.73.0
 *
 * @date           23.08.22
 */

namespace FastyBird\DevicesModule\Models\States;

use FastyBird\DevicesModule\Exceptions as DevicesModuleExceptions;
use FastyBird\DevicesModule\Models as DevicesModuleModels;
use FastyBird\Metadata;
use Nette;
use Nette\Utils;
use Psr\Log;

/**
 * Useful channel dynamic property state helpers
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Helpers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ChannelPropertyStateManager
{

	use Nette\SmartObject;

	/** @var DevicesModuleModels\DataStorage\IChannelsRepository */
	private DevicesModuleModels\DataStorage\IChannelsRepository $channelsRepository;

	/** @var DevicesModuleModels\States\ChannelPropertiesRepository */
	private DevicesModuleModels\States\ChannelPropertiesRepository $channelPropertyStateRepository;

	/** @var DevicesModuleModels\States\ChannelPropertiesManager */
	private DevicesModuleModels\States\ChannelPropertiesManager $channelPropertiesStatesManager;

	/** @var Log\LoggerInterface */
	private Log\LoggerInterface $logger;

	/**
	 * @param DevicesModuleModels\DataStorage\IChannelsRepository $channelsRepository
	 * @param ChannelPropertiesRepository $channelPropertyStateRepository
	 * @param ChannelPropertiesManager $channelPropertiesStatesManager
	 * @param Log\LoggerInterface|null $logger
	 */
	public function __construct(
		DevicesModuleModels\DataStorage\IChannelsRepository $channelsRepository,
		DevicesModuleModels\States\ChannelPropertiesRepository $channelPropertyStateRepository,
		DevicesModuleModels\States\ChannelPropertiesManager $channelPropertiesStatesManager,
		?Log\LoggerInterface $logger
	) {
		$this->channelsRepository = $channelsRepository;

		$this->channelPropertyStateRepository = $channelPropertyStateRepository;
		$this->channelPropertiesStatesManager = $channelPropertiesStatesManager;

		$this->logger = $logger ?? new Log\NullLogger();
	}

	/**
	 * @param Metadata\Entities\Modules\DevicesModule\IChannelDynamicPropertyEntity $property
	 * @param Utils\ArrayHash $data
	 *
	 * @return void
	 */
	public function setValue(
		Metadata\Entities\Modules\DevicesModule\IChannelDynamicPropertyEntity $property,
		Utils\ArrayHash $data
	): void {
		try {
			$propertyState = $this->channelPropertyStateRepository->findOne($property);
		} catch (DevicesModuleExceptions\NotImplementedException) {
			$this->logger->warning(
				'States repository is not configured. State could not be fetched',
				[
					'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
					'type'   => 'channel-property-state-manager',
				]
			);

			return;
		}

		try {
			// In case synchronization failed...
			if ($propertyState === null) {
				// ...create state in storage
				$propertyState = $this->channelPropertiesStatesManager->create(
					$property,
					$data
				);

				$channel = $this->channelsRepository->findById($property->getChannel());

				$this->logger->debug(
					'Channel property state was created',
					[
						'source'   => Metadata\Constants::MODULE_DEVICES_SOURCE,
						'type'     => 'channel-property-state-manager',
						'device'   => [
							'id' => $channel?->getDevice()->toString(),
						],
						'channel'  => [
							'id' => $property->getChannel()->toString(),
						],
						'property' => [
							'id'    => $property->getId()->toString(),
							'state' => $propertyState->toArray(),
						],
					]
				);
			} else {
				$propertyState = $this->channelPropertiesStatesManager->update(
					$property,
					$propertyState,
					$data
				);

				$channel = $this->channelsRepository->findById($property->getChannel());

				$this->logger->debug(
					'Channel property state was updated',
					[
						'source'   => Metadata\Constants::MODULE_DEVICES_SOURCE,
						'type'     => 'channel-property-state-manager',
						'device'   => [
							'id' => $channel?->getDevice()->toString(),
						],
						'channel'  => [
							'id' => $property->getChannel()->toString(),
						],
						'property' => [
							'id'    => $property->getId()->toString(),
							'state' => $propertyState->toArray(),
						],
					]
				);
			}
		} catch (DevicesModuleExceptions\NotImplementedException) {
			$this->logger->warning(
				'States manager is not configured. State could not be saved',
				[
					'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
					'type'   => 'channel-property-state-manager',
				]
			);
		}
	}

	/**
	 * @param Metadata\Entities\Modules\DevicesModule\IChannelDynamicPropertyEntity|Metadata\Entities\Modules\DevicesModule\IChannelDynamicPropertyEntity[] $property
	 * @param bool $state
	 *
	 * @return void
	 */
	public function setValidState(
		Metadata\Entities\Modules\DevicesModule\IChannelDynamicPropertyEntity|array $property,
		bool $state
	): void {
		if (is_array($property)) {
			foreach ($property as $item) {
				$this->setValue($item, Utils\ArrayHash::from([
					'valid' => $state,
				]));
			}
		} else {
			$this->setValue($property, Utils\ArrayHash::from([
				'valid' => $state,
			]));
		}
	}

}
