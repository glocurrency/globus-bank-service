<?php

namespace GloCurrency\GlobusBank\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use GloCurrency\MiddlewareBlocks\Enums\TransactionTypeEnum as MTransactionTypeEnum;
use GloCurrency\MiddlewareBlocks\Enums\TransactionStateCodeEnum as MTransactionStateCodeEnum;
use GloCurrency\GlobusBank\Tests\Fixtures\TransactionFixture;

class TransactionFixtureFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TransactionFixture::class;

    /**
     * Define the model's default state.
     *
     * @return array<string,mixed>
     */
    public function definition()
    {
        return [
            'id' => $this->faker->uuid(),
            'type' => MTransactionTypeEnum::BANK,
            'state_code' => MTransactionStateCodeEnum::INITIAL,
        ];
    }
}
