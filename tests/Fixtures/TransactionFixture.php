<?php

namespace GloCurrency\GlobusBank\Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use GloCurrency\GlobusBank\Tests\Database\Factories\TransactionFixtureFactory;
use BrokeYourBike\BaseModels\BaseUuid;

// implements MTransactionInterface
class TransactionFixture extends BaseUuid
{
    use HasFactory;

    protected $table = 'transactions';

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return TransactionFixtureFactory::new();
    }
}
