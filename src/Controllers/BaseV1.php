<?php declare(strict_types = 1);

/**
 * BaseV1.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 * @since          1.0.0
 *
 * @date           13.04.19
 */

namespace FastyBird\Module\Devices\Controllers;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence;
use Exception;
use FastyBird\JsonApi\Builder as JsonApiBuilder;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use FastyBird\JsonApi\Hydrators as JsonApiHydrators;
use FastyBird\Module\Devices\Exceptions;
use FastyBird\Module\Devices\Router;
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
use RuntimeException;
use stdClass;
use function array_key_exists;
use function in_array;
use function is_array;
use function strtoupper;
use function strval;

/**
 * API base controller
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
abstract class BaseV1
{

	use Nette\SmartObject;

	protected Localization\Translator $translator;

	protected Persistence\ManagerRegistry $managerRegistry;

	protected JsonApiBuilder\Builder $builder;

	protected Router\Validator $routesValidator;

	protected JsonApiHydrators\Container $hydratorsContainer;

	protected Log\LoggerInterface $logger;

	public function injectTranslator(Localization\Translator $translator): void
	{
		$this->translator = $translator;
	}

	public function injectManagerRegistry(Persistence\ManagerRegistry $managerRegistry): void
	{
		$this->managerRegistry = $managerRegistry;
	}

	public function injectLogger(Log\LoggerInterface|null $logger = null): void
	{
		$this->logger = $logger ?? new Log\NullLogger();
	}

	public function injectJsonApiBuilder(JsonApiBuilder\Builder $builder): void
	{
		$this->builder = $builder;
	}

	public function injectRoutesValidator(Router\Validator $validator): void
	{
		$this->routesValidator = $validator;
	}

	public function injectHydratorsContainer(JsonApiHydrators\Container $hydratorsContainer): void
	{
		$this->hydratorsContainer = $hydratorsContainer;
	}

	/**
	 * @throws JsonApiExceptions\JsonApi
	 */
	public function readRelationship(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response,
	): ResponseInterface
	{
		// & relation entity name
		$relationEntity = Utils\Strings::lower(strval($request->getAttribute(Router\Routes::RELATION_ENTITY)));

		if ($relationEntity !== '') {
			throw new JsonApiExceptions\JsonApiError(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//devices-module.base.messages.relationNotFound.heading'),
				$this->translator->translate(
					'//devices-module.base.messages.relationNotFound.message',
					['relation' => $relationEntity],
				),
			);
		}

		throw new JsonApiExceptions\JsonApiError(
			StatusCodeInterface::STATUS_NOT_FOUND,
			$this->translator->translate('//devices-module.base.messages.unknownRelation.heading'),
			$this->translator->translate('//devices-module.base.messages.unknownRelation.message'),
		);
	}

	/**
	 * @throws JsonApiExceptions\JsonApi
	 * @throws RuntimeException
	 */
	protected function createDocument(Message\ServerRequestInterface $request): JsonAPIDocument\IDocument
	{
		try {
			$content = Utils\Json::decode($request->getBody()->getContents());

			if (!$content instanceof stdClass) {
				throw new JsonApiExceptions\JsonApiError(
					StatusCodeInterface::STATUS_BAD_REQUEST,
					$this->translator->translate('//devices-module.base.messages.notValidJsonApi.heading'),
					$this->translator->translate('//devices-module.base.messages.notValidJsonApi.message'),
				);
			}

			$document = new JsonAPIDocument\Document($content);

		} catch (Utils\JsonException) {
			throw new JsonApiExceptions\JsonApiError(
				StatusCodeInterface::STATUS_BAD_REQUEST,
				$this->translator->translate('//devices-module.base.messages.notValidJson.heading'),
				$this->translator->translate('//devices-module.base.messages.notValidJson.message'),
			);
		} catch (JsonAPIDocument\Exceptions\RuntimeException) {
			throw new JsonApiExceptions\JsonApiError(
				StatusCodeInterface::STATUS_BAD_REQUEST,
				$this->translator->translate('//devices-module.base.messages.notValidJsonApi.heading'),
				$this->translator->translate('//devices-module.base.messages.notValidJsonApi.message'),
			);
		}

		return $document;
	}

	/**
	 * @throws JsonApiExceptions\JsonApiError
	 */
	protected function validateIdentifier(
		Message\ServerRequestInterface $request,
		JsonAPIDocument\IDocument $document,
	): bool
	{
		if (
			in_array(strtoupper($request->getMethod()), [
				RequestMethodInterface::METHOD_POST,
				RequestMethodInterface::METHOD_PATCH,
			], true)
			&& $request->getAttribute(Router\Routes::URL_ITEM_ID) !== null
			&& $request->getAttribute(Router\Routes::URL_ITEM_ID) !== $document->getResource()->getId()
		) {
			throw new JsonApiExceptions\JsonApiError(
				StatusCodeInterface::STATUS_BAD_REQUEST,
				$this->translator->translate('//devices-module.base.messages.invalidIdentifier.heading'),
				$this->translator->translate('//devices-module.base.messages.invalidIdentifier.message'),
			);
		}

		return true;
	}

	/**
	 * @throws Exceptions\Runtime
	 */
	protected function getOrmConnection(): Connection
	{
		$connection = $this->managerRegistry->getConnection();

		if ($connection instanceof Connection) {
			return $connection;
		}

		throw new Exceptions\Runtime('Transformer manager could not be loaded');
	}

	/**
	 * @param DoctrineCrud\Entities\IEntity|ResultSet<DoctrineCrud\Entities\IEntity>|array<DoctrineCrud\Entities\IEntity> $data
	 *
	 * @throws Exception
	 */
	protected function buildResponse(
		Message\ServerRequestInterface $request,
		ResponseInterface $response,
		ResultSet|DoctrineCrud\Entities\IEntity|array $data,
	): ResponseInterface
	{
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

			/** @var array<DoctrineCrud\Entities\IEntity> $entity */
			$entity = $data->toArray();

		} elseif (is_array($data)) {
			/** @var array<DoctrineCrud\Entities\IEntity> $entity */
			$entity = $data;

		} else {
			$entity = $data;
		}

		return $this->builder->build(
			$request,
			$response,
			$entity,
			$totalCount,
			fn (string $link): bool => $this->routesValidator->validate($link),
		);
	}

}
