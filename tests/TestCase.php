<?php

namespace GloCurrency\GlobusBank\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use GloCurrency\GlobusBank\GlobusBankServiceProvider;
use GloCurrency\GlobusBank\GlobusBank;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [GlobusBankServiceProvider::class];
    }
}