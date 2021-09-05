<?php declare(strict_types = 1);

/**
 * TKey.php
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

/**
 * @property-read string|null $key
 */
trait TKey
{

	public function setKey(string $key): void
	{
		$this->key = $key;
	}

	public function getKey(): ?string
	{
		return $this->key;
	}

}
