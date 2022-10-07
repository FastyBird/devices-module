<?php declare(strict_types = 1);

/**
 * Property.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.31.0
 *
 * @date           08.02.22
 */

namespace FastyBird\DevicesModule\Entities\Connectors\Properties;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_devices_module_connectors_properties",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Connectors properties"
 *     },
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint(name="property_identifier_unique", columns={"property_identifier", "connector_id"})
 *     },
 *     indexes={
 *       @ORM\Index(name="property_identifier_idx", columns={"property_identifier"}),
 *       @ORM\Index(name="property_settable_idx", columns={"property_settable"}),
 *       @ORM\Index(name="property_queryable_idx", columns={"property_queryable"})
 *     }
 * )
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="property_type", type="string", length=40)
 * @ORM\DiscriminatorMap({
 *    "variable" = "FastyBird\DevicesModule\Entities\Connectors\Properties\Variable",
 *    "dynamic"  = "FastyBird\DevicesModule\Entities\Connectors\Properties\Dynamic"
 * })
 * @ORM\MappedSuperclass
 */
abstract class Property extends Entities\Property
{

	/**
	 * @var Entities\Connectors\Connector
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\ManyToOne(targetEntity="FastyBird\DevicesModule\Entities\Connectors\Connector", inversedBy="properties")
	 * @ORM\JoinColumn(name="connector_id", referencedColumnName="connector_id", onDelete="CASCADE", nullable=false)
	 */
	protected Entities\Connectors\Connector $connector;

	/**
	 * @param Entities\Connectors\Connector $connector
	 * @param string $identifier
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		Entities\Connectors\Connector $connector,
		string $identifier,
		?Uuid\UuidInterface $id = null
	) {
		parent::__construct($identifier, $id);

		$this->connector = $connector;

		$connector->addProperty($this);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'connector' => $this->getConnector()->getPlainId(),

			'owner' => $this->getConnector()->getOwnerId(),
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getConnector(): Entities\Connectors\Connector
	{
		return $this->connector;
	}

}
