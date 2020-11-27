<?php declare(strict_types = 1);

/**
 * TDeviceFinder.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 * @since          0.1.0
 *
 * @date           13.04.19
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
 * @property-read Models\Devices\IDeviceRepository $deviceRepository
 */
trait TDeviceFinder
{

	/**
	 * @param string $id
	 *
	 * @return Entities\Devices\IDevice
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	protected function findDevice(string $id): Entities\Devices\IDevice
	{
		try {
			$findQuery = new Queries\FindDevicesQuery();
			$findQuery->byId(Uuid\Uuid::fromString($id));

			$device = $this->deviceRepository->findOneBy($findQuery);

			if ($device === null) {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_NOT_FOUND,
					$this->translator->translate('//module.base.messages.notFound.heading'),
					$this->translator->translate('//module.base.messages.notFound.message')
				);
			}

		} catch (Uuid\Exception\InvalidUuidStringException $ex) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//module.base.messages.notFound.heading'),
				$this->translator->translate('//module.base.messages.notFound.message')
			);
		}

		return $device;
	}

}
