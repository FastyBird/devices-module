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
 * @date           15.11.23
 */

namespace FastyBird\Module\Devices\Models\Configuration\Connectors\Controls;

use FastyBird\Library\Metadata\Documents as MetadataDocuments;
use FastyBird\Module\Devices\Caching;
use FastyBird\Module\Devices\Documents;
use FastyBird\Module\Devices\Exceptions;
use FastyBird\Module\Devices\Models;
use FastyBird\Module\Devices\Queries;
use FastyBird\Module\Devices\Types;
use Nette\Caching as NetteCaching;
use Ramsey\Uuid;
use Throwable;
use function array_map;
use function array_merge;
use function is_array;

/**
 * Connectors controls configuration repository
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Repository extends Models\Configuration\Repository
{

	public function __construct(
		private readonly Models\Configuration\Builder $builder,
		private readonly Caching\Container $moduleCaching,
		private readonly MetadataDocuments\DocumentFactory $documentFactory,
	)
	{
	}

	/**
	 * @throws Exceptions\InvalidState
	 */
	public function find(
		Uuid\UuidInterface $id,
	): Documents\Connectors\Controls\Control|null
	{
		$queryObject = new Queries\Configuration\FindConnectorControls();
		$queryObject->byId($id);

		return $this->findOneBy($queryObject);
	}

	/**
	 * @param Queries\Configuration\FindConnectorControls<Documents\Connectors\Controls\Control> $queryObject
	 *
	 * @throws Exceptions\InvalidState
	 */
	public function findOneBy(
		Queries\Configuration\FindConnectorControls $queryObject,
	): Documents\Connectors\Controls\Control|null
	{
		try {
			/** @phpstan-var Documents\Connectors\Controls\Control|false $document */
			$document = $this->moduleCaching->getConfigurationRepositoryCache()->load(
				$this->createKeyOne($queryObject),
				function (&$dependencies) use ($queryObject): Documents\Connectors\Controls\Control|false {
					$space = $this->builder
						->load(Types\ConfigurationType::CONNECTORS_CONTROLS);

					$result = $queryObject->fetch($space);

					if (!is_array($result) || $result === []) {
						return false;
					}

					$document = $this->documentFactory->create(
						Documents\Connectors\Controls\Control::class,
						$result[0],
					);

					$dependencies = [
						NetteCaching\Cache::Tags => [
							Types\ConfigurationType::CONNECTORS_CONTROLS->value,
							$document->getId()->toString(),
						],
					];

					return $document;
				},
				[
					NetteCaching\Cache::Tags => [
						Types\ConfigurationType::CONNECTORS_CONTROLS->value,
					],
				],
			);
		} catch (Throwable $ex) {
			throw new Exceptions\InvalidState('Could not load document', $ex->getCode(), $ex);
		}

		if ($document === false) {
			return null;
		}

		return $document;
	}

	/**
	 * @param Queries\Configuration\FindConnectorControls<Documents\Connectors\Controls\Control> $queryObject
	 *
	 * @return array<Documents\Connectors\Controls\Control>
	 *
	 * @throws Exceptions\InvalidState
	 */
	public function findAllBy(
		Queries\Configuration\FindConnectorControls $queryObject,
	): array
	{
		try {
			/** @phpstan-var array<Documents\Connectors\Controls\Control> $documents */
			$documents = $this->moduleCaching->getConfigurationRepositoryCache()->load(
				$this->createKeyAll($queryObject),
				function (&$dependencies) use ($queryObject): array {
					$space = $this->builder
						->load(Types\ConfigurationType::CONNECTORS_CONTROLS);

					$result = $queryObject->fetch($space);

					if (!is_array($result)) {
						return [];
					}

					$documents = array_map(
						fn (array $item): Documents\Connectors\Controls\Control => $this->documentFactory->create(
							Documents\Connectors\Controls\Control::class,
							$item,
						),
						$result,
					);

					$dependencies = [
						NetteCaching\Cache::Tags => array_merge(
							[
								Types\ConfigurationType::CONNECTORS_CONTROLS->value,
							],
							array_map(
								static fn (Documents\Connectors\Controls\Control $document): string => $document->getId()->toString(),
								$documents,
							),
						),
					];

					return $documents;
				},
				[
					NetteCaching\Cache::Tags => [
						Types\ConfigurationType::CONNECTORS_CONTROLS->value,
					],
				],
			);
		} catch (Throwable $ex) {
			throw new Exceptions\InvalidState('Could not load documents', $ex->getCode(), $ex);
		}

		return $documents;
	}

}
