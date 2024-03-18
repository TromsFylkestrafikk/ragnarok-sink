<?php

namespace Ragnarok\Sink\Tests;

use Ragnarok\Sink\SinkServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function setup(): void
    {
        parent::setUp();
        // $this->withoutExceptionHandling();
        // $this->artisan('migrate', ['--database' => 'testing']);

        // $this->loadMigrationsFrom(__DIR__ . '/../src/database/migrations');
        // $this->loadLaravelMigrations(['--database' => 'testing']);

        // $this->withFactories(__DIR__ . '/../src/database/factories');
    }

    protected function getEnvironmentSetUp($app)
    {
        // $app['config']->set('app.key', 'AckfSECXIvnK5r28GVIWUAxmbBSjTsmF');
        // $app['config']->set('database.default', 'testing');
        // $app['config']->set('database.connections.testing', [
        //     'driver'   => 'sqlite',
        //     'database' => ':memory:',
        //     'prefix'   => '',
        // ]);
        include_once __DIR__ .'/2024_03_14_100000_test_table.php';
        (new \TestTable)->up();
    }

    protected function getPackageProviders($app)
    {
        return [SinkServiceProvider::class];
    }
}
