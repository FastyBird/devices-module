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

use Contributte\Translation;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Router;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use FastyBird\WebServer\Http as WebServerHttp;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use IPub\JsonAPIDocument;
use Nette;
use Nette\Utils;
use Psr\Http\Message;
use Psr\Log;

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

	/** @var string */
	protected string $translationDomain = '';

	/** @var Translation\PrefixedTranslator */
	protected Translation\PrefixedTranslator $translator;

	/** @var Persistence\ManagerRegistry */
	protected Persistence\ManagerRegistry $managerRegistry;

	/** @var Log\LoggerInterface */
	protected Log\LoggerInterface $logger;

	/**
	 * @param Translation\Translator $translator
	 *
	 * @return void
	 */
	public function injectTranslator(Translation\Translator $translator): void
	{
		$this->translator = new Translation\PrefixedTranslator($translator, $this->translationDomain);
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
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	public function readRelationship(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		// & relation entity name
		$relationEntity = strtolower($request->getAttribute(Router\Routes::RELATION_ENTITY));

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
			$document = new JsonAPIDocument\Document(Utils\Json::decode($request->getBody()->getContents()));

		} catch (Utils\JsonException $ex) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_BAD_REQUEST,
				$this->translator->translate('//devices-module.base.messages.notValidJson.heading'),
				$this->translator->translate('//devices-module.base.messages.notValidJson.message')
			);

		} catch (JsonAPIDocument\Exceptions\RuntimeException $ex) {
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

}
