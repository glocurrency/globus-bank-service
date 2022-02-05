<?php

namespace GloCurrency\GlobusBank\Models;

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

class Transaction extends BaseUuid implements ModelWithStateCodeInterface, SourceModelInterface, TransactionInterface
{
    use HasFactory;

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
        return $this->created_at;
    }
}