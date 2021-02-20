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
		$datatype = Types\DataTypeType::get(Types\DataTypeType::DATA_TYPE_INT);

		Assert::type(Types\DataTypeType::class, $datatype);

		$datatype = Types\DataTypeType::get(Types\DataTypeType::DATA_TYPE_FLOAT);

		Assert::type(Types\DataTypeType::class, $datatype);

		$datatype = Types\DataTypeType::get(Types\DataTypeType::DATA_TYPE_BOOLEAN);

		Assert::type(Types\DataTypeType::class, $datatype);

		$datatype = Types\DataTypeType::get(Types\DataTypeType::DATA_TYPE_STRING);

		Assert::type(Types\DataTypeType::class, $datatype);

		$datatype = Types\DataTypeType::get(Types\DataTypeType::DATA_TYPE_ENUM);

		Assert::type(Types\DataTypeType::class, $datatype);

		$datatype = Types\DataTypeType::get(Types\DataTypeType::DATA_TYPE_COLOR);

		Assert::type(Types\DataTypeType::class, $datatype);
	}

	/**
	 * @throws Consistence\Enum\InvalidEnumValueException
	 */
	public function testInvalidDatatype(): void
	{
		$datatype = Types\DataTypeType::get('invalidtype');
	}

}

$test_case = new DatatypeTypeTest();
$test_case->run();
