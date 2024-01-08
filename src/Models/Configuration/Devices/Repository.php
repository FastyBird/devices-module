<?php declare(strict_types = 1);

/**
 * Repository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          1.0.0
 *
 * @date           14.11.23
 */

namespace FastyBird\Module\Devices\Models\Configuration\Devices;

use Contributte\Cache;
use FastyBird\Library\Metadata\Documents as MetadataDocuments;
use FastyBird\Module\Devices;
use FastyBird\Module\Devices\Exceptions;
use FastyBird\Module\Devices\Models;
use FastyBird\Module\Devices\Queries;
use Ramsey\Uuid;
use stdClass;
use Throwable;
use function array_map;
use function is_array;
use function md5;

/**
 * Devices configuration repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Repository extends Models\Configuration\Repository
{

	public function __construct(
		Models\Configuration\Builder $builder,
		Cache\CacheFactory $cacheFactory,
		private readonly MetadataDocuments\DocumentFactory $entityFactory,
	)
	{
		parent::__construct($builder, $cacheFactory);
	}

	/**
	 * @template T of MetadataDocuments\DevicesModule\Device
	 *
	 * @param class-string<T> $type
	 *
	 * @return T|null
	 *
	 * @throws Exceptions\InvalidState
	 */
	public function find(
		Uuid\UuidInterface $id,
		string $type = MetadataDocuments\DevicesModule\Device::class,
	): MetadataDocuments\DevicesModule\Device|null
	{
		$queryObject = new Queries\Configuration\FindDevices();
		$queryObject->byId($id);

		$document = $this->findOneBy($queryObject, $type);

		if ($document !== null && !$document instanceof $type) {
			throw new Exceptions\InvalidState('Could not load document');
		}

		return $document;
	}

	/**
	 * @template T of MetadataDocuments\DevicesModule\Device
	 *
	 * @param Queries\Configuration\FindDevices<T> $queryObject
	 * @param class-string<T> $type
	 *
	 * @return T|null
	 *
	 * @throws Exceptions\InvalidState
	 */
	public function findOneBy(
		Queries\Configuration\FindDevices $queryObject,
		string $type = MetadataDocuments\DevicesModule\Device::class,
	): MetadataDocuments\DevicesModule\Device|null
	{
		try {
			$document = $this->cache->load(
				$this->createKeyOne($queryObject) . '_' . md5($type),
				function () use ($queryObject, $type): MetadataDocuments\DevicesModule\Device|false {
					$space = $this->builder
						->load()
						->find('.' . Devices\Constants::DATA_STORAGE_DEVICES_KEY . '.*');

					$result = $queryObject->fetch($space);

					if (!is_array($result) || $result === []) {
						return false;
					}

					return $this->entityFactory->create($type, $result[0]);
				},
			);
		} catch (Throwable $ex) {
			throw new Exceptions\InvalidState('Could not load document', $ex->getCode(), $ex);
		}

		if ($document === false) {
			return null;
		}

		if (!$document instanceof $type) {
			throw new Exceptions\InvalidState('Could not load document');
		}

		return $document;
	}

	/**
	 * @template T of MetadataDocuments\DevicesModule\Device
	 *
	 * @param Queries\Configuration\FindDevices<T> $queryObject
	 * @param class-string<T> $type
	 *
	 * @return array<T>
	 *
	 * @throws Exceptions\InvalidState
	 */
	public function findAllBy(
		Queries\Configuration\FindDevices $queryObject,
		string $type = MetadataDocuments\DevicesModule\Device::class,
	): array
	{
		try {
			$documents = $this->cache->load(
				$this->createKeyAll($queryObject) . '_' . md5($type),
				function () use ($queryObject, $type): array {
					$space = $this->builder
						->load()
						->find('.' . Devices\Constants::DATA_STORAGE_DEVICES_KEY . '.*');

					$result = $queryObject->fetch($space);

					if (!is_array($result)) {
						return [];
					}

					return array_map(
						fn (stdClass $item): MetadataDocuments\DevicesModule\Device => $this->entityFactory->create(
							$type,
							$item,
						),
						$result,
					);
				},
			);
		} catch (Throwable $ex) {
			throw new Exceptions\InvalidState('Could not load documents', $ex->getCode(), $ex);
		}

		if (!is_array($documents)) {
			throw new Exceptions\InvalidState('Could not load documents');
		}

		return $documents;
	}

}
