<?php declare(strict_types = 1);

/**
 * TChannel.php
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

namespace FastyBird\Module\Devices\Controllers\Finders;

use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use FastyBird\Library\Application\Exceptions as ApplicationExceptions;
use FastyBird\Module\Devices\Entities;
use FastyBird\Module\Devices\Models;
use FastyBird\Module\Devices\Queries;
use Fig\Http\Message\StatusCodeInterface;
use Nette\Localization;
use Ramsey\Uuid;

/**
 * @property-read Localization\Translator $translator
 * @property-read Models\Entities\Channels\ChannelsRepository $channelsRepository
 */
trait TChannel
{

	/**
	 * @throws ApplicationExceptions\InvalidState
	 * @throws JsonApiExceptions\JsonApi
	 */
	protected function findChannel(
		string $id,
		Entities\Devices\Device|null $device = null,
	): Entities\Channels\Channel
	{
		try {
			if ($device !== null) {
				$findQuery = new Queries\Entities\FindChannels();
				$findQuery->forDevice($device);
				$findQuery->byId(Uuid\Uuid::fromString($id));

				$channel = $this->channelsRepository->findOneBy($findQuery);

			} else {
				$channel = $this->channelsRepository->find(Uuid\Uuid::fromString($id));
			}

			if ($channel === null) {
				throw new JsonApiExceptions\JsonApiError(
					StatusCodeInterface::STATUS_NOT_FOUND,
					$this->translator->translate('//devices-module.base.messages.notFound.heading'),
					$this->translator->translate('//devices-module.base.messages.notFound.message'),
				);
			}
		} catch (Uuid\Exception\InvalidUuidStringException) {
			throw new JsonApiExceptions\JsonApiError(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//devices-module.base.messages.notFound.heading'),
				$this->translator->translate('//devices-module.base.messages.notFound.message'),
			);
		}

		return $channel;
	}

}
