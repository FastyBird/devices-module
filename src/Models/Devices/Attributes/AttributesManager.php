<?php declare(strict_types = 1);

/**
 * AttributeManager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.57.0
 *
 * @date           22.04.22
 */

namespace FastyBird\DevicesModule\Models\Devices\Attributes;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Models;
use IPub\DoctrineCrud\Crud;
use Nette;
use Nette\Utils;

/**
 * Devices attributes entities manager
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class AttributesManager implements IAttributesManager
{

	use Nette\SmartObject;

	/**
	 * @var Crud\IEntityCrud<Entities\Devices\Attributes\IAttribute>
	 */
	private Crud\IEntityCrud $entityCrud;

	/**
	 * @param Crud\IEntityCrud<Entities\Devices\Attributes\IAttribute> $entityCrud
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
	): Entities\Devices\Attributes\IAttribute {
		/** @var Entities\Devices\Attributes\IAttribute $entity */
		$entity = $this->entityCrud->getEntityCreator()->create($values);

		return $entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function update(
		Entities\Devices\Attributes\IAttribute $entity,
		Utils\ArrayHash $values
	): Entities\Devices\Attributes\IAttribute {
		/** @var Entities\Devices\Attributes\IAttribute $entity */
		$entity = $this->entityCrud->getEntityUpdater()->update($values, $entity);

		return $entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(
		Entities\Devices\Attributes\IAttribute $entity
	): bool {
		// Delete entity from database
		return $this->entityCrud->getEntityDeleter()->delete($entity);
	}

}
