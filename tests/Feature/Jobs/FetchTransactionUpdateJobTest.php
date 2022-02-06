<?php

namespace GloCurrency\GlobusBank\Tests\Feature\Jobs;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use GloCurrency\GlobusBank\Tests\FeatureTestCase;
use GloCurrency\GlobusBank\Models\Transaction;
use GloCurrency\GlobusBank\Jobs\FetchTransactionUpdateJob;
use GloCurrency\GlobusBank\Events\TransactionCreatedEvent;
use GloCurrency\GlobusBank\Enums\TransactionStateCodeEnum;
use BrokeYourBike\GlobusBank\Enums\PaymentStatusEnum;
use BrokeYourBike\GlobusBank\Enums\ErrorCodeEnum;
use BrokeYourBike\GlobusBank\Client;

class FetchTransactionUpdateJobTest extends FeatureTestCase
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
    public function it_can_update_state_code()
    {
        $targetTransaction = Transaction::factory()->create([
            'state_code' => TransactionStateCodeEnum::PROCESSING,
            'batch_reference' => 'batch-1',
        ]);

        [$httpMock] = $this->mockApiFor(Client::class);
        $httpMock->append($this->makeAuthResponse());
        $httpMock->append(new \GuzzleHttp\Psr7\Response(200, [], '{
            "result": [
                {
                    "id": 12341234,
                    "batchId": "batch-1",
                    "sourceAccount": "ACME INC",
                    "paymentStatus": "'. PaymentStatusEnum::SUCCESSFUL->value .'",
                    "paymentType": "Other Payment",
                    "paymentMethod": "INSTANT PAYMENT",
                    "paymentDate": "2022-01-28T16:20:45",
                    "valueDate": "2022-01-28T00:00:00",
                    "amount": 100,
                    "transactionRef": "23562356",
                    "beneficiaryAccount": "43653456235",
                    "beneficiaryName": "JOHN DOE",
                    "narration": "00124",
                    "singeNarration": "00124",
                    "subsidiaryName": "ACME INC"
                }
            ],
            "responseCode": "'. ErrorCodeEnum::SUCCESS->value .'",
            "responseDescription": "Successful"
        }'));

        FetchTransactionUpdateJob::dispatchSync($targetTransaction);

        $targetTransaction = $targetTransaction->fresh();
        $this->assertInstanceOf(Transaction::class, $targetTransaction);

        $this->assertEquals(TransactionStateCodeEnum::PAID, $targetTransaction->state_code);
    }
}
