<?php declare(strict_types = 1);

namespace Tests\Cases;

use DateTimeImmutable;
use FastyBird\DateTimeFactory;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Helpers;
use Mockery;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class EntityKeyTest extends BaseMockeryTestCase
{

	public function testGenerateDefault(): void
	{
		$dateTimeFactory = Mockery::mock(DateTimeFactory\DateTimeFactory::class);
		$dateTimeFactory
			->shouldReceive('getNow')
			->andReturn(new DateTimeImmutable('2020-04-01T12:00:00+00:00'));

		$entityKeyHelper = new Helpers\EntityKey($dateTimeFactory);

		$entity = Mockery::mock(Entities\IEntity::class);

		Assert::same('bJtCcu', $entityKeyHelper->generate($entity));
	}

	public function testGenerateCustomCallback(): void
	{
		$dateTimeFactory = Mockery::mock(DateTimeFactory\DateTimeFactory::class);

		$entityKeyHelper = new Helpers\EntityKey($dateTimeFactory);
		$entityKeyHelper->setCustomGenerator(function (): string {
			return 'custom-generated';
		});

		$entity = Mockery::mock(Entities\IEntity::class);

		Assert::same('custom-generated', $entityKeyHelper->generate($entity));
	}

}

$test_case = new EntityKeyTest();
$test_case->run();
