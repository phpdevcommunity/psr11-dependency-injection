<?php

namespace Test\PhpDevCommunity\DependencyInjection;


use PhpDevCommunity\DependencyInjection\Container;
use PhpDevCommunity\DependencyInjection\Exception\NotFoundException;
use Psr\Container\ContainerInterface;
use Test\PhpDevCommunity\DependencyInjection\TestClass\Database;
use  Test\PhpDevCommunity\DependencyInjection\TestClass\LazyService;

/**
 * Class AutoWireTest
 * @package Test\PhpDevCommunity\DependencyInjection
 */
class ContainerTest extends \PhpDevCommunity\UniTester\TestCase
{

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
      $this->testDefinition();
      $this->testNotFoundClass();
      $this->testNotFoundParameter();

    }

    public function testDefinition()
    {
        $container = new Container([
            'database.host' => '127.0.0.1',
            'database.port' => null,
            Database::class => static function (ContainerInterface $container) {
                return new Database();
            },
            LazyService::class => static function (ContainerInterface $container) {
                return new LazyService($container->get(Database::class));
            }
        ]);


        $database = $container->get(Database::class);
        /**
         * @var LazyService $service
         */
        $service = $container->get(LazyService::class);

        $this->assertEquals('127.0.0.1', $container->get('database.host'));
        $this->assertEquals(null, $container->get('database.port'));
        $this->assertInstanceOf(Database::class, $database);
        $this->assertInstanceOf(LazyService::class, $service);

        $this->assertStrictEquals($database, $service->getDatabase());
        $this->assertTrue($container->has(LazyService::class));
        $this->assertFalse($container->has('database.user'));
    }

    public function testNotFoundClass()
    {

        $container = new Container([]);

        $this->expectException(NotFoundException::class, function () use ($container) {
            $container->get(LazyService::class);
        });
    }

    public function testNotFoundParameter()
    {

        $container = new Container([]);

        $this->expectException(NotFoundException::class, function () use ($container) {
            $container->get('database.user');
        });
    }
}
