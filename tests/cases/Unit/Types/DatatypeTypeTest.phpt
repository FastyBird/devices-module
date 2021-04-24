<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;
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
		$datatype = ModulesMetadataTypes\DataTypeType::get(ModulesMetadataTypes\DataTypeType::DATA_TYPE_INT);

		Assert::type(ModulesMetadataTypes\DataTypeType::class, $datatype);

		$datatype = ModulesMetadataTypes\DataTypeType::get(ModulesMetadataTypes\DataTypeType::DATA_TYPE_FLOAT);

		Assert::type(ModulesMetadataTypes\DataTypeType::class, $datatype);

		$datatype = ModulesMetadataTypes\DataTypeType::get(ModulesMetadataTypes\DataTypeType::DATA_TYPE_BOOLEAN);

		Assert::type(ModulesMetadataTypes\DataTypeType::class, $datatype);

		$datatype = ModulesMetadataTypes\DataTypeType::get(ModulesMetadataTypes\DataTypeType::DATA_TYPE_STRING);

		Assert::type(ModulesMetadataTypes\DataTypeType::class, $datatype);

		$datatype = ModulesMetadataTypes\DataTypeType::get(ModulesMetadataTypes\DataTypeType::DATA_TYPE_ENUM);

		Assert::type(ModulesMetadataTypes\DataTypeType::class, $datatype);

		$datatype = ModulesMetadataTypes\DataTypeType::get(ModulesMetadataTypes\DataTypeType::DATA_TYPE_COLOR);

		Assert::type(ModulesMetadataTypes\DataTypeType::class, $datatype);
	}

	/**
	 * @throws Consistence\Enum\InvalidEnumValueException
	 */
	public function testInvalidDatatype(): void
	{
		$datatype = ModulesMetadataTypes\DataTypeType::get('invalidtype');
	}

}

$test_case = new DatatypeTypeTest();
$test_case->run();
