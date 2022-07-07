<?php declare(strict_types = 1);

/**
 * ChannelPropertiesRepository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.9.0
 *
 * @date           09.01.22
 */

namespace FastyBird\DevicesModule\Models\States;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\States;
use FastyBird\Metadata\Entities as MetadataEntities;
use Nette;
use Ramsey\Uuid;

/**
 * Channel property repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ChannelPropertiesRepository
{

	use Nette\SmartObject;

	/** @var IChannelPropertiesRepository|null */
	private ?IChannelPropertiesRepository $repository;

	public function __construct(
		?IChannelPropertiesRepository $repository
	) {
		$this->repository = $repository;
	}

	/**
	 * @param Entities\Channels\Properties\IProperty|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity $property
	 *
	 * @return States\IChannelProperty|null
	 */
	public function findOne(
		MetadataEntities\Modules\DevicesModule\IChannelMappedPropertyEntity|MetadataEntities\Modules\DevicesModule\IChannelDynamicPropertyEntity|Entities\Channels\Properties\IProperty $property
	): ?States\IChannelProperty {
		if ($this->repository === null) {
			throw new Exceptions\NotImplementedException('Channel properties state repository is not registered');
		}

		if ($property->getParent() !== null) {
			if ($property->getParent() instanceof Entities\Channels\Properties\IProperty) {
				return $this->repository->findOne($property->getParent());

			} else {
				return $this->repository->findOneById($property->getParent());
			}
		}

		return $this->repository->findOne($property);
	}

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return States\IChannelProperty|null
	 */
	public function findOneById(
		Uuid\UuidInterface $id
	): ?States\IChannelProperty {
		if ($this->repository === null) {
			throw new Exceptions\NotImplementedException('Channel properties state repository is not registered');
		}

		return $this->repository->findOneById($id);
	}

}
