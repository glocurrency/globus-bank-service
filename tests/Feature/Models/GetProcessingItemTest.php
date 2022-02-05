<?php

namespace GloCurrency\GlobusBank\Tests\Feature\Models;

use Illuminate\Support\Facades\Event;
use GloCurrency\GlobusBank\Tests\Fixtures\ProcessingItemFixture;
use GloCurrency\GlobusBank\Tests\FeatureTestCase;
use GloCurrency\GlobusBank\Models\Transaction;
use GloCurrency\GlobusBank\GlobusBank;
use GloCurrency\GlobusBank\Events\TransactionCreatedEvent;

class GetProcessingItemTest extends FeatureTestCase
{
    /** @test */
    public function it_can_get_processing_item(): void
    {
        Event::fake([
            TransactionCreatedEvent::class,
        ]);

        $processingItem = ProcessingItemFixture::factory()->create();
        $this->assertSame(GlobusBank::$processingItemModel, $processingItem::class);

        $targetTransaction = Transaction::factory()->create([
            'processing_item_id' => $processingItem->id,
        ]);

        $this->assertSame($processingItem->id, $targetTransaction->fresh()->processingItem->id);
    }
}
