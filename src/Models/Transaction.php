<?php

namespace GloCurrency\GlobusBank\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GloCurrency\MiddlewareBlocks\Contracts\ModelWithStateCodeInterface;
use GloCurrency\GlobusBank\Events\TransactionUpdatedEvent;
use GloCurrency\GlobusBank\Events\TransactionCreatedEvent;
use GloCurrency\GlobusBank\Enums\TransactionStateCodeEnum;
use BrokeYourBike\HasSourceModel\SourceModelInterface;
use BrokeYourBike\GlobusBank\Interfaces\TransactionInterface;
use BrokeYourBike\GlobusBank\Enums\PaymentTypeEnum;
use BrokeYourBike\GlobusBank\Enums\PaymentStatusEnum;
use BrokeYourBike\GlobusBank\Enums\PaymentMethodEnum;
use BrokeYourBike\GlobusBank\Enums\ErrorCodeEnum;
use BrokeYourBike\BaseModels\BaseUuid;

/**
 * GloCurrency\GlobusBank\Models\Transaction
 *
 * @property string $id
 * @property string $transaction_id
 * @property string $processing_item_id
 * @property \GloCurrency\GlobusBank\Enums\TransactionStateCodeEnum $state_code
 * @property string|null $state_code_reason
 * @property \BrokeYourBike\GlobusBank\Enums\ErrorCodeEnum|null $error_code
 * @property string|null $error_code_description
 * @property \BrokeYourBike\GlobusBank\Enums\PaymentStatusEnum|null $status_code
 * @property string|null $status_code_description
 * @property string $reference
 * @property string|null $batch_reference
 * @property \BrokeYourBike\GlobusBank\Enums\PaymentTypeEnum $payment_type
 * @property \BrokeYourBike\GlobusBank\Enums\PaymentMethodEnum $payment_method
 * @property string $recipient_bank_account
 * @property string $recipient_bank_code
 * @property string $recipient_name
 * @property float $amount
 * @property string $currency_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Transaction extends BaseUuid implements ModelWithStateCodeInterface, SourceModelInterface, TransactionInterface
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'globus_transactions';

    /**
     * @var array<mixed>
     */
    protected $casts = [
        'state_code' => TransactionStateCodeEnum::class,
        'error_code' => ErrorCodeEnum::class,
        'status_code' => PaymentStatusEnum::class,
        'payment_type' => PaymentTypeEnum::class,
        'payment_method' => PaymentMethodEnum::class,
        'amount' => 'double',
    ];

    /**
     * @var array<mixed>
     */
    protected $dispatchesEvents = [
        'created' => TransactionCreatedEvent::class,
        'updated' => TransactionUpdatedEvent::class,
    ];

    public function getStateCode(): TransactionStateCodeEnum
    {
        return $this->state_code;
    }

    public function getStateCodeReason(): ?string
    {
        return $this->state_code_reason;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getPaymentType(): PaymentTypeEnum
    {
        return $this->payment_type;
    }

    public function getPaymentMethod(): PaymentMethodEnum
    {
        return $this->payment_method;
    }

    public function getBeneficiaryAccount(): string
    {
        return $this->recipient_bank_account;
    }

    public function getBeneficiaryBankCode(): string
    {
        return $this->recipient_bank_code;
    }

    public function getBeneficiaryName(): string
    {
        return $this->recipient_name;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getValueDate(): \DateTimeInterface
    {
        return $this->created_at ?? now();
    }
}