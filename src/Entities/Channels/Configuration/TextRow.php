<?php declare(strict_types = 1);

/**
 * TextRow.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           26.10.18
 */

namespace FastyBird\DevicesModule\Entities\Channels\Configuration;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule;

/**
 * @ORM\Entity
 */
class TextRow extends Row implements ITextRow
{

	/** @var string */
	protected string $type = DevicesModule\Constants::DATA_TYPE_TEXT;

	/**
	 * {@inheritDoc}
	 */
	public function getValue(): ?string
	{
		if ($this->value === null) {
			return null;
		}

		return (string) $this->value;
	}

}
