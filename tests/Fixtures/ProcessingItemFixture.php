<?php

namespace GloCurrency\GlobusBank\Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use GloCurrency\MiddlewareBlocks\Contracts\ProcessingItemInterface as MProcessingItemInterface;
use GloCurrency\GlobusBank\Tests\Database\Factories\ProcessingItemFixtureFactory;
use BrokeYourBike\BaseModels\BaseUuid;

//  implements MProcessingItemInterface
class ProcessingItemFixture extends BaseUuid
{
    use HasFactory;

    protected $table = 'processing_items';

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return ProcessingItemFixtureFactory::new();
    }
}
