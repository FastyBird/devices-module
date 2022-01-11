<?php declare(strict_types = 1);

/**
 * ConnectorControlsV1Controller.php
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
final class ConnectorControlsV1Controller extends BaseV1Controller
{

	use Controllers\Finders\TConnectorFinder;

	/** @var string */
	protected string $translationDomain = 'devices-module.connectorControls';

	/** @var Models\Connectors\IConnectorRepository */
	protected Models\Connectors\IConnectorRepository $connectorRepository;

	/** @var Models\Connectors\Controls\IControlRepository */
	private Models\Connectors\Controls\IControlRepository $controlRepository;

	public function __construct(
		Models\Connectors\IConnectorRepository $connectorRepository,
		Models\Connectors\Controls\IControlRepository $controlRepository
	) {
		$this->connectorRepository = $connectorRepository;
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
		// At first, try to load connector
		$connector = $this->findConnector($request->getAttribute(Router\Routes::URL_CONNECTOR_ID));

		$findQuery = new Queries\FindConnectorControlsQuery();
		$findQuery->forConnector($connector);

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
		// At first, try to load connector
		$connector = $this->findConnector($request->getAttribute(Router\Routes::URL_CONNECTOR_ID));

		if (Uuid\Uuid::isValid($request->getAttribute(Router\Routes::URL_ITEM_ID))) {
			$findQuery = new Queries\FindConnectorControlsQuery();
			$findQuery->forConnector($connector);
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
		// At first, try to load connector
		$connector = $this->findConnector($request->getAttribute(Router\Routes::URL_CONNECTOR_ID));

		// & relation entity name
		$relationEntity = strtolower($request->getAttribute(Router\Routes::RELATION_ENTITY));

		if (Uuid\Uuid::isValid($request->getAttribute(Router\Routes::URL_ITEM_ID))) {
			$findQuery = new Queries\FindConnectorControlsQuery();
			$findQuery->forConnector($connector);
			$findQuery->byId(Uuid\Uuid::fromString($request->getAttribute(Router\Routes::URL_ITEM_ID)));

			// & control
			$control = $this->controlRepository->findOneBy($findQuery);

			if ($control !== null) {
				if ($relationEntity === Schemas\Connectors\Controls\ControlSchema::RELATIONSHIPS_CONNECTOR) {
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
