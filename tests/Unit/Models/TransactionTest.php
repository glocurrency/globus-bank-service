<?php

namespace GloCurrency\GlobusBank\Tests\Unit\Models;

use Illuminate\Support\Carbon;
use GloCurrency\GlobusBank\Tests\TestCase;
use GloCurrency\GlobusBank\Models\Transaction;
use GloCurrency\GlobusBank\Enums\TransactionStateCodeEnum;
use BrokeYourBike\HasSourceModel\SourceModelInterface;
use BrokeYourBike\GlobusBank\Enums\PaymentTypeEnum;
use BrokeYourBike\GlobusBank\Enums\PaymentStatusEnum;
use BrokeYourBike\GlobusBank\Enums\PaymentMethodEnum;
use BrokeYourBike\GlobusBank\Enums\ErrorCodeEnum;
use BrokeYourBike\BaseModels\BaseUuid;

class TransactionTest extends TestCase
{
    /** @test */
    public function it_extends_base_model(): void
    {
        $parent = get_parent_class(Transaction::class);

        $this->assertSame(BaseUuid::class, $parent);
    }

    /** @test */
    public function it_implemets_source_model_interface(): void
    {
        $this->assertInstanceOf(SourceModelInterface::class, new Transaction());
    }

    /** @test */
    public function it_returns_proper_values_for_transaction_interface()
    {
        Carbon::setTestNow(now());

        $transaction = new Transaction();
        $transaction->reference = 'ref001';
        $transaction->payment_type = PaymentTypeEnum::OTHER_PAYMENT;
        $transaction->payment_method = PaymentMethodEnum::INSTANT;
        $transaction->recipient_bank_account = '123456789';
        $transaction->recipient_bank_code = '456';
        $transaction->recipient_name = 'John Doe';
        $transaction->amount = 10;
        $transaction->created_at = now()->subDay();

        $this->assertSame('ref001', $transaction->getReference());
        $this->assertSame(PaymentTypeEnum::OTHER_PAYMENT, $transaction->getPaymentType());
        $this->assertSame(PaymentMethodEnum::INSTANT, $transaction->getPaymentMethod());
        $this->assertSame('123456789', $transaction->getBeneficiaryAccount());
        $this->assertSame('456', $transaction->getBeneficiaryBankCode());
        $this->assertSame('John Doe', $transaction->getBeneficiaryName());
        $this->assertSame(10.0, $transaction->getAmount());
        $this->assertEquals(now()->subDay(), $transaction->getValueDate());
    }

    /** @test */
    public function it_will_use_current_time_if_created_at_is_null()
    {
        Carbon::setTestNow(now());

        $transaction = new Transaction();
        $this->assertNull($transaction->created_at);

        $this->assertEquals(now(), $transaction->getValueDate());
    }

    /** @test */
    public function it_returns_amount_as_float(): void
    {
        $transaction = new Transaction();
        $transaction->amount = '10';

        $this->assertIsFloat($transaction->amount);
    }

    /** @test */
    public function it_returns_state_code_as_enum(): void
    {
        $transaction = new Transaction();
        $transaction->setRawAttributes([
            'state_code' => TransactionStateCodeEnum::PAID->value,
        ]);

        $this->assertSame(TransactionStateCodeEnum::PAID, $transaction->state_code);
    }

    /** @test */
    public function it_returns_error_code_as_enum(): void
    {
        $transaction = new Transaction();
        $transaction->setRawAttributes([
            'error_code' => ErrorCodeEnum::SUCCESS->value,
        ]);

        $this->assertSame(ErrorCodeEnum::SUCCESS, $transaction->error_code);
    }

    /** @test */
    public function it_returns_status_code_as_enum(): void
    {
        $transaction = new Transaction();
        $transaction->setRawAttributes([
            'status_code' => PaymentStatusEnum::SUCCESSFUL->value,
        ]);

        $this->assertSame(PaymentStatusEnum::SUCCESSFUL, $transaction->status_code);
    }

    /** @test */
    public function it_returns_payment_type_as_enum(): void
    {
        $transaction = new Transaction();
        $transaction->setRawAttributes([
            'payment_type' => PaymentTypeEnum::OTHER_PAYMENT->value,
        ]);

        $this->assertSame(PaymentTypeEnum::OTHER_PAYMENT, $transaction->payment_type);
    }

    /** @test */
    public function it_returns_payment_method_as_enum(): void
    {
        $transaction = new Transaction();
        $transaction->setRawAttributes([
            'payment_method' => PaymentMethodEnum::INSTANT->value,
        ]);

        $this->assertSame(PaymentMethodEnum::INSTANT, $transaction->payment_method);
    }
}
