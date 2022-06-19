<?php declare(strict_types = 1);

/**
 * ConnectorControlsRepository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DataStorage!
 * @subpackage     Models
 * @since          0.62.0
 *
 * @date           17.06.22
 */

namespace FastyBird\DevicesModule\Models\DataStorage;

use Countable;
use FastyBird\Metadata\Entities as MetadataEntities;
use IteratorAggregate;
use Nette;
use Ramsey\Uuid;
use RecursiveArrayIterator;
use SplObjectStorage;

/**
 * Data storage connector controls repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @implements IteratorAggregate<int, MetadataEntities\Modules\DevicesModule\IConnectorControlEntity>
 */
final class ConnectorControlsRepository implements IConnectorControlsRepository, Countable, IteratorAggregate
{

	use Nette\SmartObject;

	/** @var SplObjectStorage<MetadataEntities\Modules\DevicesModule\IConnectorControlEntity, string> */
	private SplObjectStorage $controls;

	public function __construct()
	{
		$this->controls = new SplObjectStorage();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IConnectorControlEntity
	{
		$this->controls->rewind();

		foreach ($this->controls as $control) {
			if ($control->getId()->equals($id)) {
				return $control;
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findAllByConnector(Uuid\UuidInterface $connector): array
	{
		$controls = [];

		$this->controls->rewind();

		foreach ($this->controls as $control) {
			if ($control->getConnector()->equals($connector)) {
				$controls[] = $control;
			}
		}

		return $controls;
	}

	/**
	 * {@inheritDoc}
	 */
	public function append(MetadataEntities\Modules\DevicesModule\IConnectorControlEntity $entity): void
	{
		$existing = $this->findById($entity->getId());

		if ($existing !== null) {
			$this->controls->detach($existing);
		}

		if (!$this->controls->contains($entity)) {
			$this->controls->attach($entity, $entity->getId()->toString());
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset(): void
	{
		$this->controls = new SplObjectStorage();
	}

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return $this->controls->count();
	}

	/**
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IConnectorControlEntity>
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$controls = [];

		foreach ($this->controls as $control) {
			$controls[] = $control;
		}

		return new RecursiveArrayIterator($controls);
	}

}
