<?php declare(strict_types = 1);

/**
 * TConnectorFinder.php
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

namespace FastyBird\DevicesModule\Controllers\Finders;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use Fig\Http\Message\StatusCodeInterface;
use Nette\Localization;
use Ramsey\Uuid;

/**
 * @property-read Localization\ITranslator $translator
 * @property-read Models\Connectors\IConnectorsRepository $connectorsRepository
 */
trait TConnectorFinder
{

	/**
	 * @param string $id
	 *
	 * @return Entities\Connectors\IConnector
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	protected function findConnector(string $id): Entities\Connectors\IConnector
	{
		try {
			$findQuery = new Queries\FindConnectorsQuery();
			$findQuery->byId(Uuid\Uuid::fromString($id));

			$connector = $this->connectorsRepository->findOneBy($findQuery);

			if ($connector === null) {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_NOT_FOUND,
					$this->translator->translate('//devices-module.base.messages.notFound.heading'),
					$this->translator->translate('//devices-module.base.messages.notFound.message')
				);
			}
		} catch (Uuid\Exception\InvalidUuidStringException $ex) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//devices-module.base.messages.notFound.heading'),
				$this->translator->translate('//devices-module.base.messages.notFound.message')
			);
		}

		return $connector;
	}

}
