<?php declare(strict_types = 1);

/**
 * TChannel.php
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

namespace FastyBird\DevicesModule\Controllers\Finders;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use Fig\Http\Message\StatusCodeInterface;
use Nette\Localization;
use Ramsey\Uuid;

/**
 * @property-read Localization\Translator $translator
 * @property-read Models\Channels\ChannelsRepository $channelsRepository
 */
trait TChannel
{

	/**
	 * @param string $id
	 * @param Entities\Devices\Device $device
	 *
	 * @return Entities\Channels\Channel
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	protected function findChannel(
		string $id,
		Entities\Devices\Device $device
	): Entities\Channels\Channel {
		try {
			$findQuery = new Queries\FindChannels();
			$findQuery->byId(Uuid\Uuid::fromString($id));
			$findQuery->forDevice($device);

			$channel = $this->channelsRepository->findOneBy($findQuery);

			if ($channel === null) {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_NOT_FOUND,
					$this->translator->translate('//devices-module.base.messages.notFound.heading'),
					$this->translator->translate('//devices-module.base.messages.notFound.message')
				);
			}
		} catch (Uuid\Exception\InvalidUuidStringException) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//devices-module.base.messages.notFound.heading'),
				$this->translator->translate('//devices-module.base.messages.notFound.message')
			);
		}

		return $channel;
	}

}
