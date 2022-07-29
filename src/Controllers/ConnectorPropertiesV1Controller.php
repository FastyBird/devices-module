<?php declare(strict_types = 1);

/**
 * ConnectorPropertiesV1Controller.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 * @since          0.31.0
 *
 * @date           08.02.22
 */

namespace FastyBird\DevicesModule\Controllers;

use Doctrine;
use Exception;
use FastyBird\DevicesModule\Controllers;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use FastyBird\DevicesModule\Router;
use FastyBird\DevicesModule\Schemas;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use FastyBird\Metadata;
use Fig\Http\Message\StatusCodeInterface;
use IPub\DoctrineCrud\Exceptions as DoctrineCrudExceptions;
use Nette\Utils;
use Psr\Http\Message;
use Ramsey\Uuid;
use Throwable;

/**
 * Connector properties API controller
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @Secured
 * @Secured\User(loggedIn)
 */
final class ConnectorPropertiesV1Controller extends BaseV1Controller
{

	use Controllers\Finders\TDeviceFinder;
	use Controllers\Finders\TConnectorFinder;

	/** @var Models\Connectors\IConnectorsRepository */
	protected Models\Connectors\IConnectorsRepository $connectorsRepository;

	/** @var Models\Connectors\Properties\IPropertiesRepository */
	protected Models\Connectors\Properties\IPropertiesRepository $connectorPropertiesRepository;

	/** @var Models\Connectors\Properties\IPropertiesManager */
	protected Models\Connectors\Properties\IPropertiesManager $connectorPropertiesManager;

	/**
	 * @param Models\Connectors\IConnectorsRepository $connectorsRepository
	 * @param Models\Connectors\Properties\IPropertiesRepository $connectorPropertiesRepository
	 * @param Models\Connectors\Properties\IPropertiesManager $connectorPropertiesManager
	 */
	public function __construct(
		Models\Connectors\IConnectorsRepository $connectorsRepository,
		Models\Connectors\Properties\IPropertiesRepository $connectorPropertiesRepository,
		Models\Connectors\Properties\IPropertiesManager $connectorPropertiesManager
	) {
		$this->connectorsRepository = $connectorsRepository;
		$this->connectorPropertiesRepository = $connectorPropertiesRepository;
		$this->connectorPropertiesManager = $connectorPropertiesManager;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param Message\ResponseInterface $response
	 *
	 * @return Message\ResponseInterface
	 *
	 * @throws Exception
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	public function index(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): Message\ResponseInterface {
		// At first, try to load connector
		$connector = $this->findConnector($request->getAttribute(Router\Routes::URL_CONNECTOR_ID));

		$findQuery = new Queries\FindConnectorPropertiesQuery();
		$findQuery->forConnector($connector);

		$properties = $this->connectorPropertiesRepository->getResultSet($findQuery);

		// @phpstan-ignore-next-line
		return $this->buildResponse($request, $response, $properties);
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param Message\ResponseInterface $response
	 *
	 * @return Message\ResponseInterface
	 *
	 * @throws Exception
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	public function read(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): Message\ResponseInterface {
		// At first, try to load connector
		$connector = $this->findConnector($request->getAttribute(Router\Routes::URL_CONNECTOR_ID));
		// & property
		$property = $this->findProperty($request->getAttribute(Router\Routes::URL_ITEM_ID), $connector);

		return $this->buildResponse($request, $response, $property);
	}

	/**
	 * @param string $id
	 * @param Entities\Connectors\IConnector $connector
	 *
	 * @return Entities\Connectors\Properties\IProperty
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	private function findProperty(
		string $id,
		Entities\Connectors\IConnector $connector
	): Entities\Connectors\Properties\IProperty {
		try {
			$findQuery = new Queries\FindConnectorPropertiesQuery();
			$findQuery->forConnector($connector);
			$findQuery->byId(Uuid\Uuid::fromString($id));

			$property = $this->connectorPropertiesRepository->findOneBy($findQuery);

			if ($property === null) {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_NOT_FOUND,
					$this->translator->translate('//devices-module.base.messages.notFound.heading'),
					$this->translator->translate('//devices-module.base.messages.notFound.message')
				);
			}
		} catch (Uuid\Exception\InvalidUuidStringException) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//devices-module.base.messages.notFound.heading'),
				$this->translator->translate('//devices-module.base.messages.notFound.message')
			);
		}

		return $property;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param Message\ResponseInterface $response
	 *
	 * @return Message\ResponseInterface
	 *
	 * @throws Doctrine\DBAL\Exception
	 * @throws Exception
	 * @throws JsonApiExceptions\IJsonApiException
	 * @throws JsonApiExceptions\JsonApiErrorException
	 *
	 * @Secured
	 * @Secured\Role(manager,administrator)
	 */
	public function create(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): Message\ResponseInterface {
		// At first, try to load connector
		$this->findConnector($request->getAttribute(Router\Routes::URL_CONNECTOR_ID));

		$document = $this->createDocument($request);

		$hydrator = $this->hydratorsContainer->findHydrator($document);

		if ($hydrator !== null) {
			try {
				// Start transaction connection to the database
				$this->getOrmConnection()->beginTransaction();

				$property = $this->connectorPropertiesManager->create($hydrator->hydrate($document));

				// Commit all changes into database
				$this->getOrmConnection()->commit();

			} catch (JsonApiExceptions\IJsonApiException $ex) {
				throw $ex;

			} catch (DoctrineCrudExceptions\MissingRequiredFieldException $ex) {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//devices-module.base.messages.missingAttribute.heading'),
					$this->translator->translate('//devices-module.base.messages.missingAttribute.message'),
					[
						'pointer' => 'data/attributes/' . $ex->getField(),
					]
				);

			} catch (DoctrineCrudExceptions\EntityCreationException $ex) {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//devices-module.base.messages.missingAttribute.heading'),
					$this->translator->translate('//devices-module.base.messages.missingAttribute.message'),
					[
						'pointer' => 'data/attributes/' . $ex->getField(),
					]
				);

			} catch (Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
				if (preg_match("%PRIMARY'%", $ex->getMessage(), $match) === 1) {
					throw new JsonApiExceptions\JsonApiErrorException(
						StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
						$this->translator->translate('//devices-module.base.messages.uniqueIdentifier.heading'),
						$this->translator->translate('//devices-module.base.messages.uniqueIdentifier.message'),
						[
							'pointer' => '/data/id',
						]
					);

				} elseif (preg_match("%key '(?P<key>.+)_unique'%", $ex->getMessage(), $match) === 1) {
					$columnParts = explode('.', $match['key']);
					$columnKey = end($columnParts);

					if (is_string($columnKey) && Utils\Strings::startsWith($columnKey, 'property_')) {
						throw new JsonApiExceptions\JsonApiErrorException(
							StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
							$this->translator->translate('//devices-module.base.messages.uniqueAttribute.heading'),
							$this->translator->translate('//devices-module.base.messages.uniqueAttribute.message'),
							[
								'pointer' => '/data/attributes/' . Utils\Strings::substring($columnKey, 7),
							]
						);
					}
				}

				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//devices-module.base.messages.uniqueAttribute.heading'),
					$this->translator->translate('//devices-module.base.messages.uniqueAttribute.message')
				);

			} catch (Throwable $ex) {
				// Log caught exception
				$this->logger->error('An unhandled error occurred', [
					'source'    => Metadata\Constants::MODULE_DEVICES_SOURCE,
					'type'      => 'connector-properties-controller',
					'exception' => [
						'message' => $ex->getMessage(),
						'code'    => $ex->getCode(),
					],
				]);

				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//devices-module.base.messages.notCreated.heading'),
					$this->translator->translate('//devices-module.base.messages.notCreated.message')
				);

			} finally {
				// Revert all changes when error occur
				if ($this->getOrmConnection()->isTransactionActive()) {
					$this->getOrmConnection()->rollBack();
				}
			}

			$response = $this->buildResponse($request, $response, $property);

			return $response->withStatus(StatusCodeInterface::STATUS_CREATED);
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
	 * @throws Doctrine\DBAL\Exception
	 * @throws Exception
	 * @throws JsonApiExceptions\IJsonApiException
	 * @throws JsonApiExceptions\JsonApiErrorException
	 *
	 * @Secured
	 * @Secured\Role(manager,administrator)
	 */
	public function update(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): Message\ResponseInterface {
		// At first, try to load connector
		$connector = $this->findConnector($request->getAttribute(Router\Routes::URL_CONNECTOR_ID));
		// & property
		$property = $this->findProperty($request->getAttribute(Router\Routes::URL_ITEM_ID), $connector);

		$document = $this->createDocument($request);

		$this->validateIdentifier($request, $document);

		$hydrator = $this->hydratorsContainer->findHydrator($document);

		if ($hydrator !== null) {
			try {
				// Start transaction connection to the database
				$this->getOrmConnection()->beginTransaction();

				$property = $this->connectorPropertiesManager->update($property, $hydrator->hydrate($document, $property));

				// Commit all changes into database
				$this->getOrmConnection()->commit();

			} catch (JsonApiExceptions\IJsonApiException $ex) {
				throw $ex;

			} catch (Throwable $ex) {
				// Log caught exception
				$this->logger->error('An unhandled error occurred', [
					'source'    => Metadata\Constants::MODULE_DEVICES_SOURCE,
					'type'      => 'connector-properties-controller',
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

			return $this->buildResponse($request, $response, $property);
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
	 * @throws Doctrine\DBAL\Exception
	 * @throws JsonApiExceptions\IJsonApiException
	 * @throws JsonApiExceptions\JsonApiErrorException
	 *
	 * @Secured
	 * @Secured\Role(manager,administrator)
	 */
	public function delete(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): Message\ResponseInterface {
		// At first, try to load connector
		$connector = $this->findConnector($request->getAttribute(Router\Routes::URL_CONNECTOR_ID));
		// & property
		$property = $this->findProperty($request->getAttribute(Router\Routes::URL_ITEM_ID), $connector);

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			// Remove connector
			$this->connectorPropertiesManager->delete($property);

			// Commit all changes into database
			$this->getOrmConnection()->commit();

		} catch (Throwable $ex) {
			// Log caught exception
			$this->logger->error('An unhandled error occurred', [
				'source'    => Metadata\Constants::MODULE_DEVICES_SOURCE,
				'type'      => 'connector-properties-controller',
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//devices-module.base.messages.notDeleted.heading'),
				$this->translator->translate('//devices-module.base.messages.notDeleted.message')
			);

		} finally {
			// Revert all changes when error occur
			if ($this->getOrmConnection()->isTransactionActive()) {
				$this->getOrmConnection()->rollBack();
			}
		}

		return $response->withStatus(StatusCodeInterface::STATUS_NO_CONTENT);
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param Message\ResponseInterface $response
	 *
	 * @return Message\ResponseInterface
	 *
	 * @throws Exception
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	public function readRelationship(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): Message\ResponseInterface {
		// At first, try to load connector
		$connector = $this->findConnector($request->getAttribute(Router\Routes::URL_CONNECTOR_ID));

		// & relation entity name
		$relationEntity = strtolower($request->getAttribute(Router\Routes::RELATION_ENTITY));

		if (Uuid\Uuid::isValid($request->getAttribute(Router\Routes::URL_ITEM_ID))) {
			// & property
			$property = $this->findProperty($request->getAttribute(Router\Routes::URL_ITEM_ID), $connector);

			if ($relationEntity === Schemas\Connectors\Properties\PropertySchema::RELATIONSHIPS_CONNECTOR) {
				return $this->buildResponse($request, $response, $property->getConnector());
			}
		}

		return parent::readRelationship($request, $response);
	}

}
