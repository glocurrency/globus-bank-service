<?php

namespace Tests\Feature\Commands\GlobusBank;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use GloCurrency\GlobusBank\Tests\FeatureTestCase;
use GloCurrency\GlobusBank\Models\Transaction;
use GloCurrency\GlobusBank\Jobs\FetchTransactionUpdateJob;
use GloCurrency\GlobusBank\Events\TransactionUpdatedEvent;
use GloCurrency\GlobusBank\Events\TransactionCreatedEvent;
use GloCurrency\GlobusBank\Enums\TransactionStateCodeEnum;

class FetchTransactionsUpdateCommandTest extends FeatureTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([
            TransactionCreatedEvent::class,
            TransactionUpdatedEvent::class,
        ]);
    }

    /** @test */
    public function exit_if_no_transactions_found(): void
    {
        Queue::fake();

        $transaction = Transaction::factory()->create();
        $transaction->delete();

        $this->artisan('globus:fetch-update')
            ->expectsOutput('You do not have any unfinished GlobusBank/Transaction')
            ->assertExitCode(0);

        Queue::assertNotPushed(FetchTransactionUpdateJob::class);
    }

    /**
     * @test
     * @dataProvider transactionStateCodeProvider
     * */
    public function dispatch_job_for_transactions_with_state_code(TransactionStateCodeEnum $stateCode, int $dispatchCount): void
    {
        Bus::fake([FetchTransactionUpdateJob::class]);

        Transaction::factory()->create([
            'state_code' => $stateCode,
        ]);

        $this->artisan('globus:fetch-update')
            ->assertExitCode(0);

        Bus::assertDispatchedTimes(FetchTransactionUpdateJob::class, $dispatchCount);
    }

    public function transactionStateCodeProvider(): array
    {
        $states = collect(TransactionStateCodeEnum::cases())
            ->filter(fn($c) => !in_array($c, [
                TransactionStateCodeEnum::PROCESSING,
            ]))
            ->flatten()
            ->map(fn($c) => [$c, 0])
            ->toArray();

        $states[] = [TransactionStateCodeEnum::PROCESSING, 1];

        return $states;
    }
}
