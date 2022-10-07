<?php declare(strict_types = 1);

/**
 * PropertiesManager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.31.0
 *
 * @date           08.02.22
 */

namespace FastyBird\DevicesModule\Models\Connectors\Properties;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Models;
use IPub\DoctrineCrud\Crud;
use Nette;
use Nette\Utils;
use function assert;

/**
 * Connectors properties entities manager
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class PropertiesManager
{

	use Nette\SmartObject;

	/**
	 * @param Crud\IEntityCrud<Entities\Connectors\Properties\Property> $entityCrud
	 */
	public function __construct(private Crud\IEntityCrud $entityCrud)
	{
		// Entity CRUD for handling entities
	}

	public function create(
		Utils\ArrayHash $values,
	): Entities\Connectors\Properties\Property
	{
		$entity = $this->entityCrud->getEntityCreator()->create($values);
		assert($entity instanceof Entities\Connectors\Properties\Property);

		return $entity;
	}

	public function update(
		Entities\Connectors\Properties\Property $entity,
		Utils\ArrayHash $values,
	): Entities\Connectors\Properties\Property
	{
		$entity = $this->entityCrud->getEntityUpdater()->update($values, $entity);
		assert($entity instanceof Entities\Connectors\Properties\Property);

		return $entity;
	}

	public function delete(
		Entities\Connectors\Properties\Property $entity,
	): bool
	{
		// Delete entity from database
		return $this->entityCrud->getEntityDeleter()->delete($entity);
	}

}
