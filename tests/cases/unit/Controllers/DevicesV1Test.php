<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\Controllers;

use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use InvalidArgumentException;
use IPub\SlimRouter;
use IPub\SlimRouter\Http as SlimRouterHttp;
use Nette;
use Nette\Utils;
use React\Http\Message\ServerRequest;
use RuntimeException;
use Tests\Cases\Unit\DbTestCase;
use Tests\Tools;
use function file_get_contents;

final class DevicesV1Test extends DbTestCase
{

	/**
	 * @throws InvalidArgumentException
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 * @throws Utils\JsonException
	 *
	 * @dataProvider devicesRead
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
	public function devicesRead(): array
	{
		return [
			// Valid responses
			//////////////////
			'readAll' => [
				'/v1/devices',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_OK,
				__DIR__ . '/../../../fixtures/Controllers/responses/devices.index.json',
			],
			'readAllPaging' => [
				'/v1/devices?page[offset]=1&page[limit]=1',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_OK,
				__DIR__ . '/../../../fixtures/Controllers/responses/devices.index.paging.json',
			],
			'readOne' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_OK,
				__DIR__ . '/../../../fixtures/Controllers/responses/devices.read.json',
			],
			'readRelationshipsProperties' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/relationships/properties',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_OK,
				__DIR__ . '/../../../fixtures/Controllers/responses/devices.relationships.properties.json',
			],
			'readRelationshipsChannels' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/relationships/channels',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_OK,
				__DIR__ . '/../../../fixtures/Controllers/responses/devices.relationships.channels.json',
			],
			'readRelationshipsChildren' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/relationships/children',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_OK,
				__DIR__ . '/../../../fixtures/Controllers/responses/devices.relationships.children.json',
			],

			// Invalid responses
			////////////////////
			'readOneUnknown' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009af',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_NOT_FOUND,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/notFound.json',
			],
			'readRelationshipsUnknown' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/relationships/unknown',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_NOT_FOUND,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/relation.unknown.json',
			],
			'readRelationshipsUnknownEntity' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009af/relationships/children',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_NOT_FOUND,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/notFound.json',
			],
			'readAllMissingToken' => [
				'/v1/devices',
				null,
				StatusCodeInterface::STATUS_FORBIDDEN,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/forbidden.json',
			],
			'readOneMissingToken' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa',
				null,
				StatusCodeInterface::STATUS_FORBIDDEN,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/forbidden.json',
			],
			'readAllEmptyToken' => [
				'/v1/devices',
				'',
				StatusCodeInterface::STATUS_FORBIDDEN,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/forbidden.json',
			],
			'readOneEmptyToken' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa',
				'',
				StatusCodeInterface::STATUS_FORBIDDEN,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/forbidden.json',
			],
			'readAllInvalidToken' => [
				'/v1/devices',
				'Bearer ' . self::INVALID_TOKEN,
				StatusCodeInterface::STATUS_UNAUTHORIZED,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/unauthorized.json',
			],
			'readOneInvalidToken' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa',
				'Bearer ' . self::INVALID_TOKEN,
				StatusCodeInterface::STATUS_UNAUTHORIZED,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/unauthorized.json',
			],
			'readAllExpiredToken' => [
				'/v1/devices',
				'Bearer ' . self::EXPIRED_TOKEN,
				StatusCodeInterface::STATUS_UNAUTHORIZED,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/unauthorized.json',
			],
			'readOneExpiredToken' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa',
				'Bearer ' . self::EXPIRED_TOKEN,
				StatusCodeInterface::STATUS_UNAUTHORIZED,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/unauthorized.json',
			],
		];
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 * @throws Utils\JsonException
	 *
	 * @dataProvider devicesCreate
	 */
	public function testCreate(
		string $url,
		string|null $token,
		string $body,
		int $statusCode,
		string $fixture,
		string|null $secondUrl = null,
		string|null $secondToken = null,
		int|null $secondStatusCode = null,
		string|null $secondFixture = null,
	): void
	{
		$router = $this->getContainer()->getByType(SlimRouter\Routing\IRouter::class);

		$headers = [];

		if ($token !== null) {
			$headers['authorization'] = $token;
		}

		$request = new ServerRequest(
			RequestMethodInterface::METHOD_POST,
			$url,
			$headers,
			$body,
		);

		$response = $router->handle($request);

		self::assertTrue($response instanceof SlimRouterHttp\Response);
		self::assertSame($statusCode, $response->getStatusCode());
		Tools\JsonAssert::assertFixtureMatch(
			$fixture,
			(string) $response->getBody(),
		);

		if ($secondUrl !== null && $secondStatusCode !== null && $secondFixture !== null) {
			$headers = [];

			if ($secondToken !== null) {
				$headers['authorization'] = $secondToken;
			}

			$request = new ServerRequest(
				RequestMethodInterface::METHOD_GET,
				$secondUrl,
				$headers,
			);

			$response = $router->handle($request);

			self::assertTrue($response instanceof SlimRouterHttp\Response);
			self::assertSame($secondStatusCode, $response->getStatusCode());
			Tools\JsonAssert::assertFixtureMatch(
				$secondFixture,
				(string) $response->getBody(),
			);
		}
	}

	/**
	 * @return Array<string, Array<bool|string|int|null>>
	 */
	public function devicesCreate(): array
	{
		return [
			// Valid responses
			//////////////////
			'create' => [
				'/v1/devices',
				'Bearer ' . self::VALID_TOKEN,
				file_get_contents(__DIR__ . '/../../../fixtures/Controllers/requests/devices.create.json'),
				StatusCodeInterface::STATUS_CREATED,
				__DIR__ . '/../../../fixtures/Controllers/responses/devices.create.json',
			],
			'createChild' => [
				'/v1/devices',
				'Bearer ' . self::VALID_TOKEN,
				file_get_contents(__DIR__ . '/../../../fixtures/Controllers/requests/devices.create.child.json'),
				StatusCodeInterface::STATUS_CREATED,
				__DIR__ . '/../../../fixtures/Controllers/responses/devices.create.child.json',
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_OK,
				__DIR__ . '/../../../fixtures/Controllers/responses/devices.read.child.created.json',
			],

			// Invalid responses
			////////////////////
			'missingRequired' => [
				'/v1/devices',
				'Bearer ' . self::VALID_TOKEN,
				file_get_contents(
					__DIR__ . '/../../../fixtures/Controllers/requests/devices.create.missing.required.json',
				),
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				__DIR__ . '/../../../fixtures/Controllers/responses/devices.create.missing.required.json',
			],
			'notUnique' => [
				'/v1/devices',
				'Bearer ' . self::VALID_TOKEN,
				file_get_contents(__DIR__ . '/../../../fixtures/Controllers/requests/devices.create.notUnique.json'),
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				__DIR__ . '/../../../fixtures/Controllers/responses/devices.create.notUnique.json',
			],
			'invalidType' => [
				'/v1/devices',
				'Bearer ' . self::VALID_TOKEN,
				file_get_contents(__DIR__ . '/../../../fixtures/Controllers/requests/devices.create.invalid.type.json'),
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/invalid.type.json',
			],
			'missingToken' => [
				'/v1/devices',
				null,
				file_get_contents(__DIR__ . '/../../../fixtures/Controllers/requests/devices.create.json'),
				StatusCodeInterface::STATUS_FORBIDDEN,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/forbidden.json',
			],
			'invalidToken' => [
				'/v1/devices',
				'Bearer ' . self::INVALID_TOKEN,
				file_get_contents(__DIR__ . '/../../../fixtures/Controllers/requests/devices.create.json'),
				StatusCodeInterface::STATUS_UNAUTHORIZED,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/unauthorized.json',
			],
			'emptyToken' => [
				'/v1/devices',
				'',
				file_get_contents(__DIR__ . '/../../../fixtures/Controllers/requests/devices.create.json'),
				StatusCodeInterface::STATUS_FORBIDDEN,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/forbidden.json',
			],
			'expiredToken' => [
				'/v1/devices',
				'Bearer ' . self::EXPIRED_TOKEN,
				file_get_contents(__DIR__ . '/../../../fixtures/Controllers/requests/devices.create.json'),
				StatusCodeInterface::STATUS_UNAUTHORIZED,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/unauthorized.json',
			],
			'notAllowed' => [
				'/v1/devices',
				'Bearer ' . self::VALID_TOKEN_USER,
				file_get_contents(__DIR__ . '/../../../fixtures/Controllers/requests/devices.create.json'),
				StatusCodeInterface::STATUS_FORBIDDEN,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/forbidden.json',
			],
		];
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 * @throws Utils\JsonException
	 *
	 * @dataProvider devicesUpdate
	 */
	public function testUpdate(string $url, string|null $token, string $body, int $statusCode, string $fixture): void
	{
		$router = $this->getContainer()->getByType(SlimRouter\Routing\IRouter::class);

		$headers = [];

		if ($token !== null) {
			$headers['authorization'] = $token;
		}

		$request = new ServerRequest(
			RequestMethodInterface::METHOD_PATCH,
			$url,
			$headers,
			$body,
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
	 * @return Array<string, Array<bool|string|int|null>>
	 */
	public function devicesUpdate(): array
	{
		return [
			// Valid responses
			//////////////////
			'update' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa',
				'Bearer ' . self::VALID_TOKEN,
				file_get_contents(__DIR__ . '/../../../fixtures/Controllers/requests/devices.update.json'),
				StatusCodeInterface::STATUS_OK,
				__DIR__ . '/../../../fixtures/Controllers/responses/devices.update.json',
			],

			// Invalid responses
			////////////////////
			'invalidType' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa',
				'Bearer ' . self::VALID_TOKEN,
				file_get_contents(__DIR__ . '/../../../fixtures/Controllers/requests/devices.update.invalid.type.json'),
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/invalid.type.json',
			],
			'idMismatch' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa',
				'Bearer ' . self::VALID_TOKEN,
				file_get_contents(__DIR__ . '/../../../fixtures/Controllers/requests/devices.update.invalid.id.json'),
				StatusCodeInterface::STATUS_BAD_REQUEST,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/invalid.identifier.json',
			],
			'missingToken' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa',
				null,
				file_get_contents(__DIR__ . '/../../../fixtures/Controllers/requests/devices.update.json'),
				StatusCodeInterface::STATUS_FORBIDDEN,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/forbidden.json',
			],
			'invalidToken' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa',
				'Bearer ' . self::INVALID_TOKEN,
				file_get_contents(__DIR__ . '/../../../fixtures/Controllers/requests/devices.update.json'),
				StatusCodeInterface::STATUS_UNAUTHORIZED,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/unauthorized.json',
			],
			'emptyToken' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa',
				'',
				file_get_contents(__DIR__ . '/../../../fixtures/Controllers/requests/devices.update.json'),
				StatusCodeInterface::STATUS_FORBIDDEN,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/forbidden.json',
			],
			'expiredToken' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa',
				'Bearer ' . self::EXPIRED_TOKEN,
				file_get_contents(__DIR__ . '/../../../fixtures/Controllers/requests/devices.create.json'),
				StatusCodeInterface::STATUS_UNAUTHORIZED,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/unauthorized.json',
			],
			'notAllowed' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa',
				'Bearer ' . self::VALID_TOKEN_USER,
				file_get_contents(__DIR__ . '/../../../fixtures/Controllers/requests/devices.update.json'),
				StatusCodeInterface::STATUS_FORBIDDEN,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/forbidden.json',
			],
		];
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 * @throws Utils\JsonException
	 *
	 * @dataProvider devicesDelete
	 */
	public function testDelete(string $url, string|null $token, int $statusCode, string $fixture): void
	{
		$router = $this->getContainer()->getByType(SlimRouter\Routing\IRouter::class);

		$headers = [];

		if ($token !== null) {
			$headers['authorization'] = $token;
		}

		$request = new ServerRequest(
			RequestMethodInterface::METHOD_DELETE,
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
	public function devicesDelete(): array
	{
		return [
			// Valid responses
			//////////////////
			'delete' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_NO_CONTENT,
				__DIR__ . '/../../../fixtures/Controllers/responses/devices.delete.json',
			],

			// Invalid responses
			////////////////////
			'unknown' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009af',
				'Bearer ' . self::VALID_TOKEN,
				StatusCodeInterface::STATUS_NOT_FOUND,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/notFound.json',
			],
			'missingToken' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa',
				null,
				StatusCodeInterface::STATUS_FORBIDDEN,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/forbidden.json',
			],
			'invalidToken' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa',
				'Bearer ' . self::INVALID_TOKEN,
				StatusCodeInterface::STATUS_UNAUTHORIZED,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/unauthorized.json',
			],
			'emptyToken' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa',
				'',
				StatusCodeInterface::STATUS_FORBIDDEN,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/forbidden.json',
			],
			'expiredToken' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa',
				'Bearer ' . self::EXPIRED_TOKEN,
				StatusCodeInterface::STATUS_UNAUTHORIZED,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/unauthorized.json',
			],
			'notAllowed' => [
				'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa',
				'Bearer ' . self::VALID_TOKEN_USER,
				StatusCodeInterface::STATUS_FORBIDDEN,
				__DIR__ . '/../../../fixtures/Controllers/responses/generic/forbidden.json',
			],
		];
	}

}
