<?php declare(strict_types = 1);

/**
 * FindChannelControls.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Queries
 * @since          1.0.0
 *
 * @date           15.11.23
 */

namespace FastyBird\Module\Devices\Queries\Configuration;

use FastyBird\Module\Devices\Documents;
use FastyBird\Module\Devices\Exceptions;
use Flow\JSONPath;
use Nette\Utils;
use Ramsey\Uuid;
use function serialize;

/**
 * Find channels controls configuration query
 *
 * @template T of Documents\Channels\Controls\Control
 * @extends  QueryObject<T>
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Queries
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class FindChannelControls extends QueryObject
{

	/** @var array<string> */
	private array $filter = [];

	public function __construct()
	{
		$this->filter[] = '.[?(@.channel != "")]';
	}

	public function byId(Uuid\UuidInterface $id): void
	{
		$this->filter[] = '.[?(@.id =~ /(?i).*^' . $id->toString() . '*$/)]';
	}

	public function byName(string $name): void
	{
		$this->filter[] = '.[?(@.name =~ /(?i).*^' . $name . '*$/)]';
	}

	public function forChannel(Documents\Channels\Channel $channel): void
	{
		$this->filter[] = '.[?(@.channel =~ /(?i).*^' . $channel->getId()->toString() . '*$/)]';
	}

	public function byChannelId(Uuid\UuidInterface $channelId): void
	{
		$this->filter[] = '.[?(@.channel =~ /(?i).*^' . $channelId->toString() . '*$/)]';
	}

	/**
	 * @throws JSONPath\JSONPathException
	 */
	protected function doCreateQuery(JSONPath\JSONPath $repository): JSONPath\JSONPath
	{
		$filtered = $repository;

		foreach ($this->filter as $filter) {
			$filtered = $filtered->find($filter);
		}

		return $filtered;
	}

	/**
	 * @throws Exceptions\InvalidState
	 */
	public function toString(): string
	{
		try {
			return serialize(Utils\Json::encode($this->filter));
		} catch (Utils\JsonException) {
			throw new Exceptions\InvalidState('Cache key could not be generated');
		}
	}

}
