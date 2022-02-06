<?php

namespace GloCurrency\GlobusBank\Tests\Feature\Jobs;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use GloCurrency\GlobusBank\Tests\FeatureTestCase;
use GloCurrency\GlobusBank\Models\Transaction;
use GloCurrency\GlobusBank\Jobs\SendTransactionJob;
use GloCurrency\GlobusBank\Jobs\FetchTransactionUpdateJob;
use GloCurrency\GlobusBank\Exceptions\SendTransactionException;
use GloCurrency\GlobusBank\Events\TransactionCreatedEvent;
use GloCurrency\GlobusBank\Enums\TransactionStateCodeEnum;
use BrokeYourBike\GlobusBank\Enums\ErrorCodeEnum;
use BrokeYourBike\GlobusBank\Client;

class SendTransactionJobTest extends FeatureTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([
            TransactionCreatedEvent::class,
        ]);
    }

    private function makeAuthResponse(): \GuzzleHttp\Psr7\Response
    {
        return new \GuzzleHttp\Psr7\Response(200, [], '{
            "corpCode": "john",
            "token": "super-secure-token",
            "responseCode": "'. ErrorCodeEnum::SUCCESS->value .'",
            "responseDescription": "Successful"
        }');
    }

    /** @test */
    public function it_will_throw_if_cannot_payout_transaction(): void
    {
        $targetTransaction = Transaction::factory()->create([
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
        ]);

        [$httpMock] = $this->mockApiFor(Client::class);
        $httpMock->append($this->makeAuthResponse());
        $httpMock->append(new \GuzzleHttp\Psr7\Response(200, [], '{}')); // will throw

        $this->expectExceptionMessage('Cannot assign null to property');
        $this->expectException(SendTransactionException::class);

        SendTransactionJob::dispatchSync($targetTransaction);
    }

    /** @test */
    public function it_will_throw_if_result_code_is_unexpected(): void
    {
        $targetTransaction = Transaction::factory()->create([
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
        ]);

        [$httpMock] = $this->mockApiFor(Client::class);
        $httpMock->append($this->makeAuthResponse());
        $httpMock->append(new \GuzzleHttp\Psr7\Response(200, [], '{
            "corpCode": "john",
            "accountNumber": "123465798",
            "batchReference": "TEST000001",
            "responseCode": "NOT-A-VALID-CODE",
            "responseDescription": "Successful"
        }'));

        $this->expectExceptionMessage('Unexpected ' . ErrorCodeEnum::class . ': `NOT-A-VALID-CODE`');
        $this->expectException(SendTransactionException::class);

        SendTransactionJob::dispatchSync($targetTransaction);
    }

    /** @test */
    public function it_will_update_state_if_exception_occured()
    {
        $targetTransaction = Transaction::factory()->create([
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
            'state_code_reason' => 'random-reason',
        ]);

        [$httpMock] = $this->mockApiFor(Client::class);
        $httpMock->append($this->makeAuthResponse());
        $httpMock->append(new \GuzzleHttp\Psr7\Response(200, [], '{}'));

        try {
            SendTransactionJob::dispatchSync($targetTransaction);
        } catch (\Throwable $th) {
            $this->assertInstanceOf(SendTransactionException::class, $th);
            $this->assertStringContainsString('Cannot assign null to property', $th->getMessage());

            $this->assertEquals(TransactionStateCodeEnum::API_REQUEST_EXCEPTION, $targetTransaction->fresh()->state_code);
            $this->assertStringContainsString('Cannot assign null to property', $targetTransaction->fresh()->state_code_reason);
            return;
        }

        $this->fail('Exception was not thrown');
    }

    /** @test */
    public function it_can_send_transaction(): void
    {
        Bus::fake([FetchTransactionUpdateJob::class]);

        /** @var Transaction */
        $targetTransaction = Transaction::factory()->create([
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
            'state_code_reason' => null,
        ]);

        [$httpMock] = $this->mockApiFor(Client::class);
        $httpMock->append($this->makeAuthResponse());
        $httpMock->append(new \GuzzleHttp\Psr7\Response(200, [], '{
            "corpCode": "john",
            "accountNumber": "123465798",
            "batchReference": "TEST000001",
            "responseCode": "'. ErrorCodeEnum::SUCCESS->value .'",
            "responseDescription": "Successful"
        }'));

        SendTransactionJob::dispatchSync($targetTransaction);

        /** @var Transaction */
        $targetTransaction = $targetTransaction->fresh();

        $this->assertEquals(TransactionStateCodeEnum::PROCESSING, $targetTransaction->state_code);
        $this->assertEquals(ErrorCodeEnum::SUCCESS, $targetTransaction->error_code);
        $this->assertSame('Successful', $targetTransaction->error_code_description);
        $this->assertSame('TEST000001', $targetTransaction->batch_reference);

        Bus::assertDispatchedTimes(FetchTransactionUpdateJob::class, 1);
    }
}
