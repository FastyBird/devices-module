<?php declare(strict_types = 1);

/**
 * PagingMiddleware.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Middleware
 * @since          0.1.0
 *
 * @date           25.05.20
 */

namespace FastyBird\DevicesModule\Middleware;

use FastyBird\WebServer\Http as WebServerHttp;
use IPub\DoctrineOrmQuery;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * Records paging handling middleware
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Middleware
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class PagingMiddleware implements MiddlewareInterface
{

	/**
	 * @param ServerRequestInterface $request
	 * @param RequestHandlerInterface $handler
	 *
	 * @return ResponseInterface
	 *
	 * @throws Throwable
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$response = $handler->handle($request);

		if ($response instanceof WebServerHttp\Response) {
			$entity = $response->getEntity();

			if ($entity instanceof WebServerHttp\ScalarEntity) {
				$data = $entity->getData();

				if ($data instanceof DoctrineOrmQuery\ResultSet) {
					if (array_key_exists('page', $request->getQueryParams())) {
						$queryParams = $request->getQueryParams();

						$pageOffset = isset($queryParams['page']['offset']) ? (int) $queryParams['page']['offset'] : null;
						$pageLimit = isset($queryParams['page']['limit']) ? (int) $queryParams['page']['limit'] : null;

					} else {
						$pageOffset = null;
						$pageLimit = null;
					}

					if ($pageOffset !== null && $pageLimit !== null) {
						if ($data->getTotalCount() > $pageLimit) {
							$data->applyPaging($pageOffset, $pageLimit);
						}

						$response = $response
							->withAttribute(WebServerHttp\ResponseAttributes::ATTR_TOTAL_COUNT, $data->getTotalCount())
							->withEntity(WebServerHttp\ScalarEntity::from($data->toArray()));

					} else {
						$response = $response
							->withEntity(WebServerHttp\ScalarEntity::from($data->toArray()));
					}
				}
			}
		}

		return $response;
	}

}
