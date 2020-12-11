<?php declare(strict_types = 1);

/**
 * ICredentialsManager.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           10.12.20
 */

namespace FastyBird\DevicesModule\Models\Devices\Credentials;

use FastyBird\DevicesModule\Entities;
use Nette\Utils;

/**
 * Device credentials entities manager interface
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface ICredentialsManager
{

	/**
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Devices\Credentials\ICredentials
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Devices\Credentials\ICredentials;

	/**
	 * @param Entities\Devices\Credentials\ICredentials $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Devices\Credentials\ICredentials
	 */
	public function update(
		Entities\Devices\Credentials\ICredentials $entity,
		Utils\ArrayHash $values
	): Entities\Devices\Credentials\ICredentials;

}
