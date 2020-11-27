<?php declare(strict_types = 1);

/**
 * DeviceChildrenV1Controller.php
 *
 * @license        More in license.md
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
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use FastyBird\WebServer\Http as WebServerHttp;
use Psr\Http\Message;

/**
 * Device children API controller
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @Secured
 * @Secured\User(loggedIn)
 */
final class DeviceChildrenV1Controller extends BaseV1Controller
{

	use Controllers\Finders\TDeviceFinder;

	/** @var Models\Devices\IDeviceRepository */
	protected $deviceRepository;

	/** @var string */
	protected $translationDomain = 'module.deviceChildren';

	public function __construct(
		Models\Devices\IDeviceRepository $deviceRepository
	) {
		$this->deviceRepository = $deviceRepository;
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
		$device = $this->findDevice($request->getAttribute(Router\Router::URL_DEVICE_ID));

		$findQuery = new Queries\FindDevicesQuery();
		$findQuery->forParent($device);

		$children = $this->deviceRepository->getResultSet($findQuery);

		return $response
			->withEntity(WebServerHttp\ScalarEntity::from($children));
	}

}
