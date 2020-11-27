<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\DevicesModule\Entities;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use FastyBird\DevicesModule\Types\FirmwareManufacturerType;
use Nette\Utils;
use Tester\Assert;

require_once __DIR__ . '/../../../../bootstrap.php';
require_once __DIR__ . '/../../DbTestCase.php';

/**
 * @testCase
 */
final class FirmwareManagerTest extends DbTestCase
{

	public function testCreate(): void
	{
		/** @var Models\Devices\IDeviceRepository $deviceRepository */
		$deviceRepository = $this->getContainer()->getByType(Models\Devices\DeviceRepository::class);

		$findDeviceQuery = new Queries\FindDevicesQuery();
		$findDeviceQuery->byIdentifier('second-device');

		/** @var Entities\Devices\NetworkDevice $device */
		$device = $deviceRepository->findOneBy($findDeviceQuery);

		Assert::true(is_object($device));
		Assert::type(Entities\Devices\NetworkDevice::class, $device);
		Assert::same('second-device', $device->getIdentifier());

		/** @var Models\Devices\PhysicalDevice\IFirmwareManager $manager */
		$manager = $this->getContainer()->getByType(Models\Devices\PhysicalDevice\FirmwareManager::class);

		$entity = $manager->create(Utils\ArrayHash::from([
			'name'         => 'firmware',
			'manufacturer' => FirmwareManufacturerType::get(FirmwareManufacturerType::MANUFACTURER_GENERIC),
			'device'       => $device,
		]));

		Assert::true(is_object($entity));
		Assert::type(Entities\Devices\PhysicalDevice\Firmware::class, $entity);

		/** @var Entities\Devices\NetworkDevice $device */
		$device = $deviceRepository->findOneBy($findDeviceQuery);

		Assert::true(is_object($device->getFirmware()));
		Assert::type(Entities\Devices\PhysicalDevice\Firmware::class, $device->getFirmware());
		Assert::same($entity, $device->getFirmware());
	}

	public function testUpdate(): void
	{
		/** @var Models\Devices\IDeviceRepository $deviceRepository */
		$deviceRepository = $this->getContainer()->getByType(Models\Devices\DeviceRepository::class);

		$findDeviceQuery = new Queries\FindDevicesQuery();
		$findDeviceQuery->byIdentifier('third-device');

		/** @var Entities\Devices\LocalDevice $device */
		$device = $deviceRepository->findOneBy($findDeviceQuery);

		Assert::true(is_object($device));
		Assert::type(Entities\Devices\LocalDevice::class, $device);
		Assert::same('third-device', $device->getIdentifier());

		/** @var Models\Devices\PhysicalDevice\IFirmwareManager $manager */
		$manager = $this->getContainer()->getByType(Models\Devices\PhysicalDevice\FirmwareManager::class);

		$entity = $manager->update($device->getFirmware(), Utils\ArrayHash::from([
			'name' => 'updated',
		]));

		Assert::true(is_object($entity));
		Assert::same('updated', $entity->getName());
	}

}

$test_case = new FirmwareManagerTest();
$test_case->run();
