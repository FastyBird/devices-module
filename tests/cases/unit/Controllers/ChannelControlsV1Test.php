<?php declare(strict_types = 1);

namespace FastyBird\DevicesModule\Tests\Cases\Unit\Controllers;

use FastyBird\DevicesModule\Tests\Cases\Unit\DbTestCase;
use FastyBird\DevicesModule\Tests\Tools;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use InvalidArgumentException;
use IPub\SlimRouter;
use IPub\SlimRouter\Http as SlimRouterHttp;
use Nette;
use Nette\Utils;
use React\Http\Message\ServerRequest;
use RuntimeException;

final class ChannelControlsV1Test extends DbTestCase
{

	/**
	 * @throws InvalidArgumentException
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 * @throws Utils\JsonException
	 *
	 * @dataProvider channelControlsRead
	 */
	public function testRead(string $url, string|null $token, int $statusCode, string $fixture): void
	{
		$router = $this->getContainer()->getByType(SlimRouter\Routing\IRouter::class);

		$headers = [];

		if ($token !== null) {
			$headers['authorization'] = $token;
		}

		$request = new ServerRequest(
			RequestMethodInterface::METHOD_GET,
			$url,
			$headers,
		);

		$response = $router->handle($request);

		self::assertTrue($response instanceof SlimRouterHttp\Response);
		self::assertSame($statusCode, $response->getStatusCode());
		Tools\JsonAssert::assertFixtureMatch(
			$fixture,
			(string) $response->getBody(),
		);
	}

	/**
	 * @return Array<string, Array<string|int|null>>
	 */
	public function channelControlsRead(): array
	{
		return [
			// Valid responses
			//////////////////
			'readAll' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/channels/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_OK,
				__DIR__ . '/../../../fixtures/Controllers/responses/channel.controls.index.json',
			],
			'readAllPaging' => [
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/channels/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls?page[offset]=1&page[limit]=1',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_OK,
				__DIR__ . '/../../../fixtures/Controllers/responses/channel.controls.index.paging.json',
			],
			'readOne' => [
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/channels/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls/15db9bef-3b57-4a87-bf67-e3c19fc3ba34',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_OK,
				__DIR__ . '/../../../fixtures/Controllers/responses/channel.controls.read.json',
			],
			'readRelationshipsChannel' => [
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/channels/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls/15db9bef-3b57-4a87-bf67-e3c19fc3ba34/relationships/channel',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_OK,
				__DIR__ . '/../../../fixtures/Controllers/responses/channel.controls.relationships.channel.json',
			],

			// Invalid responses
			////////////////////
			'readOneUnknown' => [
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/channels/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls/28bc0d38-2f7c-4a71-aa74-27b102f8dfc4',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_NOT_FOUND,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/notFound.json',
			],
			'readRelationshipsChannelUnknownControl' => [
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/channels/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls/28bc0d38-2f7c-4a71-aa74-27b102f8dfc4/relationships/channel',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_NOT_FOUND,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/notFound.json',
			],
			'readRelationshipsUnknownDevice' => [
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009af/channels/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls/15db9bef-3b57-4a87-bf67-e3c19fc3ba34/relationships/channel',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_NOT_FOUND,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/notFound.json',
			],
			'readRelationshipsUnknownChannel' => [
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/channels/28bc0d38-2edd-438e-8c49-faa4e38e5a5e/controls/15db9bef-3b57-4a87-bf67-e3c19fc3ba34',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_NOT_FOUND,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/notFound.json',
			],
			'readRelationshipsUnknown' => [
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/channels/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls/15db9bef-3b57-4a87-bf67-e3c19fc3ba34/relationships/unknown',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_NOT_FOUND,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/relation.unknown.json',
			],
			'readAllMissingToken' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/channels/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls',
				null,
				StatusCodeInterface::STATUS_FORBIDDEN,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/forbidden.json',
			],
			'readOneMissingToken' => [
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/channels/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls/15db9bef-3b57-4a87-bf67-e3c19fc3ba34',
				null,
				StatusCodeInterface::STATUS_FORBIDDEN,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/forbidden.json',
			],
			'readAllEmptyToken' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/channels/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls',
				'',
				StatusCodeInterface::STATUS_FORBIDDEN,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/forbidden.json',
			],
			'readOneEmptyToken' => [
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/channels/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls/15db9bef-3b57-4a87-bf67-e3c19fc3ba34',
				'',
				StatusCodeInterface::STATUS_FORBIDDEN,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/forbidden.json',
			],
			'readAllInvalidToken' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/channels/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls',
				'Bearer ' . self::INVALID_TOKEN,
				StatusCodeInterface::STATUS_UNAUTHORIZED,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/unauthorized.json',
			],
			'readOneInvalidToken' => [
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/channels/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls/15db9bef-3b57-4a87-bf67-e3c19fc3ba34',
				'Bearer ' . self::INVALID_TOKEN,
				StatusCodeInterface::STATUS_UNAUTHORIZED,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/unauthorized.json',
			],
			'readAllExpiredToken' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/channels/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls',
				'Bearer ' . self::EXPIRED_TOKEN,
				StatusCodeInterface::STATUS_UNAUTHORIZED,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/unauthorized.json',
			],
			'readOneExpiredToken' => [
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/channels/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls/15db9bef-3b57-4a87-bf67-e3c19fc3ba34',
				'Bearer ' . self::EXPIRED_TOKEN,
				StatusCodeInterface::STATUS_UNAUTHORIZED,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/unauthorized.json',
			],
		];
	}

}
