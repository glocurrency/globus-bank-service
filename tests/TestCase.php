<?php

namespace GloCurrency\GlobusBank\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use GloCurrency\GlobusBank\Tests\Fixtures\TransactionFixture;
use GloCurrency\GlobusBank\Tests\Fixtures\ProcessingItemFixture;
use GloCurrency\GlobusBank\GlobusBankServiceProvider;
use GloCurrency\GlobusBank\GlobusBank;

abstract class TestCase extends OrchestraTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        GlobusBank::useTransactionModel(TransactionFixture::class);
        GlobusBank::useProcessingItemModel(ProcessingItemFixture::class);
    }

    protected function getPackageProviders($app)
    {
        return [GlobusBankServiceProvider::class];
    }
}
