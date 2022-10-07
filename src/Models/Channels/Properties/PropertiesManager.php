<?php declare(strict_types = 1);

/**
 * PropertiesManager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           02.11.18
 */

namespace FastyBird\DevicesModule\Models\Channels\Properties;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Models;
use IPub\DoctrineCrud\Crud;
use Nette;
use Nette\Utils;

/**
 * Channels properties entities manager
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class PropertiesManager
{

	use Nette\SmartObject;

	/** @var Crud\IEntityCrud<Entities\Channels\Properties\Property> */
	private Crud\IEntityCrud $entityCrud;

	/**
	 * @param Crud\IEntityCrud<Entities\Channels\Properties\Property> $entityCrud
	 */
	public function __construct(
		Crud\IEntityCrud $entityCrud
	) {
		// Entity CRUD for handling entities
		$this->entityCrud = $entityCrud;
	}

	/**
	 * {@inheritDoc}
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Channels\Properties\Property {
		/** @var Entities\Channels\Properties\Property $entity */
		$entity = $this->entityCrud->getEntityCreator()->create($values);

		return $entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function update(
		Entities\Channels\Properties\Property $entity,
		Utils\ArrayHash $values
	): Entities\Channels\Properties\Property {
		/** @var Entities\Channels\Properties\Property $entity */
		$entity = $this->entityCrud->getEntityUpdater()->update($values, $entity);

		return $entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(
		Entities\Channels\Properties\Property $entity
	): bool {
		// Delete entity from database
		return $this->entityCrud->getEntityDeleter()->delete($entity);
	}

}
