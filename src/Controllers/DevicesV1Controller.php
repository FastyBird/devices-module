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

	/** @var string */
	protected string $translationDomain = 'devices-module.devices';

	/** @var Models\Devices\IDeviceRepository */
	protected Models\Devices\IDeviceRepository $deviceRepository;

	/** @var Models\Devices\IDevicesManager */
	protected Models\Devices\IDevicesManager $devicesManager;

	/** @var Models\Channels\IChannelRepository */
	protected Models\Channels\IChannelRepository $channelRepository;

	/** @var Models\Channels\IChannelsManager */
	protected Models\Channels\IChannelsManager $channelsManager;

	/** @var Hydrators\Devices\NetworkDeviceHydrator */
	private Hydrators\Devices\NetworkDeviceHydrator $networkDeviceHydrator;

	/** @var Hydrators\Devices\LocalDeviceHydrator */
	private Hydrators\Devices\LocalDeviceHydrator $localDeviceHydrator;

	/** @var Hydrators\Devices\VirtualDeviceHydrator */
	private Hydrators\Devices\VirtualDeviceHydrator $virtualDeviceHydrator;

	/** @var Hydrators\Devices\HomekitDeviceHydrator */
	private Hydrators\Devices\HomekitDeviceHydrator $homekitDeviceHydrator;

	public function __construct(
		Models\Devices\IDeviceRepository $deviceRepository,
		Models\Devices\IDevicesManager $devicesManager,
		Models\Channels\IChannelRepository $channelRepository,
		Models\Channels\IChannelsManager $channelsManager,
		Hydrators\Devices\NetworkDeviceHydrator $networkDeviceHydrator,
		Hydrators\Devices\LocalDeviceHydrator $localDeviceHydrator,
		Hydrators\Devices\VirtualDeviceHydrator $virtualDeviceHydrator,
		Hydrators\Devices\HomekitDeviceHydrator $homekitDeviceHydrator
	) {
		$this->deviceRepository = $deviceRepository;
		$this->devicesManager = $devicesManager;
		$this->channelRepository = $channelRepository;
		$this->channelsManager = $channelsManager;
		$this->networkDeviceHydrator = $networkDeviceHydrator;
		$this->localDeviceHydrator = $localDeviceHydrator;
		$this->virtualDeviceHydrator = $virtualDeviceHydrator;
		$this->homekitDeviceHydrator = $homekitDeviceHydrator;
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
		$findQuery = new Queries\FindDevicesQuery();

		$devices = $this->deviceRepository->getResultSet($findQuery);

		// @phpstan-ignore-next-line
		return $this->buildResponse($request, $response, $devices);
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
		$device = $this->findDevice($request->getAttribute(Router\Routes::URL_ITEM_ID));

		return $this->buildResponse($request, $response, $device);
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
	): Message\ResponseInterface {
		$document = $this->createDocument($request);

		if (
			$document->getResource()->getType() === Schemas\Devices\NetworkDeviceSchema::SCHEMA_TYPE
			|| $document->getResource()->getType() === Schemas\Devices\LocalDeviceSchema::SCHEMA_TYPE
			|| $document->getResource()->getType() === Schemas\Devices\VirtualDeviceSchema::SCHEMA_TYPE
			|| $document->getResource()->getType() === Schemas\Devices\HomekitDeviceSchema::SCHEMA_TYPE
		) {
			try {
				// Start transaction connection to the database
				$this->getOrmConnection()->beginTransaction();

				if ($document->getResource()->getType() === Schemas\Devices\NetworkDeviceSchema::SCHEMA_TYPE) {
					$device = $this->devicesManager->create($this->networkDeviceHydrator->hydrate($document));

				} elseif ($document->getResource()->getType() === Schemas\Devices\LocalDeviceSchema::SCHEMA_TYPE) {
					$device = $this->devicesManager->create($this->localDeviceHydrator->hydrate($document));

				} elseif ($document->getResource()->getType() === Schemas\Devices\VirtualDeviceSchema::SCHEMA_TYPE) {
					$device = $this->devicesManager->create($this->virtualDeviceHydrator->hydrate($document));

				} elseif ($document->getResource()->getType() === Schemas\Devices\HomekitDeviceSchema::SCHEMA_TYPE) {
					$device = $this->devicesManager->create($this->homekitDeviceHydrator->hydrate($document));

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
				var_dump($ex->getMessage());
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

			$response = $this->buildResponse($request, $response, $device);
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
		$document = $this->createDocument($request);

		$this->validateIdentifier($request, $document);

		$device = $this->findDevice($request->getAttribute(Router\Routes::URL_ITEM_ID));

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			if (
				$document->getResource()->getType() === Schemas\Devices\NetworkDeviceSchema::SCHEMA_TYPE
				&& $device instanceof Entities\Devices\INetworkDevice
			) {
				$updateDeviceData = $this->networkDeviceHydrator->hydrate($document, $device);

			} elseif (
				$document->getResource()->getType() === Schemas\Devices\LocalDeviceSchema::SCHEMA_TYPE
				&& $device instanceof Entities\Devices\ILocalDevice
			) {
				$updateDeviceData = $this->localDeviceHydrator->hydrate($document, $device);

			} elseif (
				$document->getResource()->getType() === Schemas\Devices\VirtualDeviceSchema::SCHEMA_TYPE
				&& $device instanceof Entities\Devices\IVirtualDevice
			) {
				$updateDeviceData = $this->virtualDeviceHydrator->hydrate($document, $device);

			} elseif (
				$document->getResource()->getType() === Schemas\Devices\HomekitDeviceSchema::SCHEMA_TYPE
				&& $device instanceof Entities\Devices\IHomekitDevice
			) {
				$updateDeviceData = $this->homekitDeviceHydrator->hydrate($document, $device);

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

			$device = $this->devicesManager->update($device, $updateDeviceData);

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

		return $this->buildResponse($request, $response, $device);
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
		$device = $this->findDevice($request->getAttribute(Router\Routes::URL_ITEM_ID));

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
		$device = $this->findDevice($request->getAttribute(Router\Routes::URL_ITEM_ID));

		$relationEntity = strtolower($request->getAttribute(Router\Routes::RELATION_ENTITY));

		if ($relationEntity === Schemas\Devices\DeviceSchema::RELATIONSHIPS_PROPERTIES) {
			return $this->buildResponse($request, $response, $device->getProperties());

		} elseif ($relationEntity === Schemas\Devices\DeviceSchema::RELATIONSHIPS_CONFIGURATION) {
			return $this->buildResponse($request, $response, $device->getConfiguration());

		} elseif ($relationEntity === Schemas\Devices\DeviceSchema::RELATIONSHIPS_CHILDREN) {
			return $this->buildResponse($request, $response, $device->getChildren());

		} elseif ($relationEntity === Schemas\Devices\DeviceSchema::RELATIONSHIPS_CHANNELS) {
			$findQuery = new Queries\FindChannelsQuery();
			$findQuery->forDevice($device);

			return $this->buildResponse($request, $response, $this->channelRepository->findAllBy($findQuery));
		}

		return parent::readRelationship($request, $response);
	}

}
