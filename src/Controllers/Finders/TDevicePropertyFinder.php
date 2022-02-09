<?php declare(strict_types = 1);

/**
 * TDevicePropertyFinder.php
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
 * @property-read Models\Devices\Properties\IPropertiesRepository $devicePropertiesRepository
 */
trait TDevicePropertyFinder
{

	/**
	 * @param string $id
	 * @param Entities\Devices\IDevice $device
	 *
	 * @return Entities\Devices\Properties\IProperty
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	private function findProperty(
		string $id,
		Entities\Devices\IDevice $device
	): Entities\Devices\Properties\IProperty {
		try {
			$findQuery = new Queries\FindDevicePropertiesQuery();
			$findQuery->forDevice($device);
			$findQuery->byId(Uuid\Uuid::fromString($id));

			$property = $this->devicePropertiesRepository->findOneBy($findQuery);

			if ($property === null) {
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

		return $property;
	}

}
