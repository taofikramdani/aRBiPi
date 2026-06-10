<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();
        $database = (string) config('database.connections.'.config('database.default').'.database');

        if (! $app->environment('testing') || ! str_ends_with($database, '_test')) {
            throw new RuntimeException(
                "Test dibatalkan untuk melindungi database. Environment: {$app->environment()}, database: {$database}."
            );
        }

        return $app;
    }
}
