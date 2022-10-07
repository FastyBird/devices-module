<?php declare(strict_types = 1);

/**
 * DeviceControlsV1.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 * @since          0.4.0
 *
 * @date           29.09.21
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
 * Device controls API controller
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @Secured
 * @Secured\User(loggedIn)
 */
final class DeviceControlsV1 extends BaseV1
{

	use Controllers\Finders\TDevice;

	/** @var Models\Devices\DevicesRepository */
	protected Models\Devices\DevicesRepository $devicesRepository;

	/** @var Models\Devices\Controls\ControlsRepository */
	private Models\Devices\Controls\ControlsRepository $deviceControlsRepository;

	/**
	 * @param Models\Devices\DevicesRepository $devicesRepository
	 * @param Models\Devices\Controls\ControlsRepository $deviceControlsRepository
	 */
	public function __construct(
		Models\Devices\DevicesRepository $devicesRepository,
		Models\Devices\Controls\ControlsRepository $deviceControlsRepository
	) {
		$this->devicesRepository = $devicesRepository;
		$this->deviceControlsRepository = $deviceControlsRepository;
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

		$findQuery = new Queries\FindDeviceControls();
		$findQuery->forDevice($device);

		$controls = $this->deviceControlsRepository->getResultSet($findQuery);

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

		if (Uuid\Uuid::isValid(strval($request->getAttribute(Router\Routes::URL_ITEM_ID)))) {
			$findQuery = new Queries\FindDeviceControls();
			$findQuery->forDevice($device);
			$findQuery->byId(Uuid\Uuid::fromString(strval($request->getAttribute(Router\Routes::URL_ITEM_ID))));

			// & control
			$control = $this->deviceControlsRepository->findOneBy($findQuery);

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

		// & relation entity name
		$relationEntity = Utils\Strings::lower(strval($request->getAttribute(Router\Routes::RELATION_ENTITY)));

		if (Uuid\Uuid::isValid(strval($request->getAttribute(Router\Routes::URL_ITEM_ID)))) {
			$findQuery = new Queries\FindDeviceControls();
			$findQuery->forDevice($device);
			$findQuery->byId(Uuid\Uuid::fromString(strval($request->getAttribute(Router\Routes::URL_ITEM_ID))));

			// & control
			$control = $this->deviceControlsRepository->findOneBy($findQuery);

			if ($control !== null) {
				if ($relationEntity === Schemas\Devices\Controls\Control::RELATIONSHIPS_DEVICE) {
					return $this->buildResponse($request, $response, $control->getDevice());
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
