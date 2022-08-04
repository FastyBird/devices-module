<?php declare(strict_types = 1);

/**
 * DevicesManager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           28.07.18
 */

namespace FastyBird\DevicesModule\Models\Devices;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Models;
use IPub\DoctrineCrud\Crud;
use Nette;
use Nette\Utils;

/**
 * Device entities manager
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class DevicesManager implements IDevicesManager
{

	use Nette\SmartObject;

	/**
	 * @var Crud\IEntityCrud<Entities\Devices\IDevice>
	 */
	private Crud\IEntityCrud $entityCrud;

	/**
	 * @param Crud\IEntityCrud<Entities\Devices\IDevice> $entityCrud
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
	): Entities\Devices\IDevice {
		/** @var Entities\Devices\IDevice $entity */
		$entity = $this->entityCrud->getEntityCreator()->create($values);

		return $entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function update(
		Entities\Devices\IDevice $entity,
		Utils\ArrayHash $values
	): Entities\Devices\IDevice {
		/** @var Entities\Devices\IDevice $entity */
		$entity = $this->entityCrud->getEntityUpdater()->update($values, $entity);

		return $entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(
		Entities\Devices\IDevice $entity
	): bool {
		// Delete entity from database
		return $this->entityCrud->getEntityDeleter()->delete($entity);
	}

}
