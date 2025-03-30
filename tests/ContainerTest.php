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
        $this->testVariableExtractionAndReplacement();
        $this->testVariableReplacementWithMissingValues();
        $this->testNestedVariableReplacement();
        $this->testVariableReplacementWithSpecialCharacters();
        $this->testNoVariableToReplace();
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

    public function testVariableExtractionAndReplacement()
    {
        $container = new Container([
            'database.host' => '127.0.0.1',
            'database.port' => '3306',
            'database.user' => 'root',
            'database.dsn' => 'mysql://${database.user}@${database.host}:${database.port}/mydb'
        ]);

        $this->assertEquals('mysql://root@127.0.0.1:3306/mydb', $container->get('database.dsn'));
    }

    public function testVariableReplacementWithMissingValues()
    {
        $container = new Container([
            'database.host' => '127.0.0.1',
            'database.dsn' => 'mysql://${database.user}@${database.host}:${database.port}/mydb'
        ]);


        $this->expectException(NotFoundException::class, function () use ($container) {
            $container->get('database.dsn');
        });
    }

    public function testNestedVariableReplacement()
    {
        $container = new Container([
            'base.url' => '127.0.0.1',
            'api.url' => 'http://${base.url}/api',
            'api.endpoint' => '${api.url}/v1/resource'
        ]);

        $this->assertEquals('http://127.0.0.1/api/v1/resource', $container->get('api.endpoint'));
    }

    public function testVariableReplacementWithSpecialCharacters()
    {
        $container = new Container([
            'user.name' => 'admin@domain.com',
            'database.dsn' => 'mysql://${user.name}:pass@localhost/db'
        ]);
        $this->assertEquals('mysql://admin@domain.com:pass@localhost/db', $container->get('database.dsn'));
    }

    public function testNoVariableToReplace()
    {
        $container = new Container([
            'app.name' => 'MySuperApp'
        ]);

        // Aucune substitution ne doit être faite
        $this->assertEquals('MySuperApp', $container->get('app.name'));
    }
}
