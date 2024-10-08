<?php

namespace Test\PhpDevCommunity\DependencyInjection;



use PhpDevCommunity\DependencyInjection\Container;
use PhpDevCommunity\DependencyInjection\Exception\ContainerException;
use PhpDevCommunity\DependencyInjection\ReflectionResolver;
use PhpDevCommunity\UniTester\TestCase;
use Test\PhpDevCommunity\DependencyInjection\TestClass\Database;
use  Test\PhpDevCommunity\DependencyInjection\TestClass\LazyService;
use  Test\PhpDevCommunity\DependencyInjection\TestClass\Mailer;
use  Test\PhpDevCommunity\DependencyInjection\TestClass\Parameters;

/**
 * Class AutoWireTest
 * @package Test\PhpDevCommunity\DependencyInjection
 */
class AutoWireTest extends TestCase {

    protected function setUp(): void
    {
        // TODO: Implement setUp() method.
    }

    protected function tearDown(): void
    {
        // TODO: Implement tearDown() method.
    }

    protected function execute(): void
    {
        $this->testAutoWire();
        $this->testAutoWireDefaultParameter();
        $this->testAutoWireInverse();
    }

    public function testAutoWire()
    {
        $container = new Container([], new ReflectionResolver());

        $this->assertTrue($container->has(LazyService::class));
        $this->assertTrue($container->has(Database::class));

        $database = $container->get(Database::class);
        /**
         * @var LazyService $service
         */
        $service = $container->get(LazyService::class);
        $this->assertInstanceOf(LazyService::class, $service);
        $this->assertInstanceOf(Database::class, $database);
        $this->assertStrictEquals($database, $service->getDatabase());
    }

    public function testAutoWireDefaultParameter()
    {
        $container = new Container([], new ReflectionResolver());
        $this->assertInstanceOf(Parameters::class, $container->get(Parameters::class));

        $this->expectException(ContainerException::class, function () use ($container) {
            $container->get(Mailer::class);
        });
    }

    public function testAutoWireInverse()
    {
        $container = new Container([], new ReflectionResolver());

        /**
         * @var LazyService $service
         */
        $service = $container->get(LazyService::class);
        $this->assertStrictEquals($container->get(Database::class), $service->getDatabase());
    }

}
