<?php declare(strict_types = 1);

/**
 * DevicesController.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 * @since          0.1.0
 *
 * @date           13.04.19
 */

namespace FastyBird\DevicesModule\Controllers;

use Doctrine;
use Exception;
use FastyBird\DevicesModule\Controllers;
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
use Throwable;

/**
 * API devices controller
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @Secured
 * @Secured\User(loggedIn)
 */
class DevicesV1Controller extends BaseV1Controller
{

	use Controllers\Finders\TDeviceFinder;

	/** @var Models\Devices\IDevicesRepository */
	protected Models\Devices\IDevicesRepository $devicesRepository;

	/** @var Models\Devices\IDevicesManager */
	protected Models\Devices\IDevicesManager $devicesManager;

	/** @var Models\Channels\IChannelsRepository */
	protected Models\Channels\IChannelsRepository $channelsRepository;

	/** @var Models\Channels\IChannelsManager */
	protected Models\Channels\IChannelsManager $channelsManager;

	/**
	 * @param Models\Devices\IDevicesRepository $devicesRepository
	 * @param Models\Devices\IDevicesManager $devicesManager
	 * @param Models\Channels\IChannelsRepository $channelsRepository
	 * @param Models\Channels\IChannelsManager $channelsManager
	 */
	public function __construct(
		Models\Devices\IDevicesRepository $devicesRepository,
		Models\Devices\IDevicesManager $devicesManager,
		Models\Channels\IChannelsRepository $channelsRepository,
		Models\Channels\IChannelsManager $channelsManager
	) {
		$this->devicesRepository = $devicesRepository;
		$this->devicesManager = $devicesManager;
		$this->channelsRepository = $channelsRepository;
		$this->channelsManager = $channelsManager;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param Message\ResponseInterface $response
	 *
	 * @return Message\ResponseInterface
	 *
	 * @throws Exception
	 */
	public function index(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): Message\ResponseInterface {
		$findQuery = new Queries\FindDevicesQuery();

		$devices = $this->devicesRepository->getResultSet($findQuery);

		// @phpstan-ignore-next-line
		return $this->buildResponse($request, $response, $devices);
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
		$device = $this->findDevice(strval($request->getAttribute(Router\Routes::URL_ITEM_ID)));

		return $this->buildResponse($request, $response, $device);
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
		$document = $this->createDocument($request);

		$hydrator = $this->hydratorsContainer->findHydrator($document);

		if ($hydrator !== null) {
			try {
				// Start transaction connection to the database
				$this->getOrmConnection()->beginTransaction();

				$device = $this->devicesManager->create($hydrator->hydrate($document));

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

					if (is_string($columnKey) && Utils\Strings::startsWith($columnKey, 'device_')) {
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
					'type'      => 'devices-controller',
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

			$response = $this->buildResponse($request, $response, $device);
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
		$document = $this->createDocument($request);

		$this->validateIdentifier($request, $document);

		$device = $this->findDevice(strval($request->getAttribute(Router\Routes::URL_ITEM_ID)));

		$hydrator = $this->hydratorsContainer->findHydrator($document);

		if ($hydrator !== null) {
			try {
				// Start transaction connection to the database
				$this->getOrmConnection()->beginTransaction();

				$device = $this->devicesManager->update($device, $hydrator->hydrate($document, $device));

				// Commit all changes into database
				$this->getOrmConnection()->commit();

			} catch (JsonApiExceptions\IJsonApiException $ex) {
				throw $ex;

			} catch (Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
				if (preg_match("%key '(?P<key>.+)_unique'%", $ex->getMessage(), $match) !== false) {
					$columnParts = explode('.', $match['key']);
					$columnKey = end($columnParts);

					if (is_string($columnKey) && Utils\Strings::startsWith($columnKey, 'device_')) {
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
					'type'      => 'devices-controller',
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

			return $this->buildResponse($request, $response, $device);
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
		$device = $this->findDevice(strval($request->getAttribute(Router\Routes::URL_ITEM_ID)));

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			foreach ($device->getChannels() as $channel) {
				// Remove channels. Newly connected device will be reinitialized with all channels
				$this->channelsManager->delete($channel);
			}

			// Move device back into warehouse
			$this->devicesManager->delete($device);

			// Commit all changes into database
			$this->getOrmConnection()->commit();

		} catch (Throwable $ex) {
			// Log caught exception
			$this->logger->error('An unhandled error occurred', [
				'source'    => Metadata\Constants::MODULE_DEVICES_SOURCE,
				'type'      => 'devices-controller',
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
		$device = $this->findDevice(strval($request->getAttribute(Router\Routes::URL_ITEM_ID)));

		$relationEntity = Utils\Strings::lower(strval($request->getAttribute(Router\Routes::RELATION_ENTITY)));

		if ($relationEntity === Schemas\Devices\DeviceSchema::RELATIONSHIPS_CONNECTOR) {
			return $this->buildResponse($request, $response, $device->getConnector());

		} elseif ($relationEntity === Schemas\Devices\DeviceSchema::RELATIONSHIPS_PROPERTIES) {
			return $this->buildResponse($request, $response, $device->getProperties());

		} elseif ($relationEntity === Schemas\Devices\DeviceSchema::RELATIONSHIPS_CONTROLS) {
			return $this->buildResponse($request, $response, $device->getControls());

		} elseif ($relationEntity === Schemas\Devices\DeviceSchema::RELATIONSHIPS_PARENTS) {
			return $this->buildResponse($request, $response, $device->getParents());

		} elseif ($relationEntity === Schemas\Devices\DeviceSchema::RELATIONSHIPS_CHILDREN) {
			return $this->buildResponse($request, $response, $device->getChildren());

		} elseif ($relationEntity === Schemas\Devices\DeviceSchema::RELATIONSHIPS_CHANNELS) {
			$findQuery = new Queries\FindChannelsQuery();
			$findQuery->forDevice($device);

			return $this->buildResponse($request, $response, $this->channelsRepository->findAllBy($findQuery));
		}

		return parent::readRelationship($request, $response);
	}

}
