<?php declare(strict_types = 1);

/**
 * BaseV1Controller.php
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

use Doctrine\DBAL\Connection;
use Doctrine\Persistence;
use Exception;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Router;
use FastyBird\JsonApi\Builder as JsonApiBuilder;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use FastyBird\JsonApi\Hydrators as JsonApiHydrators;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use IPub\DoctrineCrud;
use IPub\DoctrineOrmQuery\ResultSet;
use IPub\JsonAPIDocument;
use Nette;
use Nette\Localization;
use Nette\Utils;
use Psr\Http\Message;
use Psr\Http\Message\ResponseInterface;
use Psr\Log;
use stdClass;

/**
 * API base controller
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
abstract class BaseV1Controller
{

	use Nette\SmartObject;

	/** @var Localization\Translator */
	protected Localization\Translator $translator;

	/** @var Persistence\ManagerRegistry */
	protected Persistence\ManagerRegistry $managerRegistry;

	/** @var JsonApiBuilder\Builder */
	protected JsonApiBuilder\Builder $builder;

	/** @var Router\Validator */
	protected Router\Validator $routesValidator;

	/** @var JsonApiHydrators\HydratorsContainer */
	protected JsonApiHydrators\HydratorsContainer $hydratorsContainer;

	/** @var Log\LoggerInterface */
	protected Log\LoggerInterface $logger;

	/**
	 * @param Localization\Translator $translator
	 *
	 * @return void
	 */
	public function injectTranslator(Localization\Translator $translator): void
	{
		$this->translator = $translator;
	}

	/**
	 * @param Persistence\ManagerRegistry $managerRegistry
	 *
	 * @return void
	 */
	public function injectManagerRegistry(Persistence\ManagerRegistry $managerRegistry): void
	{
		$this->managerRegistry = $managerRegistry;
	}

	/**
	 * @param Log\LoggerInterface|null $logger
	 *
	 * @return void
	 */
	public function injectLogger(?Log\LoggerInterface $logger): void
	{
		$this->logger = $logger ?? new Log\NullLogger();
	}

	/**
	 * @param JsonApiBuilder\Builder $builder
	 *
	 * @return void
	 */
	public function injectJsonApiBuilder(JsonApiBuilder\Builder $builder): void
	{
		$this->builder = $builder;
	}

	/**
	 * @param Router\Validator $validator
	 *
	 * @return void
	 */
	public function injectRoutesValidator(Router\Validator $validator): void
	{
		$this->routesValidator = $validator;
	}

	/**
	 * @param JsonApiHydrators\HydratorsContainer $hydratorsContainer
	 *
	 * @return void
	 */
	public function injectHydratorsContainer(JsonApiHydrators\HydratorsContainer $hydratorsContainer): void
	{
		$this->hydratorsContainer = $hydratorsContainer;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param Message\ResponseInterface $response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	public function readRelationship(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): ResponseInterface {
		// & relation entity name
		$relationEntity = Utils\Strings::lower(strval($request->getAttribute(Router\Routes::RELATION_ENTITY)));

		if ($relationEntity !== '') {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//devices-module.base.messages.relationNotFound.heading'),
				$this->translator->translate('//devices-module.base.messages.relationNotFound.message', ['relation' => $relationEntity])
			);
		}

		throw new JsonApiExceptions\JsonApiErrorException(
			StatusCodeInterface::STATUS_NOT_FOUND,
			$this->translator->translate('//devices-module.base.messages.unknownRelation.heading'),
			$this->translator->translate('//devices-module.base.messages.unknownRelation.message')
		);
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 *
	 * @return JsonAPIDocument\IDocument
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	protected function createDocument(Message\ServerRequestInterface $request): JsonAPIDocument\IDocument
	{
		try {
			$content = Utils\Json::decode($request->getBody()->getContents());

			if (!$content instanceof stdClass) {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_BAD_REQUEST,
					$this->translator->translate('//devices-module.base.messages.notValidJsonApi.heading'),
					$this->translator->translate('//devices-module.base.messages.notValidJsonApi.message')
				);
			}

			$document = new JsonAPIDocument\Document($content);

		} catch (Utils\JsonException) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_BAD_REQUEST,
				$this->translator->translate('//devices-module.base.messages.notValidJson.heading'),
				$this->translator->translate('//devices-module.base.messages.notValidJson.message')
			);

		} catch (JsonAPIDocument\Exceptions\RuntimeException) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_BAD_REQUEST,
				$this->translator->translate('//devices-module.base.messages.notValidJsonApi.heading'),
				$this->translator->translate('//devices-module.base.messages.notValidJsonApi.message')
			);
		}

		return $document;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param JsonAPIDocument\IDocument $document
	 *
	 * @return bool
	 *
	 * @throws JsonApiExceptions\JsonApiErrorException
	 */
	protected function validateIdentifier(
		Message\ServerRequestInterface $request,
		JsonAPIDocument\IDocument $document
	): bool {
		if (
			in_array(strtoupper($request->getMethod()), [
				RequestMethodInterface::METHOD_POST,
				RequestMethodInterface::METHOD_PATCH,
			], true)
			&& $request->getAttribute(Router\Routes::URL_ITEM_ID) !== null
			&& $request->getAttribute(Router\Routes::URL_ITEM_ID) !== $document->getResource()->getId()
		) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_BAD_REQUEST,
				$this->translator->translate('//devices-module.base.messages.invalidIdentifier.heading'),
				$this->translator->translate('//devices-module.base.messages.invalidIdentifier.message')
			);
		}

		return true;
	}

	/**
	 * @return Connection
	 */
	protected function getOrmConnection(): Connection
	{
		$connection = $this->managerRegistry->getConnection();

		if ($connection instanceof Connection) {
			return $connection;
		}

		throw new Exceptions\RuntimeException('Entity manager could not be loaded');
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param DoctrineCrud\Entities\IEntity|ResultSet<DoctrineCrud\Entities\IEntity>|Array<DoctrineCrud\Entities\IEntity> $data
	 *
	 * @return ResponseInterface
	 *
	 * @throws Exception
	 */
	protected function buildResponse(
		Message\ServerRequestInterface $request,
		ResponseInterface $response,
		ResultSet|DoctrineCrud\Entities\IEntity|array $data
	): ResponseInterface {
		$totalCount = null;

		if ($data instanceof ResultSet) {
			if (array_key_exists('page', $request->getQueryParams())) {
				$queryParams = $request->getQueryParams();

				$pageOffset = isset($queryParams['page']['offset']) ? (int) $queryParams['page']['offset'] : null;
				$pageLimit = isset($queryParams['page']['limit']) ? (int) $queryParams['page']['limit'] : null;

				$totalCount = $data->getTotalCount();

				if ($data->getTotalCount() > $pageLimit) {
					$data->applyPaging($pageOffset, $pageLimit);
				}
			}

			/** @var DoctrineCrud\Entities\IEntity[] $entity */
			$entity = $data->toArray();

		} elseif (is_array($data)) {
			/** @var DoctrineCrud\Entities\IEntity[] $entity */
			$entity = $data;

		} else {
			$entity = $data;
		}

		return $this->builder->build(
			$request,
			$response,
			$entity,
			$totalCount,
			function (string $link): bool {
				return $this->routesValidator->validate($link);
			}
		);
	}

}
