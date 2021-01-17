<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\DevicesModule\Consumers;
use FastyBird\DevicesModule\States;
use Mockery;
use Nette\Utils;
use Tester\Assert;
use Tests\Tools;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../DbTestCase.php';

/**
 * @testCase
 */
final class ChannelPropertyMessageConsumerTest extends DbTestCase
{

	public function setUp(): void
	{
		$this->registerNeonConfigurationFile(__DIR__ . '/services.neon');

		parent::setUp();
	}

	/**
	 * @param string $routingKey
	 * @param string $origin
	 * @param Utils\ArrayHash $payload
	 * @param mixed[] $state
	 *
	 * @dataProvider ./../../../fixtures/Consumers/channelPropertyMessage.php
	 */
	public function testProcessMessage(string $routingKey, string $origin, Utils\ArrayHash $payload, array $state): void
	{
		$stateMock = Mockery::mock(States\IProperty::class);
		$stateMock
			->shouldReceive('getValue')
			->andReturn($state['value'] ?? null)
			->getMock()
			->shouldReceive('getExpected')
			->andReturn($state['expected'] ?? null)
			->getMock()
			->shouldReceive('isPending')
			->andReturn($state['pending'] ?? false)
			->getMock()
			->shouldReceive('toArray')
			->andReturn($state)
			->getMock();

		$this->mockStateManagement($stateMock);

		/** @var Consumers\ChannelPropertyMessageConsumer $consumer */
		$consumer = $this->getContainer()->getByType(Consumers\ChannelPropertyMessageConsumer::class);

		$consumer->consume($origin, $routingKey, $payload);

		Assert::true(true);
	}

	private function mockStateManagement(States\IProperty $stateMock): void
	{
		$statePropertyRepositoryMock = Mockery::mock(Tools\DummyStateRepository::class);
		$statePropertyRepositoryMock
			->shouldReceive('findOne')
			->andReturn($stateMock)
			->times(1);

		$this->mockContainerService(
			Tools\DummyStateRepository::class,
			$statePropertyRepositoryMock
		);

		$statePropertiesManagerMock = Mockery::mock(Tools\DummyStatesManager::class);
		$statePropertiesManagerMock
			->shouldReceive('updateState')
			->andReturn($stateMock)
			->times(1)
			->getMock();

		$this->mockContainerService(
			Tools\DummyStatesManager::class,
			$statePropertiesManagerMock
		);
	}

}

$test_case = new ChannelPropertyMessageConsumerTest();
$test_case->run();
