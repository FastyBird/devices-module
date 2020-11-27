<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\DevicesModule\Types;
use Ninjify\Nunjuck\TestCase\BaseTestCase;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class DatatypeTypeTest extends BaseTestCase
{

	public function testCreateDatatype(): void
	{
		$datatype = Types\DatatypeType::get(Types\DatatypeType::DATA_TYPE_INTEGER);

		Assert::type(Types\DatatypeType::class, $datatype);

		$datatype = Types\DatatypeType::get(Types\DatatypeType::DATA_TYPE_FLOAT);

		Assert::type(Types\DatatypeType::class, $datatype);

		$datatype = Types\DatatypeType::get(Types\DatatypeType::DATA_TYPE_BOOLEAN);

		Assert::type(Types\DatatypeType::class, $datatype);

		$datatype = Types\DatatypeType::get(Types\DatatypeType::DATA_TYPE_STRING);

		Assert::type(Types\DatatypeType::class, $datatype);

		$datatype = Types\DatatypeType::get(Types\DatatypeType::DATA_TYPE_ENUM);

		Assert::type(Types\DatatypeType::class, $datatype);

		$datatype = Types\DatatypeType::get(Types\DatatypeType::DATA_TYPE_COLOR);

		Assert::type(Types\DatatypeType::class, $datatype);
	}

	/**
	 * @throws Consistence\Enum\InvalidEnumValueException
	 */
	public function testInvalidDatatype(): void
	{
		$datatype = Types\DatatypeType::get('invalidtype');
	}

}

$test_case = new DatatypeTypeTest();
$test_case->run();
