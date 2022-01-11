<?php declare(strict_types = 1);

/**
 * DevicePropertiesV1Controller.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 * @since          0.1.0
 *
 * @date           04.06.19
 */

namespace FastyBird\DevicesModule\Controllers;

use Doctrine;
use FastyBird\DevicesModule\Controllers;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Hydrators;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use FastyBird\DevicesModule\Router;
use FastyBird\DevicesModule\Schemas;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use Fig\Http\Message\StatusCodeInterface;
use IPub\DoctrineCrud\Exceptions as DoctrineCrudExceptions;
use Nette\Utils;
use Psr\Http\Message;
use Ramsey\Uuid;
use Throwable;

/**
 * Device properties API controller
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @Secured
 * @Secured\User(loggedIn)
 */
final class DevicePropertiesV1Controller extends BaseV1Controller
{

	use Controllers\Finders\TDeviceFinder;

	/** @var string */
	protected string $translationDomain = 'devices-module.deviceProperties';

	/** @var Models\Devices\IDeviceRepository */
	protected Models\Devices\IDeviceRepository $deviceRepository;

	/** @var Models\Devices\Properties\IPropertyRepository */
	private Models\Devices\Properties\IPropertyRepository $propertyRepository;

	/** @var Models\Devices\Properties\IPropertiesManager */
	protected Models\Devices\Properties\IPropertiesManager $propertiesManager;

	/** @var Hydrators\Properties\DeviceDynamicPropertyHydrator */
	protected Hydrators\Properties\DeviceDynamicPropertyHydrator $dynamicPropertyHydrator;

	/** @var Hydrators\Properties\DeviceStaticPropertyHydrator */
	protected Hydrators\Properties\DeviceStaticPropertyHydrator $staticPropertyHydrator;

	public function __construct(
		Models\Devices\IDeviceRepository $deviceRepository,
		Models\Devices\Properties\IPropertyRepository $propertyRepository,
		Models\Devices\Properties\IPropertiesManager $propertiesManager,
		Hydrators\Properties\DeviceDynamicPropertyHydrator $dynamicPropertyHydrator,
		Hydrators\Properties\DeviceStaticPropertyHydrator $staticPropertyHydrator
	) {
		$this->deviceRepository = $deviceRepository;
		$this->propertyRepository = $propertyRepository;
		$this->propertiesManager = $propertiesManager;
		$this->dynamicPropertyHydrator = $dynamicPropertyHydrator;
		$this->staticPropertyHydrator = $staticPropertyHydrator;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param Message\ResponseInterface $response
	 *
	 * @return Message\ResponseInterface
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	public function index(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): Message\ResponseInterface {
		// At first, try to load device
		$device = $this->findDevice($request->getAttribute(Router\Routes::URL_DEVICE_ID));

		$findQuery = new Queries\FindDevicePropertiesQuery();
		$findQuery->forDevice($device);

		$properties = $this->propertyRepository->getResultSet($findQuery);

		// @phpstan-ignore-next-line
		return $this->buildResponse($request, $response, $properties);
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
		// At first, try to load device
		$device = $this->findDevice($request->getAttribute(Router\Routes::URL_DEVICE_ID));
		// & property
		$property = $this->findProperty($request->getAttribute(Router\Routes::URL_ITEM_ID), $device);

		return $this->buildResponse($request, $response, $property);
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
	public function create(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): Message\ResponseInterface
	{
		// At first, try to load device
		$this->findDevice($request->getAttribute(Router\Routes::URL_DEVICE_ID));

		$document = $this->createDocument($request);

		if (
			$document->getResource()->getType() === Schemas\Devices\Properties\DynamicPropertySchema::SCHEMA_TYPE
			|| $document->getResource()->getType() === Schemas\Devices\Properties\StaticPropertySchema::SCHEMA_TYPE
		) {
			try {
				// Start transaction connection to the database
				$this->getOrmConnection()->beginTransaction();

				if ($document->getResource()->getType() === Schemas\Devices\Properties\DynamicPropertySchema::SCHEMA_TYPE) {
					$property = $this->propertiesManager->create($this->dynamicPropertyHydrator->hydrate($document));

				} elseif ($document->getResource()->getType() === Schemas\Devices\Properties\StaticPropertySchema::SCHEMA_TYPE) {
					$property = $this->propertiesManager->create($this->staticPropertyHydrator->hydrate($document));

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
				$this->logger->error('[FB:DEVICES_MODULE:CONTROLLER] ' . $ex->getMessage(), [
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
			/** @var Message\ResponseInterface $response */
			$response = $response->withStatus(StatusCodeInterface::STATUS_CREATED);

			return $response;
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
	 * @throws Doctrine\DBAL\ConnectionException
	 *
	 * @Secured
	 * @Secured\Role(manager,administrator)
	 */
	public function update(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): Message\ResponseInterface {
		// At first, try to load device
		$device = $this->findDevice($request->getAttribute(Router\Routes::URL_DEVICE_ID));
		// & property
		$property = $this->findProperty($request->getAttribute(Router\Routes::URL_ITEM_ID), $device);

		$document = $this->createDocument($request);

		$this->validateIdentifier($request, $document);

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			if (
				$document->getResource()->getType() === Schemas\Devices\Properties\DynamicPropertySchema::SCHEMA_TYPE
				&& $property instanceof Entities\Devices\Properties\IDynamicProperty
			) {
				$updatePropertyData = $this->dynamicPropertyHydrator->hydrate($document, $property);

			} elseif (
				$document->getResource()->getType() === Schemas\Devices\Properties\StaticPropertySchema::SCHEMA_TYPE
				&& $property instanceof Entities\Devices\Properties\IStaticProperty
			) {
				$updatePropertyData = $this->staticPropertyHydrator->hydrate($document, $property);

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

			$property = $this->propertiesManager->update($property, $updatePropertyData);

			// Commit all changes into database
			$this->getOrmConnection()->commit();

		} catch (JsonApiExceptions\IJsonApiException $ex) {
			throw $ex;

		} catch (Throwable $ex) {
			// Log caught exception
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

		return $this->buildResponse($request, $response, $property);
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
	public function delete(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): Message\ResponseInterface {
		// At first, try to load device
		$device = $this->findDevice($request->getAttribute(Router\Routes::URL_DEVICE_ID));
		// & property
		$property = $this->findProperty($request->getAttribute(Router\Routes::URL_ITEM_ID), $device);

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			// Remove property
			$this->propertiesManager->delete($property);

			// Commit all changes into database
			$this->getOrmConnection()->commit();

		} catch (Throwable $ex) {
			// Log caught exception
			$this->logger->error('[FB:DEVICES_MODULE:CONTROLLER] ' . $ex->getMessage(), [
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

		/** @var Message\ResponseInterface $response */
		$response = $response->withStatus(StatusCodeInterface::STATUS_NO_CONTENT);

		return $response;
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
		// At first, try to load device
		$device = $this->findDevice($request->getAttribute(Router\Routes::URL_DEVICE_ID));

		// & relation entity name
		$relationEntity = strtolower($request->getAttribute(Router\Routes::RELATION_ENTITY));

		if (Uuid\Uuid::isValid($request->getAttribute(Router\Routes::URL_ITEM_ID))) {
			// & property
			$property = $this->findProperty($request->getAttribute(Router\Routes::URL_ITEM_ID), $device);

			if ($relationEntity === Schemas\Devices\Properties\PropertySchema::RELATIONSHIPS_DEVICE) {
				return $this->buildResponse($request, $response, $property->getDevice());
			}
		}

		return parent::readRelationship($request, $response);
	}

	/**
	 * @param string $id
	 * @param Entities\Devices\IDevice $device
	 *
	 * @return Entities\Devices\Properties\IProperty
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	private function findProperty(
		string $id,
		Entities\Devices\IDevice $device
	): Entities\Devices\Properties\IProperty {
		try {
			$findQuery = new Queries\FindDevicePropertiesQuery();
			$findQuery->forDevice($device);
			$findQuery->byId(Uuid\Uuid::fromString($id));

			$property = $this->propertyRepository->findOneBy($findQuery);

			if ($property === null) {
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

		return $property;
	}

}
