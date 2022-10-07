<?php declare(strict_types = 1);

/**
 * ChannelControlsV1.php
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

use Exception;
use FastyBird\DevicesModule\Controllers;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use FastyBird\DevicesModule\Router;
use FastyBird\DevicesModule\Schemas;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use Fig\Http\Message\StatusCodeInterface;
use Nette\Utils;
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
final class ChannelControlsV1 extends BaseV1
{

	use Controllers\Finders\TDevice;
	use Controllers\Finders\TChannel;

	/** @var Models\Devices\DevicesRepository */
	protected Models\Devices\DevicesRepository $devicesRepository;

	/** @var Models\Channels\ChannelsRepository */
	protected Models\Channels\ChannelsRepository $channelsRepository;

	/** @var Models\Channels\Controls\ControlsRepository */
	protected Models\Channels\Controls\ControlsRepository $channelControlsRepository;

	/**
	 * @param Models\Devices\DevicesRepository $devicesRepository
	 * @param Models\Channels\ChannelsRepository $channelsRepository
	 * @param Models\Channels\Controls\ControlsRepository $channelControlsRepository
	 */
	public function __construct(
		Models\Devices\DevicesRepository $devicesRepository,
		Models\Channels\ChannelsRepository $channelsRepository,
		Models\Channels\Controls\ControlsRepository $channelControlsRepository
	) {
		$this->devicesRepository = $devicesRepository;
		$this->channelsRepository = $channelsRepository;
		$this->channelControlsRepository = $channelControlsRepository;
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
		// At first, try to load device
		$device = $this->findDevice(strval($request->getAttribute(Router\Routes::URL_DEVICE_ID)));

		// & channel
		$channel = $this->findChannel(strval($request->getAttribute(Router\Routes::URL_CHANNEL_ID)), $device);

		$findQuery = new Queries\FindChannelControls();
		$findQuery->forChannel($channel);

		$controls = $this->channelControlsRepository->getResultSet($findQuery);

		// @phpstan-ignore-next-line
		return $this->buildResponse($request, $response, $controls);
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
		// At first, try to load device
		$device = $this->findDevice(strval($request->getAttribute(Router\Routes::URL_DEVICE_ID)));

		// & channel
		$channel = $this->findChannel(strval($request->getAttribute(Router\Routes::URL_CHANNEL_ID)), $device);

		if (Uuid\Uuid::isValid(strval($request->getAttribute(Router\Routes::URL_ITEM_ID)))) {
			$findQuery = new Queries\FindChannelControls();
			$findQuery->forChannel($channel);
			$findQuery->byId(Uuid\Uuid::fromString(strval($request->getAttribute(Router\Routes::URL_ITEM_ID))));

			// & control
			$control = $this->channelControlsRepository->findOneBy($findQuery);

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
	 * @throws Exception
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	public function readRelationship(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): Message\ResponseInterface {
		// At first, try to load device
		$device = $this->findDevice(strval($request->getAttribute(Router\Routes::URL_DEVICE_ID)));

		// & channel
		$channel = $this->findChannel(strval($request->getAttribute(Router\Routes::URL_CHANNEL_ID)), $device);

		// & relation entity name
		$relationEntity = Utils\Strings::lower(strval($request->getAttribute(Router\Routes::RELATION_ENTITY)));

		if (Uuid\Uuid::isValid(strval($request->getAttribute(Router\Routes::URL_ITEM_ID)))) {
			$findQuery = new Queries\FindChannelControls();
			$findQuery->forChannel($channel);
			$findQuery->byId(Uuid\Uuid::fromString(strval($request->getAttribute(Router\Routes::URL_ITEM_ID))));

			// & control
			$control = $this->channelControlsRepository->findOneBy($findQuery);

			if ($control !== null) {
				if ($relationEntity === Schemas\Channels\Controls\Control::RELATIONSHIPS_CHANNEL) {
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
