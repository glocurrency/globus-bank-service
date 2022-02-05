<?php

namespace GloCurrency\GlobusBank\Tests\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use GloCurrency\MiddlewareBlocks\Enums\QueueTypeEnum as MQueueTypeEnum;
use GloCurrency\MiddlewareBlocks\Contracts\ProcessingItemInterface as MProcessingItemInterface;
use GloCurrency\GlobusBank\Tests\TestCase;
use GloCurrency\GlobusBank\Jobs\CreateTransactionJob;

class CreateTransactionJobTest extends TestCase
{
    /** @test */
    public function it_has_tries_defined(): void
    {
        /** @var MProcessingItemInterface */
        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();

        $job = (new CreateTransactionJob($processingItem));
        $this->assertSame(1, $job->tries);
    }

    /** @test */
    public function it_will_execute_after_commit()
    {
        /** @var MProcessingItemInterface */
        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();

        $job = (new CreateTransactionJob($processingItem));
        $this->assertTrue($job->afterCommit);
    }

    /** @test */
    public function it_has_dispatch_queue_specified()
    {
        /** @var MProcessingItemInterface */
        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();

        $job = (new CreateTransactionJob($processingItem));
        $this->assertEquals(MQueueTypeEnum::SERVICES->value, $job->queue);
    }

    /** @test */
    public function it_implements_should_be_unique(): void
    {
        /** @var MProcessingItemInterface */
        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();

        $job = (new CreateTransactionJob($processingItem));
        $this->assertInstanceOf(ShouldBeUnique::class, $job);
        $this->assertSame($processingItem->getId(), $job->uniqueId());
    }

    /** @test */
    public function it_implements_should_be_encrypted(): void
    {
        /** @var MProcessingItemInterface */
        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();

        $job = (new CreateTransactionJob($processingItem));
        $this->assertInstanceOf(ShouldBeEncrypted::class, $job);
    }
}
