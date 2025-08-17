<?php

namespace Saidabdulsalam\LaravelMemo\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Saidabdulsalam\LaravelMemo\MemoServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            MemoServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('memo.members_models', [\Saidabdulsalam\LaravelMemo\Tests\Models\User::class]);
        $app['config']->set('memo.user_department_id_column', 'department_id');
        $app['config']->set('memo.user_office_id_column', 'office_id');
    }
}
