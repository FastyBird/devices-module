<?php declare(strict_types = 1);

/**
 * IKey.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           05.02.21
 */

namespace FastyBird\DevicesModule\Entities;

interface IKey
{

	public function setKey(string $key): void;

	public function getKey(): ?string;

}
