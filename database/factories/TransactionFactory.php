<?php

namespace Database\Factories\GlobusBank;

use Illuminate\Database\Eloquent\Factories\Factory;
use GloCurrency\GlobusBank\GlobusBank;
use GloCurrency\GlobusBank\Enums\TransactionStateCodeEnum;
use BrokeYourBike\GlobusBank\Enums\PaymentTypeEnum;
use BrokeYourBike\GlobusBank\Enums\PaymentMethodEnum;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string,mixed>
     */
    public function definition()
    {
        $transactionModel = GlobusBank::$transactionModel;
        $processingItemModel = GlobusBank::$processingItemModel;

        return [
            'id' => $this->faker->uuid(),
            'transaction_id' => $transactionModel::factory(),
            'processing_item_id' => $processingItemModel::factory(),
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
            'reference' => $this->faker->uuid(),
            'payment_type' => PaymentTypeEnum::OTHER_PAYMENT,
            'payment_method' => PaymentMethodEnum::INSTANT,
            'recipient_bank_account' => $this->faker->numerify('#########'),
            'recipient_bank_code' => $this->faker->numerify('###'),
            'recipient_name' => $this->faker->name(),
            'amount' => $this->faker->randomFloat(2, 1),
            'currency_code' => $this->faker->currencyCode(),
        ];
    }
}
