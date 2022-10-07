<?php declare(strict_types = 1);

/**
 * DeviceParentsV1.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 * @since          0.42.0
 *
 * @date           25.03.22
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
 * Device parents API controller
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @Secured
 * @Secured\User(loggedIn)
 */
final class DeviceParentsV1 extends BaseV1
{

	use Controllers\Finders\TDevice;

	/** @var Models\Devices\DevicesRepository */
	protected Models\Devices\DevicesRepository $devicesRepository;

	/**
	 * @param Models\Devices\DevicesRepository $devicesRepository
	 */
	public function __construct(
		Models\Devices\DevicesRepository $devicesRepository
	) {
		$this->devicesRepository = $devicesRepository;
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

		$findQuery = new Queries\FindDevices();
		$findQuery->forChild($device);

		$parents = $this->devicesRepository->getResultSet($findQuery);

		// @phpstan-ignore-next-line
		return $this->buildResponse($request, $response, $parents);
	}

}
