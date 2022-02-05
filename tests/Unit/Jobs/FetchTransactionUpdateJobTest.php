<?php

namespace GloCurrency\GlobusBank\Tests\Unit\Jobs;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use GloCurrency\MiddlewareBlocks\Enums\QueueTypeEnum as MQueueTypeEnum;
use GloCurrency\GlobusBank\Tests\TestCase;
use GloCurrency\GlobusBank\Models\Transaction;
use GloCurrency\GlobusBank\Jobs\FetchTransactionUpdateJob;

class FetchTransactionUpdateJobTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_tries_defined(): void
    {
        $transaction = new Transaction();

        $job = (new FetchTransactionUpdateJob($transaction));
        $this->assertSame(1, $job->tries);
    }

    /** @test */
    public function it_will_execute_after_commit()
    {
        $transaction = new Transaction();

        $job = (new FetchTransactionUpdateJob($transaction));
        $this->assertTrue($job->afterCommit);
    }

    /** @test */
    public function it_has_dispatch_queue_specified()
    {
        $transaction = new Transaction();

        $job = (new FetchTransactionUpdateJob($transaction));
        $this->assertEquals(MQueueTypeEnum::SERVICES->value, $job->queue);
    }

    /** @test */
    public function it_implements_should_be_unique(): void
    {
        $transaction = new Transaction();

        $job = (new FetchTransactionUpdateJob($transaction));
        $this->assertInstanceOf(ShouldBeUnique::class, $job);
        $this->assertSame($transaction->id, $job->uniqueId());
    }

    /** @test */
    public function it_implements_should_be_encrypted(): void
    {
        $transaction = new Transaction();

        $job = (new FetchTransactionUpdateJob($transaction));
        $this->assertInstanceOf(ShouldBeEncrypted::class, $job);
    }
}
