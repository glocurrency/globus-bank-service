<?php

namespace GloCurrency\GlobusBank\Tests\Feature\Jobs;

use Money\Money;
use Money\Currency;
use Illuminate\Support\Facades\Event;
use GloCurrency\MiddlewareBlocks\Enums\TransactionTypeEnum as MTransactionTypeEnum;
use GloCurrency\MiddlewareBlocks\Enums\TransactionStateCodeEnum as MTransactionStateCodeEnum;
use GloCurrency\MiddlewareBlocks\Contracts\TransactionInterface as MTransactionInterface;
use GloCurrency\MiddlewareBlocks\Contracts\SenderInterface as MSenderInterface;
use GloCurrency\MiddlewareBlocks\Contracts\RecipientInterface as MRecipientInterface;
use GloCurrency\MiddlewareBlocks\Contracts\ProcessingItemInterface as MProcessingItemInterface;
use GloCurrency\GlobusBank\Tests\FeatureTestCase;
use GloCurrency\GlobusBank\Models\Transaction;
use GloCurrency\GlobusBank\Jobs\CreateTransactionJob;
use GloCurrency\GlobusBank\Exceptions\CreateTransactionException;
use GloCurrency\GlobusBank\Events\TransactionCreatedEvent;
use GloCurrency\GlobusBank\Enums\TransactionStateCodeEnum;
use BrokeYourBike\GlobusBank\Enums\PaymentTypeEnum;
use BrokeYourBike\GlobusBank\Enums\PaymentMethodEnum;

class CreateTransactionJobTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([
            TransactionCreatedEvent::class,
        ]);
    }

    /** @test */
    public function it_will_throw_without_transaction(): void
    {
        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn(null);

        $this->expectExceptionMessage('transaction not found');
        $this->expectException(CreateTransactionException::class);

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_if_target_transaction_already_exist(): void
    {
        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        /** @var MTransactionInterface $transaction */
        $targetTransaction = Transaction::factory()->create([
            'transaction_id' => $transaction->getId(),
        ]);

        $this->expectExceptionMessage("Transaction cannot be created twice, `{$targetTransaction->id}`");
        $this->expectException(CreateTransactionException::class);

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_transaction_sender(): void
    {
        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn(null);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        $this->expectExceptionMessage('sender not found');
        $this->expectException(CreateTransactionException::class);

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_transaction_recipient(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn(null);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        $this->expectExceptionMessage('recipient not found');
        $this->expectException(CreateTransactionException::class);

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_bank_code_in_transaction_recipient(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();
        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getId')->willReturn($this->faker()->uuid());
        $recipient->method('getBankCode')->willReturn(null);

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn($recipient);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        /** @var MRecipientInterface $recipient */
        $this->expectExceptionMessage("`{$recipient->getId()}` has no `bank_code`");
        $this->expectException(CreateTransactionException::class);

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_bank_account_in_transaction_recipient(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();
        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getId')->willReturn($this->faker()->uuid());
        $recipient->method('getBankCode')->willReturn($this->faker()->word());
        $recipient->method('getBankAccount')->willReturn(null);

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn($recipient);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        /** @var MRecipientInterface $recipient */

        $this->expectExceptionMessage("`{$recipient->getId()}` has no `bank_account`");
        $this->expectException(CreateTransactionException::class);

        CreateTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_can_create_transaction(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();
        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getBankCode')->willReturn($this->faker->unique()->word());
        $recipient->method('getBankAccount')->willReturn($this->faker->numerify('##########'));

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::BANK);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn($recipient);
        $transaction->method('getOutputAmount')->willReturn(new Money('200', new Currency('NGN')));

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        $this->assertNull(Transaction::first());

        /**
         * @var MRecipientInterface $recipient
         * @var MTransactionInterface $transaction
         * @var MProcessingItemInterface $processingItem
         */
        CreateTransactionJob::dispatchSync($processingItem);

        $this->assertNotNull($targetTransaction = Transaction::first());
        $this->assertSame($transaction->getId(), $targetTransaction->transaction_id);
        $this->assertSame($processingItem->getId(), $targetTransaction->processing_item_id);
        $this->assertSame($transaction->getReferenceForHumans(), $targetTransaction->reference);
        $this->assertSame(TransactionStateCodeEnum::LOCAL_UNPROCESSED, $targetTransaction->state_code);
        $this->assertSame($transaction->getOutputAmount()->getCurrency()->getCode(), $targetTransaction->currency_code);
        $this->assertSame(PaymentTypeEnum::OTHER_PAYMENT, $targetTransaction->payment_type);
        $this->assertSame(PaymentMethodEnum::INSTANT, $targetTransaction->payment_method);
        $this->assertSame($recipient->getBankAccount(), $targetTransaction->recipient_bank_account);
        $this->assertSame($recipient->getBankCode(), $targetTransaction->recipient_bank_code);
        $this->assertSame($recipient->getName(), $targetTransaction->recipient_name);
        $this->assertSame(2.0, $targetTransaction->amount);

    }
}
