<?php declare(strict_types = 1);

/**
 * IConnector.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Connectors
 * @since          0.60.0
 *
 * @date           31.05.22
 */

namespace FastyBird\DevicesModule\Connectors;

/**
 * Devices connector interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Connectors
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IConnector
{

	/**
	 * @return void
	 */
	public function execute(): void;

	/**
	 * @return void
	 */
	public function terminate(): void;

	/**
	 * @return bool
	 */
	public function hasUnfinishedTasks(): bool;

}
