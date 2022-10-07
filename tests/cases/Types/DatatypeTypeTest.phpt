<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\Metadata\Types as MetadataTypes;
use Ninjify\Nunjuck\TestCase\BaseTestCase;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class DatatypeTypeTest extends BaseTestCase
{

	public function testCreateDatatype(): void
	{
		$datatype = MetadataTypes\DataTypeType::get(MetadataTypes\DataTypeType::DATA_TYPE_INT);

		Assert::type(MetadataTypes\DataTypeType::class, $datatype);

		$datatype = MetadataTypes\DataTypeType::get(MetadataTypes\DataTypeType::DATA_TYPE_FLOAT);

		Assert::type(MetadataTypes\DataTypeType::class, $datatype);

		$datatype = MetadataTypes\DataTypeType::get(MetadataTypes\DataTypeType::DATA_TYPE_BOOLEAN);

		Assert::type(MetadataTypes\DataTypeType::class, $datatype);

		$datatype = MetadataTypes\DataTypeType::get(MetadataTypes\DataTypeType::DATA_TYPE_STRING);

		Assert::type(MetadataTypes\DataTypeType::class, $datatype);

		$datatype = MetadataTypes\DataTypeType::get(MetadataTypes\DataTypeType::DATA_TYPE_ENUM);

		Assert::type(MetadataTypes\DataTypeType::class, $datatype);

		$datatype = MetadataTypes\DataTypeType::get(MetadataTypes\DataTypeType::DATA_TYPE_COLOR);

		Assert::type(MetadataTypes\DataTypeType::class, $datatype);
	}

	/**
	 * @throws Consistence\Enum\InvalidEnumValueException
	 */
	public function testInvalidDatatype(): void
	{
		MetadataTypes\DataTypeType::get('invalidtype');
	}

}

$test_case = new DatatypeTypeTest();
$test_case->run();
