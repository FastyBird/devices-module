<?php declare(strict_types = 1);

/**
 * ConnectorConnectionStateManager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     DynamicProperties
 * @since          0.73.0
 *
 * @date           19.07.22
 */

namespace FastyBird\DevicesModule\Models\States;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use FastyBird\Metadata;
use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Types as MetadataTypes;
use Nette;
use Nette\Utils;
use Psr\Log;

/**
 * Connector connection states manager
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     DynamicProperties
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ConnectorConnectionStateManager
{

	use Nette\SmartObject;

	private Models\DataStorage\ConnectorPropertiesRepository $dataStorageRepository;

	private Log\LoggerInterface $logger;

	public function __construct(
		private Models\Connectors\ConnectorsRepository $connectorsRepository,
		Models\DataStorage\ConnectorPropertiesRepository $repository,
		private Models\Connectors\Properties\PropertiesManager $manager,
		private Models\States\ConnectorPropertiesRepository $statesRepository,
		private Models\States\ConnectorPropertiesManager $statesManager,
		Log\LoggerInterface|null $logger = null,
	)
	{
		$this->dataStorageRepository = $repository;

		$this->logger = $logger ?? new Log\NullLogger();
	}

	public function setState(
		Entities\Connectors\Connector|MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector,
		MetadataTypes\ConnectionStateType $state,
	): bool
	{
		$stateProperty = $this->dataStorageRepository->findByIdentifier(
			$connector->getId(),
			MetadataTypes\ConnectorPropertyIdentifierType::IDENTIFIER_STATE,
		);

		if ($stateProperty === null) {
			if (!$connector instanceof Entities\Connectors\Connector) {
				$findConnectorQuery = new Queries\FindConnectors();
				$findConnectorQuery->byId($connector->getId());

				$connector = $this->connectorsRepository->findOneBy($findConnectorQuery);

				if ($connector === null) {
					throw new Exceptions\InvalidState('Connector could not be loaded');
				}
			}

			$stateProperty = $this->manager->create(Utils\ArrayHash::from([
				'connector' => $connector,
				'entity' => Entities\Connectors\Properties\Dynamic::class,
				'identifier' => MetadataTypes\ConnectorPropertyIdentifierType::IDENTIFIER_STATE,
				'dataType' => MetadataTypes\DataTypeType::get(MetadataTypes\DataTypeType::DATA_TYPE_ENUM),
				'unit' => null,
				'format' => [
					MetadataTypes\ConnectionStateType::STATE_RUNNING,
					MetadataTypes\ConnectionStateType::STATE_STOPPED,
					MetadataTypes\ConnectionStateType::STATE_UNKNOWN,
					MetadataTypes\ConnectionStateType::STATE_SLEEPING,
					MetadataTypes\ConnectionStateType::STATE_ALERT,
				],
				'settable' => false,
				'queryable' => false,
			]));
		}

		if (
			$stateProperty instanceof MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity
			|| $stateProperty instanceof Entities\Connectors\Properties\Dynamic
		) {
			try {
				$statePropertyState = $this->statesRepository->findOne($stateProperty);

			} catch (Exceptions\NotImplemented) {
				$this->logger->warning(
					'DynamicProperties repository is not configured. State could not be fetched',
					[
						'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
						'type' => 'connector-connection-state-manager',
					],
				);

				MetadataTypes\ConnectionStateType::get(MetadataTypes\ConnectionStateType::STATE_UNKNOWN);

				return false;
			}

			if ($statePropertyState === null) {
				try {
					$this->statesManager->create($stateProperty, Utils\ArrayHash::from([
						'actualValue' => $state->getValue(),
						'expectedValue' => null,
						'pending' => false,
						'valid' => true,
					]));

					return true;
				} catch (Exceptions\NotImplemented) {
					$this->logger->warning(
						'DynamicProperties manager is not configured. State could not be saved',
						[
							'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
							'type' => 'connector-connection-state-manager',
						],
					);
				}
			} else {
				try {
					$this->statesManager->update(
						$stateProperty,
						$statePropertyState,
						Utils\ArrayHash::from([
							'actualValue' => $state->getValue(),
							'expectedValue' => null,
							'pending' => false,
							'valid' => true,
						]),
					);

					return true;
				} catch (Exceptions\NotImplemented) {
					$this->logger->warning(
						'DynamicProperties manager is not configured. State could not be saved',
						[
							'source' => Metadata\Constants::MODULE_DEVICES_SOURCE,
							'type' => 'connector-connection-state-manager',
						],
					);
				}
			}
		}

		return false;
	}

	public function getState(
		Entities\Connectors\Connector|MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector,
	): MetadataTypes\ConnectionStateType
	{
		$stateProperty = $this->dataStorageRepository->findByIdentifier(
			$connector->getId(),
			MetadataTypes\ConnectorPropertyIdentifierType::IDENTIFIER_STATE,
		);

		if (
			$stateProperty instanceof MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity
			&& $stateProperty->getActualValue() !== null
			&& MetadataTypes\ConnectionStateType::isValidValue($stateProperty->getActualValue())
		) {
			return MetadataTypes\ConnectionStateType::get($stateProperty->getActualValue());
		}

		return MetadataTypes\ConnectionStateType::get(MetadataTypes\ConnectionStateType::STATE_UNKNOWN);
	}

}
