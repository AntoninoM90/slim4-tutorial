<?php

namespace App\Test;

use DI\Container;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use UnexpectedValueException;

/**
 * Container Trait.
 */
trait AppTestTrait
{
    /** @var ContainerInterface|Container */
    protected $container;

    /** @var App */
    protected $app;

    /**
     * Bootstrap app.
     *
     * @throws UnexpectedValueException
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->app = require __DIR__ . '/../config/bootstrap.php';

        $container = $this->app->getContainer();
        if ($container === null) {
            throw new UnexpectedValueException('Container must be initialized');
        }

        $this->container = $container;
    }

    /**
     * Add mock to container.
     *
     * @param string $class The class or interface
     *
     * @return MockObject The mock
     */
    protected function mock(string $class): MockObject
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException(sprintf('Class not found: %s', $class));
        }

        $mock = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();

        if ($this->container instanceof Container) {
            $this->container->set($class, $mock);
        }

        return $mock;
    }

    /**
     * Create a mocked class method.
     *
     * @param array|callable $method The class and method
     *
     * @return InvocationMocker The mocker
     */
    protected function mockMethod($method): InvocationMocker
    {
        return $this->mock((string)$method[0])->method((string)$method[1]);
    }

    /**
     * Create a server request.
     *
     * @param string $method The HTTP method
     * @param string|UriInterface $uri The URI
     * @param array $serverParams The server parameters
     *
     * @return ServerRequestInterface
     */
    protected function createRequest(
        string $method,
        $uri,
        array $serverParams = []
    ): ServerRequestInterface {
        return (new ServerRequestFactory())->createServerRequest($method, $uri, $serverParams);
    }

    /**
     * Add Json data.
     *
     * @param ServerRequestInterface $request The request
     * @param mixed[] $data The data
     *
     * @return ServerRequestInterface
     */
    protected function withJson(
        ServerRequestInterface $request,
        array $data
    ): ServerRequestInterface {
        $request = $request->withParsedBody($data);

        return $request->withHeader('Content-Type', 'application/json');
    }

    /**
     * Make request.
     *
     * @param ServerRequestInterface $request The request
     *
     * @return ResponseInterface
     */
    protected function request(ServerRequestInterface $request): ResponseInterface
    {
        return $this->app->handle($request);
    }
}
