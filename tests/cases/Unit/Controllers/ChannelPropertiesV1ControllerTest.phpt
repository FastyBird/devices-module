<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\DevicesModule\Connections;
use FastyBird\DevicesModule\Router;
use FastyBird\WebServer\Http;
use Fig\Http\Message\RequestMethodInterface;
use Mockery;
use PHPOnCouch;
use Ramsey\Uuid;
use React\Http\Message\ServerRequest;
use stdClass;
use Tester\Assert;
use Tests\Tools;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../DbTestCase.php';

/**
 * @testCase
 */
final class ChannelPropertiesV1ControllerTest extends DbTestCase
{

	public function setUp(): void
	{
		parent::setUp();

		$doc = new stdClass();
		$doc->id = Uuid\Uuid::uuid4();

		$docs = [];
		$docs[] = $doc;

		$storageClient = Mockery::mock(PHPOnCouch\CouchClient::class);
		$storageClient
			->shouldReceive('asCouchDocuments')
			->getMock()
			->shouldReceive('find')
			->andReturn($docs)
			->getMock()
			->shouldReceive('storeDoc')
			->getMock();

		$storageConnection = Mockery::mock(Connections\CouchDbConnection::class);
		$storageConnection
			->shouldReceive('getClient')
			->andReturn($storageClient)
			->getMock();

		$this->mockContainerService(
			Connections\CouchDbConnection::class,
			$storageConnection
		);
	}

	/**
	 * @param string $url
	 * @param string|null $token
	 * @param int $statusCode
	 * @param string $fixture
	 *
	 * @dataProvider ./../../../fixtures/Controllers/channelPropertiesRead.php
	 */
	public function testRead(string $url, ?string $token, int $statusCode, string $fixture): void
	{
		/** @var Router\Router $router */
		$router = $this->getContainer()->getByType(Router\Router::class);

		$headers = [];

		if ($token !== null) {
			$headers['authorization'] = $token;
		}

		$request = new ServerRequest(
			RequestMethodInterface::METHOD_GET,
			$url,
			$headers
		);

		$response = $router->handle($request);

		Tools\JsonAssert::assertFixtureMatch(
			$fixture,
			(string) $response->getBody()
		);
		Assert::same($statusCode, $response->getStatusCode());
		Assert::type(Http\Response::class, $response);
	}

}

$test_case = new ChannelPropertiesV1ControllerTest();
$test_case->run();
