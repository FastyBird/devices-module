<?php declare(strict_types = 1);

/**
 * ConnectorsV1Controller.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 * @since          0.1.0
 *
 * @date           17.01.21
 */

namespace FastyBird\DevicesModule\Controllers;

use Doctrine;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Hydrators;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use FastyBird\DevicesModule\Router;
use FastyBird\DevicesModule\Schemas;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use FastyBird\WebServer\Http as WebServerHttp;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message;
use Ramsey\Uuid;
use Throwable;

/**
 * API connectors controller
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @Secured
 * @Secured\User(loggedIn)
 */
class ConnectorsV1Controller extends BaseV1Controller
{

	/** @var string */
	protected string $translationDomain = 'devices-module.connectors';

	/** @var Models\Connectors\IConnectorsManager */
	private Models\Connectors\IConnectorsManager $connectorsManager;

	/** @var Models\Connectors\IConnectorRepository */
	private Models\Connectors\IConnectorRepository $connectorRepository;

	/** @var Hydrators\Connectors\FbBusConnectorHydrator */
	private Hydrators\Connectors\FbBusConnectorHydrator $fbBusConnectorHydrator;

	/** @var Hydrators\Connectors\FbMqttV1ConnectorHydrator */
	private Hydrators\Connectors\FbMqttV1ConnectorHydrator $fbMqttV1ConnectorHydrator;

	public function __construct(
		Models\Connectors\IConnectorRepository $connectorRepository,
		Models\Connectors\IConnectorsManager $connectorsManager,
		Hydrators\Connectors\FbBusConnectorHydrator $fbBusConnectorHydrator,
		Hydrators\Connectors\FbMqttV1ConnectorHydrator $fbMqttV1ConnectorHydrator
	) {
		$this->connectorRepository = $connectorRepository;
		$this->connectorsManager = $connectorsManager;
		$this->fbBusConnectorHydrator = $fbBusConnectorHydrator;
		$this->fbMqttV1ConnectorHydrator = $fbMqttV1ConnectorHydrator;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 */
	public function index(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$findQuery = new Queries\FindConnectorsQuery();

		$connectors = $this->connectorRepository->getResultSet($findQuery);

		return $response
			->withEntity(WebServerHttp\ScalarEntity::from($connectors));
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	public function read(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$connector = $this->findConnector($request->getAttribute(Router\Routes::URL_ITEM_ID));

		return $response
			->withEntity(WebServerHttp\ScalarEntity::from($connector));
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 * @throws Doctrine\DBAL\ConnectionException
	 *
	 * @Secured
	 * @Secured\Role(manager,administrator)
	 */
	public function update(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$connector = $this->findConnector($request->getAttribute(Router\Routes::URL_ITEM_ID));

		$document = $this->createDocument($request);

		$this->validateIdentifier($request, $document);

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			if ($document->getResource()->getType() === Schemas\Connectors\FbBusConnectorSchema::SCHEMA_TYPE) {
				$updateConnectorData = $this->fbBusConnectorHydrator->hydrate($document, $connector);

			} elseif ($document->getResource()->getType() === Schemas\Connectors\FbMqttV1ConnectorSchema::SCHEMA_TYPE) {
				$updateConnectorData = $this->fbMqttV1ConnectorHydrator->hydrate($document, $connector);

			} else {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//devices-module.base.messages.invalidType.heading'),
					$this->translator->translate('//devices-module.base.messages.invalidType.message'),
					[
						'pointer' => '/data/type',
					]
				);
			}

			$connector = $this->connectorsManager->update($connector, $updateConnectorData);

			// Commit all changes into database
			$this->getOrmConnection()->commit();

		} catch (JsonApiExceptions\IJsonApiException $ex) {
			throw $ex;

		} catch (Throwable $ex) {
			// Log catched exception
			$this->logger->error('[FB:DEVICES_MODULE:CONTROLLER] ' . $ex->getMessage(), [
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//devices-module.base.messages.notUpdated.heading'),
				$this->translator->translate('//devices-module.base.messages.notUpdated.message')
			);

		} finally {
			// Revert all changes when error occur
			if ($this->getOrmConnection()->isTransactionActive()) {
				$this->getOrmConnection()->rollBack();
			}
		}

		return $response
			->withEntity(WebServerHttp\ScalarEntity::from($connector));
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	public function readRelationship(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$connector = $this->findConnector($request->getAttribute(Router\Routes::URL_ITEM_ID));

		$relationEntity = strtolower($request->getAttribute(Router\Routes::RELATION_ENTITY));

		if ($relationEntity === Schemas\Connectors\ConnectorSchema::RELATIONSHIPS_DEVICES) {
			return $response
				->withEntity(WebServerHttp\ScalarEntity::from($connector->getDevices()));
		}

		return parent::readRelationship($request, $response);
	}

	/**
	 * @param string $id
	 *
	 * @return Entities\Connectors\IConnector
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	protected function findConnector(string $id): Entities\Connectors\IConnector
	{
		try {
			$findQuery = new Queries\FindConnectorsQuery();
			$findQuery->byId(Uuid\Uuid::fromString($id));

			$connector = $this->connectorRepository->findOneBy($findQuery);

			if ($connector === null) {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_NOT_FOUND,
					$this->translator->translate('//devices-module.base.messages.notFound.heading'),
					$this->translator->translate('//devices-module.base.messages.notFound.message')
				);
			}

		} catch (Uuid\Exception\InvalidUuidStringException $ex) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//devices-module.base.messages.notFound.heading'),
				$this->translator->translate('//devices-module.base.messages.notFound.message')
			);
		}

		return $connector;
	}

}
