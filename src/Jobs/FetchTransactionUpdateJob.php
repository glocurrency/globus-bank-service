<?php

namespace GloCurrency\GlobusBank\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Bus\Queueable;
use GloCurrency\MiddlewareBlocks\Enums\QueueTypeEnum as MQueueTypeEnum;
use GloCurrency\GlobusBank\Models\Transaction;
use GloCurrency\GlobusBank\Exceptions\FetchTransactionUpdateException;
use GloCurrency\GlobusBank\Enums\TransactionStateCodeEnum;
use BrokeYourBike\GlobusBank\Enums\PaymentStatusEnum;
use BrokeYourBike\GlobusBank\Enums\ErrorCodeEnum;
use BrokeYourBike\GlobusBank\Client;

class FetchTransactionUpdateJob implements ShouldQueue, ShouldBeUnique, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    private Transaction $targetTransaction;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Transaction $targetTransaction)
    {
        $this->targetTransaction = $targetTransaction;
        $this->afterCommit();
        $this->onQueue(MQueueTypeEnum::SERVICES->value);
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return $this->targetTransaction->id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (TransactionStateCodeEnum::PROCESSING !== $this->targetTransaction->state_code) {
            throw FetchTransactionUpdateException::stateNotAllowed($this->targetTransaction);
        }

        if (empty($this->targetTransaction->batch_reference)) {
            throw FetchTransactionUpdateException::emptyBatchReference($this->targetTransaction);
        }

        try {
            $api = app()->make(Client::class);
            $response = $api->getBatchStatus($this->targetTransaction->batch_reference);
        } catch (\Throwable $e) {
            report($e);
            throw FetchTransactionUpdateException::apiRequestException($e);
        }

        $errorCode = ErrorCodeEnum::tryFrom($response->responseCode);

        if (!$errorCode) {
            throw FetchTransactionUpdateException::unexpectedErrorCode($response->responseCode);
        }

        if (empty($response->result)) {
            throw FetchTransactionUpdateException::emptyBatchResult($response->getRawResponse());
        }

        [$payment] = $response->result;
        $statusCode = PaymentStatusEnum::tryFrom($payment->paymentStatus);

        if (!$statusCode) {
            throw FetchTransactionUpdateException::unexpectedStatusCode($payment->paymentStatus);
        }

        $this->targetTransaction->state_code = TransactionStateCodeEnum::makeFrom($errorCode, $statusCode);
        $this->targetTransaction->save();
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        report($exception);
    }
}
