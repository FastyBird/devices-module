<?php declare(strict_types = 1);

namespace FastyBird\Module\Devices\Tests\Cases\Unit\Documents;

use Error;
use FastyBird\Library\Application\Exceptions as ApplicationExceptions;
use FastyBird\Library\Metadata\Documents as MetadataDocuments;
use FastyBird\Library\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Module\Devices\Documents;
use FastyBird\Module\Devices\Tests;
use Nette;
use function file_get_contents;

final class ChannelPropertyActionDocumentTest extends Tests\Cases\Unit\BaseTestCase
{

	/**
	 * @throws ApplicationExceptions\InvalidArgument
	 * @throws ApplicationExceptions\InvalidState
	 * @throws Error
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\MalformedInput
	 * @throws MetadataExceptions\Mapping
	 * @throws Nette\DI\MissingServiceException
	 *
	 * @dataProvider channelProperty
	 */
	public function testCreateDocument(string $data, string $class): void
	{
		$factory = $this->getContainer()->getByType(MetadataDocuments\DocumentFactory::class);

		$document = $factory->create(Documents\States\Channels\Properties\Actions\Action::class, $data);

		self::assertTrue($document instanceof $class);
	}

	/**
	 * @throws ApplicationExceptions\InvalidArgument
	 * @throws ApplicationExceptions\InvalidState
	 * @throws Error
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\MalformedInput
	 * @throws MetadataExceptions\Mapping
	 * @throws Nette\DI\MissingServiceException
	 *
	 * @dataProvider channelPropertyInvalid
	 */
	public function testCreateDocumentInvalid(string $data): void
	{
		$factory = $this->getContainer()->getByType(MetadataDocuments\DocumentFactory::class);

		$this->expectException(MetadataExceptions\InvalidArgument::class);

		$factory->create(Documents\States\Channels\Properties\Actions\Action::class, $data);
	}

	/**
	 * @return array<string, array<string|bool>>
	 */
	public static function channelProperty(): array
	{
		return [
			'get' => [
				file_get_contents(__DIR__ . '/../../../fixtures/Documents/channel.property.action.get.json'),
				Documents\States\Channels\Properties\Actions\Action::class,
			],
			'set' => [
				file_get_contents(__DIR__ . '/../../../fixtures/Documents/channel.property.action.set.json'),
				Documents\States\Channels\Properties\Actions\Action::class,
			],
		];
	}

	/**
	 * @return array<string, array<string|bool>>
	 */
	public static function channelPropertyInvalid(): array
	{
		return [
			'missing' => [
				file_get_contents(
					__DIR__ . '/../../../fixtures/Documents/channel.property.action.missing.json',
				),
			],
		];
	}

}
