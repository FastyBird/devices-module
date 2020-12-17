<?php declare(strict_types = 1);

/**
 * DeviceCredentialsV1Controller.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 * @since          0.1.0
 *
 * @date           10.12.20
 */

namespace FastyBird\DevicesModule\Controllers;

use Doctrine;
use FastyBird\DevicesModule\Controllers;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Hydrators;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Router;
use FastyBird\DevicesModule\Schemas;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use FastyBird\WebServer\Http as WebServerHttp;
use Fig\Http\Message\StatusCodeInterface;
use IPub\DoctrineCrud\Exceptions as DoctrineCrudExceptions;
use Psr\Http\Message;
use Throwable;

/**
 * Device credentials API controller
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @Secured
 * @Secured\Role(manager,administrator)
 */
final class DeviceCredentialsV1Controller extends BaseV1Controller
{

	use Controllers\Finders\TDeviceFinder;

	/** @var Models\Devices\IDeviceRepository */
	protected Models\Devices\IDeviceRepository $deviceRepository;

	/** @var Models\Devices\Credentials\ICredentialsManager */
	private Models\Devices\Credentials\ICredentialsManager $credentialsManager;

	/** @var Hydrators\Credentials\CredentialsHydrator */
	private Hydrators\Credentials\CredentialsHydrator $credentialsHydrator;

	public function __construct(
		Models\Devices\IDeviceRepository $deviceRepository,
		Models\Devices\Credentials\ICredentialsManager $credentialsManager,
		Hydrators\Credentials\CredentialsHydrator $credentialsHydrator
	) {
		$this->deviceRepository = $deviceRepository;
		$this->credentialsManager = $credentialsManager;
		$this->credentialsHydrator = $credentialsHydrator;
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
		// At first, try to load device
		$device = $this->findDevice($request->getAttribute(Router\Routes::URL_DEVICE_ID));

		if (!$device instanceof Entities\Devices\INetworkDevice) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//dvices-module.base.messages.notFound.heading'),
				$this->translator->translate('//dvices-module.base.messages.notFound.message')
			);
		}

		return $response
			->withEntity(WebServerHttp\ScalarEntity::from($device->getCredentials()));
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 * @throws Doctrine\DBAL\ConnectionException
	 */
	public function create(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$document = $this->createDocument($request);

		$device = $this->findDevice($request->getAttribute(Router\Routes::URL_DEVICE_ID));

		if (!$device instanceof Entities\Devices\INetworkDevice) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//dvices-module.base.messages.notFound.heading'),
				$this->translator->translate('//dvices-module.base.messages.notFound.message')
			);
		}

		if ($device->getCredentials() !== null) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//dvices-module.base.messages.invalidRelation.heading'),
				$this->translator->translate('//dvices-module.base.messages.invalidRelation.message'),
				[
					'pointer' => '/data/relationships/device',
				]
			);
		}

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			if ($document->getResource()->getType() === Schemas\Devices\Credentials\CredentialsSchema::SCHEMA_TYPE) {
				$createCredentialsData = $this->credentialsHydrator->hydrate($document);
				$createCredentialsData->offsetSet('device', $device);

			} else {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//dvices-module.base.messages.invalidType.heading'),
					$this->translator->translate('//dvices-module.base.messages.invalidType.message'),
					[
						'pointer' => '/data/type',
					]
				);
			}

			$credentials = $this->credentialsManager->create($createCredentialsData);

			// Commit all changes into database
			$this->getOrmConnection()->commit();

		} catch (JsonApiExceptions\IJsonApiException $ex) {
			throw $ex;

		} catch (DoctrineCrudExceptions\MissingRequiredFieldException $ex) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//dvices-module.base.messages.missingAttribute.heading'),
				$this->translator->translate('//dvices-module.base.messages.missingAttribute.message'),
				[
					'pointer' => 'data/attributes/' . $ex->getField(),
				]
			);

		} catch (DoctrineCrudExceptions\EntityCreationException $ex) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//dvices-module.base.messages.missingAttribute.heading'),
				$this->translator->translate('//dvices-module.base.messages.missingAttribute.message'),
				[
					'pointer' => 'data/attributes/' . $ex->getField(),
				]
			);

		} catch (Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
			if (preg_match("%PRIMARY'%", $ex->getMessage(), $match) === 1) {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//dvices-module.base.messages.uniqueIdentifier.heading'),
					$this->translator->translate('//dvices-module.base.messages.uniqueIdentifier.message'),
					[
						'pointer' => '/data/id',
					]
				);
			}

			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//dvices-module.base.messages.uniqueAttribute.heading'),
				$this->translator->translate('//dvices-module.base.messages.uniqueAttribute.message')
			);

		} catch (Throwable $ex) {
			// Log catched exception
			$this->logger->error('[CONTROLLER] ' . $ex->getMessage(), [
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//dvices-module.base.messages.notCreated.heading'),
				$this->translator->translate('//dvices-module.base.messages.notCreated.message')
			);

		} finally {
			// Revert all changes when error occur
			if ($this->getOrmConnection()->isTransactionActive()) {
				$this->getOrmConnection()->rollBack();
			}
		}

		/** @var WebServerHttp\Response $response */
		$response = $response
			->withEntity(WebServerHttp\ScalarEntity::from($credentials))
			->withStatus(StatusCodeInterface::STATUS_CREATED);

		return $response;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 * @throws Doctrine\DBAL\ConnectionException
	 */
	public function update(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$document = $this->createDocument($request);

		$device = $this->findDevice($request->getAttribute(Router\Routes::URL_DEVICE_ID));

		if (!$device instanceof Entities\Devices\INetworkDevice || $device->getCredentials() === null) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//dvices-module.base.messages.notFound.heading'),
				$this->translator->translate('//dvices-module.base.messages.notFound.message')
			);
		}

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			if ($document->getResource()->getType() === Schemas\Devices\Credentials\CredentialsSchema::SCHEMA_TYPE) {
				$updateCredentialsData = $this->credentialsHydrator->hydrate($document, $device->getCredentials());

			} else {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//dvices-module.base.messages.invalidType.heading'),
					$this->translator->translate('//dvices-module.base.messages.invalidType.message'),
					[
						'pointer' => '/data/type',
					]
				);
			}

			$credentials = $this->credentialsManager->update($device->getCredentials(), $updateCredentialsData);

			// Commit all changes into database
			$this->getOrmConnection()->commit();

		} catch (JsonApiExceptions\IJsonApiException $ex) {
			throw $ex;

		} catch (Throwable $ex) {
			// Log catched exception
			$this->logger->error('[CONTROLLER] ' . $ex->getMessage(), [
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//dvices-module.base.messages.notUpdated.heading'),
				$this->translator->translate('//dvices-module.base.messages.notUpdated.message')
			);

		} finally {
			// Revert all changes when error occur
			if ($this->getOrmConnection()->isTransactionActive()) {
				$this->getOrmConnection()->rollBack();
			}
		}

		return $response
			->withEntity(WebServerHttp\ScalarEntity::from($credentials));
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
		// At first, try to load device
		$device = $this->findDevice($request->getAttribute(Router\Routes::URL_DEVICE_ID));

		if (!$device instanceof Entities\Devices\INetworkDevice) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//dvices-module.base.messages.notFound.heading'),
				$this->translator->translate('//dvices-module.base.messages.notFound.message')
			);
		}

		// & relation entity name
		$relationEntity = strtolower($request->getAttribute(Router\Routes::RELATION_ENTITY));

		if ($relationEntity === Schemas\Devices\Credentials\CredentialsSchema::RELATIONSHIPS_DEVICE) {
			return $response
				->withEntity(WebServerHttp\ScalarEntity::from($device));
		}

		return parent::readRelationship($request, $response);
	}

}
