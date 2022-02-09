<?php declare(strict_types = 1);

namespace Tests\Cases;

use Doctrine\ORM;
use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Subscribers;
use FastyBird\Exchange\Publisher as ExchangePublisher;
use FastyBird\Metadata;
use Mockery;
use Nette\Utils;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;
use Ramsey\Uuid;
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
		$publisher = Mockery::mock(ExchangePublisher\Publisher::class);

		$entityManager = Mockery::mock(ORM\EntityManagerInterface::class);

		$connectorPropertiesStateRepository = Mockery::mock(Models\States\ConnectorPropertiesRepository::class);
		$connectorPropertiesStateRepository
			->shouldReceive('findOne')
			->andThrow(Exceptions\NotImplementedException::class);

		$devicePropertiesStateRepository = Mockery::mock(Models\States\DevicePropertiesRepository::class);
		$devicePropertiesStateRepository
			->shouldReceive('findOne')
			->andThrow(Exceptions\NotImplementedException::class);

		$channelPropertiesStateRepository = Mockery::mock(Models\States\ChannelPropertiesRepository::class);
		$channelPropertiesStateRepository
			->shouldReceive('findOne')
			->andThrow(Exceptions\NotImplementedException::class);

		$subscriber = new Subscribers\EntitiesSubscriber(
			$entityManager,
			$devicePropertiesStateRepository,
			$channelPropertiesStateRepository,
			$connectorPropertiesStateRepository,
			$publisher
		);

		Assert::same([
			'onFlush',
			'postPersist',
			'postUpdate',
		], $subscriber->getSubscribedEvents());
	}

	public function testPublishCreatedEntity(): void
	{
		$publisher = Mockery::mock(ExchangePublisher\Publisher::class);
		$publisher
			->shouldReceive('publish')
			->withArgs(function (string $source, string $key, Utils\ArrayHash $data): bool {
				unset($data['id']);

				Assert::same(Metadata\Constants::MODULE_DEVICES_SOURCE, $source);
				Assert::same(Metadata\Constants::MESSAGE_BUS_DEVICES_CREATED_ENTITY_ROUTING_KEY, $key);
				Assert::equal(Utils\ArrayHash::from([
					'identifier'            => 'device-name',
					'type'                  => 'virtual',
					'parent'                => null,
					'owner'                 => null,
					'name'                  => 'Device custom name',
					'comment'               => null,
					'enabled'               => true,
					'hardware_version'      => null,
					'hardware_manufacturer' => 'generic',
					'hardware_model'        => 'custom',
					'hardware_mac_address'  => null,
					'firmware_manufacturer' => 'generic',
					'firmware_version'      => null,
					'connector'             => 'dd6aa4bc-2611-40c3-84ef-0a438cf51e67',
				]), $data);

				return true;
			})
			->times(1);

		$entityManager = $this->getEntityManager();

		$connectorPropertiesStateRepository = Mockery::mock(Models\States\ConnectorPropertiesRepository::class);
		$connectorPropertiesStateRepository
			->shouldReceive('findOne')
			->andThrow(Exceptions\NotImplementedException::class);

		$devicePropertiesStateRepository = Mockery::mock(Models\States\DevicePropertiesRepository::class);
		$devicePropertiesStateRepository
			->shouldReceive('findOne')
			->andThrow(Exceptions\NotImplementedException::class);

		$channelPropertiesStateRepository = Mockery::mock(Models\States\ChannelPropertiesRepository::class);
		$channelPropertiesStateRepository
			->shouldReceive('findOne')
			->andThrow(Exceptions\NotImplementedException::class);

		$subscriber = new Subscribers\EntitiesSubscriber(
			$entityManager,
			$devicePropertiesStateRepository,
			$channelPropertiesStateRepository,
			$connectorPropertiesStateRepository,
			$publisher
		);

		$connectorEntity = new Entities\Connectors\VirtualConnector('virtual-connector-name', Uuid\Uuid::fromString('dd6aa4bc-2611-40c3-84ef-0a438cf51e67'));

		$entity = new Entities\Devices\VirtualDevice('device-name', $connectorEntity, 'device-name');
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
		$publisher = Mockery::mock(ExchangePublisher\Publisher::class);
		$publisher
			->shouldReceive('publish')
			->withArgs(function (string $source, string $key, Utils\ArrayHash $data): bool {
				unset($data['id']);

				Assert::same(Metadata\Constants::MODULE_DEVICES_SOURCE, $source);
				Assert::same(Metadata\Constants::MESSAGE_BUS_DEVICES_UPDATED_ENTITY_ROUTING_KEY, $key);
				Assert::equal(Utils\ArrayHash::from([
					'identifier'            => 'device-name',
					'type'                  => 'virtual',
					'parent'                => null,
					'owner'                 => null,
					'name'                  => 'Device custom name',
					'comment'               => null,
					'enabled'               => true,
					'hardware_version'      => null,
					'hardware_manufacturer' => 'generic',
					'hardware_model'        => 'custom',
					'hardware_mac_address'  => null,
					'firmware_manufacturer' => 'generic',
					'firmware_version'      => null,
					'connector'             => 'dd6aa4bc-2611-40c3-84ef-0a438cf51e67',
				]), $data);

				return true;
			})
			->times(1);

		$entityManager = $this->getEntityManager(true);

		$connectorPropertiesStateRepository = Mockery::mock(Models\States\ConnectorPropertiesRepository::class);
		$connectorPropertiesStateRepository
			->shouldReceive('findOne')
			->andThrow(Exceptions\NotImplementedException::class);

		$devicePropertiesStateRepository = Mockery::mock(Models\States\DevicePropertiesRepository::class);
		$devicePropertiesStateRepository
			->shouldReceive('findOne')
			->andThrow(Exceptions\NotImplementedException::class);

		$channelPropertiesStateRepository = Mockery::mock(Models\States\ChannelPropertiesRepository::class);
		$channelPropertiesStateRepository
			->shouldReceive('findOne')
			->andThrow(Exceptions\NotImplementedException::class);

		$subscriber = new Subscribers\EntitiesSubscriber(
			$entityManager,
			$devicePropertiesStateRepository,
			$channelPropertiesStateRepository,
			$connectorPropertiesStateRepository,
			$publisher
		);

		$connectorEntity = new Entities\Connectors\VirtualConnector('virtual-connector-name', Uuid\Uuid::fromString('dd6aa4bc-2611-40c3-84ef-0a438cf51e67'));

		$entity = new Entities\Devices\VirtualDevice('device-name', $connectorEntity, 'device-name');
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
		$publisher = Mockery::mock(ExchangePublisher\Publisher::class);
		$publisher
			->shouldReceive('publish')
			->withArgs(function (string $source, string $key, Utils\ArrayHash $data): bool {
				unset($data['id']);

				Assert::same(Metadata\Constants::MODULE_DEVICES_SOURCE, $source);
				Assert::same(Metadata\Constants::MESSAGE_BUS_DEVICES_DELETED_ENTITY_ROUTING_KEY, $key);
				Assert::equal(Utils\ArrayHash::from([
					'identifier'            => 'device-name',
					'type'                  => 'virtual',
					'parent'                => null,
					'owner'                 => null,
					'name'                  => 'Device custom name',
					'comment'               => null,
					'enabled'               => true,
					'hardware_version'      => null,
					'hardware_manufacturer' => 'generic',
					'hardware_model'        => 'custom',
					'hardware_mac_address'  => null,
					'firmware_manufacturer' => 'generic',
					'firmware_version'      => null,
					'connector'             => 'dd6aa4bc-2611-40c3-84ef-0a438cf51e67',
				]), $data);

				return true;
			})
			->times(1);

		$connectorEntity = new Entities\Connectors\VirtualConnector('virtual-connector-name', Uuid\Uuid::fromString('dd6aa4bc-2611-40c3-84ef-0a438cf51e67'));

		$entity = new Entities\Devices\VirtualDevice('device-name', $connectorEntity, 'device-name');
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

		$connectorPropertiesStateRepository = Mockery::mock(Models\States\ConnectorPropertiesRepository::class);
		$connectorPropertiesStateRepository
			->shouldReceive('findOne')
			->andThrow(Exceptions\NotImplementedException::class);

		$devicePropertiesStateRepository = Mockery::mock(Models\States\DevicePropertiesRepository::class);
		$devicePropertiesStateRepository
			->shouldReceive('findOne')
			->andThrow(Exceptions\NotImplementedException::class);

		$channelPropertiesStateRepository = Mockery::mock(Models\States\ChannelPropertiesRepository::class);
		$channelPropertiesStateRepository
			->shouldReceive('findOne')
			->andThrow(Exceptions\NotImplementedException::class);

		$subscriber = new Subscribers\EntitiesSubscriber(
			$entityManager,
			$devicePropertiesStateRepository,
			$channelPropertiesStateRepository,
			$connectorPropertiesStateRepository,
			$publisher
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
