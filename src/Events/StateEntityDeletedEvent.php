<?php declare(strict_types = 1);

/**
 * StateEntityDeletedEvent.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Events
 * @since          0.65.0
 *
 * @date           22.06.22
 */

namespace FastyBird\DevicesModule\Events;

use FastyBird\DevicesModule\Entities;
use FastyBird\Metadata\Entities as MetadataEntities;
use Symfony\Contracts\EventDispatcher;

/**
 * State entity was deleted event
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Events
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class StateEntityDeletedEvent extends EventDispatcher\Event
{

	private MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity|Entities\IProperty $property;

	public function __construct(
		MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity|Entities\IProperty $property
	) {
		$this->property = $property;
	}

	public function getProperty(): MetadataEntities\Modules\DevicesModule\IDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IStaticPropertyEntity|MetadataEntities\Modules\DevicesModule\IMappedPropertyEntity|Entities\IProperty
	{
		return $this->property;
	}

}
