<?php declare(strict_types = 1);

/**
 * QueryObject.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Queries
 * @since          1.0.0
 *
 * @date           13.11.23
 */

namespace FastyBird\Module\Devices\Queries\Configuration;

use FastyBird\Library\Metadata\Documents as MetadataDocuments;
use Flow\JSONPath;

/**
 * Configuration query object
 *
 * @template T of MetadataDocuments\Document
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Queries
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
abstract class QueryObject
{

	public function fetch(JSONPath\JSONPath $repository): mixed
	{
		return $this->doCreateQuery($repository)->getData();
	}

	abstract protected function doCreateQuery(JSONPath\JSONPath $repository): JSONPath\JSONPath;

}
