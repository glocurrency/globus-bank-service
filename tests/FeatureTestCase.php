<?php

namespace GloCurrency\GlobusBank\Tests;

use Money\Formatter\DecimalMoneyFormatter;
use Money\Currencies\ISOCurrencies;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class FeatureTestCase extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton(DecimalMoneyFormatter::class, function () {
            return new DecimalMoneyFormatter(new ISOCurrencies());
        });
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }
}
