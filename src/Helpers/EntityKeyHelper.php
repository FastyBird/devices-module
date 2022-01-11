<?php declare(strict_types = 1);

/**
 * EntityKeyHelper.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Helpers
 * @since          0.1.0
 *
 * @date           05.02.21
 */

namespace FastyBird\DevicesModule\Helpers;

use Closure;
use FastyBird\DateTimeFactory;
use FastyBird\DevicesModule\Entities\IEntity;

/**
 * Translates a number to a short alphanumeric version
 *
 * Translated any number up to 9007199254740992
 * to a shorter version in letters e.g.:
 * 9007199254740989 --> PpQXn7COf
 *
 * specifying the second argument true, it will
 * translate back e.g.:
 * PpQXn7COf --> 9007199254740989
 *
 * this function is based on any2dec && dec2any by
 * fragmer[at]mail[dot]ru
 * see: https://nl3.php.net/manual/en/function.base-convert.php#52450
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Helpers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class EntityKeyHelper
{

	private const INDEX = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	/** @var int */
	private int $maxLen = 6;
	/** @var Closure|null */
	private ?Closure $customCallback = null;
	/** @var DateTimeFactory\DateTimeFactory */
	private DateTimeFactory\DateTimeFactory $dateTimeFactory;

	public function __construct(
		DateTimeFactory\DateTimeFactory $dateTimeFactory
	) {
		$this->dateTimeFactory = $dateTimeFactory;
	}

	public function setCustomGenerator(Closure $callback): void
	{
		$this->customCallback = $callback;
	}

	/**
	 * @param IEntity $entity
	 *
	 * @return string
	 */
	public function generate(IEntity $entity): string
	{
		if (is_callable($this->customCallback)) {
			return call_user_func($this->customCallback, $entity);
		}

		return $this->alphaIdToHash($this->dateTimeFactory->getNow()->getTimestamp());
	}

	/**
	 * @param int $id
	 * @param int|null $padUp
	 * @param string|null $passKey
	 *
	 * @return string
	 */
	public function alphaIdToHash(int $id, ?int $padUp = null, ?string $passKey = null): string
	{
		$result = '';
		$index = self::INDEX;

		$base = strlen(self::INDEX);

		if ($passKey !== null) {
			$index = $this->hashIndex($passKey);
		}

		// Digital number  -->>  alphabet letter code
		if ($padUp !== null) {
			$padUp--;

			if ($padUp > 0) {
				$id += pow($base, $padUp);
			}
		}

		for ($t = ($id !== 0 ? floor(log($id, $base)) : 0); $t >= 0; $t--) {
			$bcp = bcpow((string) $base, (string) $t);

			$a = floor($id / $bcp) % $base;

			$result .= substr($index, $a, 1);

			$id -= ($a * $bcp);
		}

		return $result;
	}

	/**
	 * @param string $passKey
	 *
	 * @return string
	 */
	private function hashIndex(string $passKey): string
	{
		// Although this function's purpose is to just make the
		// ID short - and not so much secure,
		// with this patch by Simon Franz (https://blog.snaky.org/)
		// you can optionally supply a password to make it harder
		// to calculate the corresponding numeric ID

		$i = [];
		$p = [];

		for ($n = 0; $n < strlen(self::INDEX); $n++) {
			$i[] = substr(self::INDEX, $n, 1);
		}

		$pass_hash = hash('sha256', $passKey);
		$pass_hash = (strlen($pass_hash) < strlen(self::INDEX) ? hash('sha512', $passKey) : $pass_hash);

		for ($n = 0; $n < strlen(self::INDEX); $n++) {
			$p[] = substr($pass_hash, $n, 1);
		}

		array_multisort($p, SORT_DESC, $i);

		return implode($i);
	}

	/**
	 * @param string $hash
	 * @param int|null $padUp
	 * @param string|null $passKey
	 *
	 * @return int
	 */
	public function alphaIdToNum(string $hash, ?int $padUp = null, ?string $passKey = null): int
	{
		$result = 0;
		$index = self::INDEX;

		$base = strlen(self::INDEX);

		if ($passKey !== null) {
			$index = $this->hashIndex($passKey);
		}

		// Digital number  <<--  alphabet letter code
		$len = strlen($hash) - 1;

		for ($t = $len; $t >= 0; $t--) {
			$bcp = bcpow((string) $base, (string) ($len - $t));

			$result += strpos($index, substr($hash, $t, 1)) * $bcp;
		}

		if ($padUp !== null) {
			$padUp--;

			if ($padUp > 0) {
				$result -= pow($base, $padUp);
			}
		}

		return (int) $result;
	}

}
