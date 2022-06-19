<?php declare(strict_types = 1);

/**
 * ExchangeMessage.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Connectors
 * @since          0.62.0
 *
 * @date           15.06.22
 */

namespace FastyBird\DevicesModule\Connectors\Messages;

use FastyBird\Metadata\Entities as MetadataEntities;
use FastyBird\Metadata\Types as MetadataTypes;
use Nette;

/**
 * Message from exchange
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Connectors
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ExchangeMessage implements IMessage
{

	use Nette\SmartObject;

	/** @var MetadataTypes\RoutingKeyType */
	private MetadataTypes\RoutingKeyType $routingKey;

	/** @var MetadataEntities\IEntity */
	private MetadataEntities\IEntity $entity;

	public function __construct(
		MetadataTypes\RoutingKeyType $routingKey,
		MetadataEntities\IEntity $entity
	) {
		$this->routingKey = $routingKey;
		$this->entity = $entity;
	}

	/**
	 * @return MetadataTypes\RoutingKeyType
	 */
	public function getRoutingKey(): MetadataTypes\RoutingKeyType
	{
		return $this->routingKey;
	}

	/**
	 * @return MetadataEntities\IEntity
	 */
	public function getEntity(): MetadataEntities\IEntity
	{
		return $this->entity;
	}

}
