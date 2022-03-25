<?php declare(strict_types = 1);

/**
 * BlankDevice.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.9.0
 *
 * @date           07.01.22
 */

namespace FastyBird\DevicesModule\Entities\Devices;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Entities;

/**
 * @ORM\Entity
 */
class BlankDevice extends Entities\Devices\Device implements IBlankDevice
{

	public const DEVICE_TYPE = 'blank';

	/**
	 * {@inheritDoc}
	 */
	public function getType(): string
	{
		return self::DEVICE_TYPE;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDiscriminatorName(): string
	{
		return self::DEVICE_TYPE;
	}

}
