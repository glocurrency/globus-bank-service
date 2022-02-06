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

    /**
     * Create the HTTP mock for API.
     *
     * @return array<\GuzzleHttp\Handler\MockHandler|\GuzzleHttp\HandlerStack> [$httpMock, $handlerStack]
     */
    protected function mockApiFor(string $class): array
    {
        $httpMock = new \GuzzleHttp\Handler\MockHandler();
        $handlerStack = \GuzzleHttp\HandlerStack::create($httpMock);

        $this->app->when($class)
            ->needs(\GuzzleHttp\ClientInterface::class)
            ->give(function () use ($handlerStack) {
                return new \GuzzleHttp\Client(['handler' => $handlerStack]);
            });

        return [$httpMock, $handlerStack];
    }
}
