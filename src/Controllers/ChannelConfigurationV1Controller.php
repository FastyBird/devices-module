<?php declare(strict_types = 1);

/**
 * ChannelConfigurationV1Controller.php
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

use FastyBird\DevicesModule\Controllers;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use FastyBird\DevicesModule\Router;
use FastyBird\DevicesModule\Schemas;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use FastyBird\ModulesMetadata;
use FastyBird\WebServer\Http as WebServerHttp;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message;
use Ramsey\Uuid;

/**
 * Device channel configuration API controller
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @Secured
 * @Secured\User(loggedIn)
 */
final class ChannelConfigurationV1Controller extends BaseV1Controller
{

	use Controllers\Finders\TDeviceFinder;
	use Controllers\Finders\TChannelFinder;

	/** @var string */
	protected string $translationDomain = 'devices-module.channelConfiguration';

	/** @var Models\Devices\IDeviceRepository */
	protected Models\Devices\IDeviceRepository $deviceRepository;

	/** @var Models\Channels\IChannelRepository */
	protected Models\Channels\IChannelRepository $channelRepository;

	/** @var Models\Channels\Configuration\IRowRepository */
	private Models\Channels\Configuration\IRowRepository $rowRepository;

	public function __construct(
		Models\Devices\IDeviceRepository $deviceRepository,
		Models\Channels\IChannelRepository $channelRepository,
		Models\Channels\Configuration\IRowRepository $rowRepository
	) {
		$this->deviceRepository = $deviceRepository;
		$this->channelRepository = $channelRepository;
		$this->rowRepository = $rowRepository;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	public function index(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		// At first, try to load device
		$device = $this->findDevice($request->getAttribute(Router\Routes::URL_DEVICE_ID));

		// & channel
		$channel = $this->findChannel($request->getAttribute(Router\Routes::URL_CHANNEL_ID), $device);

		if (!$channel->hasControl(ModulesMetadata\Constants::CONTROL_CONFIG)) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//devices-module.base.messages.notFound.heading'),
				$this->translator->translate('//devices-module.base.messages.notFound.message')
			);
		}

		$findQuery = new Queries\FindChannelConfigurationQuery();
		$findQuery->forChannel($channel);

		$rows = $this->rowRepository->getResultSet($findQuery);

		return $response
			->withEntity(WebServerHttp\ScalarEntity::from($rows));
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

		// & channel
		$channel = $this->findChannel($request->getAttribute(Router\Routes::URL_CHANNEL_ID), $device);

		if (!$channel->hasControl(ModulesMetadata\Constants::CONTROL_CONFIG)) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//devices-module.base.messages.notFound.heading'),
				$this->translator->translate('//devices-module.base.messages.notFound.message')
			);
		}

		if (Uuid\Uuid::isValid($request->getAttribute(Router\Routes::URL_ITEM_ID))) {
			$findQuery = new Queries\FindChannelConfigurationQuery();
			$findQuery->forChannel($channel);
			$findQuery->byId(Uuid\Uuid::fromString($request->getAttribute(Router\Routes::URL_ITEM_ID)));

			// & configuration row
			$row = $this->rowRepository->findOneBy($findQuery);

			if ($row !== null) {
				return $response
					->withEntity(WebServerHttp\ScalarEntity::from($row));
			}
		}

		throw new JsonApiExceptions\JsonApiErrorException(
			StatusCodeInterface::STATUS_NOT_FOUND,
			$this->translator->translate('//devices-module.base.messages.notFound.heading'),
			$this->translator->translate('//devices-module.base.messages.notFound.message')
		);
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

		// & channel
		$channel = $this->findChannel($request->getAttribute(Router\Routes::URL_CHANNEL_ID), $device);

		if (!$channel->hasControl(ModulesMetadata\Constants::CONTROL_CONFIG)) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//devices-module.base.messages.notFound.heading'),
				$this->translator->translate('//devices-module.base.messages.notFound.message')
			);
		}

		// & relation entity name
		$relationEntity = strtolower($request->getAttribute(Router\Routes::RELATION_ENTITY));

		if (Uuid\Uuid::isValid($request->getAttribute(Router\Routes::URL_ITEM_ID))) {
			$findQuery = new Queries\FindChannelConfigurationQuery();
			$findQuery->forChannel($channel);
			$findQuery->byId(Uuid\Uuid::fromString($request->getAttribute(Router\Routes::URL_ITEM_ID)));

			// & configuration row
			$row = $this->rowRepository->findOneBy($findQuery);

			if ($row !== null) {
				if ($relationEntity === Schemas\Channels\Configuration\RowSchema::RELATIONSHIPS_CHANNEL) {
					return $response
						->withEntity(WebServerHttp\ScalarEntity::from($device));
				}

			} else {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_NOT_FOUND,
					$this->translator->translate('//devices-module.base.messages.notFound.heading'),
					$this->translator->translate('//devices-module.base.messages.notFound.message')
				);
			}
		}

		return parent::readRelationship($request, $response);
	}

}
