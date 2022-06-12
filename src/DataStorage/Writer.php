<?php declare(strict_types = 1);

/**
 * BaseV1Controller.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Controllers
 * @since          0.1.0
 *
 * @date           13.04.19
 */

namespace FastyBird\DevicesModule\DataStorage;

use FastyBird\DevicesModule;
use FastyBird\DevicesModule\Models;
use FastyBird\DevicesModule\Queries;
use League\Flysystem;
use Nette\Utils;

final class Writer
{

	/** @var Models\Connectors\IConnectorsRepository */
	private Models\Connectors\IConnectorsRepository $connectorsRepository;

	/** @var Flysystem\Filesystem */
	private Flysystem\Filesystem $filesystem;

	public function __construct(
		Models\Connectors\IConnectorsRepository $connectorsRepository,
		Flysystem\Filesystem $filesystem
	) {
		$this->connectorsRepository = $connectorsRepository;
		$this->filesystem = $filesystem;
	}

	/**
	 * @return void
	 *
	 * @throws Flysystem\FilesystemException
	 * @throws Utils\JsonException
	 */
	public function write(): void
	{
		$data = [];

		$findConnectors = new Queries\FindConnectorsQuery();

		$connectors = $this->connectorsRepository->findAllBy($findConnectors);

		foreach ($connectors as $connector) {
			$devices = [];

			foreach ($connector->getDevices() as $device) {
				$channels = [];

				foreach ($device->getChannels() as $channel) {
					$properties = [];

					foreach ($channel->getProperties() as $property) {
						$properties[$property->getPlainId()] = $property->toArray();
					}

					$controls = [];

					foreach ($channel->getControls() as $control) {
						$controls[$control->getPlainId()] = $control->toArray();
					}

					$channels[$channel->getPlainId()] = array_merge($channel->toArray(), [
						'properties' => $properties,
						'controls'   => $controls,
					]);
				}

				$properties = [];

				foreach ($device->getProperties() as $property) {
					$properties[$property->getPlainId()] = $property->toArray();
				}

				$attributes = [];

				foreach ($device->getAttributes() as $attribute) {
					$attributes[$attribute->getPlainId()] = $attribute->toArray();
				}

				$controls = [];

				foreach ($device->getControls() as $control) {
					$controls[$control->getPlainId()] = $control->toArray();
				}

				$devices[$device->getPlainId()] = array_merge($device->toArray(), [
					'properties' => $properties,
					'attributes' => $attributes,
					'controls'   => $controls,
					'channels'   => $channels,
				]);
			}

			$properties = [];

			foreach ($connector->getProperties() as $property) {
				$properties[$property->getPlainId()] = $property->toArray();
			}

			$controls = [];

			foreach ($connector->getControls() as $control) {
				$controls[$control->getPlainId()] = $control->toArray();
			}

			$data[$connector->getPlainId()] = array_merge($connector->toArray(), [
				'properties' => $properties,
				'controls'   => $controls,
				'devices'    => $devices,
			]);
		}

		$this->filesystem->write(DevicesModule\Constants::CONFIGURATION_FILE_FILENAME, Utils\Json::encode($data));
	}

}
