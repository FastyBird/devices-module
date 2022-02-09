<?php declare(strict_types = 1);

/**
 * ConnectorsV1Controller.php
 *
 * @license        More in LICENSE.md
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
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use FastyBird\DevicesModule\Router;
use FastyBird\DevicesModule\Schemas;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
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

	/** @var Models\Connectors\IConnectorsManager */
	private Models\Connectors\IConnectorsManager $connectorsManager;

	/** @var Models\Connectors\IConnectorsRepository */
	private Models\Connectors\IConnectorsRepository $connectorsRepository;

	public function __construct(
		Models\Connectors\IConnectorsRepository $connectorsRepository,
		Models\Connectors\IConnectorsManager    $connectorsManager
	) {
		$this->connectorsRepository = $connectorsRepository;
		$this->connectorsManager = $connectorsManager;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param Message\ResponseInterface $response
	 *
	 * @return Message\ResponseInterface
	 */
	public function index(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): Message\ResponseInterface {
		$findQuery = new Queries\FindConnectorsQuery();

		$connectors = $this->connectorsRepository->getResultSet($findQuery);

		// @phpstan-ignore-next-line
		return $this->buildResponse($request, $response, $connectors);
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param Message\ResponseInterface $response
	 *
	 * @return Message\ResponseInterface
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	public function read(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): Message\ResponseInterface {
		$connector = $this->findConnector($request->getAttribute(Router\Routes::URL_ITEM_ID));

		return $this->buildResponse($request, $response, $connector);
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

			$connector = $this->connectorsRepository->findOneBy($findQuery);

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

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param Message\ResponseInterface $response
	 *
	 * @return Message\ResponseInterface
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 * @throws Doctrine\DBAL\ConnectionException
	 *
	 * @Secured
	 * @Secured\Role(manager,administrator)
	 */
	public function update(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): Message\ResponseInterface {
		$connector = $this->findConnector($request->getAttribute(Router\Routes::URL_ITEM_ID));

		$document = $this->createDocument($request);

		$this->validateIdentifier($request, $document);

		$hydrator = $this->hydratorsContainer->findHydrator($document);

		if ($hydrator !== null) {
			try {
				// Start transaction connection to the database
				$this->getOrmConnection()->beginTransaction();

				$connector = $this->connectorsManager->update($connector, $hydrator->hydrate($document, $connector));

				// Commit all changes into database
				$this->getOrmConnection()->commit();

			} catch (JsonApiExceptions\IJsonApiException $ex) {
				throw $ex;

			} catch (Throwable $ex) {
				// Log caught exception
				$this->logger->error('An unhandled error occurred', [
					'source'    => 'devices-module-connectors-controller',
					'type'      => 'update',
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

			return $this->buildResponse($request, $response, $connector);
		}

		throw new JsonApiExceptions\JsonApiErrorException(
			StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
			$this->translator->translate('//devices-module.base.messages.invalidType.heading'),
			$this->translator->translate('//devices-module.base.messages.invalidType.message'),
			[
				'pointer' => '/data/type',
			]
		);
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param Message\ResponseInterface $response
	 *
	 * @return Message\ResponseInterface
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	public function readRelationship(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): Message\ResponseInterface {
		$connector = $this->findConnector($request->getAttribute(Router\Routes::URL_ITEM_ID));

		$relationEntity = strtolower($request->getAttribute(Router\Routes::RELATION_ENTITY));

		if ($relationEntity === Schemas\Connectors\ConnectorSchema::RELATIONSHIPS_DEVICES) {
			return $this->buildResponse($request, $response, $connector->getDevices());

		} elseif ($relationEntity === Schemas\Connectors\ConnectorSchema::RELATIONSHIPS_PROPERTIES) {
			return $this->buildResponse($request, $response, $connector->getProperties());

		} elseif ($relationEntity === Schemas\Connectors\ConnectorSchema::RELATIONSHIPS_CONTROLS) {
			return $this->buildResponse($request, $response, $connector->getControls());
		}

		return parent::readRelationship($request, $response);
	}

}
