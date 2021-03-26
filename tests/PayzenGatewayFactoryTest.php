<?php

namespace Ekyna\Component\Payum\Payzen\Tests;

use Ekyna\Component\Payum\Payzen\Api\Api;
use Ekyna\Component\Payum\Payzen\PayzenGatewayFactory;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Class PayzenGatewayFactoryTest
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class PayzenGatewayFactoryTest extends TestCase
{
    public function test_extends_GatewayFactory()
    {
        $rc = new ReflectionClass(PayzenGatewayFactory::class);

        $this->assertTrue($rc->isSubclassOf(GatewayFactory::class));
    }

    public function test_construct_without_any_arguments()
    {
        /** @noinspection PhpExpressionResultUnusedInspection */
        new PayzenGatewayFactory();

        $this->assertTrue(true);
    }

    public function test_create_gateway()
    {
        $factory = new PayzenGatewayFactory();

        $gateway = $factory->create([
            'ctx_mode'    => Api::MODE_PRODUCTION,
            'site_id'     => '123456',
            'certificate' => '123456',
            'directory'   => __DIR__ . '/../cache',
        ]);

        $this->assertInstanceOf('Payum\Core\Gateway', $gateway);
    }

    public function test_create_config()
    {
        $factory = new PayzenGatewayFactory();

        $config = $factory->createConfig();

        $this->assertNotEmpty($config);
    }

    public function test_config_defaults_passed_in_constructor()
    {
        $factory = new PayzenGatewayFactory([
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ]);

        $config = $factory->createConfig();

        $this->assertArrayHasKey('foo', $config);
        $this->assertEquals('fooVal', $config['foo']);

        $this->assertArrayHasKey('bar', $config);
        $this->assertEquals('barVal', $config['bar']);
    }

    public function test_config_contains_factory_name_and_title()
    {
        $factory = new PayzenGatewayFactory();

        $config = $factory->createConfig();

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertEquals('payzen', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertEquals('Payzen', $config['payum.factory_title']);
    }

    public function test_throw_if_required_options_not_passed()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The site_id, certificate, ctx_mode, directory fields are required.');

        $factory = new PayzenGatewayFactory();

        $factory->create();
    }

    public function test_configure_paths()
    {
        $factory = new PayzenGatewayFactory();

        $config = $factory->createConfig();

        $this->assertNotEmpty($config);
        $this->assertNotEmpty($config['payum.paths']);

        $this->assertArrayHasKey('PayumCore', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['PayumCore']);
        $this->assertTrue(file_exists($config['payum.paths']['PayumCore']));
    }
}
