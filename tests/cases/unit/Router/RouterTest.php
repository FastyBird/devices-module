<?php declare(strict_types = 1);

namespace FastyBird\Module\Devices\Tests\Cases\Unit\Router;

use FastyBird\Library\Bootstrap\Exceptions as BootstrapExceptions;
use FastyBird\Library\Metadata;
use FastyBird\Module\Devices\Exceptions;
use FastyBird\Module\Devices\Tests\Cases\Unit\DbTestCase;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use InvalidArgumentException;
use IPub\SlimRouter;
use Nette;
use React\Http\Message\ServerRequest;
use RuntimeException;

const VALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjQ3MTBlOTYtYTZmYi00ZmM3LWFhMzAtNDc'
	. 'yNzkwNWQzMDRjIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pb'
	. 'mlzdHJhdG9yIl19.QH_Oo_uzTXAb3pNnHvXYnnX447nfVq2_ggQ9ZxStu4s';

final class RouterTest extends DbTestCase
{

	public function setUp(): void
	{
		$this->registerNeonConfigurationFile(__DIR__ . '/prefixedRoutes.neon');

		parent::setUp();
	}

	/**
	 * @throws BootstrapExceptions\InvalidArgument
	 * @throws Exceptions\InvalidArgument
	 * @throws InvalidArgumentException
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 *
	 * @dataProvider prefixedRoutes
	 */
	public function testPrefixedRoutes(string $url, string $token, int $statusCode): void
	{
		$router = $this->getContainer()->getByType(SlimRouter\Routing\IRouter::class);

		$headers = [
			'authorization' => $token,
		];

		$request = new ServerRequest(
			RequestMethodInterface::METHOD_GET,
			$url,
			$headers,
		);

		$response = $router->handle($request);

		self::assertSame($statusCode, $response->getStatusCode());
	}

	/**
	 * @return array<string, array<string|int>>
	 */
	public static function prefixedRoutes(): array
	{
		return [
			'readAllValid' => [
				'/' . Metadata\Constants::MODULE_DEVICES_PREFIX . '/v1/devices',
				'Bearer ' . VALID_TOKEN,
				StatusCodeInterface::STATUS_OK,
			],
			'readAllInvalid' => [
				'/v1/devices',
				'Bearer ' . VALID_TOKEN,
				StatusCodeInterface::STATUS_NOT_FOUND,
			],
		];
	}

}
