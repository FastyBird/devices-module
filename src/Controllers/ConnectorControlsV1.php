<?php declare(strict_types = 1);

/**
 * ConnectorControlsV1.php
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
 * Connector controls API controller
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @Secured
 * @Secured\User(loggedIn)
 */
final class ConnectorControlsV1 extends BaseV1
{

	use Controllers\Finders\TConnector;

	/** @var Models\Connectors\ConnectorsRepository */
	protected Models\Connectors\ConnectorsRepository $connectorsRepository;

	/** @var Models\Connectors\Controls\ControlsRepository */
	private Models\Connectors\Controls\ControlsRepository $connectorControlsRepository;

	/**
	 * @param Models\Connectors\ConnectorsRepository $connectorsRepository
	 * @param Models\Connectors\Controls\ControlsRepository $connectorControlsRepository
	 */
	public function __construct(
		Models\Connectors\ConnectorsRepository $connectorsRepository,
		Models\Connectors\Controls\ControlsRepository $connectorControlsRepository
	) {
		$this->connectorsRepository = $connectorsRepository;
		$this->connectorControlsRepository = $connectorControlsRepository;
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
		// At first, try to load connector
		$connector = $this->findConnector(strval($request->getAttribute(Router\Routes::URL_CONNECTOR_ID)));

		$findQuery = new Queries\FindConnectorControls();
		$findQuery->forConnector($connector);

		$controls = $this->connectorControlsRepository->getResultSet($findQuery);

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
		// At first, try to load connector
		$connector = $this->findConnector(strval($request->getAttribute(Router\Routes::URL_CONNECTOR_ID)));

		if (Uuid\Uuid::isValid(strval($request->getAttribute(Router\Routes::URL_ITEM_ID)))) {
			$findQuery = new Queries\FindConnectorControls();
			$findQuery->forConnector($connector);
			$findQuery->byId(Uuid\Uuid::fromString(strval($request->getAttribute(Router\Routes::URL_ITEM_ID))));

			// & control
			$control = $this->connectorControlsRepository->findOneBy($findQuery);

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
		// At first, try to load connector
		$connector = $this->findConnector(strval($request->getAttribute(Router\Routes::URL_CONNECTOR_ID)));

		// & relation entity name
		$relationEntity = Utils\Strings::lower(strval($request->getAttribute(Router\Routes::RELATION_ENTITY)));

		if (Uuid\Uuid::isValid(strval($request->getAttribute(Router\Routes::URL_ITEM_ID)))) {
			$findQuery = new Queries\FindConnectorControls();
			$findQuery->forConnector($connector);
			$findQuery->byId(Uuid\Uuid::fromString(strval($request->getAttribute(Router\Routes::URL_ITEM_ID))));

			// & control
			$control = $this->connectorControlsRepository->findOneBy($findQuery);

			if ($control !== null) {
				if ($relationEntity === Schemas\Connectors\Controls\Control::RELATIONSHIPS_CONNECTOR) {
					return $this->buildResponse($request, $response, $control->getConnector());
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
