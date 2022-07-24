<?php declare(strict_types = 1);

/**
 * ConnectorConnectionStateManager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     States
 * @since          0.73.0
 *
 * @date           19.07.22
 */

namespace FastyBird\DevicesModule\Models\States;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Types as MetadataTypes;
use Nette;
use Nette\Utils;
use Psr\Log;

/**
 * Connector connection states manager
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     States
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ConnectorConnectionStateManager
{

	use Nette\SmartObject;

	/** @var Models\Connectors\IConnectorsRepository */
	private Models\Connectors\IConnectorsRepository $connectorsRepository;

	/** @var Models\Connectors\Properties\IPropertiesManager */
	private Models\Connectors\Properties\IPropertiesManager $manager;

	/** @var Models\DataStorage\IConnectorPropertiesRepository */
	private Models\DataStorage\IConnectorPropertiesRepository $dataStorageRepository;

	/** @var Models\States\ConnectorPropertiesRepository */
	private Models\States\ConnectorPropertiesRepository $statesRepository;

	/** @var Models\States\ConnectorPropertiesManager */
	private Models\States\ConnectorPropertiesManager $statesManager;

	/** @var Log\LoggerInterface */
	private Log\LoggerInterface $logger;

	/**
	 * @param Models\Connectors\IConnectorsRepository $connectorsRepository
	 * @param Models\DataStorage\IConnectorPropertiesRepository $repository
	 * @param Models\Connectors\Properties\IPropertiesManager $manager
	 * @param ConnectorPropertiesRepository $statesRepository
	 * @param ConnectorPropertiesManager $statesManager
	 * @param Log\LoggerInterface|null $logger
	 */
	public function __construct(
		Models\Connectors\IConnectorsRepository $connectorsRepository,
		Models\DataStorage\IConnectorPropertiesRepository $repository,
		Models\Connectors\Properties\IPropertiesManager $manager,
		Models\States\ConnectorPropertiesRepository $statesRepository,
		Models\States\ConnectorPropertiesManager $statesManager,
		?Log\LoggerInterface $logger = null
	) {
		$this->connectorsRepository = $connectorsRepository;
		$this->dataStorageRepository = $repository;
		$this->manager = $manager;
		$this->statesRepository = $statesRepository;
		$this->statesManager = $statesManager;

		$this->logger = $logger ?? new Log\NullLogger();
	}

	/**
	 * @param Entities\Connectors\IConnector|MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector
	 * @param MetadataTypes\ConnectionStateType $state
	 *
	 * @return bool
	 */
	public function setState(
		Entities\Connectors\IConnector|MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector,
		MetadataTypes\ConnectionStateType $state
	): bool {
		$stateProperty = $this->dataStorageRepository->findByIdentifier(
			$connector->getId(),
			MetadataTypes\ConnectorPropertyNameType::NAME_STATE
		);

		if ($stateProperty === null) {
			if (!$connector instanceof Entities\Connectors\IConnector) {
				$findConnectorQuery = new Queries\FindConnectorsQuery();
				$findConnectorQuery->byId($connector->getId());

				$connector = $this->connectorsRepository->findOneBy($findConnectorQuery);

				if ($connector === null) {
					throw new Exceptions\InvalidStateException('Connector could not be loaded');
				}
			}

			$stateProperty = $this->manager->create(Utils\ArrayHash::from([
				'connector'  => $connector,
				'entity'     => Entities\Connectors\Properties\DynamicProperty::class,
				'identifier' => MetadataTypes\ConnectorPropertyNameType::NAME_STATE,
				'dataType'   => MetadataTypes\DataTypeType::get(MetadataTypes\DataTypeType::DATA_TYPE_ENUM),
				'unit'       => null,
				'format'     => [
					MetadataTypes\ConnectionStateType::STATE_RUNNING,
					MetadataTypes\ConnectionStateType::STATE_STOPPED,
					MetadataTypes\ConnectionStateType::STATE_UNKNOWN,
					MetadataTypes\ConnectionStateType::STATE_SLEEPING,
					MetadataTypes\ConnectionStateType::STATE_ALERT,
				],
				'settable'   => false,
				'queryable'  => false,
			]));
		}

		if (
			$stateProperty instanceof MetadataEntities\Modules\DevicesModule\IConnectorDynamicPropertyEntity
			|| $stateProperty instanceof Entities\Connectors\Properties\IDynamicProperty
		) {
			try {
				$statePropertyState = $this->statesRepository->findOne($stateProperty);

			} catch (Exceptions\NotImplementedException $ex) {
				$this->logger->warning(
					'States repository is not configured. State could not be fetched',
					[
						'source' => 'devices-module',
						'type'   => 'connection-state-manager',
					]
				);

				MetadataTypes\ConnectionStateType::get(MetadataTypes\ConnectionStateType::STATE_UNKNOWN);

				return false;
			}

			if ($statePropertyState === null) {
				try {
					$this->statesManager->create($stateProperty, Utils\ArrayHash::from([
						'actualValue'   => $state->getValue(),
						'expectedValue' => null,
						'pending'       => false,
						'valid'         => true,
					]));

					return true;

				} catch (Exceptions\NotImplementedException $ex) {
					$this->logger->warning(
						'States manager is not configured. State could not be saved',
						[
							'source' => 'devices-module',
							'type'   => 'connection-state-manager',
						]
					);
				}
			} else {
				try {
					$this->statesManager->update(
						$stateProperty,
						$statePropertyState,
						Utils\ArrayHash::from([
							'actualValue'   => $state->getValue(),
							'expectedValue' => null,
							'pending'       => false,
							'valid'         => true,
						])
					);

					return true;

				} catch (Exceptions\NotImplementedException $ex) {
					$this->logger->warning(
						'States manager is not configured. State could not be saved',
						[
							'source' => 'devices-module',
							'type'   => 'connection-state-manager',
						]
					);
				}
			}
		}

		return false;
	}

	/**
	 * @param Entities\Connectors\IConnector|MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector
	 *
	 * @return MetadataTypes\ConnectionStateType
	 */
	public function getState(
		Entities\Connectors\IConnector|MetadataEntities\Modules\DevicesModule\IConnectorEntity $connector
	): MetadataTypes\ConnectionStateType {
		$stateProperty = $this->dataStorageRepository->findByIdentifier(
			$connector->getId(),
			MetadataTypes\ConnectorPropertyNameType::NAME_STATE
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
