<?php declare(strict_types = 1);

namespace Tests\Cases;

use Doctrine\ORM;
use FastyBird\ApplicationExchange\Publisher as ApplicationExchangePublisher;
use FastyBird\DateTimeFactory;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Helpers;
use FastyBird\DevicesModule\Subscribers;
use Mockery;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;
use stdClass;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class EntitiesSubscriberTest extends BaseMockeryTestCase
{

	public function testSubscriberEvents(): void
	{
		$publisher = Mockery::mock(ApplicationExchangePublisher\IPublisher::class);

		$entityManager = Mockery::mock(ORM\EntityManagerInterface::class);

		$numberHashHelper = Mockery::mock(Helpers\NumberHashHelper::class);

		$dateTimeFactory = Mockery::mock(DateTimeFactory\DateTimeFactory::class);

		$subscriber = new Subscribers\EntitiesSubscriber(
			$numberHashHelper,
			$dateTimeFactory,
			$publisher,
			$entityManager
		);

		Assert::same(['preFlush', 'onFlush', 'prePersist', 'postPersist', 'postUpdate'], $subscriber->getSubscribedEvents());
	}

	public function testPublishCreatedEntity(): void
	{
		$publisher = Mockery::mock(ApplicationExchangePublisher\IPublisher::class);
		$publisher
			->shouldReceive('publish')
			->withArgs(function (string $key, array $data): bool {
				unset($data['id']);

				Assert::same('fb.bus.entity.created.device', $key);
				Assert::equal([
					'identifier'            => 'device-name',
					'key'                   => 'bLikmS',
					'parent'                => null,
					'device'                => 'device-name',
					'owner'                 => null,
					'name'                  => 'Device custom name',
					'comment'               => null,
					'state'                 => 'unknown',
					'enabled'               => true,
					'hardware_version'      => null,
					'hardware_manufacturer' => 'generic',
					'hardware_model'        => 'custom',
					'mac_address'           => null,
					'firmware_manufacturer' => 'generic',
					'firmware_version'      => null,
					'control'               => [],
					'params'                => [],
				], $data);

				return true;
			})
			->times(1);

		$entityManager = $this->getEntityManager();

		$numberHashHelper = Mockery::mock(Helpers\NumberHashHelper::class);

		$dateTimeFactory = Mockery::mock(DateTimeFactory\DateTimeFactory::class);

		$subscriber = new Subscribers\EntitiesSubscriber(
			$numberHashHelper,
			$dateTimeFactory,
			$publisher,
			$entityManager
		);

		$entity = new Entities\Devices\Device('device-name', 'device-name');
		$entity->setKey('bLikmS');
		$entity->setName('Device custom name');

		$eventArgs = Mockery::mock(ORM\Event\LifecycleEventArgs::class);
		$eventArgs
			->shouldReceive('getObject')
			->withNoArgs()
			->andReturn($entity)
			->times(1);

		$subscriber->postPersist($eventArgs);
	}

	public function testPublishUpdatedEntity(): void
	{
		$publisher = Mockery::mock(ApplicationExchangePublisher\IPublisher::class);
		$publisher
			->shouldReceive('publish')
			->withArgs(function (string $key, array $data): bool {
				unset($data['id']);

				Assert::same('fb.bus.entity.updated.device', $key);
				Assert::equal([
					'identifier'            => 'device-name',
					'key'                   => 'bLikmS',
					'parent'                => null,
					'device'                => 'device-name',
					'owner'                 => null,
					'name'                  => 'Device custom name',
					'comment'               => null,
					'state'                 => 'unknown',
					'enabled'               => true,
					'hardware_version'      => null,
					'hardware_manufacturer' => 'generic',
					'hardware_model'        => 'custom',
					'mac_address'           => null,
					'firmware_manufacturer' => 'generic',
					'firmware_version'      => null,
					'control'               => [],
					'params'                => [],
				], $data);

				return true;
			})
			->times(1);

		$entityManager = $this->getEntityManager(true);

		$numberHashHelper = Mockery::mock(Helpers\NumberHashHelper::class);

		$dateTimeFactory = Mockery::mock(DateTimeFactory\DateTimeFactory::class);

		$subscriber = new Subscribers\EntitiesSubscriber(
			$numberHashHelper,
			$dateTimeFactory,
			$publisher,
			$entityManager
		);

		$entity = new Entities\Devices\Device('device-name', 'device-name');
		$entity->setKey('bLikmS');
		$entity->setName('Device custom name');

		$eventArgs = Mockery::mock(ORM\Event\LifecycleEventArgs::class);
		$eventArgs
			->shouldReceive('getObject')
			->andReturn($entity)
			->times(1);

		$subscriber->postUpdate($eventArgs);
	}

	public function testPublishDeletedEntity(): void
	{
		$publisher = Mockery::mock(ApplicationExchangePublisher\IPublisher::class);
		$publisher
			->shouldReceive('publish')
			->withArgs(function (string $key, array $data): bool {
				unset($data['id']);

				Assert::same('fb.bus.entity.deleted.device', $key);
				Assert::equal([
					'identifier'            => 'device-name',
					'key'                   => 'bLikmS',
					'parent'                => null,
					'device'                => 'device-name',
					'owner'                 => null,
					'name'                  => 'Device custom name',
					'comment'               => null,
					'state'                 => 'unknown',
					'enabled'               => true,
					'hardware_version'      => null,
					'hardware_manufacturer' => 'generic',
					'hardware_model'        => 'custom',
					'mac_address'           => null,
					'firmware_manufacturer' => 'generic',
					'firmware_version'      => null,
					'control'               => [],
					'params'                => [],
				], $data);

				return true;
			})
			->times(1);

		$entity = new Entities\Devices\Device('device-name', 'device-name');
		$entity->setKey('bLikmS');
		$entity->setName('Device custom name');

		$uow = Mockery::mock(ORM\UnitOfWork::class);
		$uow
			->shouldReceive('getScheduledEntityDeletions')
			->withNoArgs()
			->andReturn([$entity])
			->times(1)
			->getMock()
			->shouldReceive('getEntityIdentifier')
			->andReturn([
				123,
			])
			->times(1);

		$entityManager = $this->getEntityManager();
		$entityManager
			->shouldReceive('getUnitOfWork')
			->withNoArgs()
			->andReturn($uow)
			->times(1);

		$numberHashHelper = Mockery::mock(Helpers\NumberHashHelper::class);

		$dateTimeFactory = Mockery::mock(DateTimeFactory\DateTimeFactory::class);

		$subscriber = new Subscribers\EntitiesSubscriber(
			$numberHashHelper,
			$dateTimeFactory,
			$publisher,
			$entityManager
		);

		$subscriber->onFlush();
	}

	/**
	 * @param bool $withUow
	 *
	 * @return ORM\EntityManagerInterface|Mockery\MockInterface
	 */
	private function getEntityManager(bool $withUow = false): Mockery\MockInterface
	{
		$metadata = new stdClass();
		$metadata->fieldMappings = [
			[
				'fieldName' => 'identifier',
			],
			[
				'fieldName' => 'name',
			],
		];

		$entityManager = Mockery::mock(ORM\EntityManagerInterface::class);
		$entityManager
			->shouldReceive('getClassMetadata')
			->withArgs([Entities\Devices\Device::class])
			->andReturn($metadata);

		if ($withUow) {
			$uow = Mockery::mock(ORM\UnitOfWork::class);
			$uow
				->shouldReceive('getEntityChangeSet')
				->andReturn(['name'])
				->times(1)
				->getMock()
				->shouldReceive('isScheduledForDelete')
				->andReturn(false)
				->getMock();

			$entityManager
				->shouldReceive('getUnitOfWork')
				->withNoArgs()
				->andReturn($uow)
				->times(1);
		}

		return $entityManager;
	}

}

$test_case = new EntitiesSubscriberTest();
$test_case->run();
