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
use FastyBird\Metadata\Exceptions as MetadataExceptions;
use IteratorAggregate;
use Nette;
use Ramsey\Uuid;
use RecursiveArrayIterator;

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

	/** @var Array<string, Array<string, mixed>> */
	private array $controls;

	private MetadataEntities\Modules\DevicesModule\ConnectorControlEntityFactory $entityFactory;

	public function __construct(
		MetadataEntities\Modules\DevicesModule\ConnectorControlEntityFactory $entityFactory
	) {
		$this->entityFactory = $entityFactory;

		$this->controls = [];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findById(Uuid\UuidInterface $id): ?MetadataEntities\Modules\DevicesModule\IConnectorControlEntity
	{
		if (array_key_exists($id->toString(), $this->controls)) {
			return $this->entityFactory->create($this->controls[$id->toString()]);
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function findAllByConnector(Uuid\UuidInterface $connector): array
	{
		$controls = [];

		foreach ($this->controls as $control) {
			if (array_key_exists('connector', $control) && $connector->toString() === $control['connector']) {
				$controls[] = $this->entityFactory->create($control);
			}
		}

		return $controls;
	}

	/**
	 * {@inheritDoc}
	 */
	public function append(Uuid\UuidInterface $id, array $entity): void
	{
		$this->controls[$id->toString()] = $entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset(): void
	{
		$this->controls = [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return count($this->controls);
	}

	/**
	 * @return RecursiveArrayIterator<int, MetadataEntities\Modules\DevicesModule\IConnectorControlEntity>
	 *
	 * @throws MetadataExceptions\FileNotFoundException
	 */
	public function getIterator(): RecursiveArrayIterator
	{
		$controls = [];

		foreach ($this->controls as $control) {
			$controls[] = $this->entityFactory->create($control);
		}

		return new RecursiveArrayIterator($controls);
	}

}
