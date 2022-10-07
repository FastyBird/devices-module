<?php declare(strict_types = 1);

/**
 * Entity.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           25.05.20
 */

namespace FastyBird\DevicesModule\Entities;

use FastyBird\Metadata\Types as MetadataTypes;
use IPub\DoctrineCrud;
use Ramsey\Uuid;

/**
 * Base entity interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface Entity extends DoctrineCrud\Entities\IEntity
{

	/**
	 * @return Uuid\UuidInterface
	 */
	public function getId(): Uuid\UuidInterface;

	/**
	 * @return string
	 */
	public function getPlainId(): string;

	/**
	 * @return Array<string, mixed>
	 */
	public function toArray(): array;

	/**
	 * @return MetadataTypes\ModuleSourceType|MetadataTypes\ConnectorSourceType|MetadataTypes\PluginSourceType
	 */
	public function getSource(): MetadataTypes\ModuleSourceType|MetadataTypes\PluginSourceType|MetadataTypes\ConnectorSourceType;

}
