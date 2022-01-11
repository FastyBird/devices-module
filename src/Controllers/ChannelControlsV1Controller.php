<?php declare(strict_types = 1);

/**
 * ChannelControlsV1Controller.php
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
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message;
use Ramsey\Uuid;

/**
 * Device channel controls API controller
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @Secured
 * @Secured\User(loggedIn)
 */
final class ChannelControlsV1Controller extends BaseV1Controller
{

	use Controllers\Finders\TDeviceFinder;
	use Controllers\Finders\TChannelFinder;

	/** @var string */
	protected string $translationDomain = 'devices-module.channelControls';

	/** @var Models\Devices\IDeviceRepository */
	protected Models\Devices\IDeviceRepository $deviceRepository;

	/** @var Models\Channels\IChannelRepository */
	protected Models\Channels\IChannelRepository $channelRepository;

	/** @var Models\Channels\Controls\IControlRepository */
	protected Models\Channels\Controls\IControlRepository $controlRepository;

	public function __construct(
		Models\Devices\IDeviceRepository $deviceRepository,
		Models\Channels\IChannelRepository $channelRepository,
		Models\Channels\Controls\IControlRepository $controlRepository
	) {
		$this->deviceRepository = $deviceRepository;
		$this->channelRepository = $channelRepository;
		$this->controlRepository = $controlRepository;
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

		// & channel
		$channel = $this->findChannel($request->getAttribute(Router\Routes::URL_CHANNEL_ID), $device);

		$findQuery = new Queries\FindChannelControlsQuery();
		$findQuery->forChannel($channel);

		$controls = $this->controlRepository->getResultSet($findQuery);

		// @phpstan-ignore-next-line
		return $this->buildResponse($request, $response, $controls);
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

		// & channel
		$channel = $this->findChannel($request->getAttribute(Router\Routes::URL_CHANNEL_ID), $device);

		if (Uuid\Uuid::isValid($request->getAttribute(Router\Routes::URL_ITEM_ID))) {
			$findQuery = new Queries\FindChannelControlsQuery();
			$findQuery->forChannel($channel);
			$findQuery->byId(Uuid\Uuid::fromString($request->getAttribute(Router\Routes::URL_ITEM_ID)));

			// & control
			$control = $this->controlRepository->findOneBy($findQuery);

			if ($control !== null) {
				return $this->buildResponse($request, $response, $control);
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

		// & channel
		$channel = $this->findChannel($request->getAttribute(Router\Routes::URL_CHANNEL_ID), $device);

		// & relation entity name
		$relationEntity = strtolower($request->getAttribute(Router\Routes::RELATION_ENTITY));

		if (Uuid\Uuid::isValid($request->getAttribute(Router\Routes::URL_ITEM_ID))) {
			$findQuery = new Queries\FindChannelControlsQuery();
			$findQuery->forChannel($channel);
			$findQuery->byId(Uuid\Uuid::fromString($request->getAttribute(Router\Routes::URL_ITEM_ID)));

			// & control
			$control = $this->controlRepository->findOneBy($findQuery);

			if ($control !== null) {
				if ($relationEntity === Schemas\Channels\Controls\ControlSchema::RELATIONSHIPS_CHANNEL) {
					return $this->buildResponse($request, $response, $device);
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
