<?php declare(strict_types = 1);

/**
 * ChannelPropertyChildrenV1Controller.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 * @since          0.33.0
 *
 * @date           09.02.22
 */

namespace FastyBird\DevicesModule\Controllers;

use Exception;
use FastyBird\DevicesModule\Controllers;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use FastyBird\DevicesModule\Router;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use Psr\Http\Message;

/**
 * Device property children API controller
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @Secured
 * @Secured\User(loggedIn)
 */
final class ChannelPropertyChildrenV1Controller extends BaseV1Controller
{

	use Controllers\Finders\TDeviceFinder;
	use Controllers\Finders\TChannelFinder;
	use Controllers\Finders\TChannelPropertyFinder;

	/** @var Models\Devices\IDevicesRepository */
	protected Models\Devices\IDevicesRepository $devicesRepository;

	/** @var Models\Channels\Properties\IPropertiesRepository */
	protected Models\Channels\Properties\IPropertiesRepository $channelPropertiesRepository;

	/**
	 * @param Models\Devices\IDevicesRepository $devicesRepository
	 * @param Models\Channels\Properties\IPropertiesRepository $channelPropertiesRepository
	 */
	public function __construct(
		Models\Devices\IDevicesRepository $devicesRepository,
		Models\Channels\Properties\IPropertiesRepository $channelPropertiesRepository
	) {
		$this->devicesRepository = $devicesRepository;
		$this->channelPropertiesRepository = $channelPropertiesRepository;
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
		$device = $this->findDevice($request->getAttribute(Router\Routes::URL_DEVICE_ID));
		// & channel
		$channel = $this->findChannel($request->getAttribute(Router\Routes::URL_CHANNEL_ID), $device);
		// & property
		$property = $this->findProperty($request->getAttribute(Router\Routes::URL_PROPERTY_ID), $channel);

		$findQuery = new Queries\FindChannelPropertiesQuery();
		$findQuery->forParent($property);

		$children = $this->channelPropertiesRepository->getResultSet($findQuery);

		// @phpstan-ignore-next-line
		return $this->buildResponse($request, $response, $children);
	}

}
